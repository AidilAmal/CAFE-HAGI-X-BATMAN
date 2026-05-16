# Langkah Lengkap dari Awal Sampai Jalan

## A. Sebelum ekstrak
1. buka folder project Laravel kamu
2. pastikan itu masih template kosong
3. commit backup

```bash
git add .
git commit -m "backup before cafe source pack"
```

## B. Masukin source
1. ekstrak zip ini
2. copy semua folder/file
3. paste ke root project Laravel
4. kalau ada file yang sama, pilih replace

## C. Install package
```bash
composer require barryvdh/laravel-dompdf maatwebsite/excel
npm install
composer dump-autoload
```

## D. Atur database
Buka `.env`, lalu sesuaikan:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cafe_hagi
DB_USERNAME=root
DB_PASSWORD=
```

## E. Jalankan database
```bash
php artisan migrate:fresh --seed
php artisan storage:link
```

## F. Jalankan project
Terminal 1:
```bash
npm run dev
```

Terminal 2:
```bash
php artisan serve
```

## G. Login
- admin: `admin@cafe.test`
- owner: `owner@cafe.test`
- password: `password`

## H. Cek fitur
- login berhasil
- dashboard tampil
- kategori barang tampil
- supplier tampil
- barang tampil
- stok masuk jalan
- stok keluar jalan
- adjustment jalan
- kategori menu tampil
- menu tampil
- laporan tampil
- export PDF jalan
- export Excel jalan
- halaman publik `/menu` tampil
- tombol install app muncul di browser yang support PWA
