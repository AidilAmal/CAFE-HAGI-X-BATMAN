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

@if (session('ai_stock_prediction'))
    @php($prediction = session('ai_stock_prediction'))
    <div class="mb-4 rounded-3xl border border-cyan-200 bg-cyan-50 p-5 text-sm text-cyan-900 shadow-sm dark:border-cyan-900 dark:bg-cyan-950/40 dark:text-cyan-100">
        <div class="mb-3 flex flex-col gap-1 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-600 dark:text-cyan-300">AI Stock-out Prediction</p>
                <h4 class="text-lg font-semibold">{{ $prediction['menu_name'] ?? 'Menu' }}</h4>
            </div>
            <div class="rounded-2xl bg-white px-4 py-2 text-right shadow-sm dark:bg-stone-950">
                <p class="text-xs text-stone-500 dark:text-stone-400">Prediksi habis</p>
                <p class="font-semibold">{{ $prediction['predicted_stock_out_date'] ?? 'Tidak habis dalam window' }}</p>
            </div>
        </div>

        <div class="grid gap-3 md:grid-cols-3">
            <div class="rounded-2xl bg-white p-3 dark:bg-stone-950">
                <p class="text-xs text-stone-500 dark:text-stone-400">Stok saat ini</p>
                <p class="font-semibold">{{ $prediction['current_stock'] ?? '-' }}</p>
            </div>
            <div class="rounded-2xl bg-white p-3 dark:bg-stone-950">
                <p class="text-xs text-stone-500 dark:text-stone-400">Hari menuju habis</p>
                <p class="font-semibold">{{ $prediction['days_until_stock_out'] ?? '-' }}</p>
            </div>
            <div class="rounded-2xl bg-white p-3 dark:bg-stone-950">
                <p class="text-xs text-stone-500 dark:text-stone-400">Sisa stok akhir forecast</p>
                <p class="font-semibold">{{ $prediction['remaining_stock_after_forecast'] ?? '-' }}</p>
            </div>
        </div>

        @if (!empty($prediction['note']))
            <p class="mt-3 text-cyan-800 dark:text-cyan-200">{{ $prediction['note'] }}</p>
        @endif
    </div>
@endif

<div class="card-surface p-5">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead><tr class="border-b border-stone-200 dark:border-stone-800"><th class="px-4 py-3 text-left">Menu</th><th class="px-4 py-3 text-left">Kategori</th><th class="px-4 py-3 text-left">Bahan</th><th class="px-4 py-3 text-left">Terjual</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3 text-left">Visible</th><th class="px-4 py-3 text-left">AI Forecast</th><th class="px-4 py-3 text-right">Aksi</th></tr></thead>
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
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('menus.ai.stock-out', $menu) }}" class="grid min-w-56 gap-2">
                                @csrf
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="number" name="current_stock" value="80" min="0" max="100000" class="input-ui px-3 py-2 text-xs" placeholder="Stok" required>
                                    <input type="number" name="forecast_days" value="30" min="1" max="90" class="input-ui px-3 py-2 text-xs" placeholder="Hari">
                                </div>
                                <button type="submit" class="rounded-2xl bg-cyan-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-cyan-700">
                                    Predict AI
                                </button>
                            </form>
                        </td>
                        <td class="px-4 py-3"><div class="flex justify-end gap-2"><a href="{{ route('menus.edit', $menu) }}" class="btn-secondary">Edit</a><form action="{{ route('menus.destroy', $menu) }}" method="POST" onsubmit="return confirm('Hapus menu ini?')">@csrf @method('DELETE')<button class="rounded-2xl bg-red-100 px-4 py-3 text-red-700 dark:bg-red-950 dark:text-red-300">Hapus</button></form></div></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-6 text-center text-stone-500">Belum ada menu.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $menus->links() }}</div>
</div>
@endsection
