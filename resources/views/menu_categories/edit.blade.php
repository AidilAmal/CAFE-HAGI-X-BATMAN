@extends('layouts.app')
@section('title', 'Edit Kategori Menu')
@section('page-title', 'Edit Kategori Menu')
@section('content')
@include('partials.form-errors')
<div class="card-surface p-6">
    <form action="{{ route('menu-categories.update', $menuCategory) }}" method="POST" class="space-y-4">
        @csrf @method('PUT')
        <div><label class="mb-2 block text-sm font-medium">Nama Kategori Menu</label><input type="text" name="name" value="{{ old('name', $menuCategory->name) }}" class="input-ui" required></div>
        <div class="flex gap-3"><a href="{{ route('menu-categories.index') }}" class="btn-secondary">Batal</a><button class="btn-primary">Update</button></div>
    </form>
</div>
@endsection
