# Update Revisi Detail Order Cafe Hagi

## Fitur baru
- Halaman detail menu publik dengan foto besar, deskripsi lengkap, komposisi bahan, dan tombol order.
- Quantity plus/minus dan estimasi total harga langsung berubah.
- Popup order berhasil berisi kode order, jumlah porsi, dan total pembayaran.
- Riwayat pesanan untuk admin/owner.
- Dashboard sekarang punya kartu total pesanan, daftar menu paling sering dipesan, dan pesanan terbaru.
- Form menu admin sekarang mendukung catatan resep dan komposisi bahan.
- Status menu sinkron otomatis dari stok bahan:
  - semua bahan aman => Tersedia
  - ada bahan menipis => Hampir Habis
  - ada bahan habis / kurang dari kebutuhan resep => Habis
- Gambar menu publik memakai query version `?v=timestamp` supaya update foto lebih cepat muncul tanpa harus Ctrl+F5 berkali-kali.
- Tombol dark mode dan install app tersedia di halaman publik.

## Langkah setelah extract
Jalankan di root project:

```bash
php artisan migrate
php artisan db:seed --class=DemoDataSeeder
php artisan optimize:clear
php artisan storage:link
npm run dev
```

Kalau mau ulang semua data demo dari awal:

```bash
php artisan migrate:fresh --seed
```

## Cara pakai resep menu
Masuk admin > Menu Cafe > Tambah/Edit Menu:
1. Isi nama, kategori, harga, foto, deskripsi.
2. Isi catatan resep.
3. Tambahkan komposisi bahan.
4. Isi jumlah kebutuhan bahan per 1 porsi.
5. Simpan.

Sesudah itu, status menu di publik dan admin akan mengikuti stok bahan yang dipakai.

## Cara kerja order
- Pelanggan klik card menu di halaman publik.
- Masuk ke halaman detail menu.
- Pilih jumlah porsi.
- Klik order.
- Sistem membuat riwayat order.
- Sistem mengurangi stok semua bahan sesuai resep x jumlah porsi.
- Admin bisa lihat hasilnya di Dashboard, Riwayat Pesanan, dan Riwayat Stok.
