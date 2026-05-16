<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderItem;
use App\Models\Item;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $drink = Category::create(['name' => 'Minuman', 'slug' => 'minuman']);
        $food = Category::create(['name' => 'Makanan', 'slug' => 'makanan']);
        $snack = Category::create(['name' => 'Snack', 'slug' => 'snack']);

        $supplierA = Supplier::create(['name' => 'PT Kopi Nusantara', 'phone' => '081234567890', 'address' => 'Jakarta Selatan']);
        $supplierB = Supplier::create(['name' => 'CV Bahan Segar', 'phone' => '082233445566', 'address' => 'Depok']);

        $items = collect([
            ['category_id' => $drink->id, 'supplier_id' => $supplierA->id, 'name' => 'Biji Kopi Arabica', 'code' => 'BRG-001', 'barcode' => '8991000000011', 'unit' => 'shot', 'stock' => 18, 'min_stock' => 6, 'expired_at' => now()->addDays(30)->toDateString(), 'price' => 180000, 'status' => 'active', 'description' => 'Untuk base espresso.'],
            ['category_id' => $drink->id, 'supplier_id' => $supplierA->id, 'name' => 'Susu Fresh Milk', 'code' => 'BRG-002', 'barcode' => '8991000000028', 'unit' => 'gelas', 'stock' => 16, 'min_stock' => 6, 'expired_at' => now()->addDays(3)->toDateString(), 'price' => 22000, 'status' => 'active', 'description' => 'Bahan minuman milk base.'],
            ['category_id' => $food->id, 'supplier_id' => $supplierB->id, 'name' => 'Roti Burger', 'code' => 'BRG-003', 'barcode' => '8991000000035', 'unit' => 'pcs', 'stock' => 12, 'min_stock' => 5, 'expired_at' => now()->addDays(5)->toDateString(), 'price' => 4500, 'status' => 'active', 'description' => 'Roti burger premium.'],
            ['category_id' => $snack->id, 'supplier_id' => $supplierB->id, 'name' => 'Matcha Powder', 'code' => 'BRG-004', 'barcode' => '8991000000042', 'unit' => 'scoop', 'stock' => 9, 'min_stock' => 4, 'expired_at' => now()->addDays(14)->toDateString(), 'price' => 38000, 'status' => 'active', 'description' => 'Serbuk matcha untuk latte.'],
            ['category_id' => $food->id, 'supplier_id' => $supplierB->id, 'name' => 'Beef Patty', 'code' => 'BRG-005', 'barcode' => '8991000000059', 'unit' => 'pcs', 'stock' => 4, 'min_stock' => 3, 'expired_at' => now()->addDays(2)->toDateString(), 'price' => 18000, 'status' => 'active', 'description' => 'Patty burger beef.'],
        ])->map(fn ($row) => Item::create($row))->keyBy('name');

        $coffeeCat = MenuCategory::create(['name' => 'Coffee', 'slug' => 'coffee']);
        $nonCoffeeCat = MenuCategory::create(['name' => 'Non Coffee', 'slug' => 'non-coffee']);
        $mealCat = MenuCategory::create(['name' => 'Main Course', 'slug' => 'main-course']);

        $imageFiles = [
            'menus/bn6WNwwugpIyX6faJdGeY7YCxEB30JWLGL1bO6iE.jpg',
            'menus/jS9Zakjrxv9XFnSYzbdYqQRtfmZ5qMH6l1saYgkJ.jpg',
            'menus/UqjmRHTjNdh1D6Rg1K9GwlCKXdqQLIOUhaw11hDB.jpg',
            'menus/VzjrAwFvoDANCBdthyFcPRm84y00KxCAYoqWHuMH.jpg',
        ];

        $menus = [
            ['menu_category_id' => $coffeeCat->id, 'name' => 'Es Kopi Susu', 'slug' => 'es-kopi-susu', 'price' => 22000, 'image' => $imageFiles[0], 'description' => 'Kopi susu creamy dengan gula aren.', 'recipe_notes' => "1 shot espresso\n1 gelas fresh milk\nTambahkan es batu penuh", 'is_visible' => true],
            ['menu_category_id' => $coffeeCat->id, 'name' => 'Americano', 'slug' => 'americano', 'price' => 18000, 'image' => $imageFiles[1], 'description' => 'Espresso dengan air panas.', 'recipe_notes' => "1 shot espresso\nTambahkan air panas dan es opsional", 'is_visible' => true],
            ['menu_category_id' => $nonCoffeeCat->id, 'name' => 'Matcha Latte', 'slug' => 'matcha-latte', 'price' => 25000, 'image' => $imageFiles[2], 'description' => 'Matcha lembut dengan susu segar.', 'recipe_notes' => "1 scoop matcha powder\n1 gelas fresh milk\nKocok hingga smooth", 'is_visible' => true],
            ['menu_category_id' => $mealCat->id, 'name' => 'Burger Beef', 'slug' => 'burger-beef', 'price' => 32000, 'image' => $imageFiles[3], 'description' => 'Burger daging dengan saus spesial.', 'recipe_notes' => "1 roti burger\n1 beef patty\nSaus spesial house blend", 'is_visible' => true],
        ];

        $menus = collect($menus)->map(fn ($row) => Menu::create($row))->keyBy('name');

        $menus['Es Kopi Susu']->ingredients()->sync([
            $items['Biji Kopi Arabica']->id => ['qty_required' => 1],
            $items['Susu Fresh Milk']->id => ['qty_required' => 1],
        ]);
        $menus['Americano']->ingredients()->sync([
            $items['Biji Kopi Arabica']->id => ['qty_required' => 1],
        ]);
        $menus['Matcha Latte']->ingredients()->sync([
            $items['Matcha Powder']->id => ['qty_required' => 1],
            $items['Susu Fresh Milk']->id => ['qty_required' => 1],
        ]);
        $menus['Burger Beef']->ingredients()->sync([
            $items['Roti Burger']->id => ['qty_required' => 1],
            $items['Beef Patty']->id => ['qty_required' => 1],
        ]);

        $admin = User::where('email', 'admin@cafe.test')->first();

        foreach (Item::take(4)->get() as $index => $item) {
            StockMovement::create([
                'item_id' => $item->id,
                'user_id' => $admin->id,
                'type' => $index % 2 === 0 ? 'in' : 'out',
                'qty' => $index + 1,
                'stock_before' => max($item->stock - ($index + 1), 0),
                'stock_after' => $item->stock,
                'note' => 'Data demo awal',
                'movement_date' => now()->subDays(2 - $index)->format('Y-m-d'),
            ]);
        }

        $sampleOrders = [
            ['menu' => 'Es Kopi Susu', 'qty' => 3, 'customer' => 'Aidil', 'status' => CustomerOrder::STATUS_COMPLETED, 'ordered_at' => now()->subHours(8)],
            ['menu' => 'Americano', 'qty' => 2, 'customer' => 'Raka', 'status' => CustomerOrder::STATUS_PROCESSING, 'ordered_at' => now()->subHours(6)],
            ['menu' => 'Es Kopi Susu', 'qty' => 2, 'customer' => 'Tamu Lounge', 'status' => CustomerOrder::STATUS_PENDING, 'ordered_at' => now()->subHours(5)],
            ['menu' => 'Burger Beef', 'qty' => 1, 'customer' => 'Salsa', 'status' => CustomerOrder::STATUS_CANCELLED, 'ordered_at' => now()->subHours(3)],
            ['menu' => 'Matcha Latte', 'qty' => 2, 'customer' => 'Nadia', 'status' => CustomerOrder::STATUS_COMPLETED, 'ordered_at' => now()->subHours(2)],
        ];

        foreach ($sampleOrders as $index => $sampleOrder) {
            $menu = $menus[$sampleOrder['menu']];
            $order = CustomerOrder::create([
                'order_code' => 'ORD-DEMO-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'customer_name' => $sampleOrder['customer'],
                'total_qty' => $sampleOrder['qty'],
                'total_amount' => $menu->price * $sampleOrder['qty'],
                'status' => $sampleOrder['status'],
                'ordered_at' => $sampleOrder['ordered_at'],
                'processing_at' => in_array($sampleOrder['status'], [CustomerOrder::STATUS_PROCESSING, CustomerOrder::STATUS_COMPLETED]) ? $sampleOrder['ordered_at']->copy()->addMinutes(10) : null,
                'completed_at' => $sampleOrder['status'] === CustomerOrder::STATUS_COMPLETED ? $sampleOrder['ordered_at']->copy()->addMinutes(25) : null,
                'cancelled_at' => $sampleOrder['status'] === CustomerOrder::STATUS_CANCELLED ? $sampleOrder['ordered_at']->copy()->addMinutes(15) : null,
                'stock_applied_at' => $sampleOrder['status'] === CustomerOrder::STATUS_COMPLETED ? $sampleOrder['ordered_at']->copy()->addMinutes(25) : null,
            ]);

            CustomerOrderItem::create([
                'customer_order_id' => $order->id,
                'menu_id' => $menu->id,
                'qty' => $sampleOrder['qty'],
                'unit_price' => $menu->price,
                'subtotal' => $menu->price * $sampleOrder['qty'],
            ]);

            if ($sampleOrder['status'] === CustomerOrder::STATUS_COMPLETED) {
                foreach ($menu->ingredients as $ingredient) {
                    $requiredQty = max((int) ($ingredient->pivot->qty_required ?? 1), 1) * $sampleOrder['qty'];
                    $item = Item::find($ingredient->id);
                    $before = $item->stock;
                    $after = max($before - $requiredQty, 0);
                    $item->update(['stock' => $after]);

                    StockMovement::create([
                        'item_id' => $item->id,
                        'user_id' => $admin->id,
                        'type' => 'out',
                        'qty' => $requiredQty,
                        'stock_before' => $before,
                        'stock_after' => $after,
                        'note' => 'Seeder order selesai ' . $order->order_code,
                        'movement_date' => $sampleOrder['ordered_at']->toDateString(),
                    ]);
                }
            }
        }
    }
}
