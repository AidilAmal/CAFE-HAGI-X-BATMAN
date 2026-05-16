@extends('layouts.app')

@section('title', 'Manajemen Pesanan')
@section('page-title', 'Manajemen Pesanan')

@section('content')
<div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
    <div class="card-surface p-5">
        <p class="text-sm text-stone-500 dark:text-stone-400">Total Pesanan</p>
        <h3 class="mt-2 text-3xl font-bold">{{ (int) ($statusSummary->total ?? 0) }}</h3>
    </div>
    <div class="card-surface p-5">
        <p class="text-sm text-stone-500 dark:text-stone-400">Pending</p>
        <h3 class="mt-2 text-3xl font-bold text-violet-600">{{ (int) ($statusSummary->pending_count ?? 0) }}</h3>
    </div>
    <div class="card-surface p-5">
        <p class="text-sm text-stone-500 dark:text-stone-400">Diproses</p>
        <h3 class="mt-2 text-3xl font-bold text-sky-600">{{ (int) ($statusSummary->processing_count ?? 0) }}</h3>
    </div>
    <div class="card-surface p-5">
        <p class="text-sm text-stone-500 dark:text-stone-400">Selesai</p>
        <h3 class="mt-2 text-3xl font-bold text-green-600">{{ (int) ($statusSummary->completed_count ?? 0) }}</h3>
    </div>
    <div class="card-surface p-5">
        <p class="text-sm text-stone-500 dark:text-stone-400">Dibatalkan</p>
        <h3 class="mt-2 text-3xl font-bold text-stone-500">{{ (int) ($statusSummary->cancelled_count ?? 0) }}</h3>
    </div>
</div>

<div class="mt-6 card-surface p-5">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h3 class="text-xl font-semibold">Riwayat Pesanan</h3>
            <p class="text-sm text-stone-500 dark:text-stone-400">Admin bisa memproses, menyelesaikan, atau membatalkan pesanan dari halaman ini.</p>
        </div>
    </div>

    <form method="GET" class="mt-5 grid gap-4 md:grid-cols-[1.2fr_0.8fr_0.5fr]">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode order atau nama customer" class="input-ui">
        <select name="status" class="input-ui">
            <option value="">Semua status</option>
            <option value="pending" @selected(request('status') === 'pending')>Pending</option>
            <option value="processing" @selected(request('status') === 'processing')>Diproses</option>
            <option value="completed" @selected(request('status') === 'completed')>Selesai</option>
            <option value="cancelled" @selected(request('status') === 'cancelled')>Dibatalkan</option>
        </select>
        <button class="btn-secondary">Filter</button>
    </form>

    <div class="mt-5 overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-stone-200 dark:border-stone-800">
                    <th class="px-4 py-3 text-left">Order</th>
                    <th class="px-4 py-3 text-left">Customer</th>
                    <th class="px-4 py-3 text-left">Ringkasan Menu</th>
                    <th class="px-4 py-3 text-left">Qty</th>
                    <th class="px-4 py-3 text-left">Total</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Waktu</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr class="border-b border-stone-100 align-top dark:border-stone-900">
                        <td class="px-4 py-4">
                            <p class="font-semibold">{{ $order->order_code }}</p>
                            <p class="text-xs text-stone-500 dark:text-stone-400">{{ $order->items->count() }} item order</p>
                        </td>
                        <td class="px-4 py-4">{{ $order->customer_name ?: 'Walk-in Customer' }}</td>
                        <td class="px-4 py-4">
                            <div class="space-y-1">
                                @foreach($order->items->take(2) as $item)
                                    <p>{{ $item->menu?->name ?? 'Menu' }} <span class="text-xs text-stone-500">x{{ $item->qty }}</span></p>
                                @endforeach
                                @if($order->items->count() > 2)
                                    <p class="text-xs text-stone-500 dark:text-stone-400">+{{ $order->items->count() - 2 }} item lainnya</p>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-4">{{ $order->total_qty }} porsi</td>
                        <td class="px-4 py-4 font-semibold text-amber-700">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-4"><span class="{{ $order->status_badge_class }}">{{ $order->status_label }}</span></td>
                        <td class="px-4 py-4 text-stone-500 dark:text-stone-400">{{ optional($order->ordered_at)->format('d M Y H:i') }}</td>
                        <td class="px-4 py-4">
                            <div class="flex flex-wrap justify-end gap-2">
                                <a href="{{ route('orders.show', $order) }}" class="btn-secondary px-3 py-2">Detail</a>
                                @if(auth()->user()->role === 'admin')
                                    @if(in_array($order->status, ['pending']))
                                        <form action="{{ route('orders.update-status', $order) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="processing">
                                            <button class="rounded-xl bg-sky-100 px-3 py-2 text-sky-700 transition hover:bg-sky-200 dark:bg-sky-950 dark:text-sky-300">Proses</button>
                                        </form>
                                    @endif

                                    @if(in_array($order->status, ['pending', 'processing']))
                                        <form action="{{ route('orders.update-status', $order) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="completed">
                                            <button class="rounded-xl bg-green-100 px-3 py-2 text-green-700 transition hover:bg-green-200 dark:bg-green-950 dark:text-green-300">Selesaikan</button>
                                        </form>
                                        <form action="{{ route('orders.update-status', $order) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="cancelled">
                                            <button class="rounded-xl bg-stone-200 px-3 py-2 text-stone-700 transition hover:bg-stone-300 dark:bg-stone-800 dark:text-stone-200">Batalkan</button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-center text-stone-500">Belum ada order.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $orders->links() }}</div>
</div>
@endsection
