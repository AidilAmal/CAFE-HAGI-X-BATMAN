from __future__ import annotations

from datetime import date
from typing import Literal

from pydantic import BaseModel, Field, field_validator


MenuSegment = Literal["bestseller", "normal", "dead_stock"]


class DemandPredictionRequest(BaseModel):
    menu_id: int = Field(..., ge=1, examples=[1])
    menu_name: str = Field(..., min_length=2, examples=["Es Kopi Susu"])
    menu_category: str = Field(..., min_length=2, examples=["Coffee"])
    is_coffee_based: bool = Field(..., examples=[True])
    menu_segment: MenuSegment = Field(..., examples=["bestseller"])
    unit_price: int = Field(..., ge=0, examples=[22000])
    target_date: date = Field(..., examples=["2026-05-25"])


class DemandPredictionResponse(BaseModel):
    menu_id: int
    menu_name: str
    target_date: date
    day_of_week_num: int
    is_weekend: bool
    predicted_daily_qty: float


class StockOutPredictionRequest(BaseModel):
    menu_id: int = Field(..., ge=1, examples=[1])
    menu_name: str = Field(..., min_length=2, examples=["Es Kopi Susu"])
    menu_category: str = Field(..., min_length=2, examples=["Coffee"])
    is_coffee_based: bool = Field(..., examples=[True])
    menu_segment: MenuSegment = Field(..., examples=["bestseller"])
    unit_price: int = Field(..., ge=0, examples=[22000])
    current_stock: int = Field(..., ge=0, examples=[80])
    start_date: date = Field(..., examples=["2026-05-25"])
    forecast_days: int = Field(30, ge=1, le=90, examples=[30])

    @field_validator("forecast_days")
    @classmethod
    def limit_forecast_window(cls, value: int) -> int:
        if value > 90:
            raise ValueError("forecast_days maksimal 90 hari agar prediksi tetap realistis.")
        return value


class DailyForecastItem(BaseModel):
    date: date
    predicted_qty: float
    remaining_stock: float


class StockOutPredictionResponse(BaseModel):
    menu_id: int
    menu_name: str
    current_stock: int
    predicted_stock_out_date: date | None
    days_until_stock_out: int | None
    remaining_stock_after_forecast: float
    daily_forecast: list[DailyForecastItem]
    note: str


class HealthResponse(BaseModel):
    status: str
    model_loaded: bool
    model_path: str
