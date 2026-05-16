# Update Order Workflow — Status Pesanan & Stok Turun Saat Selesai

## Fitur baru
- Status order sekarang: `pending`, `processing`, `completed`, `cancelled`
- Stok bahan **tidak langsung berkurang** saat customer order
- Stok bahan **baru berkurang saat admin klik Selesaikan**
- Admin bisa:
  - ubah pesanan ke **Diproses**
  - ubah pesanan ke **Selesai**
  - **batalkan** pesanan
- Ada halaman **detail pesanan**
- Ada ringkasan **dampak ke stok bahan** pada detail pesanan
- Dashboard sekarang ada statistik:
  - pending
  - diproses
  - selesai
  - dibatalkan
- Dashboard menu paling sering dipesan dihitung dari **pesanan selesai**

## Setelah extract ke project
Jalankan:

```bash
php artisan migrate
php artisan optimize:clear
npm run dev
```

## Kalau mau reset demo data dari nol
```bash
php artisan migrate:fresh --seed
php artisan storage:link
npm run dev
```

## Flow baru
1. Customer order dari halaman detail menu
2. Order masuk sebagai **Pending**
3. Admin buka **Riwayat Pesanan**
4. Admin bisa klik **Proses**
5. Admin klik **Selesaikan & Kurangi Stok**
6. Saat itulah stok bahan otomatis turun dan riwayat stok tercatat
7. Kalau dibatalkan, stok tidak berubah

## File utama yang berubah
- `app/Http/Controllers/PublicMenuController.php`
- `app/Http/Controllers/OrderController.php`
- `app/Http/Controllers/DashboardController.php`
- `app/Models/CustomerOrder.php`
- `app/Models/Menu.php`
- `resources/views/public/menu-detail.blade.php`
- `resources/views/orders/index.blade.php`
- `resources/views/orders/show.blade.php`
- `resources/views/dashboard/index.blade.php`
- `routes/web.php`
- `database/seeders/DemoDataSeeder.php`
- `database/migrations/2026_04_18_000110_add_order_workflow_columns_to_customer_orders_table.php`
