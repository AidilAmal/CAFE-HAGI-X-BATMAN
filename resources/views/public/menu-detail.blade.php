@extends('layouts.public')

@section('title', $menu->name . ' - Detail Menu')

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 md:px-6">
    <div class="mb-6 flex flex-wrap items-center gap-3 text-sm text-stone-500 dark:text-stone-400">
        <a href="{{ route('home') }}" class="hover:text-amber-700">Home</a>
        <span>/</span>
        <a href="{{ route('public.menu') }}" class="hover:text-amber-700">Menu</a>
        <span>/</span>
        <span>{{ $menu->name }}</span>
    </div>

    <div class="grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
        <div class="overflow-hidden rounded-[34px] border border-stone-200 bg-white shadow-sm dark:border-stone-800 dark:bg-stone-900">
            <div class="h-[420px] overflow-hidden bg-stone-100 dark:bg-stone-800">
                @if($menu->image_url)
                    <img src="{{ $menu->image_url }}" alt="{{ $menu->name }}" class="h-full w-full object-cover">
                @else
                    <div class="flex h-full items-center justify-center text-6xl">🍽️</div>
                @endif
            </div>
            <div class="p-6">
                <div class="mb-4 flex flex-wrap items-center gap-3">
                    <span class="rounded-full bg-stone-100 px-3 py-1 text-sm dark:bg-stone-800">{{ $menu->category?->name ?? 'Menu Cafe' }}</span>
                    <span class="{{ $menu->availability_status === 'available' ? 'status-safe' : ($menu->availability_status === 'low' ? 'status-low' : 'status-out') }}">{{ $menu->availability_label }}</span>
                </div>
                <h1 class="text-4xl font-black">{{ $menu->name }}</h1>
                <p class="mt-4 text-lg text-stone-600 dark:text-stone-300">{{ $menu->description }}</p>
                <p class="mt-6 text-3xl font-bold text-amber-700">Rp {{ number_format($menu->price, 0, ',', '.') }}</p>

                @if($menu->recipe_notes)
                    <div class="mt-8 rounded-[28px] border border-amber-200 bg-amber-50 p-5 dark:border-amber-900 dark:bg-amber-950/30">
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-amber-700 dark:text-amber-300">Catatan Resep</p>
                        <p class="mt-3 whitespace-pre-line text-sm text-stone-700 dark:text-stone-200">{{ $menu->recipe_notes }}</p>
                    </div>
                @endif

                <div class="mt-8 rounded-[28px] border border-stone-200 bg-stone-50 p-5 dark:border-stone-800 dark:bg-stone-950">
                    <h2 class="text-xl font-semibold">Komposisi Bahan</h2>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach($menu->ingredients as $ingredient)
                            <div class="rounded-2xl border border-stone-200 bg-white px-4 py-3 dark:border-stone-800 dark:bg-stone-900">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="font-medium">{{ $ingredient->name }}</p>
                                        <p class="text-sm text-stone-500 dark:text-stone-400">{{ $ingredient->pivot->qty_required }} {{ $ingredient->unit }} / porsi</p>
                                    </div>
                                    <span class="{{ $ingredient->stock_status === 'safe' ? 'status-safe' : ($ingredient->stock_status === 'low' ? 'status-low' : 'status-out') }}">
                                        {{ $ingredient->stock_status === 'safe' ? 'Aman' : ($ingredient->stock_status === 'low' ? 'Menipis' : 'Habis') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="order-summary-card">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-stone-500 dark:text-stone-400">Quick Order</p>
                <h2 class="mt-3 text-2xl font-bold">Pesan {{ $menu->name }}</h2>
                <p class="mt-2 text-sm text-stone-600 dark:text-stone-400">Pesanan akan masuk ke admin terlebih dahulu. Stok bahan baru berkurang saat admin menandai pesanan sebagai selesai.</p>

                @if ($errors->any())
                    <div class="mt-4 rounded-2xl bg-red-100 px-4 py-3 text-sm text-red-700 dark:bg-red-950 dark:text-red-300">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('public.menu.order', $menu) }}" method="POST" class="mt-6 space-y-5">
                    @csrf
                    <div>
                        <label class="mb-2 block text-sm font-medium">Nama Pemesan (opsional)</label>
                        <input type="text" name="customer_name" value="{{ old('customer_name') }}" class="input-ui" placeholder="Contoh: Aidil">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Jumlah Porsi</label>
                        <div data-qty-group class="flex items-center gap-3">
                            <button type="button" data-qty-minus class="qty-btn">−</button>
                            <input type="number" name="qty" value="{{ old('qty', 1) }}" min="1" max="20" data-qty-input class="input-ui text-center text-lg font-semibold">
                            <button type="button" data-qty-plus class="qty-btn">+</button>
                        </div>
                    </div>

                    <div class="rounded-[28px] bg-stone-50 p-4 dark:bg-stone-950">
                        <div class="flex items-center justify-between text-sm text-stone-500 dark:text-stone-400">
                            <span>Status menu saat ini</span>
                            <span>{{ $menu->availability_label }}</span>
                        </div>
                        <div class="mt-2 flex items-center justify-between text-xl font-bold">
                            <span>Harga / porsi</span>
                            <span class="text-amber-700">Rp {{ number_format($menu->price, 0, ',', '.') }}</span>
                        </div>
                        <div class="mt-2 flex items-center justify-between text-sm text-stone-500 dark:text-stone-400">
                            <span>Estimasi total</span>
                            <span data-total-target data-price="{{ (int) $menu->price }}" class="font-semibold text-stone-900 dark:text-stone-100">Rp {{ number_format($menu->price, 0, ',', '.') }}</span>
                        </div>
                        <div class="mt-3 rounded-2xl bg-white/70 px-4 py-3 text-xs text-stone-500 dark:bg-stone-900/60 dark:text-stone-400">
                            Setelah dikirim, pesanan masuk status <strong>Pending</strong> → admin ubah ke <strong>Diproses</strong> → saat <strong>Selesai</strong> stok bahan otomatis berkurang.
                        </div>
                    </div>

                    <button class="btn-primary w-full text-lg {{ $menu->availability_status === 'out' ? 'pointer-events-none opacity-60' : '' }}">
                        {{ $menu->availability_status === 'out' ? 'Menu Sedang Habis' : 'Kirim Pesanan' }}
                    </button>
                </form>
            </div>

            <div class="order-summary-card">
                <h3 class="text-xl font-semibold">Menu Lain yang Serupa</h3>
                <div class="mt-4 space-y-3">
                    @forelse($relatedMenus as $related)
                        <a href="{{ route('public.menu.show', $related) }}" class="flex items-center gap-3 rounded-2xl border border-stone-200 p-3 transition hover:bg-stone-50 dark:border-stone-800 dark:hover:bg-stone-950">
                            <div class="h-16 w-16 overflow-hidden rounded-2xl bg-stone-100 dark:bg-stone-800">
                                @if($related->image_url)
                                    <img src="{{ $related->image_url }}" alt="{{ $related->name }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full items-center justify-center">☕</div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <p class="font-medium">{{ $related->name }}</p>
                                <p class="text-sm text-stone-500 dark:text-stone-400">Rp {{ number_format($related->price, 0, ',', '.') }}</p>
                            </div>
                            <span class="text-sm text-amber-700">Lihat →</span>
                        </a>
                    @empty
                        <p class="text-sm text-stone-500 dark:text-stone-400">Belum ada menu terkait lainnya.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>

