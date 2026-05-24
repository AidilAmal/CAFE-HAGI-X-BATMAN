from __future__ import annotations

import json
from datetime import datetime
from pathlib import Path

import joblib
import pandas as pd
from sklearn.compose import ColumnTransformer
from sklearn.ensemble import RandomForestRegressor
from sklearn.metrics import mean_absolute_error, mean_squared_error, r2_score
from sklearn.pipeline import Pipeline
from sklearn.preprocessing import OneHotEncoder

from app.config import MODEL_META_PATH, MODEL_PATH, SALES_HISTORY_CSV
from app.services.predictor import FEATURE_COLUMNS

TARGET_COLUMN = "qty"
CATEGORICAL_COLUMNS = ["menu_name", "menu_category", "menu_segment"]
NUMERIC_COLUMNS = [
    "menu_id",
    "is_coffee_based",
    "unit_price",
    "day_of_week_num",
    "is_weekend",
]


def load_dataset(csv_path: Path) -> pd.DataFrame:
    if not csv_path.exists():
        raise FileNotFoundError(
            f"CSV tidak ditemukan: {csv_path}\n"
            "Pastikan file sales_history_ml.csv ada di tools/synthetic-data/synthetic_output/."
        )

    df = pd.read_csv(csv_path)
    required_columns = set(FEATURE_COLUMNS + ["order_date", "status", TARGET_COLUMN, "subtotal", "id"])
    missing_columns = sorted(required_columns - set(df.columns))
    if missing_columns:
        raise ValueError(f"Kolom wajib tidak ada di CSV: {missing_columns}")

    df = df[df["status"].eq("completed")].copy()
    df["order_date"] = pd.to_datetime(df["order_date"])
    return df


def build_daily_menu_grid(df: pd.DataFrame) -> pd.DataFrame:
    """
    Ubah data transaksi menjadi demand harian per menu.

    Kita sengaja membuat full date x menu grid dan mengisi hari tanpa penjualan dengan qty=0.
    Ini penting supaya model belajar bahwa dead-stock benar-benar jarang laku, bukan sekadar hilang dari data.
    """
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
    base_grid["day_of_week_num"] = base_grid["order_date"].dt.weekday
    base_grid["is_weekend"] = (base_grid["day_of_week_num"] >= 5).astype(int)

    daily_sales = (
        df.groupby(["order_date", "menu_id"], as_index=False)
        .agg(qty=("qty", "sum"), revenue=("subtotal", "sum"), order_count=("id", "count"))
    )

    full_df = base_grid.merge(daily_sales, on=["order_date", "menu_id"], how="left")
    full_df[["qty", "revenue", "order_count"]] = full_df[["qty", "revenue", "order_count"]].fillna(0)
    full_df = full_df.sort_values(["order_date", "menu_id"]).reset_index(drop=True)
    return full_df


def temporal_train_test_split(df: pd.DataFrame, test_ratio: float = 0.2):
    unique_dates = sorted(df["order_date"].unique())
    split_index = max(1, int(len(unique_dates) * (1 - test_ratio)))
    train_dates = set(unique_dates[:split_index])
    test_dates = set(unique_dates[split_index:])

    train_df = df[df["order_date"].isin(train_dates)].copy()
    test_df = df[df["order_date"].isin(test_dates)].copy()
    return train_df, test_df


def build_pipeline() -> Pipeline:
    preprocessor = ColumnTransformer(
        transformers=[
            ("categorical", OneHotEncoder(handle_unknown="ignore"), CATEGORICAL_COLUMNS),
            ("numeric", "passthrough", NUMERIC_COLUMNS),
        ]
    )

    model = RandomForestRegressor(
        n_estimators=300,
        max_depth=None,
        min_samples_leaf=1,
        random_state=42,
        n_jobs=-1,
    )

    return Pipeline(
        steps=[
            ("preprocessor", preprocessor),
            ("model", model),
        ]
    )


def train() -> None:
    raw_df = load_dataset(SALES_HISTORY_CSV)
    demand_df = build_daily_menu_grid(raw_df)

    train_df, test_df = temporal_train_test_split(demand_df)

    x_train = train_df[FEATURE_COLUMNS]
    y_train = train_df[TARGET_COLUMN]
    x_test = test_df[FEATURE_COLUMNS]
    y_test = test_df[TARGET_COLUMN]

    pipeline = build_pipeline()
    pipeline.fit(x_train, y_train)

    predictions = pipeline.predict(x_test)
    mae = mean_absolute_error(y_test, predictions)
    rmse = mean_squared_error(y_test, predictions) ** 0.5
    r2 = r2_score(y_test, predictions)

    MODEL_PATH.parent.mkdir(parents=True, exist_ok=True)
    model_bundle = {
        "model": pipeline,
        "feature_columns": FEATURE_COLUMNS,
        "target_column": TARGET_COLUMN,
        "trained_at": datetime.now().isoformat(timespec="seconds"),
        "source_csv": str(SALES_HISTORY_CSV),
        "training_granularity": "daily_menu_demand",
    }
    joblib.dump(model_bundle, MODEL_PATH)

    menu_catalog = (
        raw_df[
            [
                "menu_id",
                "menu_name",
                "menu_category",
                "is_coffee_based",
                "menu_segment",
                "unit_price",
            ]
        ]
        .drop_duplicates("menu_id")
        .sort_values("menu_id")
        .to_dict(orient="records")
    )

    metrics = {
        "model_path": str(MODEL_PATH),
        "source_csv": str(SALES_HISTORY_CSV),
        "raw_completed_rows": int(len(raw_df)),
        "daily_menu_rows": int(len(demand_df)),
        "train_rows": int(len(train_df)),
        "test_rows": int(len(test_df)),
        "mae": round(float(mae), 4),
        "rmse": round(float(rmse), 4),
        "r2": round(float(r2), 4),
        "menu_catalog": menu_catalog,
    }
    MODEL_META_PATH.write_text(json.dumps(metrics, indent=2), encoding="utf-8")

    print("\n=== Cafe Hagi Daily Demand Model Training Report ===")
    print(f"Source CSV           : {SALES_HISTORY_CSV}")
    print(f"Raw completed rows   : {len(raw_df):,}")
    print(f"Daily menu rows      : {len(demand_df):,}")
    print(f"Train rows           : {len(train_df):,}")
    print(f"Test rows            : {len(test_df):,}")
    print(f"MAE                  : {mae:.4f}")
    print(f"RMSE                 : {rmse:.4f}")
    print(f"R2                   : {r2:.4f}")
    print(f"Model saved to       : {MODEL_PATH}")
    print(f"Metrics saved to     : {MODEL_META_PATH}\n")


if __name__ == "__main__":
    train()
