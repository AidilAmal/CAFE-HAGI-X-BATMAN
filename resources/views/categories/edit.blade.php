@extends('layouts.app')
@section('title', 'Edit Kategori')
@section('page-title', 'Edit Kategori')
@section('content')
@include('partials.form-errors')
<div class="card-surface p-6">
    <form action="{{ route('categories.update', $category) }}" method="POST" class="space-y-4">
        @csrf @method('PUT')
        <div><label class="mb-2 block text-sm font-medium">Nama Kategori</label><input type="text" name="name" value="{{ old('name', $category->name) }}" class="input-ui" required></div>
        <div class="flex gap-3"><a href="{{ route('categories.index') }}" class="btn-secondary">Batal</a><button class="btn-primary">Update</button></div>
    </form>
</div>
@endsection
