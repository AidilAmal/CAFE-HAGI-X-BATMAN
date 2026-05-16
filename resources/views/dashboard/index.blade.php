@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
    <div class="card-surface p-5">
        <p class="text-sm text-stone-500 dark:text-stone-400">Total Barang</p>
        <h3 class="mt-2 text-3xl font-bold">{{ $totalItems }}</h3>
    </div>
    <div class="card-surface p-5">
        <p class="text-sm text-stone-500 dark:text-stone-400">Total Menu</p>
        <h3 class="mt-2 text-3xl font-bold">{{ $totalMenus }}</h3>
    </div>
    <div class="card-surface p-5">
        <p class="text-sm text-stone-500 dark:text-stone-400">Total Pesanan</p>
        <h3 class="mt-2 text-3xl font-bold text-amber-600">{{ $totalOrders }}</h3>
    </div>
    <div class="card-surface p-5">
        <p class="text-sm text-stone-500 dark:text-stone-400">Stok Menipis</p>
        <h3 class="mt-2 text-3xl font-bold text-amber-600">{{ $lowStockItems }}</h3>
    </div>
    <div class="card-surface p-5">
        <p class="text-sm text-stone-500 dark:text-stone-400">Stok Habis</p>
        <h3 class="mt-2 text-3xl font-bold text-red-600">{{ $outOfStockItems }}</h3>
    </div>
</div>

<div class="mt-6 grid grid-cols-2 gap-4 xl:grid-cols-4">
    <div class="card-surface p-5">
        <p class="text-sm text-stone-500 dark:text-stone-400">Pending</p>
        <h3 class="mt-2 text-3xl font-bold text-violet-600">{{ $pendingOrders }}</h3>
    </div>
    <div class="card-surface p-5">
        <p class="text-sm text-stone-500 dark:text-stone-400">Diproses</p>
        <h3 class="mt-2 text-3xl font-bold text-sky-600">{{ $processingOrders }}</h3>
    </div>
    <div class="card-surface p-5">
        <p class="text-sm text-stone-500 dark:text-stone-400">Selesai</p>
        <h3 class="mt-2 text-3xl font-bold text-green-600">{{ $completedOrders }}</h3>
    </div>
    <div class="card-surface p-5">
        <p class="text-sm text-stone-500 dark:text-stone-400">Dibatalkan</p>
        <h3 class="mt-2 text-3xl font-bold text-stone-500">{{ $cancelledOrders }}</h3>
    </div>
</div>

<div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="card-surface p-6 xl:col-span-2">
        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.25em] text-amber-600">Smart Business Analyzer</p>
                <h3 class="mt-2 text-2xl font-bold">AI-style insight untuk stok, expired, dan promo</h3>
                <p class="mt-2 text-sm text-stone-500 dark:text-stone-400">{{ $smartSummary['period_label'] }} berdasarkan riwayat stok keluar dan data expired barang.</p>
            </div>
            <span class="status-safe w-fit">Portfolio Feature</span>
        </div>

        <div class="mt-5 grid gap-3">
            @foreach($smartSummary['highlights'] as $highlight)
                <div class="rounded-2xl bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:bg-amber-950/50 dark:text-amber-100">💡 {{ $highlight }}</div>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 xl:grid-cols-1">
        <div class="card-surface p-5">
            <p class="text-sm text-stone-500 dark:text-stone-400">Risiko Stok</p>
            <h3 class="mt-2 text-3xl font-bold text-red-600">{{ $smartSummary['stock_risk_count'] }}</h3>
        </div>
        <div class="card-surface p-5">
            <p class="text-sm text-stone-500 dark:text-stone-400">Hampir Expired</p>
            <h3 class="mt-2 text-3xl font-bold text-amber-600">{{ $smartSummary['expired_risk_count'] }}</h3>
        </div>
        <div class="card-surface p-5">
            <p class="text-sm text-stone-500 dark:text-stone-400">Rekomendasi Promo</p>
            <h3 class="mt-2 text-3xl font-bold text-green-600">{{ $smartSummary['promo_count'] }}</h3>
        </div>
    </div>
</div>

