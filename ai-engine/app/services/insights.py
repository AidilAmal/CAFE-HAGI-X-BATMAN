from __future__ import annotations

import json
from datetime import date
from pathlib import Path
from typing import Any

import pandas as pd
from sklearn.cluster import KMeans
from sklearn.preprocessing import StandardScaler

from app.config import MODEL_META_PATH, SALES_HISTORY_CSV
from app.services.predictor import FEATURE_COLUMNS, build_feature_row


def _safe_float(value: Any, default: float = 0.0) -> float:
    try:
        if pd.isna(value):
            return default
        return float(value)
    except Exception:
        return default


def _safe_int(value: Any, default: int = 0) -> int:
    try:
        if pd.isna(value):
            return default
        return int(round(float(value)))
    except Exception:
        return default


def _round(value: Any, digits: int = 2) -> float:
    return round(_safe_float(value), digits)


class CafeHagiInsightEngine:
    """Analytical layer for Cafe Hagi AI dashboard.

    This service intentionally keeps the heavy ML model for demand forecasting
    separate from business-facing insights. Random Forest handles forecasting,
    K-Means handles menu clustering, and this layer turns both into dashboard
    recommendations that Laravel can render.
    """

    def __init__(self, csv_path: Path = SALES_HISTORY_CSV, meta_path: Path = MODEL_META_PATH) -> None:
        self.csv_path = csv_path
        self.meta_path = meta_path

    def load_sales_history(self) -> pd.DataFrame:
        if not self.csv_path.exists():
            raise FileNotFoundError(
                f"sales_history_ml.csv tidak ditemukan di {self.csv_path}. "
                "Jalankan synthetic data generator dulu."
            )

        df = pd.read_csv(self.csv_path)
        required_columns = {
            "id",
            "order_date",
            "hour",
            "time_block",
            "menu_id",
            "menu_name",
            "menu_category",
            "is_coffee_based",
            "menu_segment",
            "qty",
            "unit_price",
            "subtotal",
            "status",
        }
        missing = sorted(required_columns - set(df.columns))
        if missing:
            raise ValueError(f"Kolom wajib tidak ada di sales_history_ml.csv: {missing}")

        df = df[df["status"].eq("completed")].copy()
        df["order_date"] = pd.to_datetime(df["order_date"])
        df["is_coffee_based"] = df["is_coffee_based"].astype(int)
        return df

    def load_model_metrics(self) -> dict[str, Any]:
        if not self.meta_path.exists():
            return {
                "model_ready": False,
                "message": "Metrics file belum ada. Jalankan python train_demand_model.py.",
            }

        try:
            with self.meta_path.open("r", encoding="utf-8") as file:
                metrics = json.load(file)
            metrics["model_ready"] = True
            return metrics
        except Exception as exc:
            return {
                "model_ready": False,
                "message": f"Metrics file tidak bisa dibaca: {exc}",
            }

    def _build_daily_menu_grid(self, df: pd.DataFrame) -> pd.DataFrame:
        menu_catalog = df[
            ["menu_id", "menu_name", "menu_category", "is_coffee_based", "menu_segment", "unit_price"]
        ].drop_duplicates("menu_id")

        date_frame = pd.DataFrame(
            {"order_date": pd.date_range(df["order_date"].min(), df["order_date"].max(), freq="D")}
        )

        base_grid = (
            date_frame.assign(_key=1)
            .merge(menu_catalog.assign(_key=1), on="_key")
            .drop(columns="_key")
        )

        daily_sales = (
            df.groupby(["order_date", "menu_id"], as_index=False)
            .agg(qty=("qty", "sum"), revenue=("subtotal", "sum"), order_count=("id", "count"))
        )

        full_daily = base_grid.merge(daily_sales, on=["order_date", "menu_id"], how="left")
        full_daily[["qty", "revenue", "order_count"]] = full_daily[["qty", "revenue", "order_count"]].fillna(0)
        full_daily["is_weekend"] = (full_daily["order_date"].dt.weekday >= 5).astype(int)
        return full_daily

    def menu_performance(self) -> list[dict[str, Any]]:
        df = self.load_sales_history()
        full_daily = self._build_daily_menu_grid(df)
        total_days = max(1, full_daily["order_date"].nunique())

        base_stats = (
            full_daily.groupby(
                ["menu_id", "menu_name", "menu_category", "is_coffee_based", "menu_segment", "unit_price"],
                as_index=False,
            )
            .agg(
                total_qty=("qty", "sum"),
                total_revenue=("revenue", "sum"),
                avg_daily_qty=("qty", "mean"),
                active_days=("qty", lambda series: int((series > 0).sum())),
                weekend_qty=("qty", lambda series: float(series[full_daily.loc[series.index, "is_weekend"].eq(1)].sum())),
                weekday_qty=("qty", lambda series: float(series[full_daily.loc[series.index, "is_weekend"].eq(0)].sum())),
            )
        )

        rush_sales = (
            df[df["time_block"].eq("lunch_coffee_rush")]
            .groupby("menu_id", as_index=False)
            .agg(coffee_rush_qty=("qty", "sum"), coffee_rush_orders=("id", "count"))
        )

        stats = base_stats.merge(rush_sales, on="menu_id", how="left")
        stats[["coffee_rush_qty", "coffee_rush_orders"]] = stats[["coffee_rush_qty", "coffee_rush_orders"]].fillna(0)
        stats["active_day_rate"] = stats["active_days"] / total_days
        stats["coffee_rush_share"] = (stats["coffee_rush_qty"] / stats["total_qty"].replace(0, pd.NA)).fillna(0)
        stats["weekend_uplift"] = (
            (stats["weekend_qty"] / 2) / (stats["weekday_qty"].replace(0, pd.NA) / 5)
        ).replace([float("inf"), -float("inf")], 0).fillna(0)

        stats = stats.sort_values(["total_qty", "total_revenue"], ascending=[False, False])

        return [self._menu_stat_to_dict(row) for _, row in stats.iterrows()]

    def _menu_stat_to_dict(self, row: pd.Series) -> dict[str, Any]:
        return {
            "menu_id": _safe_int(row["menu_id"]),
            "menu_name": str(row["menu_name"]),
            "menu_category": str(row["menu_category"]),
            "is_coffee_based": bool(_safe_int(row["is_coffee_based"])),
            "menu_segment": str(row["menu_segment"]),
            "unit_price": _safe_int(row["unit_price"]),
            "total_qty": _safe_int(row["total_qty"]),
            "total_revenue": _safe_int(row["total_revenue"]),
            "avg_daily_qty": _round(row["avg_daily_qty"]),
            "active_days": _safe_int(row["active_days"]),
            "active_day_rate": _round(row["active_day_rate"] * 100),
            "coffee_rush_share": _round(row["coffee_rush_share"] * 100),
            "weekend_uplift": _round(row["weekend_uplift"]),
        }

    def kmeans_menu_clustering(self) -> dict[str, Any]:
        performance = self.menu_performance()
        if len(performance) < 2:
            return {
                "algorithm": "K-Means",
                "cluster_count": 0,
                "items": performance,
                "summary": [],
                "note": "Data menu belum cukup untuk clustering.",
            }

        features = pd.DataFrame(performance)
        feature_columns = [
            "total_qty",
            "total_revenue",
            "avg_daily_qty",
            "active_days",
            "active_day_rate",
            "coffee_rush_share",
            "weekend_uplift",
            "unit_price",
        ]

        n_clusters = min(4, len(features))
        scaled = StandardScaler().fit_transform(features[feature_columns])
        labels = KMeans(n_clusters=n_clusters, random_state=42, n_init=20).fit_predict(scaled)
        features["cluster_id"] = labels

        cluster_scores = (
            features.groupby("cluster_id")
            .agg(
                avg_qty=("avg_daily_qty", "mean"),
                total_revenue=("total_revenue", "sum"),
                active_rate=("active_day_rate", "mean"),
                menu_count=("menu_id", "count"),
            )
            .reset_index()
        )
        cluster_scores["score"] = (
            cluster_scores["avg_qty"].rank(pct=True)
            + cluster_scores["total_revenue"].rank(pct=True)
            + cluster_scores["active_rate"].rank(pct=True)
        )
        sorted_clusters = cluster_scores.sort_values("score", ascending=False)["cluster_id"].tolist()

        names = [
            "Star Performer",
            "Reliable Core",
            "Niche / Premium",
            "Dead Stock Risk",
        ]
        descriptions = {
            "Star Performer": "Menu dengan demand dan revenue paling kuat. Prioritaskan stok dan availability.",
            "Reliable Core": "Menu stabil untuk operasional harian. Cocok untuk bundling ringan.",
            "Niche / Premium": "Menu bernilai spesifik atau premium. Dorong pada jam/menu pairing yang tepat.",
            "Dead Stock Risk": "Menu bergerak lambat. Perlu promo, bundling, atau evaluasi resep/harga.",
        }
        cluster_name_map = {
            cluster_id: names[min(index, len(names) - 1)]
            for index, cluster_id in enumerate(sorted_clusters)
        }

        items: list[dict[str, Any]] = []
        for _, row in features.sort_values(["cluster_id", "total_qty"], ascending=[True, False]).iterrows():
            cluster_name = cluster_name_map[int(row["cluster_id"])]
            item = self._menu_stat_to_dict(row)
            item.update(
                {
                    "cluster_id": _safe_int(row["cluster_id"]),
                    "cluster_name": cluster_name,
                    "cluster_description": descriptions[cluster_name],
                }
            )
            items.append(item)

        summary = []
        for _, row in cluster_scores.iterrows():
            cluster_name = cluster_name_map[int(row["cluster_id"])]
            summary.append(
                {
                    "cluster_id": _safe_int(row["cluster_id"]),
                    "cluster_name": cluster_name,
                    "menu_count": _safe_int(row["menu_count"]),
                    "avg_daily_qty": _round(row["avg_qty"]),
                    "total_revenue": _safe_int(row["total_revenue"]),
                    "description": descriptions[cluster_name],
                }
            )

        return {
            "algorithm": "K-Means",
            "cluster_count": n_clusters,
            "items": items,
            "summary": sorted(summary, key=lambda item: names.index(item["cluster_name"])),
            "note": "Cluster dibuat dari total qty, revenue, active days, coffee rush share, weekend uplift, dan price.",
        }

    def smart_promo_recommendations(self, limit: int = 8) -> dict[str, Any]:
        clusters = self.kmeans_menu_clustering()
        items = clusters.get("items", [])
        recommendations: list[dict[str, Any]] = []

        for item in items:
            cluster_name = item.get("cluster_name", "")
            avg_daily_qty = _safe_float(item.get("avg_daily_qty"))
            active_rate = _safe_float(item.get("active_day_rate"))
            coffee_share = _safe_float(item.get("coffee_rush_share"))
            weekend_uplift = _safe_float(item.get("weekend_uplift"))

            if cluster_name == "Dead Stock Risk" or item.get("menu_segment") == "dead_stock" or avg_daily_qty < 1:
                discount = 20 if active_rate < 15 else 15
                promo_type = "dead_stock_recovery"
                priority = "High"
                reason = "Demand rendah dan active day rate kecil. Promo dipakai untuk menguji ulang minat pasar."
            elif cluster_name == "Niche / Premium":
                discount = 12
                promo_type = "niche_boost"
                priority = "Medium"
                reason = "Menu punya performa spesifik. Cocok didorong dengan pairing atau limited-time offer."
            elif coffee_share >= 50 and item.get("is_coffee_based"):
                discount = 10
                promo_type = "peak_hour_bundle"
                priority = "Medium"
                reason = "Menu kuat pada lunch coffee rush. Promo tipis cukup untuk menaikkan basket size."
            elif weekend_uplift >= 1.5:
                discount = 10
                promo_type = "weekend_bundle"
                priority = "Low"
                reason = "Demand weekend lebih kuat. Bundling bisa menaikkan revenue tanpa diskon agresif."
            else:
                continue

            if item.get("is_coffee_based") and coffee_share >= 40:
                suggested_window = "12:00-15:00"
            elif weekend_uplift >= 1.5:
                suggested_window = "Weekend"
            else:
                suggested_window = "Weekday afternoon"

            recommendations.append(
                {
                    "menu_id": item["menu_id"],
                    "menu_name": item["menu_name"],
                    "promo_type": promo_type,
                    "recommended_discount_percent": discount,
                    "priority": priority,
                    "suggested_window": suggested_window,
                    "reason": reason,
                    "action": self._promo_action(item["menu_name"], discount, suggested_window, promo_type),
                    "cluster_name": cluster_name,
                    "avg_daily_qty": item["avg_daily_qty"],
                    "active_day_rate": item["active_day_rate"],
                }
            )

        priority_order = {"High": 0, "Medium": 1, "Low": 2}
        recommendations.sort(key=lambda rec: (priority_order.get(rec["priority"], 9), rec["avg_daily_qty"]))
        return {
            "method": "Random Forest forecast context + K-Means performance cluster + business recommendation layer",
            "items": recommendations[:limit],
            "total_recommendations": len(recommendations[:limit]),
        }

    def _promo_action(self, menu_name: str, discount: int, window: str, promo_type: str) -> str:
        if promo_type == "dead_stock_recovery":
            return f"Buat promo {discount}% untuk {menu_name} pada {window}, lalu evaluasi conversion 7 hari."
        if promo_type == "peak_hour_bundle":
            return f"Bundle {menu_name} dengan pastry/snack saat {window}; diskon maksimal {discount}% agar margin tetap sehat."
        if promo_type == "weekend_bundle":
            return f"Jalankan weekend bundle {menu_name} + menu pendamping dengan diskon {discount}%."
        return f"Uji limited offer {menu_name} diskon {discount}% pada {window}."

    def peak_hour_analysis(self) -> dict[str, Any]:
        df = self.load_sales_history()

        hourly = (
            df.groupby("hour", as_index=False)
            .agg(total_qty=("qty", "sum"), total_revenue=("subtotal", "sum"), orders=("id", "count"))
            .sort_values("total_qty", ascending=False)
        )
        hourly["hour_label"] = hourly["hour"].apply(lambda hour: f"{int(hour):02d}:00")

        time_blocks = (
            df.groupby("time_block", as_index=False)
            .agg(total_qty=("qty", "sum"), coffee_qty=("qty", lambda s: float(s[df.loc[s.index, "is_coffee_based"].eq(1)].sum())), total_revenue=("subtotal", "sum"), orders=("id", "count"))
            .sort_values("total_qty", ascending=False)
        )
        time_blocks["coffee_share"] = (time_blocks["coffee_qty"] / time_blocks["total_qty"].replace(0, pd.NA)).fillna(0) * 100

        top_block = time_blocks.iloc[0].to_dict() if not time_blocks.empty else {}
        top_hour = hourly.iloc[0].to_dict() if not hourly.empty else {}

        coffee_rush = time_blocks[time_blocks["time_block"].eq("lunch_coffee_rush")]
        coffee_share = _round(coffee_rush.iloc[0]["coffee_share"]) if not coffee_rush.empty else 0

        insights = []
        if top_block:
            insights.append(
                f"Time block paling kuat adalah {top_block['time_block']} dengan {_safe_int(top_block['total_qty'])} qty."
            )
        if top_hour:
            insights.append(
                f"Jam tersibuk adalah {int(top_hour['hour']):02d}:00 dengan {_safe_int(top_hour['total_qty'])} qty."
            )
        insights.append(
            f"Coffee share pada lunch coffee rush adalah {coffee_share}%. Siapkan stok bahan kopi sebelum jam 12:00."
        )

        return {
            "top_hours": [
                {
                    "hour": _safe_int(row["hour"]),
                    "hour_label": row["hour_label"],
                    "total_qty": _safe_int(row["total_qty"]),
                    "total_revenue": _safe_int(row["total_revenue"]),
                    "orders": _safe_int(row["orders"]),
                }
                for _, row in hourly.head(8).iterrows()
            ],
            "time_blocks": [
                {
                    "time_block": str(row["time_block"]),
                    "total_qty": _safe_int(row["total_qty"]),
                    "coffee_qty": _safe_int(row["coffee_qty"]),
                    "coffee_share": _round(row["coffee_share"]),
                    "total_revenue": _safe_int(row["total_revenue"]),
                    "orders": _safe_int(row["orders"]),
                }
                for _, row in time_blocks.iterrows()
            ],
            "insights": insights,
        }

    def dashboard(self, predictor: Any, default_stock: int = 80, forecast_days: int = 30, start_date: date | None = None) -> dict[str, Any]:
        start_date = start_date or date.today()
        metrics = self.load_model_metrics()
        performance = self.menu_performance()
        clusters = self.kmeans_menu_clustering()
        promos = self.smart_promo_recommendations(limit=8)
        peak_hours = self.peak_hour_analysis()

        stock_predictions = self._batch_stock_out_predictions(
            predictor=predictor,
            menu_items=performance[:10],
            default_stock=max(1, int(default_stock)),
            forecast_days=max(1, min(int(forecast_days), 90)),
            start_date=start_date,
        )

        critical_count = sum(1 for item in stock_predictions if item.get("risk_level") == "critical")
        warning_count = sum(1 for item in stock_predictions if item.get("risk_level") == "warning")

        return {
            "service": "Cafe Hagi AI Engine",
            "generated_at": date.today().isoformat(),
            "model": {
                "ready": bool(metrics.get("model_ready")),
                "mae": metrics.get("mae"),
                "rmse": metrics.get("rmse"),
                "r2": metrics.get("r2"),
                "train_rows": metrics.get("train_rows"),
                "test_rows": metrics.get("test_rows"),
                "daily_menu_rows": metrics.get("daily_menu_rows"),
            },
            "kpis": {
                "menus_analyzed": len(performance),
                "critical_stock_count": critical_count,
                "warning_stock_count": warning_count,
                "promo_recommendation_count": promos.get("total_recommendations", 0),
                "cluster_count": clusters.get("cluster_count", 0),
            },
            "stock_predictions": stock_predictions,
            "menu_clusters": clusters,
            "smart_promos": promos,
            "peak_hours": peak_hours,
            "menu_performance": performance,
        }

    def _batch_stock_out_predictions(
        self,
        *,
        predictor: Any,
        menu_items: list[dict[str, Any]],
        default_stock: int,
        forecast_days: int,
        start_date: date,
    ) -> list[dict[str, Any]]:
        if not menu_items:
            return []

        rows: list[dict[str, Any]] = []
        meta_rows: list[tuple[int, date]] = []

        for menu_index, item in enumerate(menu_items):
            for day_offset in range(forecast_days):
                current_date = start_date + pd.Timedelta(days=day_offset).to_pytimedelta()
                rows.append(
                    build_feature_row(
                        menu_id=item["menu_id"],
                        menu_name=item["menu_name"],
                        menu_category=item["menu_category"],
                        is_coffee_based=item["is_coffee_based"],
                        menu_segment=item["menu_segment"],
                        unit_price=item["unit_price"],
                        target_date=current_date,
                    )
                )
                meta_rows.append((menu_index, current_date))

        features = pd.DataFrame(rows, columns=FEATURE_COLUMNS)
        predictions = predictor._model().predict(features)

        forecast_by_menu: dict[int, list[tuple[date, float]]] = {index: [] for index in range(len(menu_items))}
        for (menu_index, current_date), predicted_qty in zip(meta_rows, predictions):
            forecast_by_menu[menu_index].append((current_date, max(0.0, round(float(predicted_qty), 2))))

        results: list[dict[str, Any]] = []
        for menu_index, item in enumerate(menu_items):
            remaining_stock = float(default_stock)
            stock_out_date: date | None = None
            days_until_stock_out: int | None = None

            for offset, (current_date, predicted_qty) in enumerate(forecast_by_menu[menu_index], start=1):
                remaining_stock = round(remaining_stock - predicted_qty, 2)
                if remaining_stock <= 0:
                    stock_out_date = current_date
                    days_until_stock_out = offset
                    remaining_stock = 0.0
                    break

            if days_until_stock_out is None:
                risk_level = "safe"
                note = "Stok belum habis dalam window forecast. Pantau ulang jika pola penjualan berubah."
            elif days_until_stock_out <= 7:
                risk_level = "critical"
                note = "Stok berisiko habis sangat cepat. Prioritaskan restock."
            elif days_until_stock_out <= 14:
                risk_level = "warning"
                note = "Stok berisiko habis dalam dua minggu. Jadwalkan restock."
            else:
                risk_level = "watch"
                note = "Stok masih aman, tetapi tetap masuk watchlist forecast."

            results.append(
                {
                    "menu_id": item["menu_id"],
                    "menu_name": item["menu_name"],
                    "current_stock": default_stock,
                    "predicted_stock_out_date": stock_out_date.isoformat() if stock_out_date else None,
                    "days_until_stock_out": days_until_stock_out,
                    "remaining_stock_after_forecast": max(0.0, round(remaining_stock, 2)),
                    "risk_level": risk_level,
                    "note": note,
                }
            )

        return results
