@extends('layouts.app')

@section('title', 'Detail Pesanan')
@section('page-title', 'Detail Pesanan')

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <p class="text-sm text-stone-500 dark:text-stone-400">Kode Order</p>
        <h2 class="text-3xl font-black">{{ $order->order_code }}</h2>
    </div>
    <div class="flex flex-wrap items-center gap-3">
        <span class="{{ $order->status_badge_class }}">{{ $order->status_label }}</span>
        <a href="{{ route('orders.index') }}" class="btn-secondary">← Kembali</a>
    </div>
</div>

<div class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
    <div class="space-y-6">
        <div class="card-surface p-5">
            <h3 class="text-lg font-semibold">Informasi Pesanan</h3>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-sm text-stone-500 dark:text-stone-400">Nama Pemesan</p>
                    <p class="mt-1 font-semibold">{{ $order->customer_name ?: 'Walk-in Customer' }}</p>
                </div>
                <div>
                    <p class="text-sm text-stone-500 dark:text-stone-400">Waktu Order</p>
                    <p class="mt-1 font-semibold">{{ optional($order->ordered_at)->format('d M Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-sm text-stone-500 dark:text-stone-400">Total Porsi</p>
                    <p class="mt-1 font-semibold">{{ $order->total_qty }} porsi</p>
                </div>
                <div>
                    <p class="text-sm text-stone-500 dark:text-stone-400">Total Tagihan</p>
                    <p class="mt-1 font-semibold text-amber-700">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="card-surface p-5">
            <h3 class="text-lg font-semibold">Item Pesanan</h3>
            <div class="mt-4 space-y-4">
                @foreach($order->items as $item)
                    <div class="rounded-3xl border border-stone-200 p-4 dark:border-stone-800">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-lg font-semibold">{{ $item->menu?->name ?? 'Menu' }}</p>
                                <p class="text-sm text-stone-500 dark:text-stone-400">{{ $item->qty }} porsi × Rp {{ number_format($item->unit_price, 0, ',', '.') }}</p>
                            </div>
                            <p class="font-semibold text-amber-700">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="card-surface p-5">
            <h3 class="text-lg font-semibold">Dampak ke Stok Bahan</h3>
            <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">Stok baru akan berkurang saat pesanan diselesaikan.</p>
            <div class="mt-4 space-y-3">
                @forelse($ingredientSummary as $ingredient)
                    <div class="rounded-2xl bg-stone-50 px-4 py-3 dark:bg-stone-800/80">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-medium">{{ $ingredient['name'] }}</p>
                                <p class="text-sm text-stone-500 dark:text-stone-400">Butuh {{ $ingredient['qty_required'] }} {{ $ingredient['unit'] }} · stok sekarang {{ $ingredient['stock_now'] }} {{ $ingredient['unit'] }}</p>
                            </div>
                            @php
                                $enough = $ingredient['stock_now'] >= $ingredient['qty_required'];
                            @endphp
                            <span class="{{ $enough ? 'status-safe' : 'status-out' }}">{{ $enough ? 'Cukup' : 'Kurang' }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-stone-500 dark:text-stone-400">Belum ada komposisi bahan pada menu pesanan ini.</p>
                @endforelse
            </div>
        </div>

        <div class="card-surface p-5">
            <h3 class="text-lg font-semibold">Timeline Status</h3>
            <div class="mt-4 space-y-4">
                <div class="flex gap-3">
                    <div class="mt-1 h-3 w-3 rounded-full bg-violet-500"></div>
                    <div>
                        <p class="font-medium">Pending</p>
                        <p class="text-sm text-stone-500 dark:text-stone-400">{{ optional($order->ordered_at)->format('d M Y H:i') }}</p>
                    </div>
                </div>
                <div class="flex gap-3 {{ $order->processing_at ? '' : 'opacity-50' }}">
                    <div class="mt-1 h-3 w-3 rounded-full bg-sky-500"></div>
                    <div>
                        <p class="font-medium">Diproses</p>
                        <p class="text-sm text-stone-500 dark:text-stone-400">{{ $order->processing_at ? $order->processing_at->format('d M Y H:i') : 'Belum diproses' }}</p>
                    </div>
                </div>
                <div class="flex gap-3 {{ $order->completed_at ? '' : 'opacity-50' }}">
                    <div class="mt-1 h-3 w-3 rounded-full bg-green-500"></div>
                    <div>
                        <p class="font-medium">Selesai</p>
                        <p class="text-sm text-stone-500 dark:text-stone-400">
                            {{ $order->completed_at ? $order->completed_at->format('d M Y H:i') : 'Belum selesai' }}
                            @if($order->stock_applied_at)
                                · stok diperbarui
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex gap-3 {{ $order->cancelled_at ? '' : 'opacity-50' }}">
                    <div class="mt-1 h-3 w-3 rounded-full bg-stone-500"></div>
                    <div>
                        <p class="font-medium">Dibatalkan</p>
                        <p class="text-sm text-stone-500 dark:text-stone-400">{{ $order->cancelled_at ? $order->cancelled_at->format('d M Y H:i') : 'Tidak dibatalkan' }}</p>
                    </div>
                </div>
            </div>

            @if(auth()->user()->role === 'admin')
                <div class="mt-6 border-t border-stone-200 pt-5 dark:border-stone-800">
                    <h4 class="font-semibold">Aksi Admin</h4>
                    <div class="mt-4 flex flex-wrap gap-3">
                        @if($order->status === 'pending')
                            <form action="{{ route('orders.update-status', $order) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="processing">
                                <button class="rounded-2xl bg-sky-100 px-4 py-3 font-medium text-sky-700 transition hover:bg-sky-200 dark:bg-sky-950 dark:text-sky-300">Tandai Diproses</button>
                            </form>
                        @endif

                        @if(in_array($order->status, ['pending', 'processing']))
                            <form action="{{ route('orders.update-status', $order) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="completed">
                                <button class="rounded-2xl bg-green-100 px-4 py-3 font-medium text-green-700 transition hover:bg-green-200 dark:bg-green-950 dark:text-green-300">Selesaikan & Kurangi Stok</button>
                            </form>
                            <form action="{{ route('orders.update-status', $order) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="cancelled">
                                <button class="rounded-2xl bg-stone-200 px-4 py-3 font-medium text-stone-700 transition hover:bg-stone-300 dark:bg-stone-800 dark:text-stone-200">Batalkan</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