<div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="card-surface p-5 xl:col-span-2">
        <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h3 class="text-lg font-semibold">Smart Stock Prediction</h3>
                <p class="text-sm text-stone-500 dark:text-stone-400">Prediksi habis dihitung dari rata-rata stok keluar 7 hari terakhir.</p>
            </div>
            <a href="{{ route('stock.history') }}" class="btn-secondary w-fit">Lihat Riwayat</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-stone-200 dark:border-stone-800">
                        <th class="px-4 py-3 text-left">Barang</th>
                        <th class="px-4 py-3 text-left">Stok</th>
                        <th class="px-4 py-3 text-left">Rata-rata Keluar</th>
                        <th class="px-4 py-3 text-left">Prediksi</th>
                        <th class="px-4 py-3 text-left">Saran Restock</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stockPredictions as $prediction)
                        @php
                            $predictionBadge = match ($prediction['status']) {
                                'out', 'urgent' => 'status-out',
                                'warning', 'low' => 'status-low',
                                default => 'status-safe',
                            };
                        @endphp
                        <tr class="border-b border-stone-100 dark:border-stone-900">
                            <td class="px-4 py-3">
                                <p class="font-medium">{{ $prediction['item']->name }}</p>
                                <p class="text-xs text-stone-500">{{ $prediction['item']->category?->name ?? 'Tanpa kategori' }}</p>
                            </td>
                            <td class="px-4 py-3">{{ $prediction['item']->stock }} {{ $prediction['item']->unit }}</td>
                            <td class="px-4 py-3">{{ number_format($prediction['avg_daily_usage'], 2, ',', '.') }} / hari</td>
                            <td class="px-4 py-3">
                                <span class="{{ $predictionBadge }}">{{ $prediction['status_label'] }}</span>
                                @if($prediction['estimated_empty_date'])
                                    <p class="mt-1 text-xs text-stone-500">± {{ $prediction['estimated_empty_date']->format('d M Y') }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $prediction['recommended_restock'] }} {{ $prediction['item']->unit }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-stone-500">Belum ada data stok keluar yang cukup untuk prediksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-surface p-5">
        <h3 class="text-lg font-semibold">Expired Warning</h3>
        <p class="mb-4 text-sm text-stone-500 dark:text-stone-400">Barang yang expired dalam 7 hari ke depan.</p>
        <div class="space-y-3">
            @forelse($expiredWarnings as $warning)
                <div class="rounded-2xl bg-stone-50 px-4 py-3 dark:bg-stone-800/80">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-medium">{{ $warning['item']->name }}</p>
                            <p class="text-sm text-stone-500 dark:text-stone-400">Stok {{ $warning['item']->stock }} {{ $warning['item']->unit }}</p>
                            @if($warning['related_menus'])
                                <p class="mt-1 text-xs text-stone-500">Menu: {{ $warning['related_menus'] }}</p>
                            @endif
                        </div>
                        <span class="{{ in_array($warning['status'], ['expired', 'urgent']) ? 'status-out' : 'status-low' }}">{{ $warning['status_label'] }}</span>
                    </div>
                </div>
            @empty
                <p class="text-sm text-stone-500 dark:text-stone-400">Aman. Tidak ada barang yang hampir expired.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="mt-6 card-surface p-5">
    <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h3 class="text-lg font-semibold">Smart Promo Generator</h3>
            <p class="text-sm text-stone-500 dark:text-stone-400">Rekomendasi promo dibuat dari bahan hampir expired atau stok tinggi yang bergerak lambat.</p>
        </div>
        <span class="status-low w-fit">Draft rekomendasi</span>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        @forelse($promoRecommendations as $promo)
            <div class="rounded-[24px] border border-stone-200 bg-stone-50 p-4 dark:border-stone-800 dark:bg-stone-800/80">
                <div class="mb-3 flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">{{ $promo['type'] === 'expired' ? 'Expired-based' : 'Overstock' }}</p>
                        <h4 class="mt-1 font-semibold">{{ $promo['title'] }}</h4>
                    </div>
                    <span class="status-safe">{{ $promo['discount_percent'] }}%</span>
                </div>
                <p class="text-sm text-stone-600 dark:text-stone-300">{{ $promo['reason'] }}</p>
                <p class="mt-3 rounded-2xl bg-white px-3 py-2 text-sm dark:bg-stone-900">{{ $promo['action'] }}</p>
                <p class="mt-3 text-xs text-stone-500">Prioritas: {{ $promo['priority'] }}</p>
            </div>
        @empty
            <div class="rounded-2xl bg-stone-50 px-4 py-6 text-center text-sm text-stone-500 dark:bg-stone-800/80 lg:col-span-3">
                Belum ada rekomendasi promo. Isi tanggal expired atau tambahkan data stok keluar supaya analisis lebih akurat.
            </div>
        @endforelse
    </div>
</div>

<div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="card-surface p-5 xl:col-span-2">
        <h3 class="text-lg font-semibold">Grafik Stok 7 Hari Terakhir</h3>
        <p class="mb-4 text-sm text-stone-500 dark:text-stone-400">Perbandingan stok masuk dan stok keluar.</p>
        <div class="h-80">
            <canvas id="stockChart"></canvas>
        </div>
    </div>

    <div class="card-surface p-5">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold">Menu Paling Sering Dipesan</h3>
            <span class="text-sm text-stone-500 dark:text-stone-400">Selesai</span>
        </div>
        <div class="space-y-3">
            @forelse($topMenus as $menu)
                <div class="rounded-2xl bg-stone-50 px-4 py-3 dark:bg-stone-800/80">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="font-medium">{{ $menu->name }}</p>
                            <p class="text-sm text-stone-500 dark:text-stone-400">{{ (int) ($menu->sold_qty ?? 0) }} porsi selesai</p>
                        </div>
                        <span class="{{ $menu->availability_status === 'available' ? 'status-safe' : ($menu->availability_status === 'low' ? 'status-low' : 'status-out') }}">{{ $menu->availability_label }}</span>
                    </div>
                </div>
            @empty
                <p class="text-sm text-stone-500 dark:text-stone-400">Belum ada data pesanan selesai.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
    <div class="card-surface p-5">
        <h3 class="text-lg font-semibold">Perlu Restock</h3>
        <div class="mt-4 space-y-3">
            @forelse($restockItems as $item)
                <div class="rounded-2xl bg-stone-50 px-4 py-3 dark:bg-stone-800/80">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-medium">{{ $item->name }}</p>
                            <p class="text-sm text-stone-500 dark:text-stone-400">Stok {{ $item->stock }} {{ $item->unit }}</p>
                        </div>
                        <span class="{{ $item->stock_status === 'out' ? 'status-out' : 'status-low' }}">
                            {{ $item->stock_status === 'out' ? 'Habis' : 'Menipis' }}
                        </span>
                    </div>
                </div>
            @empty
                <p class="text-sm text-stone-500 dark:text-stone-400">Belum ada barang yang perlu restock.</p>
            @endforelse
        </div>
    </div>

    <div class="card-surface p-5">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold">Pesanan Terbaru</h3>
            <a href="{{ route('orders.index') }}" class="btn-secondary">Kelola</a>
        </div>
        <div class="space-y-3">
            @forelse($recentOrders as $order)
                <div class="rounded-2xl bg-stone-50 px-4 py-3 dark:bg-stone-800/80">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-medium">{{ $order->order_code }}</p>
                                <span class="{{ $order->status_badge_class }}">{{ $order->status_label }}</span>
                            </div>
                            <p class="text-sm text-stone-500 dark:text-stone-400">{{ optional($order->items->first()?->menu)->name ?? 'Menu' }} · {{ $order->total_qty }} porsi</p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-amber-700">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                            <p class="text-xs text-stone-500 dark:text-stone-400">{{ optional($order->ordered_at)->format('d M H:i') }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-stone-500 dark:text-stone-400">Belum ada pesanan baru.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="mt-6 card-surface p-5">
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold">Riwayat Stok Terbaru</h3>
            <p class="text-sm text-stone-500 dark:text-stone-400">Update terbaru dari admin dan order pelanggan.</p>
        </div>
        <a href="{{ route('stock.history') }}" class="btn-secondary">Lihat semua</a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-stone-200 dark:border-stone-800">
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-left">Barang</th>
                    <th class="px-4 py-3 text-left">Tipe</th>
                    <th class="px-4 py-3 text-left">Qty</th>
                    <th class="px-4 py-3 text-left">User</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentMovements as $movement)
                    <tr class="border-b border-stone-100 dark:border-stone-900">
                        <td class="px-4 py-3">{{ optional($movement->movement_date)->format('d M Y') }}</td>
                        <td class="px-4 py-3">{{ $movement->item?->name }}</td>
                        <td class="px-4 py-3">
                            @if($movement->type === 'in')
                                <span class="status-safe">IN</span>
                            @elseif($movement->type === 'out')
                                <span class="status-out">OUT</span>
                            @else
                                <span class="status-low">ADJUST</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $movement->qty }}</td>
                        <td class="px-4 py-3">{{ $movement->user?->name }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-stone-500">Belum ada riwayat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
window.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('stockChart');
    if (!el) return;
    new Chart(el, {
        type: 'line',
        data: {
            labels: @json($labels),
            datasets: [
                { label: 'Stok Masuk', data: @json($inSeries), borderColor: '#16a34a', backgroundColor: 'rgba(22,163,74,0.15)', tension: 0.35, fill: true },
                { label: 'Stok Keluar', data: @json($outSeries), borderColor: '#dc2626', backgroundColor: 'rgba(220,38,38,0.08)', tension: 0.35, fill: true }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
});
</script>
@endsection
