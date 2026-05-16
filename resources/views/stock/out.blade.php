@extends('layouts.app')
@section('title', 'Stok Keluar')
@section('page-title', 'Stok Keluar')
@section('content')
@include('partials.form-errors')
<div class="card-surface p-6">
    <form action="{{ route('stock.out.store') }}" method="POST" class="grid grid-cols-1 gap-4 md:grid-cols-2">
        @csrf
        <div class="md:col-span-2"><label class="mb-2 block text-sm font-medium">Pilih Barang</label><select name="item_id" class="input-ui" required><option value="">Pilih barang</option>@foreach($items as $item)<option value="{{ $item->id }}">{{ $item->name }} (stok: {{ $item->stock }} {{ $item->unit }})</option>@endforeach</select></div>
        <div><label class="mb-2 block text-sm font-medium">Jumlah Keluar</label><input type="number" name="qty" min="1" class="input-ui" required></div>
        <div><label class="mb-2 block text-sm font-medium">Tanggal</label><input type="date" name="movement_date" value="{{ now()->format('Y-m-d') }}" class="input-ui" required></div>
        <div class="md:col-span-2"><label class="mb-2 block text-sm font-medium">Keterangan</label><textarea name="note" rows="4" class="input-ui"></textarea></div>
        <div class="md:col-span-2 flex gap-3"><a href="{{ route('stock.history') }}" class="btn-secondary">Batal</a><button class="btn-primary">Simpan Stok Keluar</button></div>
    </form>
</div>
@endsection
