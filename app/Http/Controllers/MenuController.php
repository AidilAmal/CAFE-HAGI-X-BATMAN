<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Menu;
use App\Models\MenuCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $query = Menu::with(['category', 'ingredients'])->withSum('orderItems as sold_qty', 'qty')->latest();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category')) {
            $query->where('menu_category_id', $request->category);
        }

        $menus = $query->paginate(10)->withQueryString();
        $categories = MenuCategory::orderBy('name')->get();

        return view('menus.index', compact('menus', 'categories'));
    }

    public function create()
    {
        $categories = MenuCategory::orderBy('name')->get();
        $items = Item::orderBy('name')->get();

        return view('menus.create', compact('categories', 'items'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $data['slug'] = Str::slug($request->name . '-' . Str::random(5));

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('menus', 'public');
        }

        $menu = Menu::create($data);
        $this->syncIngredients($menu, $request);

        return redirect()->route('menus.index')->with('success', 'Menu berhasil ditambahkan.');
    }

    public function edit(Menu $menu)
    {
        $menu->load('ingredients');
        $categories = MenuCategory::orderBy('name')->get();
        $items = Item::orderBy('name')->get();

        return view('menus.edit', compact('menu', 'categories', 'items'));
    }

    public function update(Request $request, Menu $menu)
    {
        $data = $this->validatedData($request);
        $data['slug'] = Str::slug($request->name . '-' . $menu->id);

        if ($request->hasFile('image')) {
            if ($menu->image) {
                Storage::disk('public')->delete($menu->image);
            }

            $data['image'] = $request->file('image')->store('menus', 'public');
        }

        $menu->update($data);
        $this->syncIngredients($menu, $request);

        return redirect()->route('menus.index')->with('success', 'Menu berhasil diupdate.');
    }

    public function destroy(Menu $menu)
    {
        if ($menu->image) {
            Storage::disk('public')->delete($menu->image);
        }

        $menu->delete();

        return redirect()->route('menus.index')->with('success', 'Menu berhasil dihapus.');
    }

    protected function validatedData(Request $request): array
    {
        return $request->validate([
            'menu_category_id' => 'nullable|exists:menu_categories,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'description' => 'nullable|string',
            'recipe_notes' => 'nullable|string',
            'availability_status' => 'nullable|in:available,low,out',
            'is_visible' => 'required|boolean',
            'ingredients' => 'nullable|array',
            'ingredients.*.item_id' => 'nullable|exists:items,id',
            'ingredients.*.qty_required' => 'nullable|integer|min:1|max:999',
        ]);
    }

    protected function syncIngredients(Menu $menu, Request $request): void
    {
        $syncData = [];

        foreach ($request->input('ingredients', []) as $ingredient) {
            $itemId = $ingredient['item_id'] ?? null;
            $qtyRequired = (int) ($ingredient['qty_required'] ?? 0);

            if ($itemId && $qtyRequired > 0) {
                $syncData[$itemId] = ['qty_required' => $qtyRequired];
            }
        }

        $menu->ingredients()->sync($syncData);
    }
}
