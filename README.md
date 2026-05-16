# Cafe Hagi - Smart Cafe Inventory System

Cafe Hagi adalah aplikasi manajemen cafe berbasis **Laravel 12** untuk mengelola barang, supplier, stok masuk/keluar, menu cafe, pesanan pelanggan, laporan, serta fitur portfolio-ready bernama **Smart Business Analyzer**.

## Highlight Fitur

- Dashboard admin dan owner
- Role-based access: admin dan owner
- Manajemen kategori barang
- Manajemen supplier
- Manajemen barang dengan foto, barcode/SKU, stok minimum, dan tanggal expired
- Stok masuk, stok keluar, dan adjustment stok
- Riwayat stok otomatis dari transaksi pelanggan
- Manajemen kategori menu dan menu cafe
- Recipe / komposisi bahan menu
- Pemesanan menu dari halaman publik
- Workflow pesanan: pending, processing, completed, cancelled
- Export laporan PDF dan Excel
- Dark mode
- **Smart Stock Prediction**
- **Expired Warning**
- **Smart Promo Generator**

## Smart Business Analyzer

Fitur ini ditampilkan langsung di dashboard admin/owner. Analisis dibuat dari data stok keluar, stok barang, tanggal expired, dan relasi bahan dengan menu.

### 1. Smart Stock Prediction

Sistem menghitung rata-rata pemakaian barang dari riwayat stok keluar 7 hari terakhir, lalu memprediksi kapan stok akan habis.

Contoh output:

```text
Susu Fresh Milk
Stok saat ini: 11 gelas
Rata-rata keluar: 1,00 / hari
Prediksi: perlu restock minggu ini
Saran restock: 14 gelas
```

Rumus sederhana:

```text
Prediksi hari habis = stok sekarang / rata-rata pemakaian harian
```

### 2. Expired Warning

Barang yang memiliki `expired_at` dan akan expired dalam 7 hari ke depan otomatis muncul sebagai warning di dashboard.

Status yang ditampilkan:

- Expired hari ini
- Expired 1-2 hari lagi
- Expired 3-7 hari lagi
- Sudah expired

### 3. Smart Promo Generator

Sistem membuat rekomendasi promo otomatis berdasarkan dua kondisi:

1. Barang hampir expired dan masih punya stok.
2. Barang stoknya tinggi tetapi pergerakannya lambat.

Contoh rekomendasi:

```text
Promo Es Kopi Susu
Diskon: 15%
Alasan: Susu Fresh Milk expired 3 hari lagi dan stok masih tersedia.
Aksi: Jalankan diskon untuk mendorong penjualan sebelum bahan expired.
```

Fitur ini tidak memakai API AI eksternal, jadi aman untuk deploy gratis/hemat biaya. Logika dibuat di Laravel service `App\Services\SmartInventoryAnalyzer` supaya mudah dikembangkan menjadi integrasi AI API di masa depan.

## Tech Stack

- Laravel 12
- PHP 8.2+
- MySQL / MariaDB
- Blade Template
- Tailwind CSS
- Vite
- Chart.js
- DomPDF
- Laravel Excel

## Struktur Fitur Baru

File penting yang terkait Smart Analyzer:

```text
app/Services/SmartInventoryAnalyzer.php
app/Http/Controllers/DashboardController.php
app/Models/Item.php
database/migrations/2026_05_16_000120_add_smart_inventory_fields_to_items_table.php
resources/views/dashboard/index.blade.php
resources/views/items/create.blade.php
resources/views/items/edit.blade.php
resources/views/items/index.blade.php
resources/views/stock/in.blade.php
```

Field baru di tabel `items`:

```text
barcode     nullable, unique
expired_at  nullable, date
```

## Akun Demo

```text
Admin
Email: admin@cafe.test
Password: password

Owner
Email: owner@cafe.test
Password: password
```

## Instalasi Lokal

Clone / extract project, lalu masuk ke folder project:

```bash
cd cafe-hagi-final
```

Install dependency PHP dan Node:

```bash
composer install
npm install
```

Copy file environment:

```bash
cp .env.example .env
```

Di Windows PowerShell bisa pakai:

```powershell
copy .env.example .env
```

Atur database di `.env`:

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
```

Generate app key:

```bash
php artisan key:generate
```

Jalankan migration dan seeder:

```bash
php artisan migrate:fresh --seed
```

Buat storage link:

```bash
php artisan storage:link
```

Jalankan frontend dan backend di dua terminal berbeda:

```bash
npm run dev
```

```bash
php artisan serve
```

Buka aplikasi:

```text
http://127.0.0.1:8000
```

## Update Project Lama

Kalau database sudah pernah dibuat sebelumnya, cukup jalankan:

```bash
php artisan migrate
php artisan db:seed
```

Kalau ingin reset data demo:

```bash
php artisan migrate:fresh --seed
```

Setelah update view atau config, bersihkan cache:

```bash
php artisan optimize:clear
```

## Build untuk Deploy

Sebelum deploy, build asset frontend:

```bash
npm run build
```

Di production, pastikan `.env` memakai:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-kamu.com
```

Jalankan migration production:

```bash
php artisan migrate --force
```

Optimasi Laravel:

```bash
php artisan optimize
```

## Catatan Deploy

Untuk hosting shared / VPS, pastikan:

- PHP minimal 8.2
- Extension PHP aktif: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd`, `zip`
- Document root diarahkan ke folder `public`
- Folder `storage` dan `bootstrap/cache` writable
- `APP_KEY` sudah dibuat
- `APP_DEBUG=false`
- Database MySQL sudah dibuat

## Ide Pengembangan Lanjutan

- Barcode scanner berbasis kamera browser
- Tabel batch stok agar expired date bisa beda per stok masuk
- Approval promo menjadi promo aktif
- Integrasi AI API untuk membuat ringkasan laporan natural language
- Notifikasi restock dan expired via email/WhatsApp
- Grafik prediksi stok per barang

## Portfolio Description

Cafe Hagi is a Laravel-based cafe management system with inventory, ordering, reporting, and a Smart Business Analyzer. The analyzer predicts stock depletion, detects nearly expired items, and generates automatic promo recommendations based on inventory movement and menu ingredients.
