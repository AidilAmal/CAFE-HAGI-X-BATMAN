<?php

namespace App\Http\Controllers;

use App\Models\CustomerOrder;
use App\Models\CustomerOrderItem;
use App\Models\Menu;
use App\Models\MenuCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PublicMenuController extends Controller
{
    public function home()
    {
        $featuredMenus = Menu::with(['ingredients', 'category'])
            ->withSum('completedOrderItems as sold_qty', 'qty')
            ->where('is_visible', true)
            ->orderByDesc('sold_qty')
            ->latest()
            ->take(6)
            ->get();

        return view('public.home', compact('featuredMenus'));
    }

    public function menu(Request $request)
    {
        $query = Menu::with(['category', 'ingredients'])
            ->where('is_visible', true)
            ->latest();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category')) {
            $query->where('menu_category_id', $request->category);
        }

        $menus = $query->paginate(12)->withQueryString();
        $categories = MenuCategory::orderBy('name')->get();

        return view('public.menu', compact('menus', 'categories'));
    }

    public function show(Menu $menu)
    {
        $menu->load(['category', 'ingredients']);

        $relatedMenus = Menu::with(['ingredients'])
            ->where('is_visible', true)
            ->whereKeyNot($menu->id)
            ->when($menu->menu_category_id, fn ($q) => $q->where('menu_category_id', $menu->menu_category_id))
            ->take(3)
            ->get();

        return view('public.menu-detail', compact('menu', 'relatedMenus'));
    }

    public function order(Request $request, Menu $menu)
    {
        $request->validate([
            'qty' => 'required|integer|min:1|max:20',
            'customer_name' => 'nullable|string|max:100',
        ]);

        $menu->load(['ingredients']);

        if ($menu->ingredients->isEmpty()) {
            return back()->withErrors(['qty' => 'Menu ini belum punya komposisi bahan.'])->withInput();
        }

        if ($menu->availability_status === 'out') {
            return back()->withErrors(['qty' => 'Menu ini sedang habis.'])->withInput();
        }

        $qty = (int) $request->qty;

        foreach ($menu->ingredients as $ingredient) {
            $requiredQty = max((int) ($ingredient->pivot->qty_required ?? 1), 1) * $qty;
            if ($ingredient->stock < $requiredQty) {
                return back()->withErrors([
                    'qty' => 'Stok bahan ' . $ingredient->name . ' belum cukup untuk ' . $qty . ' porsi. Pesanan tidak bisa dibuat dulu.',
                ])->withInput();
            }
        }

        $order = DB::transaction(function () use ($menu, $qty, $request) {
            $order = CustomerOrder::create([
                'order_code' => 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5)),
                'customer_name' => $request->customer_name,
                'total_qty' => $qty,
                'total_amount' => $menu->price * $qty,
                'status' => CustomerOrder::STATUS_PENDING,
                'ordered_at' => now(),
            ]);

            CustomerOrderItem::create([
                'customer_order_id' => $order->id,
                'menu_id' => $menu->id,
                'qty' => $qty,
                'unit_price' => $menu->price,
                'subtotal' => $menu->price * $qty,
            ]);

            return $order;
        });

        return redirect()->route('public.menu.show', $menu)->with('order_success', [
            'code' => $order->order_code,
            'menu' => $menu->name,
            'qty' => $qty,
            'amount' => $order->total_amount,
            'status' => $order->status_label,
        ]);
    }

    public function about()
    {
        return view('public.about');
    }
}
