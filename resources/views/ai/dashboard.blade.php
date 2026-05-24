@extends('layouts.app')
@section('title', 'AI Dashboard')
@section('page-title', 'AI Dashboard')
@section('content')
@php
    $model = data_get($dashboard, 'model', []);
    $kpis = data_get($dashboard, 'kpis', []);
    $stockPredictions = data_get($dashboard, 'stock_predictions', []);
    $clusterSummary = data_get($dashboard, 'menu_clusters.summary', []);
    $clusterItems = data_get($dashboard, 'menu_clusters.items', []);
    $promoItems = data_get($dashboard, 'smart_promos.items', []);
    $peakInsights = data_get($dashboard, 'peak_hours.insights', []);
    $timeBlocks = data_get($dashboard, 'peak_hours.time_blocks', []);
    $topHours = data_get($dashboard, 'peak_hours.top_hours', []);
@endphp

<div class="mb-6 flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
    <div>
        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-amber-600">Machine Learning Microservice</p>
        <h2 class="mt-2 text-3xl font-bold">Cafe Hagi AI Command Center</h2>
        <p class="mt-2 max-w-3xl text-sm text-stone-500 dark:text-stone-400">
            Dashboard ini mengambil insight dari FastAPI AI Engine: Random Forest stock-out forecasting, K-Means menu clustering, smart promo recommendation, dan peak-hour analyzer.
        </p>
    </div>

    <form method="GET" class="card-surface grid gap-3 p-4 sm:grid-cols-3 xl:w-[520px]">
        <div>
            <label class="mb-1 block text-xs font-medium text-stone-500 dark:text-stone-400">Default Stock</label>
            <input type="number" name="default_stock" value="{{ $defaultStock }}" min="1" max="100000" class="input-ui py-2">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-stone-500 dark:text-stone-400">Forecast Days</label>
            <input type="number" name="forecast_days" value="{{ $forecastDays }}" min="1" max="90" class="input-ui py-2">
        </div>
        <div class="flex items-end">
            <button class="btn-primary w-full py-2">Refresh AI</button>
        </div>
    </form>
</div>

@if (! $aiOnline)
    <div class="mb-6 rounded-3xl border border-red-200 bg-red-50 p-5 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/40 dark:text-red-200">
        <p class="font-semibold">AI Engine offline atau belum siap.</p>
        <p class="mt-1">{{ $errorMessage ?? 'Pastikan FastAPI berjalan di http://127.0.0.1:8001.' }}</p>
        <code class="mt-3 block rounded-2xl bg-white px-4 py-3 text-xs text-red-900 dark:bg-stone-950 dark:text-red-100">cd ai-engine &amp;&amp; .\.venv\Scripts\Activate.ps1 &amp;&amp; uvicorn app.main:app --reload --host 127.0.0.1 --port 8001</code>
    </div>
@else
    <div class="mb-6 rounded-3xl border border-green-200 bg-green-50 p-5 text-sm text-green-800 dark:border-green-900 dark:bg-green-950/40 dark:text-green-200">
        <p class="font-semibold">AI Engine connected.</p>
        <p class="mt-1">Model Random Forest dan insight service berhasil dibaca dari FastAPI.</p>
    </div>
@endif

<div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
    <div class="card-surface p-5">
        <p class="text-sm text-stone-500 dark:text-stone-400">Model R²</p>
        <h3 class="mt-2 text-3xl font-bold text-green-600">{{ data_get($model, 'r2') !== null ? number_format((float) data_get($model, 'r2'), 4, ',', '.') : '-' }}</h3>
        <p class="mt-1 text-xs text-stone-500">Random Forest score</p>
    </div>
    <div class="card-surface p-5">
        <p class="text-sm text-stone-500 dark:text-stone-400">Menu Dianalisis</p>
        <h3 class="mt-2 text-3xl font-bold">{{ data_get($kpis, 'menus_analyzed', 0) }}</h3>
        <p class="mt-1 text-xs text-stone-500">Synthetic sales dataset</p>
    </div>
    <div class="card-surface p-5">
        <p class="text-sm text-stone-500 dark:text-stone-400">Critical Stock</p>
        <h3 class="mt-2 text-3xl font-bold text-red-600">{{ data_get($kpis, 'critical_stock_count', 0) }}</h3>
        <p class="mt-1 text-xs text-stone-500">Habis ≤ 7 hari</p>
    </div>
    <div class="card-surface p-5">
        <p class="text-sm text-stone-500 dark:text-stone-400">Promo AI</p>
        <h3 class="mt-2 text-3xl font-bold text-amber-600">{{ data_get($kpis, 'promo_recommendation_count', 0) }}</h3>
        <p class="mt-1 text-xs text-stone-500">Smart recommendation</p>
    </div>
    <div class="card-surface p-5">
        <p class="text-sm text-stone-500 dark:text-stone-400">Cluster</p>
        <h3 class="mt-2 text-3xl font-bold text-sky-600">{{ data_get($kpis, 'cluster_count', 0) }}</h3>
        <p class="mt-1 text-xs text-stone-500">K-Means groups</p>
    </div>
