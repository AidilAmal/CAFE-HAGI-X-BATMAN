# Cafe Hagi X Batman Pools — Source Pack untuk Laravel Kosong

Paket ini dibuat untuk kondisi seperti yang kamu jelaskan:
- project Laravel **sudah dibuat**
- repo GitHub **sudah tersambung**
- isi project masih **template bawaan Laravel**
- kamu ingin langsung masukin source project jadi dan lanjut jalanin

## Isi paket
- auth manual admin/owner
- dashboard modern + chart
- CRUD kategori barang
- CRUD supplier
- CRUD barang
- stok masuk
- stok keluar
- adjustment stok
- riwayat stok
- CRUD kategori menu
- CRUD menu cafe
- halaman publik home, menu, about
- dark mode admin
- PWA installable
- splash screen
- favicon/logo
- export PDF
- export Excel
- seeder akun demo + data dummy

## Cara pasang ke project Laravel kosong

### 1) Backup project kamu
```bash
git add .
git commit -m "backup template laravel kosong"
```

### 2) Ekstrak isi zip ke root project Laravel
Ekstrak semua isi zip ke root project.

### 3) Install dependency yang dibutuhkan
```bash
composer require barryvdh/laravel-dompdf maatwebsite/excel
npm install
composer dump-autoload
```

### 4) Atur database di `.env`
### 5) Jalankan migrate, seeder, dan storage link
```bash
php artisan migrate:fresh --seed
php artisan storage:link
```

### 6) Jalankan project
```bash
npm run dev
php artisan serve
```

## Akun demo
- admin: `admin@cafe.test`
- owner: `owner@cafe.test`
- password: `password`
