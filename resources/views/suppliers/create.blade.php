@extends('layouts.app')
@section('title', 'Tambah Supplier')
@section('page-title', 'Tambah Supplier')
@section('content')
@include('partials.form-errors')
<div class="card-surface p-6">
    <form action="{{ route('suppliers.store') }}" method="POST" class="space-y-4">
        @csrf
        <div><label class="mb-2 block text-sm font-medium">Nama Supplier</label><input type="text" name="name" value="{{ old('name') }}" class="input-ui" required></div>
        <div><label class="mb-2 block text-sm font-medium">No HP</label><input type="text" name="phone" value="{{ old('phone') }}" class="input-ui"></div>
        <div><label class="mb-2 block text-sm font-medium">Alamat</label><textarea name="address" rows="4" class="input-ui">{{ old('address') }}</textarea></div>
        <div class="flex gap-3"><a href="{{ route('suppliers.index') }}" class="btn-secondary">Batal</a><button class="btn-primary">Simpan</button></div>
    </form>
</div>
@endsection
