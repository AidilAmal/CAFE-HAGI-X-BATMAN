from __future__ import annotations

from datetime import date, timedelta
from pathlib import Path
from typing import Any

import joblib
import pandas as pd

from app.config import MODEL_PATH
from app.schemas import (
    DailyForecastItem,
    DemandPredictionRequest,
    DemandPredictionResponse,
    StockOutPredictionRequest,
    StockOutPredictionResponse,
)

FEATURE_COLUMNS = [
    "menu_id",
    "menu_name",
    "menu_category",
    "is_coffee_based",
    "menu_segment",
    "unit_price",
    "day_of_week_num",
    "is_weekend",
]


def build_feature_row(
    *,
    menu_id: int,
    menu_name: str,
    menu_category: str,
    is_coffee_based: bool,
    menu_segment: str,
    unit_price: int,
    target_date: date,
) -> dict[str, Any]:
    day_of_week_num = target_date.weekday()  # Monday=0, Sunday=6
    return {
        "menu_id": menu_id,
        "menu_name": menu_name,
        "menu_category": menu_category,
        "is_coffee_based": int(is_coffee_based),
        "menu_segment": menu_segment,
        "unit_price": unit_price,
        "day_of_week_num": day_of_week_num,
        "is_weekend": int(day_of_week_num >= 5),
    }


class DemandPredictor:
    def __init__(self, model_path: Path = MODEL_PATH) -> None:
        self.model_path = model_path
        self.bundle: dict[str, Any] | None = None

    @property
    def is_loaded(self) -> bool:
        return self.bundle is not None

    def load(self) -> None:
        if not self.model_path.exists():
            raise FileNotFoundError(
                f"Model belum ada di {self.model_path}. Jalankan dulu: python train_demand_model.py"
            )
        self.bundle = joblib.load(self.model_path)

    def _model(self):
        if self.bundle is None:
            self.load()
        assert self.bundle is not None
        return self.bundle["model"]

    def predict_daily_demand(self, request: DemandPredictionRequest) -> DemandPredictionResponse:
        feature_row = build_feature_row(
            menu_id=request.menu_id,
            menu_name=request.menu_name,
            menu_category=request.menu_category,
            is_coffee_based=request.is_coffee_based,
            menu_segment=request.menu_segment,
            unit_price=request.unit_price,
            target_date=request.target_date,
        )
        features = pd.DataFrame([feature_row], columns=FEATURE_COLUMNS)
        prediction = float(self._model().predict(features)[0])
        prediction = max(0.0, round(prediction, 2))

        return DemandPredictionResponse(
            menu_id=request.menu_id,
            menu_name=request.menu_name,
            target_date=request.target_date,
            day_of_week_num=feature_row["day_of_week_num"],
            is_weekend=bool(feature_row["is_weekend"]),
            predicted_daily_qty=prediction,
        )

    def predict_daily_qty(
        self,
        *,
        menu_id: int,
        menu_name: str,
        menu_category: str,
        is_coffee_based: bool,
        menu_segment: str,
        unit_price: int,
        target_date: date,
    ) -> float:
        request = DemandPredictionRequest(
            menu_id=menu_id,
            menu_name=menu_name,
            menu_category=menu_category,
            is_coffee_based=is_coffee_based,
            menu_segment=menu_segment,
            unit_price=unit_price,
            target_date=target_date,
        )
        return self.predict_daily_demand(request).predicted_daily_qty

    def predict_stock_out(self, request: StockOutPredictionRequest) -> StockOutPredictionResponse:
        remaining_stock = float(request.current_stock)
        stock_out_date: date | None = None
        days_until_stock_out: int | None = None
        daily_forecast: list[DailyForecastItem] = []

        for day_offset in range(request.forecast_days):
            current_date = request.start_date + timedelta(days=day_offset)
            predicted_qty = self.predict_daily_qty(
                menu_id=request.menu_id,
                menu_name=request.menu_name,
                menu_category=request.menu_category,
                is_coffee_based=request.is_coffee_based,
                menu_segment=request.menu_segment,
                unit_price=request.unit_price,
                target_date=current_date,
            )
            remaining_stock = round(remaining_stock - predicted_qty, 2)
            daily_forecast.append(
                DailyForecastItem(
                    date=current_date,
                    predicted_qty=predicted_qty,
                    remaining_stock=max(0.0, remaining_stock),
                )
            )

            if remaining_stock <= 0 and stock_out_date is None:
                stock_out_date = current_date
                days_until_stock_out = day_offset + 1
                break

        if stock_out_date:
            note = "Stok diprediksi habis dalam window forecast. Disarankan restock sebelum tanggal tersebut."
        else:
            note = "Stok belum habis dalam window forecast. Pantau ulang jika pola penjualan berubah."

        return StockOutPredictionResponse(
            menu_id=request.menu_id,
            menu_name=request.menu_name,
            current_stock=request.current_stock,
            predicted_stock_out_date=stock_out_date,
            days_until_stock_out=days_until_stock_out,
            remaining_stock_after_forecast=max(0.0, round(remaining_stock, 2)),
            daily_forecast=daily_forecast,
            note=note,
        )
