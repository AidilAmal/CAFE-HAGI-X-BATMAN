from __future__ import annotations

from datetime import date
from typing import Any

from fastapi import FastAPI, HTTPException, Query

from app.config import MODEL_PATH
from app.schemas import (
    DemandPredictionRequest,
    DemandPredictionResponse,
    HealthResponse,
    StockOutPredictionRequest,
    StockOutPredictionResponse,
)
from app.services.insights import CafeHagiInsightEngine
from app.services.predictor import DemandPredictor

app = FastAPI(
    title="Cafe Hagi AI Engine",
    description="Microservice FastAPI untuk demand forecasting, stock-out prediction, smart promo, K-Means clustering, dan peak-hour insights Cafe Hagi.",
    version="0.2.0",
)

predictor = DemandPredictor()
insights = CafeHagiInsightEngine()


@app.on_event("startup")
def load_model_on_startup() -> None:
    try:
        predictor.load()
    except FileNotFoundError:
        # API tetap hidup agar /health bisa memberi pesan jelas.
        pass


@app.get("/health", response_model=HealthResponse)
def health() -> HealthResponse:
    return HealthResponse(
        status="ok" if predictor.is_loaded else "model_not_loaded",
        model_loaded=predictor.is_loaded,
        model_path=str(MODEL_PATH),
    )


@app.post("/predict/demand", response_model=DemandPredictionResponse)
def predict_demand(request: DemandPredictionRequest) -> DemandPredictionResponse:
    try:
        return predictor.predict_daily_demand(request)
    except FileNotFoundError as exc:
        raise HTTPException(status_code=503, detail=str(exc)) from exc


@app.post("/predict/stock-out", response_model=StockOutPredictionResponse)
def predict_stock_out(request: StockOutPredictionRequest) -> StockOutPredictionResponse:
    try:
        return predictor.predict_stock_out(request)
    except FileNotFoundError as exc:
        raise HTTPException(status_code=503, detail=str(exc)) from exc


@app.get("/insights/menu-clusters")
def menu_clusters() -> dict[str, Any]:
    try:
        return insights.kmeans_menu_clustering()
    except Exception as exc:
        raise HTTPException(status_code=500, detail=str(exc)) from exc


@app.get("/insights/smart-promos")
def smart_promos(limit: int = Query(8, ge=1, le=30)) -> dict[str, Any]:
    try:
        return insights.smart_promo_recommendations(limit=limit)
    except Exception as exc:
        raise HTTPException(status_code=500, detail=str(exc)) from exc


@app.get("/insights/peak-hours")
def peak_hours() -> dict[str, Any]:
    try:
        return insights.peak_hour_analysis()
    except Exception as exc:
        raise HTTPException(status_code=500, detail=str(exc)) from exc


@app.get("/insights/dashboard")
def ai_dashboard(
    default_stock: int = Query(80, ge=1, le=100000),
    forecast_days: int = Query(30, ge=1, le=90),
    start_date: date | None = None,
) -> dict[str, Any]:
    try:
        return insights.dashboard(
            predictor=predictor,
            default_stock=default_stock,
            forecast_days=forecast_days,
            start_date=start_date,
        )
    except FileNotFoundError as exc:
        raise HTTPException(status_code=503, detail=str(exc)) from exc
    except Exception as exc:
        raise HTTPException(status_code=500, detail=str(exc)) from exc