</div>

<div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="card-surface p-5 xl:col-span-2">
        <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h3 class="text-lg font-semibold">AI Stock-out Forecast</h3>
                <p class="text-sm text-stone-500 dark:text-stone-400">Prediksi Random Forest berdasarkan default stock {{ $defaultStock }} dan forecast {{ $forecastDays }} hari.</p>
            </div>
            <span class="status-safe w-fit">Random Forest</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-stone-200 dark:border-stone-800">
                        <th class="px-4 py-3 text-left">Menu</th>
                        <th class="px-4 py-3 text-left">Stok</th>
                        <th class="px-4 py-3 text-left">Prediksi Habis</th>
                        <th class="px-4 py-3 text-left">Risk</th>
                        <th class="px-4 py-3 text-left">Sisa Akhir</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stockPredictions as $prediction)
                        @php
                            $risk = data_get($prediction, 'risk_level', 'safe');
                            $badge = match ($risk) {
                                'critical', 'error' => 'status-out',
                                'warning', 'watch' => 'status-low',
                                default => 'status-safe',
                            };
                        @endphp
                        <tr class="border-b border-stone-100 dark:border-stone-900">
                            <td class="px-4 py-3 font-medium">{{ data_get($prediction, 'menu_name') }}</td>
                            <td class="px-4 py-3">{{ data_get($prediction, 'current_stock') }}</td>
                            <td class="px-4 py-3">
                                <p>{{ data_get($prediction, 'predicted_stock_out_date') ?? 'Tidak habis' }}</p>
                                <p class="text-xs text-stone-500">{{ data_get($prediction, 'days_until_stock_out') ? data_get($prediction, 'days_until_stock_out') . ' hari' : 'Di luar window' }}</p>
                            </td>
                            <td class="px-4 py-3"><span class="{{ $badge }}">{{ strtoupper($risk) }}</span></td>
                            <td class="px-4 py-3">{{ data_get($prediction, 'remaining_stock_after_forecast', '-') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-stone-500">Belum ada prediksi. Pastikan AI Engine online.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-surface p-5">
        <h3 class="text-lg font-semibold">Model Quality</h3>
        <p class="mb-4 text-sm text-stone-500 dark:text-stone-400">Ringkasan training Random Forest demand forecasting.</p>
        <div class="space-y-3 text-sm">
            <div class="rounded-2xl bg-stone-50 p-4 dark:bg-stone-800/80">
                <p class="text-stone-500 dark:text-stone-400">MAE</p>
                <p class="text-xl font-bold">{{ data_get($model, 'mae') !== null ? number_format((float) data_get($model, 'mae'), 4, ',', '.') : '-' }}</p>
            </div>
            <div class="rounded-2xl bg-stone-50 p-4 dark:bg-stone-800/80">
                <p class="text-stone-500 dark:text-stone-400">RMSE</p>
                <p class="text-xl font-bold">{{ data_get($model, 'rmse') !== null ? number_format((float) data_get($model, 'rmse'), 4, ',', '.') : '-' }}</p>
            </div>
            <div class="rounded-2xl bg-stone-50 p-4 dark:bg-stone-800/80">
                <p class="text-stone-500 dark:text-stone-400">Rows</p>
                <p class="text-xl font-bold">{{ data_get($model, 'daily_menu_rows', '-') }}</p>
                <p class="text-xs text-stone-500">Train {{ data_get($model, 'train_rows', '-') }} / Test {{ data_get($model, 'test_rows', '-') }}</p>
            </div>
        </div>
    </div>
</div>

<div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="card-surface p-5 xl:col-span-2">
        <div class="mb-4 flex items-center justify-between gap-3">
            <div>
                <h3 class="text-lg font-semibold">K-Means Menu Clustering</h3>
                <p class="text-sm text-stone-500 dark:text-stone-400">Cluster performa menu berdasarkan demand, revenue, active days, coffee rush, weekend uplift, dan price.</p>
            </div>
            <span class="status-safe">K-Means</span>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            @forelse($clusterSummary as $cluster)
                <div class="rounded-[24px] border border-stone-200 bg-stone-50 p-4 dark:border-stone-800 dark:bg-stone-800/80">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Cluster {{ data_get($cluster, 'cluster_id') }}</p>
                            <h4 class="mt-1 font-semibold">{{ data_get($cluster, 'cluster_name') }}</h4>
                        </div>
                        <span class="status-muted">{{ data_get($cluster, 'menu_count') }} menu</span>
                    </div>
                    <p class="mt-3 text-sm text-stone-600 dark:text-stone-300">{{ data_get($cluster, 'description') }}</p>
                    <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-2xl bg-white p-3 dark:bg-stone-950">
                            <p class="text-xs text-stone-500">Avg daily</p>
                            <p class="font-semibold">{{ data_get($cluster, 'avg_daily_qty') }}</p>
                        </div>
                        <div class="rounded-2xl bg-white p-3 dark:bg-stone-950">
                            <p class="text-xs text-stone-500">Revenue</p>
                            <p class="font-semibold">Rp{{ number_format((float) data_get($cluster, 'total_revenue', 0), 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl bg-stone-50 px-4 py-6 text-center text-sm text-stone-500 dark:bg-stone-800/80 md:col-span-2">Belum ada cluster.</div>
            @endforelse
        </div>
    </div>

    <div class="card-surface p-5">
        <h3 class="text-lg font-semibold">Clustered Menu</h3>
        <p class="mb-4 text-sm text-stone-500 dark:text-stone-400">Top menu per cluster.</p>
        <div class="space-y-3">
            @forelse(array_slice($clusterItems, 0, 8) as $item)
                <div class="rounded-2xl bg-stone-50 px-4 py-3 dark:bg-stone-800/80">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-medium">{{ data_get($item, 'menu_name') }}</p>
                            <p class="text-xs text-stone-500">{{ data_get($item, 'cluster_name') }}</p>
                        </div>
                        <span class="status-muted">{{ data_get($item, 'avg_daily_qty') }}/hari</span>
                    </div>
                </div>
            @empty
                <p class="text-sm text-stone-500">Belum ada item cluster.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="mt-6 card-surface p-5">
    <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h3 class="text-lg font-semibold">Smart Promo ML</h3>
            <p class="text-sm text-stone-500 dark:text-stone-400">Rekomendasi promo dari cluster performa, demand pattern, dan peak-hour context.</p>
        </div>
        <span class="status-low w-fit">Recommendation Layer</span>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
        @forelse($promoItems as $promo)
            <div class="rounded-[24px] border border-stone-200 bg-stone-50 p-4 dark:border-stone-800 dark:bg-stone-800/80">
                <div class="mb-3 flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">{{ str_replace('_', ' ', data_get($promo, 'promo_type')) }}</p>
                        <h4 class="mt-1 font-semibold">{{ data_get($promo, 'menu_name') }}</h4>
                    </div>
                    <span class="status-safe">{{ data_get($promo, 'recommended_discount_percent') }}%</span>
                </div>
                <p class="text-sm text-stone-600 dark:text-stone-300">{{ data_get($promo, 'reason') }}</p>
                <p class="mt-3 rounded-2xl bg-white px-3 py-2 text-sm dark:bg-stone-900">{{ data_get($promo, 'action') }}</p>
                <div class="mt-3 flex items-center justify-between text-xs text-stone-500">
                    <span>Priority: {{ data_get($promo, 'priority') }}</span>
                    <span>{{ data_get($promo, 'suggested_window') }}</span>
                </div>
            </div>
        @empty
            <div class="rounded-2xl bg-stone-50 px-4 py-6 text-center text-sm text-stone-500 dark:bg-stone-800/80 lg:col-span-4">Belum ada rekomendasi promo.</div>
        @endforelse
    </div>
</div>

<div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="card-surface p-5 xl:col-span-2">
        <h3 class="text-lg font-semibold">Peak Hour Analyzer</h3>
        <p class="mb-4 text-sm text-stone-500 dark:text-stone-400">Analisis jam ramai dan coffee lunch rush dari synthetic sales history.</p>

        <div class="space-y-4">
            @forelse($timeBlocks as $block)
                @php
                    $qty = (float) data_get($block, 'total_qty', 0);
                    $maxQty = max(array_map(fn ($item) => (float) data_get($item, 'total_qty', 0), $timeBlocks ?: [['total_qty' => 1]]));
                    $width = $maxQty > 0 ? min(100, ($qty / $maxQty) * 100) : 0;
                @endphp
                <div>
                    <div class="mb-1 flex items-center justify-between text-sm">
                        <span class="font-medium">{{ str_replace('_', ' ', data_get($block, 'time_block')) }}</span>
                        <span>{{ data_get($block, 'total_qty') }} qty / Coffee {{ data_get($block, 'coffee_share') }}%</span>
                    </div>
                    <div class="h-3 overflow-hidden rounded-full bg-stone-100 dark:bg-stone-800">
                        <div class="h-full rounded-full bg-amber-500" style="width: {{ $width }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-stone-500">Belum ada data time block.</p>
            @endforelse
        </div>
    </div>

    <div class="card-surface p-5">
        <h3 class="text-lg font-semibold">Insight Operasional</h3>
        <div class="mt-4 space-y-3">
            @forelse($peakInsights as $insight)
                <div class="rounded-2xl bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:bg-amber-950/50 dark:text-amber-100">{{ $insight }}</div>
            @empty
                <p class="text-sm text-stone-500">Belum ada insight.</p>
            @endforelse
        </div>

        <h4 class="mt-6 font-semibold">Top Hours</h4>
        <div class="mt-3 space-y-2">
            @forelse(array_slice($topHours, 0, 5) as $hour)
                <div class="flex items-center justify-between rounded-2xl bg-stone-50 px-4 py-3 text-sm dark:bg-stone-800/80">
                    <span>{{ data_get($hour, 'hour_label') }}</span>
                    <span class="font-semibold">{{ data_get($hour, 'total_qty') }} qty</span>
                </div>
            @empty
                <p class="text-sm text-stone-500">Belum ada data jam.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
