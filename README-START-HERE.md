# Cafe Hagi X Batman Pools - Full Source Pack for Fresh Laravel Template

Paket ini dibuat untuk **project Laravel bawaan yang masih kosong**.

Cara pakai singkat:
1. Buka root project Laravel kamu.
2. Commit backup dulu.
3. Copy semua isi paket ini ke root project Laravel kamu, lalu replace file yang sama.
4. Jalankan dependency berikut:
   - `composer require barryvdh/laravel-dompdf maatwebsite/excel`
   - `npm install`
   - `composer dump-autoload`
5. Atur `.env` database.
6. Jalankan:
   - `php artisan migrate:fresh --seed`
   - `php artisan storage:link`
   - `npm run dev`
   - `php artisan serve`

Login demo:
- admin@cafe.test / password
- owner@cafe.test / password

Catatan:
- Paket ini **tidak menyertakan folder vendor dan node_modules**.
- Paket ini **bukan pengganti full core Laravel**, tapi **source lengkap yang memang dibuat untuk ditempel ke Laravel template kosong**.
