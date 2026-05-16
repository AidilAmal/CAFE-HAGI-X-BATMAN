@extends('layouts.app')
@section('title', 'Stok Masuk')
@section('page-title', 'Stok Masuk')
@section('content')
@include('partials.form-errors')
<div class="card-surface p-6">
    <form action="{{ route('stock.in.store') }}" method="POST" class="grid grid-cols-1 gap-4 md:grid-cols-2">
        @csrf
        <div class="md:col-span-2">
            <label class="mb-2 block text-sm font-medium">Pilih Barang</label>
            <select name="item_id" class="input-ui" required>
                <option value="">Pilih barang</option>
                @foreach($items as $item)
                    <option value="{{ $item->id }}" @selected(old('item_id') == $item->id)>
                        {{ $item->name }} (stok: {{ $item->stock }} {{ $item->unit }}){{ $item->barcode ? ' · Barcode: ' . $item->barcode : '' }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium">Jumlah Masuk</label>
            <input type="number" name="qty" value="{{ old('qty') }}" min="1" class="input-ui" required>
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium">Tanggal</label>
            <input type="date" name="movement_date" value="{{ old('movement_date', now()->format('Y-m-d')) }}" class="input-ui" required>
        </div>
        <div class="md:col-span-2">
            <label class="mb-2 block text-sm font-medium">Tanggal Expired Baru</label>
            <input type="date" name="expired_at" value="{{ old('expired_at') }}" class="input-ui">
            <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">Opsional. Kalau diisi, tanggal expired barang akan diperbarui dan dipakai oleh Expired Warning.</p>
        </div>
        <div class="md:col-span-2">
            <label class="mb-2 block text-sm font-medium">Keterangan</label>
            <textarea name="note" rows="4" class="input-ui">{{ old('note') }}</textarea>
        </div>
        <div class="md:col-span-2 flex gap-3">
            <a href="{{ route('stock.history') }}" class="btn-secondary">Batal</a>
            <button class="btn-primary">Simpan Stok Masuk</button>
        </div>
    </form>
</div>
@endsection
