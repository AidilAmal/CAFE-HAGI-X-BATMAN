@extends('layouts.app')
@section('title', 'Menu Cafe')
@section('page-title', 'Menu Cafe')
@section('content')
<div class="mb-4 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
    <div>
        <h3 class="text-xl font-semibold">Daftar Menu Cafe</h3>
        <p class="text-sm text-stone-500 dark:text-stone-400">Status menu otomatis sinkron dari stok bahan pada resep.</p>
    </div>
    <a href="{{ route('menus.create') }}" class="btn-primary">+ Tambah Menu</a>
</div>

<form method="GET" class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-3">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari menu..." class="input-ui">
    <select name="category" class="input-ui">
        <option value="">Semua kategori</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
        @endforeach
    </select>
    <button class="btn-secondary">Filter</button>
</form>

<div class="card-surface p-5">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead><tr class="border-b border-stone-200 dark:border-stone-800"><th class="px-4 py-3 text-left">Menu</th><th class="px-4 py-3 text-left">Kategori</th><th class="px-4 py-3 text-left">Bahan</th><th class="px-4 py-3 text-left">Terjual</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3 text-left">Visible</th><th class="px-4 py-3 text-right">Aksi</th></tr></thead>
            <tbody>
                @forelse($menus as $menu)
                    <tr class="border-b border-stone-100 dark:border-stone-900">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if($menu->image_url)
                                    <img src="{{ $menu->image_url }}" class="h-12 w-12 rounded-2xl object-cover">
                                @else
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-stone-100 dark:bg-stone-800">☕</div>
                                @endif
                                <div>
                                    <p class="font-medium">{{ $menu->name }}</p>
                                    <p class="text-xs text-stone-500">{{ $menu->slug }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">{{ $menu->category?->name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $menu->ingredients->count() }} bahan</td>
                        <td class="px-4 py-3">{{ (int) ($menu->sold_qty ?? 0) }} porsi</td>
                        <td class="px-4 py-3">@if($menu->availability_status === 'available')<span class="status-safe">Tersedia</span>@elseif($menu->availability_status === 'low')<span class="status-low">Hampir Habis</span>@else<span class="status-out">Habis</span>@endif</td>
                        <td class="px-4 py-3">{{ $menu->is_visible ? 'Ya' : 'Tidak' }}</td>
                        <td class="px-4 py-3"><div class="flex justify-end gap-2"><a href="{{ route('menus.edit', $menu) }}" class="btn-secondary">Edit</a><form action="{{ route('menus.destroy', $menu) }}" method="POST" onsubmit="return confirm('Hapus menu ini?')">@csrf @method('DELETE')<button class="rounded-2xl bg-red-100 px-4 py-3 text-red-700 dark:bg-red-950 dark:text-red-300">Hapus</button></form></div></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-stone-500">Belum ada menu.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $menus->links() }}</div>
</div>
@endsection