@if(session('order_success'))
    <div id="order-success-modal" class="fixed inset-0 z-[90] flex items-center justify-center bg-black/60 px-4">
        <div class="w-full max-w-md rounded-[32px] border border-stone-200 bg-white p-7 shadow-2xl dark:border-stone-800 dark:bg-stone-900">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100 text-3xl dark:bg-green-950">✅</div>
            <h3 class="mt-5 text-center text-2xl font-bold">Pesanan Terkirim</h3>
            <p class="mt-2 text-center text-stone-600 dark:text-stone-400">Pesananmu sudah masuk ke admin dan menunggu diproses. Stok bahan belum berkurang sampai admin menandai pesanan selesai.</p>
            <div class="mt-6 rounded-[24px] bg-stone-50 p-4 text-sm dark:bg-stone-950">
                <div class="flex items-center justify-between"><span>Kode Order</span><span class="font-semibold">{{ session('order_success.code') }}</span></div>
                <div class="mt-2 flex items-center justify-between"><span>Menu</span><span class="font-semibold">{{ session('order_success.menu') }}</span></div>
                <div class="mt-2 flex items-center justify-between"><span>Jumlah</span><span class="font-semibold">{{ session('order_success.qty') }} porsi</span></div>
                <div class="mt-2 flex items-center justify-between"><span>Status</span><span class="status-pending">{{ session('order_success.status') }}</span></div>
                <div class="mt-2 flex items-center justify-between"><span>Total</span><span class="font-semibold text-amber-700">Rp {{ number_format(session('order_success.amount'), 0, ',', '.') }}</span></div>
            </div>
            <div class="mt-6 flex gap-3">
                <button data-modal-close="#order-success-modal" class="btn-secondary flex-1">Tutup</button>
                <a href="{{ route('public.menu') }}" class="btn-primary flex-1 text-center">Lihat Menu Lain</a>
            </div>
        </div>
    </div>
@endif

<script>
window.addEventListener('DOMContentLoaded', function () {
    const qtyInput = document.querySelector('[data-qty-input]');
    const totalTarget = document.querySelector('[data-total-target]');
    if (!qtyInput || !totalTarget) return;

    const price = parseInt(totalTarget.dataset.price || '0', 10);
    const formatter = new Intl.NumberFormat('id-ID');

    function updateTotal() {
        const qty = Math.max(parseInt(qtyInput.value || '1', 10), 1);
        totalTarget.textContent = 'Rp ' + formatter.format(price * qty);
    }

    qtyInput.addEventListener('change', updateTotal);
    qtyInput.addEventListener('keyup', updateTotal);
    updateTotal();
});
</script>
@endsection
