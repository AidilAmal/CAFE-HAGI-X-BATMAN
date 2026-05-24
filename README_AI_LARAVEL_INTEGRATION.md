# Cafe Hagi Laravel ↔ FastAPI AI Integration Patch

Patch ini menambahkan tombol prediksi stok habis di halaman `Menu Cafe` Laravel.

## File yang ditambahkan/diubah

- `app/Http/Controllers/AiPredictionController.php`
- `routes/web.php`
- `config/services.php`
- `resources/views/menus/index.blade.php`
- `.env.example`

## Cara pasang

Extract isi zip ini ke root project Laravel:

```txt
C:\pindahan dari laptop lama\cafe-hagi-final
```

Pilih replace/overwrite kalau diminta.

## Tambahkan ke `.env`

```env
AI_ENGINE_URL=http://127.0.0.1:8001
AI_ENGINE_TIMEOUT=10
```

Lalu refresh config Laravel:

```powershell
php artisan config:clear
php artisan route:clear
```

## Jalankan service

Terminal 1:

```powershell
cd "C:\pindahan dari laptop lama\cafe-hagi-final\ai-engine"
.\.venv\Scripts\Activate.ps1
uvicorn app.main:app --reload --host 127.0.0.1 --port 8001
```

Terminal 2:

```powershell
cd "C:\pindahan dari laptop lama\cafe-hagi-final"
php artisan serve
```

Buka menu admin, isi stok saat ini, lalu klik `Predict AI`.
