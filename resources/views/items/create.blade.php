@extends('layouts.app')
@section('title', 'Tambah Barang')
@section('page-title', 'Tambah Barang')
@section('content')
@include('partials.form-errors')
<div class="card-surface p-6">
    <form action="{{ route('items.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 gap-4 md:grid-cols-2">
        @csrf
        <div>
            <label class="mb-2 block text-sm font-medium">Nama Barang</label>
            <input type="text" name="name" value="{{ old('name') }}" class="input-ui" required>
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium">Kode Barang</label>
            <input type="text" name="code" value="{{ old('code') }}" class="input-ui" required>
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium">Barcode / SKU</label>
            <input type="text" name="barcode" value="{{ old('barcode') }}" class="input-ui" placeholder="Opsional, bisa dari barcode scanner">
            <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">Scanner barcode USB biasanya otomatis mengisi kolom ini seperti keyboard.</p>
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium">Tanggal Expired</label>
            <input type="date" name="expired_at" value="{{ old('expired_at') }}" class="input-ui">
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium">Kategori</label>
            <select name="category_id" class="input-ui" required>
                <option value="">Pilih kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium">Supplier</label>
            <select name="supplier_id" class="input-ui">
                <option value="">Pilih supplier</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium">Satuan</label>
            <input type="text" name="unit" value="{{ old('unit', 'pcs') }}" class="input-ui" required>
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium">Harga</label>
            <input type="number" name="price" value="{{ old('price', 0) }}" class="input-ui" min="0" required>
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium">Stok Awal</label>
            <input type="number" name="stock" value="{{ old('stock', 0) }}" class="input-ui" min="0" required>
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium">Stok Minimum</label>
            <input type="number" name="min_stock" value="{{ old('min_stock', 0) }}" class="input-ui" min="0" required>
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium">Status</label>
            <select name="status" class="input-ui" required>
                <option value="active" @selected(old('status', 'active') == 'active')>Active</option>
                <option value="inactive" @selected(old('status', 'active') == 'inactive')>Inactive</option>
            </select>
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium">Foto Barang</label>
            <input type="file" name="image" class="input-ui">
        </div>
        <div class="md:col-span-2">
            <label class="mb-2 block text-sm font-medium">Deskripsi</label>
            <textarea name="description" rows="4" class="input-ui">{{ old('description') }}</textarea>
        </div>
        <div class="md:col-span-2 flex gap-3">
            <a href="{{ route('items.index') }}" class="btn-secondary">Batal</a>
            <button class="btn-primary">Simpan</button>
        </div>
    </form>
</div>
@endsection
