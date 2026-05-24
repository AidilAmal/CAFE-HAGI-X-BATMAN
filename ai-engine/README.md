# Cafe Hagi AI Engine

Microservice Python + FastAPI untuk portfolio **Cafe Hagi - Smart Cafe Inventory System**.

Fitur awal:

- Training **Random Forest Regressor** dari `sales_history_ml.csv`.
- Prediksi demand menu per jam.
- Prediksi tanggal stok habis berdasarkan stok saat ini.
- Endpoint REST API yang bisa dipanggil dari Laravel.

## Struktur

```txt
ai-engine/
├── app/
│   ├── main.py
│   ├── schemas.py
│   ├── config.py
│   └── services/
│       └── predictor.py
├── models/
├── notebooks/
├── train_demand_model.py
├── requirements.txt
├── .env.example
└── test_api_payloads.http
```

## Setup Windows PowerShell

Jalankan dari root project Laravel:

```powershell
cd "C:\pindahan dari laptop lama\cafe-hagi-final"
cd ai-engine
python -m venv .venv
.\.venv\Scripts\Activate.ps1
python -m pip install --upgrade pip
python -m pip install -r requirements.txt
```

Kalau PowerShell menolak activate script:

```powershell
Set-ExecutionPolicy -Scope CurrentUser RemoteSigned
.\.venv\Scripts\Activate.ps1
```

## Training model

Pastikan CSV ini ada:

```txt
../tools/synthetic-data/synthetic_output/sales_history_ml.csv
```

Lalu jalankan:

```powershell
python train_demand_model.py
```

Output model:

```txt
models/demand_forecast_model.joblib
models/demand_forecast_model.metrics.json
```

## Jalankan API

```powershell
uvicorn app.main:app --reload --host 127.0.0.1 --port 8001
```

Buka dokumentasi API:

```txt
http://127.0.0.1:8001/docs
```

## Endpoint

### `GET /health`

Cek apakah API hidup dan model sudah loaded.

### `POST /predict/demand`

Prediksi demand per jam untuk 1 menu.

Payload contoh:

```json
{
  "menu_id": 1,
  "menu_name": "Es Kopi Susu",
  "menu_category": "Coffee",
  "is_coffee_based": true,
  "menu_segment": "bestseller",
  "unit_price": 22000,
  "target_date": "2026-05-25",
  "hour": 13
}
```

### `POST /predict/stock-out`

Prediksi kapan stok menu akan habis.

Payload contoh:

```json
{
  "menu_id": 1,
  "menu_name": "Es Kopi Susu",
  "menu_category": "Coffee",
  "is_coffee_based": true,
  "menu_segment": "bestseller",
  "unit_price": 22000,
  "current_stock": 80,
  "start_date": "2026-05-25",
  "forecast_days": 30
}
```

## Catatan arsitektur

Untuk tahap portfolio, service ini sengaja membaca CSV synthetic agar pipeline ML terlihat jelas:

```txt
Synthetic Data -> Training Script -> Saved Model -> FastAPI -> Laravel HTTP Client
```

Tahap berikutnya bisa diganti dari CSV ke query MySQL atau endpoint Laravel internal.
