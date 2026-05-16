@extends('layouts.public')

@section('title', 'Daftar Menu')

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 md:px-6">
    <div class="mb-8 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-4xl font-black">Our Menu</h1>
            <p class="mt-2 text-stone-600 dark:text-stone-400">Klik salah satu menu buat lihat detail lengkap, resep singkat, dan langsung order.</p>
        </div>
        <button data-install-app class="btn-secondary">Install App</button>
    </div>

    <form method="GET" class="mb-8 grid grid-cols-1 gap-4 rounded-[28px] border border-stone-200 bg-white p-5 shadow-sm dark:border-stone-800 dark:bg-stone-900 md:grid-cols-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari menu..." class="input-ui">
        <select name="category" class="input-ui">
            <option value="">Semua kategori</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <button class="btn-primary">Filter Menu</button>
    </form>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @forelse($menus as $menu)
            <a href="{{ route('public.menu.show', $menu) }}" class="group overflow-hidden rounded-[28px] border border-stone-200 bg-white shadow-sm transition duration-300 hover:-translate-y-2 hover:shadow-2xl dark:border-stone-800 dark:bg-stone-900">
                <div class="relative h-56 overflow-hidden bg-stone-100 dark:bg-stone-800">
                    @if($menu->image_url)
                        <img src="{{ $menu->image_url }}" alt="{{ $menu->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                    @else
                        <div class="flex h-full items-center justify-center text-4xl">☕</div>
                    @endif
                    <div class="absolute left-4 top-4 rounded-full bg-black/60 px-3 py-1 text-xs font-medium text-white backdrop-blur">Klik untuk detail</div>
                </div>
                <div class="p-5">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <h3 class="text-lg font-semibold">{{ $menu->name }}</h3>
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
                        <span class="text-sm text-stone-500 dark:text-stone-400">{{ $menu->category?->name }}</span>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full card-surface p-10 text-center text-stone-500">Menu belum tersedia.</div>
        @endforelse
    </div>

    <div class="mt-8">{{ $menus->links() }}</div>
</section>
@endsection
