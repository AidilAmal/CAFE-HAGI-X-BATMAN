# Cafe Hagi AI Dashboard Patch

Patch ini menambahkan fitur AI berikut:

- AI Dashboard di Laravel: `/ai-dashboard`
- FastAPI endpoint: `/insights/dashboard`
- FastAPI endpoint: `/insights/menu-clusters`
- FastAPI endpoint: `/insights/smart-promos`
- FastAPI endpoint: `/insights/peak-hours`
- K-Means Menu Clustering
- Smart Promo ML recommendation layer
- Peak Hour Analyzer

## File yang ditambahkan / diubah

### FastAPI

- `ai-engine/app/main.py`
- `ai-engine/app/schemas.py`
- `ai-engine/app/services/insights.py`
- `ai-engine/test_ai_dashboard_payloads.http`

### Laravel

- `app/Http/Controllers/AiDashboardController.php`
- `app/Http/Controllers/AiPredictionController.php`
- `resources/views/ai/dashboard.blade.php`
- `resources/views/layouts/app.blade.php`
- `routes/web.php`
- `config/services.php`

## Cara pasang

Extract isi zip ke root project Laravel:

```txt
C:\pindahan dari laptop lama\cafe-hagi-final
```

Pilih replace/overwrite jika diminta.

## Tambahkan ke `.env`

```env
AI_ENGINE_URL=http://127.0.0.1:8001
AI_ENGINE_TIMEOUT=10
```

Lalu jalankan:

```powershell
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Jalankan FastAPI

```powershell
cd "C:\pindahan dari laptop lama\cafe-hagi-final\ai-engine"
.\.venv\Scripts\Activate.ps1
uvicorn app.main:app --reload --host 127.0.0.1 --port 8001
```

Buka Swagger:

```txt
http://127.0.0.1:8001/docs
```

Test endpoint baru:

```txt
GET /insights/dashboard
GET /insights/menu-clusters
GET /insights/smart-promos
GET /insights/peak-hours
```

## Jalankan Laravel

Terminal lain:

```powershell
cd "C:\pindahan dari laptop lama\cafe-hagi-final"
php artisan serve
```

Buka:

```txt
http://127.0.0.1:8000/ai-dashboard
```

## Catatan desain

Dashboard ini sengaja membaca data dari FastAPI, bukan langsung mengolah ML di Laravel. Tujuannya agar arsitektur portfolio tetap jelas:

```txt
Laravel Admin UI
    ↓ HTTP
FastAPI AI Engine
    ↓
Random Forest + K-Means + Insight Layer
```
