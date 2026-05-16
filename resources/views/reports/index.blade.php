@extends('layouts.app')
@section('title', 'Laporan')
@section('page-title', 'Laporan')
@section('content')
<form method="GET" class="mb-4 grid grid-cols-1 gap-4 rounded-[28px] border border-stone-200 bg-white p-5 shadow-sm dark:border-stone-800 dark:bg-stone-900 md:grid-cols-4">
    <div><label class="mb-2 block text-sm font-medium">Dari Tanggal</label><input type="date" name="start_date" value="{{ request('start_date') }}" class="input-ui"></div>
    <div><label class="mb-2 block text-sm font-medium">Sampai Tanggal</label><input type="date" name="end_date" value="{{ request('end_date') }}" class="input-ui"></div>
    <div><label class="mb-2 block text-sm font-medium">Tipe</label><select name="type" class="input-ui"><option value="">Semua</option><option value="in" @selected(request('type') == 'in')>IN</option><option value="out" @selected(request('type') == 'out')>OUT</option><option value="adjustment" @selected(request('type') == 'adjustment')>ADJUSTMENT</option></select></div>
    <div class="flex items-end gap-3"><button class="btn-secondary">Filter</button><a href="{{ route('reports.pdf', request()->query()) }}" class="btn-primary">Export PDF</a><a href="{{ route('reports.excel', request()->query()) }}" class="btn-primary">Export Excel</a></div>
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
                        <td class="px-4 py-3">{{ strtoupper($movement->type) }}</td>
                        <td class="px-4 py-3">{{ $movement->qty }}</td>
                        <td class="px-4 py-3">{{ $movement->stock_before }}</td>
                        <td class="px-4 py-3">{{ $movement->stock_after }}</td>
                        <td class="px-4 py-3">{{ $movement->user?->name }}</td>
                        <td class="px-4 py-3 text-stone-500">{{ $movement->note }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-6 text-center text-stone-500">Belum ada data laporan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $movements->links() }}</div>
</div>
@endsection
