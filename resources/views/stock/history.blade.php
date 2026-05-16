@extends('layouts.app')
@section('title', 'Riwayat Stok')
@section('page-title', 'Riwayat Stok')
@section('content')
<div class="mb-4 flex flex-wrap gap-3">
    @if(auth()->user()->isAdmin())
        <a href="{{ route('stock.in.form') }}" class="btn-primary">+ Stok Masuk</a>
        <a href="{{ route('stock.out.form') }}" class="btn-secondary">+ Stok Keluar</a>
        <a href="{{ route('stock.adjustment.form') }}" class="btn-secondary">+ Adjustment</a>
    @endif
</div>

<form method="GET" class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-4">
    <select name="type" class="input-ui">
        <option value="">Semua tipe</option>
        <option value="in" @selected(request('type') == 'in')>IN</option>
        <option value="out" @selected(request('type') == 'out')>OUT</option>
        <option value="adjustment" @selected(request('type') == 'adjustment')>ADJUSTMENT</option>
    </select>
    <div class="md:col-span-3"><button class="btn-secondary">Filter</button></div>
</form>

<div class="card-surface p-5">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead><tr class="border-b border-stone-200 dark:border-stone-800"><th class="px-4 py-3 text-left">Tanggal</th><th class="px-4 py-3 text-left">Barang</th><th class="px-4 py-3 text-left">Tipe</th><th class="px-4 py-3 text-left">Qty</th><th class="px-4 py-3 text-left">Sebelum</th><th class="px-4 py-3 text-left">Sesudah</th><th class="px-4 py-3 text-left">User</th><th class="px-4 py-3 text-left">Note</th></tr></thead>
            <tbody>
                @forelse($movements as $movement)
                    <tr class="border-b border-stone-100 dark:border-stone-900">
                        <td class="px-4 py-3">{{ optional($movement->movement_date)->format('d M Y') }}</td>
                        <td class="px-4 py-3">{{ $movement->item?->name }}</td>
                        <td class="px-4 py-3">@if($movement->type === 'in')<span class="status-safe">IN</span>@elseif($movement->type === 'out')<span class="status-out">OUT</span>@else<span class="status-low">ADJUST</span>@endif</td>
                        <td class="px-4 py-3">{{ $movement->qty }}</td>
                        <td class="px-4 py-3">{{ $movement->stock_before }}</td>
                        <td class="px-4 py-3">{{ $movement->stock_after }}</td>
                        <td class="px-4 py-3">{{ $movement->user?->name }}</td>
                        <td class="px-4 py-3 text-stone-500">{{ $movement->note }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-6 text-center text-stone-500">Belum ada riwayat stok.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $movements->links() }}</div>
</div>
@endsection
