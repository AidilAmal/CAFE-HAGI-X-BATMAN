@extends('layouts.public')

@section('title', 'Cafe Hagi X Batman Pools')

@section('content')
<section class="relative overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,#f59e0b_0%,transparent_28%),radial-gradient(circle_at_bottom_left,#292524_0%,transparent_35%)] opacity-20"></div>
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-16 md:px-6 lg:grid-cols-[1.1fr_0.9fr] lg:py-24">
        <div class="relative z-10 flex flex-col justify-center">
            <span class="mb-4 w-fit rounded-full bg-amber-100 px-4 py-2 text-sm font-medium text-amber-700 dark:bg-amber-950 dark:text-amber-300">Simple cafe, modern order experience</span>
            <h1 class="text-5xl font-black leading-tight text-stone-900 dark:text-white md:text-6xl">
                Menu, order, dan stok cafe yang terasa lebih hidup.
            </h1>
            <p class="mt-5 max-w-xl text-lg text-stone-600 dark:text-stone-300">
                Pelanggan bisa lihat detail menu dan langsung order. Admin langsung tahu bahan mana yang menipis karena stok sinkron otomatis dengan resep tiap menu.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('public.menu') }}" class="btn-primary">Lihat Menu</a>
                <a href="{{ route('login') }}" class="btn-secondary">Masuk Admin</a>
                <button data-install-app class="btn-secondary">Install App</button>
            </div>
        </div>

        <div class="relative z-10 grid gap-4 md:grid-cols-2">
            <div class="hero-cafe-card p-6 md:col-span-2">
                <div class="rounded-[24px] bg-[linear-gradient(135deg,#111827_0%,#292524_55%,#92400e_100%)] p-8 text-white">
                    <p class="text-sm uppercase tracking-[0.3em] text-amber-200">Cafe Atmosphere</p>
                    <h3 class="mt-4 text-3xl font-bold">Hangat, premium, dan nyaman buat nongkrong.</h3>
                    <p class="mt-3 max-w-md text-sm text-stone-200">Hero section ini sengaja fokus ke vibe cafe, jadi foto menu cukup tampil di menu unggulan dan halaman menu saja.</p>
                </div>
            </div>
            <div class="hero-cafe-card p-6">
                <div class="rounded-[24px] bg-[linear-gradient(135deg,#1c1917_0%,#44403c_100%)] p-6 text-white">
                    <p class="text-sm text-stone-300">Area indoor</p>
                    <h4 class="mt-8 text-2xl font-semibold">Coffee bar yang clean dan modern.</h4>
                </div>
            </div>
            <div class="hero-cafe-card p-6">
                <div class="rounded-[24px] bg-[linear-gradient(135deg,#78350f_0%,#1c1917_100%)] p-6 text-white">
                    <p class="text-sm text-stone-300">Area dining</p>
                    <h4 class="mt-8 text-2xl font-semibold">Nyaman untuk makan dan santai bareng teman.</h4>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-14 md:px-6">
    <div class="mb-8 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <h2 class="text-3xl font-bold">6 Menu Unggulan</h2>
            <p class="mt-2 text-stone-600 dark:text-stone-400">Best seller cafe dengan status sinkron ke stok bahan dan bisa diklik ke halaman detail order.</p>
        </div>
        <a href="{{ route('public.menu') }}" class="btn-secondary">Lihat Semua Menu</a>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($featuredMenus as $menu)
            <a href="{{ route('public.menu.show', $menu) }}" class="group overflow-hidden rounded-[30px] border border-stone-200 bg-white shadow-sm transition duration-300 hover:-translate-y-2 hover:shadow-2xl dark:border-stone-800 dark:bg-stone-900">
                <div class="h-64 overflow-hidden bg-stone-100 dark:bg-stone-800">
                    @if($menu->image_url)
                        <img src="{{ $menu->image_url }}" alt="{{ $menu->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-110">
                    @else
                        <div class="flex h-full items-center justify-center text-5xl">🍽️</div>
                    @endif
                </div>
                <div class="p-6">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <h3 class="text-xl font-semibold">{{ $menu->name }}</h3>
                        @if($menu->availability_status === 'available')
                            <span class="status-safe">Tersedia</span>
                        @elseif($menu->availability_status === 'low')
                            <span class="status-low">Hampir Habis</span>
                        @else
                            <span class="status-out">Habis</span>
                        @endif
                    </div>
                    <p class="text-sm text-stone-600 dark:text-stone-400">{{ $menu->description }}</p>
                    <div class="mt-4 flex items-center justify-between">
                        <p class="text-xl font-bold text-amber-700">Rp {{ number_format($menu->price, 0, ',', '.') }}</p>
                        <span class="text-sm text-stone-500 dark:text-stone-400">{{ (int) ($menu->sold_qty ?? 0) }} terjual</span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</section>
@endsection
