<?php

namespace App\Http\Controllers;

use App\Models\MenuCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MenuCategoryController extends Controller
{
    public function index()
    {
        $menuCategories = MenuCategory::latest()->paginate(10);
        return view('menu_categories.index', compact('menuCategories'));
    }

    public function create()
    {
        return view('menu_categories.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:menu_categories,name']);
        MenuCategory::create(['name' => $request->name, 'slug' => Str::slug($request->name)]);
        return redirect()->route('menu-categories.index')->with('success', 'Kategori menu berhasil ditambahkan.');
    }

    public function edit(MenuCategory $menu_category)
    {
        return view('menu_categories.edit', ['menuCategory' => $menu_category]);
    }

    public function update(Request $request, MenuCategory $menu_category)
    {
        $request->validate(['name' => 'required|string|max:255|unique:menu_categories,name,' . $menu_category->id]);
        $menu_category->update(['name' => $request->name, 'slug' => Str::slug($request->name)]);
        return redirect()->route('menu-categories.index')->with('success', 'Kategori menu berhasil diupdate.');
    }

    public function destroy(MenuCategory $menu_category)
    {
        $menu_category->delete();
        return redirect()->route('menu-categories.index')->with('success', 'Kategori menu berhasil dihapus.');
    }
}
