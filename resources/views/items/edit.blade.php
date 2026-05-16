@extends('layouts.app')
@section('title', 'Edit Barang')
@section('page-title', 'Edit Barang')
@section('content')
@include('partials.form-errors')
<div class="card-surface p-6">
    <form action="{{ route('items.update', $item) }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 gap-4 md:grid-cols-2">
        @csrf @method('PUT')
        <div>
            <label class="mb-2 block text-sm font-medium">Nama Barang</label>
            <input type="text" name="name" value="{{ old('name', $item->name) }}" class="input-ui" required>
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium">Kode Barang</label>
            <input type="text" name="code" value="{{ old('code', $item->code) }}" class="input-ui" required>
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium">Barcode / SKU</label>
            <input type="text" name="barcode" value="{{ old('barcode', $item->barcode) }}" class="input-ui" placeholder="Opsional, bisa dari barcode scanner">
            <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">Scanner barcode USB biasanya otomatis mengisi kolom ini seperti keyboard.</p>
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium">Tanggal Expired</label>
            <input type="date" name="expired_at" value="{{ old('expired_at', optional($item->expired_at)->format('Y-m-d')) }}" class="input-ui">
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium">Kategori</label>
            <select name="category_id" class="input-ui" required>
                <option value="">Pilih kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id', $item->category_id) == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium">Supplier</label>
            <select name="supplier_id" class="input-ui">
                <option value="">Pilih supplier</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" @selected(old('supplier_id', $item->supplier_id) == $supplier->id)>{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium">Satuan</label>
            <input type="text" name="unit" value="{{ old('unit', $item->unit) }}" class="input-ui" required>
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium">Harga</label>
            <input type="number" name="price" value="{{ old('price', $item->price) }}" class="input-ui" min="0" required>
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium">Stok</label>
            <input type="number" name="stock" value="{{ old('stock', $item->stock) }}" class="input-ui" min="0" required>
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium">Stok Minimum</label>
            <input type="number" name="min_stock" value="{{ old('min_stock', $item->min_stock) }}" class="input-ui" min="0" required>
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium">Status</label>
            <select name="status" class="input-ui" required>
                <option value="active" @selected(old('status', $item->status) == 'active')>Active</option>
                <option value="inactive" @selected(old('status', $item->status) == 'inactive')>Inactive</option>
            </select>
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium">Foto Barang</label>
            <input type="file" name="image" class="input-ui">
        </div>
        <div class="md:col-span-2">
            <label class="mb-2 block text-sm font-medium">Deskripsi</label>
            <textarea name="description" rows="4" class="input-ui">{{ old('description', $item->description) }}</textarea>
        </div>
        <div class="md:col-span-2 flex gap-3">
            <a href="{{ route('items.index') }}" class="btn-secondary">Batal</a>
            <button class="btn-primary">Update</button>
        </div>
    </form>
</div>
@endsection
