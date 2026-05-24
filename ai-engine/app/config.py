from __future__ import annotations

import os
from pathlib import Path

from dotenv import load_dotenv

load_dotenv()

BASE_DIR = Path(__file__).resolve().parents[1]

SALES_HISTORY_CSV = Path(
    os.getenv(
        "SALES_HISTORY_CSV",
        BASE_DIR.parent / "tools" / "synthetic-data" / "synthetic_output" / "sales_history_ml.csv",
    )
)

MODEL_PATH = Path(
    os.getenv("MODEL_PATH", BASE_DIR / "models" / "demand_forecast_model.joblib")
)

MODEL_META_PATH = MODEL_PATH.with_suffix(".metrics.json")
