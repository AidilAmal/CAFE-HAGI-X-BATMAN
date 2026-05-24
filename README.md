# Cafe Hagi — Smart Cafe Inventory System

**Cafe Hagi** is an AI-powered cafe inventory and sales intelligence system built with **Laravel 12**, **MySQL**, and a separate **Python FastAPI machine learning microservice**.

The project started as a Laravel-based cafe management system for inventory, suppliers, stock movement, menu management, customer orders, reports, and rule-based business analysis. It has now been upgraded into a portfolio-ready AI software engineering project with demand forecasting, stock-out prediction, menu clustering, smart promo recommendations, and peak-hour sales analytics.

---

## Table of Contents

- [Project Overview](#project-overview)
- [Core Features](#core-features)
- [AI Features](#ai-features)
- [System Architecture](#system-architecture)
- [Machine Learning Pipeline](#machine-learning-pipeline)
- [Synthetic Dataset](#synthetic-dataset)
- [Model Performance](#model-performance)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Demo Accounts](#demo-accounts)
- [Local Installation](#local-installation)
- [Running the AI Engine](#running-the-ai-engine)
- [Running Laravel](#running-laravel)
- [AI Engine API Endpoints](#ai-engine-api-endpoints)
- [Security Notes](#security-notes)
- [Deployment Notes](#deployment-notes)
- [Portfolio Summary](#portfolio-summary)
- [Future Improvements](#future-improvements)

---

## Project Overview

Cafe Hagi is a full-stack cafe management platform designed to help cafe owners manage operational data and make better inventory decisions.

The system supports:

- Inventory management
- Supplier management
- Stock-in, stock-out, and stock adjustment
- Menu and recipe management
- Customer ordering workflow
- PDF and Excel reporting
- Role-based access control
- Smart inventory analysis
- AI-powered inventory forecasting

The upgraded version introduces a **microservice-based AI architecture**:

```text
Laravel Admin UI
    ↓ HTTP Request
Python FastAPI AI Engine
    ↓
Machine Learning Models / Insight Services
    ↓
JSON Response
    ↓
Laravel Dashboard & Prediction UI
```

This separation keeps Laravel focused on business operations while FastAPI handles machine learning inference and AI analytics.

---

## Core Features

- Admin and owner dashboard
- Role-based access control for admin and owner users
- Item category management
- Supplier management
- Inventory item management with:
  - Photo upload
  - Barcode/SKU
  - Minimum stock threshold
  - Expiration date
- Stock-in transaction management
- Stock-out transaction management
- Stock adjustment
- Automatic stock history from customer transactions
- Menu category management
- Cafe menu management
- Menu recipe / ingredient composition
- Public customer ordering page
- Customer order workflow:
  - Pending
  - Processing
  - Completed
  - Cancelled
- PDF report export
- Excel report export
- Dark mode UI
- Rule-based Smart Business Analyzer
- AI-powered inventory intelligence dashboard

---

## AI Features

### 1. Random Forest Demand Forecasting

The AI Engine uses a **Random Forest Regressor** to predict daily menu demand.

The model receives menu and date-related features, then predicts expected daily quantity sold.

Example use case:

```text
Input:
- Menu: Es Kopi Susu
- Category: Coffee
- Segment: Bestseller
- Date: 2026-05-25

Output:
- Predicted daily demand: 8.27 items
```

---

### 2. AI Stock-Out Prediction

The stock-out prediction feature estimates when a menu item will run out of stock based on current stock and predicted daily demand.

Example output:

```text
Menu: Es Kopi Susu
Current stock: 80
Predicted stock-out date: 2026-06-01
Days until stock-out: 8 days
```

This feature is exposed through FastAPI and can be consumed directly by Laravel.

---

### 3. Smart Promo ML

The Smart Promo engine recommends promotional actions based on sales behavior, menu performance, demand level, and menu segment.

Example recommendations:

```text
Manual Brew V60
Recommendation: Dead-stock recovery promo
Suggested action: Apply a 20% weekday discount

Es Kopi Susu
Recommendation: Peak-hour bundle promo
Suggested action: Create a lunch-hour coffee bundle
```

---

### 4. K-Means Menu Clustering

The AI Engine groups menu items into performance-based clusters using **K-Means clustering**.

Possible cluster labels:

| Cluster Label | Meaning |
|---|---|
| Star Performer | High-demand and high-revenue item |
| Reliable Core | Stable daily performer |
| Niche / Premium | Lower-volume but valuable item |
| Dead Stock Risk | Very low movement item |

This helps identify which menu items should be promoted, monitored, expanded, or reviewed.

---

### 5. Peak Hour Analyzer

The Peak Hour Analyzer detects time-based demand patterns.

The synthetic training dataset intentionally includes a strong coffee demand pattern between:

```text
12:00 - 15:00
```

This allows the system to generate insights such as:

```text
Coffee-based products perform strongest during lunch coffee rush.
Recommended action: prepare higher coffee stock before 12:00.
```

---

### 6. AI Dashboard

The AI Dashboard provides a centralized view for:

- Model quality metrics
- Stock-out forecast summary
- Menu performance clusters
- Smart promo recommendations
- Peak-hour sales patterns
- AI-generated operational insights

Default route:

```text
/ai-dashboard
```

---

## System Architecture

```text
cafe-hagi-final/
├── app/                         # Laravel application logic
├── database/                    # Laravel migrations and seeders
├── resources/                   # Blade views and frontend assets
├── routes/                      # Laravel routes
├── tools/
│   └── synthetic-data/          # Synthetic data generator
│       ├── generate_cafe_hagi_synthetic_sales.py
│       └── synthetic_output/
│           ├── sales_history_ml.csv
│           ├── menus_synthetic.csv
│           ├── customer_orders.csv
│           └── customer_order_items.csv
└── ai-engine/                   # Python FastAPI AI microservice
    ├── app/
    │   ├── main.py
    │   ├── schemas.py
    │   ├── config.py
    │   └── services/
    │       └── predictor.py
    ├── models/
    │   ├── demand_forecast_model.joblib
    │   └── demand_forecast_model.metrics.json
    ├── train_demand_model.py
    ├── requirements.txt
    └── test_api_payloads.http
```

---

## Machine Learning Pipeline

```text
Synthetic Sales Dataset
    ↓
Data Cleaning & Feature Engineering
    ↓
Daily Menu Demand Aggregation
    ↓
Random Forest Model Training
    ↓
Model Evaluation
    ↓
Model Serialization with Joblib
    ↓
FastAPI Inference Endpoint
    ↓
Laravel UI Integration
```

### Feature examples

The demand forecasting model uses features such as:

- `menu_id`
- `menu_name`
- `menu_category`
- `is_coffee_based`
- `menu_segment`
- `unit_price`
- `day_of_week_num`
- `is_weekend`

---

## Synthetic Dataset

Because real transaction data was not available during development, this project uses a synthetic cafe sales dataset generated with **Python**, **Pandas**, and **Faker**.

The dataset includes approximately **5,000 rows** of fictional cafe sales history.

### Embedded business patterns

The synthetic dataset was designed with realistic hidden patterns so the ML model can learn meaningful signals:

| Pattern | Description |
|---|---|
| Weekend uplift | Saturday and Sunday sales are approximately 2x higher than weekdays |
| Bestseller menus | Three menu items consistently sell every day |
| Dead-stock menus | Two menu items are rarely purchased |
| Coffee rush hour | Coffee-based products sell strongly between 12:00 and 15:00 |

### Generated files

```text
tools/synthetic-data/synthetic_output/
├── sales_history_ml.csv
├── menus_synthetic.csv
├── customer_orders.csv
└── customer_order_items.csv
```

Main training dataset:

```text
sales_history_ml.csv
```

---

## Model Performance

Random Forest daily demand forecasting result:

| Metric | Value |
|---|---:|
| Raw completed rows | 4,750 |
| Daily menu rows | 1,800 |
| Train rows | 1,440 |
| Test rows | 360 |
| MAE | 1.7707 |
| RMSE | 2.7601 |
| R² Score | 0.6817 |

The model is trained on synthetic data and is intended as a portfolio-grade AI prototype, not a production model trained on real customer transaction data.

---

## Tech Stack

### Main Application

- Laravel 12
- PHP 8.2+
- MySQL / MariaDB
- Blade Template
- Tailwind CSS
- Vite
- Chart.js
- DomPDF
- Laravel Excel

### AI Engine

- Python
- FastAPI
- Uvicorn
- Pandas
- NumPy
- scikit-learn
- Joblib
- Faker

### Machine Learning

- Random Forest Regressor
- K-Means Clustering
- Feature engineering
- Synthetic data generation
- Model evaluation with MAE, RMSE, and R²

---

## Project Structure

Important Laravel files:

```text
app/Services/SmartInventoryAnalyzer.php
app/Http/Controllers/DashboardController.php
app/Http/Controllers/AiPredictionController.php
app/Http/Controllers/AiDashboardController.php
app/Models/Item.php
resources/views/dashboard/index.blade.php
resources/views/ai/dashboard.blade.php
resources/views/items/create.blade.php
resources/views/items/edit.blade.php
resources/views/items/index.blade.php
resources/views/stock/in.blade.php
routes/web.php
config/services.php
```

Important AI Engine files:

```text
ai-engine/train_demand_model.py
ai-engine/app/main.py
ai-engine/app/schemas.py
ai-engine/app/config.py
ai-engine/app/services/predictor.py
ai-engine/models/demand_forecast_model.joblib
ai-engine/models/demand_forecast_model.metrics.json
```

Important synthetic data files:

```text
tools/synthetic-data/generate_cafe_hagi_synthetic_sales.py
tools/synthetic-data/synthetic_output/sales_history_ml.csv
tools/synthetic-data/synthetic_output/menus_synthetic.csv
tools/synthetic-data/synthetic_output/customer_orders.csv
tools/synthetic-data/synthetic_output/customer_order_items.csv
```

---

## Demo Accounts

```text
Admin
Email: admin@cafe.test
Password: password

Owner
Email: owner@cafe.test
Password: password
```

---

## Local Installation

Clone or extract the project, then enter the project directory:

```bash
cd cafe-hagi-final
```

Install PHP and Node dependencies:

```bash
composer install
npm install
```

Copy the environment file:

```bash
cp .env.example .env
```

For Windows PowerShell:

```powershell
copy .env.example .env
```

Configure the database in `.env`:

```env
APP_NAME="Cafe Hagi"
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cafe_hagi
DB_USERNAME=root
DB_PASSWORD=

CACHE_STORE=file
SESSION_DRIVER=file

AI_ENGINE_URL=http://127.0.0.1:8001
AI_ENGINE_TIMEOUT=10
```

Generate the Laravel application key:

```bash
php artisan key:generate
```

Run migrations and seeders:

```bash
php artisan migrate:fresh --seed
```

Create the storage link:

```bash
php artisan storage:link
```

Clear cache:

```bash
php artisan optimize:clear
```

---

## Generate Synthetic Data

Enter the synthetic data folder:

```bash
cd tools/synthetic-data
```

Install dependencies if needed:

```bash
python -m pip install pandas faker
```

Generate the dataset:

```bash
python generate_cafe_hagi_synthetic_sales.py
```

Generated output:

```text
tools/synthetic-data/synthetic_output/
```

---

## Running the AI Engine

Enter the AI Engine folder:

```bash
cd ai-engine
```

Create a Python virtual environment:

```bash
python -m venv .venv
```

Activate the virtual environment on Windows PowerShell:

```powershell
.\.venv\Scripts\Activate.ps1
```

If PowerShell blocks script execution, run:

```powershell
Set-ExecutionPolicy -Scope CurrentUser RemoteSigned
```

Install AI Engine dependencies:

```bash
python -m pip install --upgrade pip
python -m pip install -r requirements.txt
```

Train the demand forecasting model:

```bash
python train_demand_model.py
```

Expected output includes:

```text
=== Cafe Hagi Daily Demand Model Training Report ===
Daily menu rows      : 1,800
MAE                  : 1.7707
RMSE                 : 2.7601
R2                   : 0.6817
Model saved to       : ai-engine/models/demand_forecast_model.joblib
```

Run the FastAPI server:

```bash
uvicorn app.main:app --reload --host 127.0.0.1 --port 8001
```

Open FastAPI documentation:

```text
http://127.0.0.1:8001/docs
```

---

## Running Laravel

Open a second terminal from the root project folder:

```bash
php artisan serve
```

Run the frontend development server:

```bash
npm run dev
```

Open the application:

```text
http://127.0.0.1:8000
```

Open the AI Dashboard:

```text
http://127.0.0.1:8000/ai-dashboard
```

---

## AI Engine API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/health` | Check AI Engine and model status |
| POST | `/predict/demand` | Predict daily menu demand |
| POST | `/predict/stock-out` | Predict stock-out date |
| GET | `/insights/dashboard` | Get AI Dashboard summary |
| GET | `/insights/menu-clusters` | Get K-Means menu clustering insight |
| GET | `/insights/smart-promos` | Get smart promo recommendations |
| GET | `/insights/peak-hours` | Get peak-hour sales analysis |

### Example stock-out prediction request

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

### Example stock-out prediction response

```json
{
  "menu_id": 1,
  "menu_name": "Es Kopi Susu",
  "current_stock": 80,
  "predicted_stock_out_date": "2026-06-01",
  "days_until_stock_out": 8,
  "remaining_stock_after_forecast": 0,
  "note": "Stok diprediksi habis dalam window forecast. Disarankan restock sebelum tanggal tersebut."
}
```

---

## Rule-Based Smart Business Analyzer

Before the AI microservice upgrade, Cafe Hagi included a rule-based Smart Business Analyzer built directly inside Laravel.

It analyzes:

- Recent stock-out activity
- Current stock level
- Expiration dates
- Menu ingredient relationships

### Smart Stock Prediction

The system calculates average item usage from the last 7 days of stock-out history and estimates when inventory will run out.

Formula:

```text
Estimated days until stock-out = current stock / average daily usage
```

### Expired Warning

Items with an `expired_at` date are flagged when they are close to expiration.

Statuses:

- Expired today
- Expires in 1-2 days
- Expires in 3-7 days
- Already expired

### Rule-Based Smart Promo Generator

The Laravel analyzer generates promo suggestions when:

1. An item is close to expiration and still has stock.
2. An item has high stock but low movement.

This rule-based analyzer remains useful as a fallback layer and business logic companion to the AI Engine.

---

## Security Notes

This project follows secure-by-design practices for a portfolio environment:

- `.env` must never be committed to Git.
- Database credentials must stay outside the repository.
- Laravel validation is used before sending prediction payloads to FastAPI.
- AI Engine URL is stored in Laravel config, not hardcoded in controllers.
- FastAPI runs locally during development.
- User input should be validated before being sent to the AI Engine.
- Public deployment should protect AI endpoints behind authentication, firewall rules, or internal networking.
- `vendor/`, `node_modules/`, `.venv/`, logs, and cache folders should not be committed.

Recommended `.gitignore` additions:

```gitignore
.env
.venv/
ai-engine/.venv/
vendor/
node_modules/
storage/logs/
storage/framework/cache/
storage/framework/sessions/
storage/framework/views/
```

---

## Build for Deployment

Build frontend assets:

```bash
npm run build
```

For production, configure `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
```

Run production migrations:

```bash
php artisan migrate --force
```

Optimize Laravel:

```bash
php artisan optimize
```

---

## Deployment Notes

For shared hosting or VPS deployment, make sure:

- PHP 8.2+ is available
- Required PHP extensions are enabled:
  - `pdo_mysql`
  - `mbstring`
  - `openssl`
  - `fileinfo`
  - `gd`
  - `zip`
- Web document root points to the `public` folder
- `storage` and `bootstrap/cache` are writable
- `APP_KEY` is generated
- `APP_DEBUG=false`
- MySQL database is created
- The AI Engine is deployed separately or configured as an internal service

---

## Portfolio Summary

Cafe Hagi demonstrates a practical AI software engineering workflow:

- Built a Laravel-based cafe inventory and ordering system
- Designed a synthetic data generation pipeline for ML training
- Trained a Random Forest model for daily demand forecasting
- Exposed the model through a Python FastAPI microservice
- Integrated Laravel with the AI Engine through HTTP APIs
- Added stock-out prediction for inventory planning
- Added K-Means menu clustering for menu performance analysis
- Added smart promo recommendations based on demand and menu behavior
- Added peak-hour analysis for operational decision support
- Built an AI Dashboard for admin/owner users

Recommended portfolio description:

```text
Cafe Hagi is an AI-powered cafe inventory management system built with Laravel and Python FastAPI. It uses a Random Forest model to forecast menu demand, predicts stock-out dates, applies K-Means clustering for menu performance segmentation, and provides smart promo and peak-hour insights through an AI Dashboard.
```

---

## Future Improvements

- Train models with real production transaction data
- Sync FastAPI directly with MySQL or Laravel API data
- Add scheduled model retraining
- Add authentication between Laravel and FastAPI
- Add queue-based async prediction jobs
- Add confidence intervals for demand forecasts
- Add barcode scanner using the browser camera
- Add batch-level stock tracking for different expiration dates
- Add promo approval workflow
- Add WhatsApp/email notifications for restock and expiration warnings
- Add natural language report summaries
- Add Docker Compose for Laravel, MySQL, and FastAPI
- Add automated tests for Laravel and FastAPI endpoints

---

## License

This project is intended for learning, portfolio development, and demonstration purposes.
