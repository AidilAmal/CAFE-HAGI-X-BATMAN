@extends('layouts.app')
@section('title', 'Barang')
@section('page-title', 'Barang')
@section('content')
<div class="mb-4 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
    <div>
        <h3 class="text-xl font-semibold">Daftar Barang</h3>
        <p class="text-sm text-stone-500 dark:text-stone-400">Kelola stok, barcode/SKU, dan tanggal expired untuk Smart Analyzer.</p>
    </div>
    <a href="{{ route('items.create') }}" class="btn-primary">+ Tambah Barang</a>
</div>

<form method="GET" class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-3">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / kode / barcode" class="input-ui">
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
            <thead>
                <tr class="border-b border-stone-200 dark:border-stone-800">
                    <th class="px-4 py-3 text-left">Barang</th>
                    <th class="px-4 py-3 text-left">Kategori</th>
                    <th class="px-4 py-3 text-left">Supplier</th>
                    <th class="px-4 py-3 text-left">Stok</th>
                    <th class="px-4 py-3 text-left">Expired</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr class="border-b border-stone-100 dark:border-stone-900">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if($item->image)
                                    <img src="{{ asset('storage/' . $item->image) }}" class="h-12 w-12 rounded-2xl object-cover" alt="{{ $item->name }}">
                                @else
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-stone-100 dark:bg-stone-800">📦</div>
                                @endif
                                <div>
                                    <p class="font-medium">{{ $item->name }}</p>
                                    <p class="text-xs text-stone-500">{{ $item->code }}</p>
                                    @if($item->barcode)
                                        <p class="text-xs text-stone-500">Barcode: {{ $item->barcode }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">{{ $item->category?->name }}</td>
                        <td class="px-4 py-3">{{ $item->supplier?->name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $item->stock }} {{ $item->unit }}</td>
                        <td class="px-4 py-3">
                            @if($item->expiry_status === 'none')
                                <span class="status-muted">Belum diisi</span>
                            @elseif(in_array($item->expiry_status, ['expired', 'urgent']))
                                <span class="status-out">{{ $item->expiry_label }}</span>
                            @elseif($item->expiry_status === 'warning')
                                <span class="status-low">{{ $item->expiry_label }}</span>
                            @else
                                <span class="status-safe">{{ optional($item->expired_at)->format('d M Y') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($item->stock_status === 'safe')
                                <span class="status-safe">Aman</span>
                            @elseif($item->stock_status === 'low')
                                <span class="status-low">Menipis</span>
                            @else
                                <span class="status-out">Habis</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('items.edit', $item) }}" class="btn-secondary">Edit</a>
                                <form action="{{ route('items.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus barang ini?')">
                                    @csrf @method('DELETE')
                                    <button class="rounded-2xl bg-red-100 px-4 py-3 text-red-700 dark:bg-red-950 dark:text-red-300">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-stone-500">Belum ada data barang.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $items->links() }}</div>
</div>
@endsection
