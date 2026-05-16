@extends('layouts.app')
@section('title', 'Tambah Kategori Menu')
@section('page-title', 'Tambah Kategori Menu')
@section('content')
@include('partials.form-errors')
<div class="card-surface p-6">
    <form action="{{ route('menu-categories.store') }}" method="POST" class="space-y-4">
        @csrf
        <div><label class="mb-2 block text-sm font-medium">Nama Kategori Menu</label><input type="text" name="name" value="{{ old('name') }}" class="input-ui" required></div>
        <div class="flex gap-3"><a href="{{ route('menu-categories.index') }}" class="btn-secondary">Batal</a><button class="btn-primary">Simpan</button></div>
    </form>
</div>
@endsection
