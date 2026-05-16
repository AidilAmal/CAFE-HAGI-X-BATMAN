<!DOCTYPE html>
<html lang="id" class="min-h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen md:flex">
        <aside class="hidden w-72 flex-col bg-stone-900 p-6 text-white dark:bg-black md:flex">
            <div class="mb-8 flex items-center gap-3">
                <img src="{{ asset('assets/logo-mark.png') }}" alt="Logo" class="h-12 w-12 rounded-2xl">
                <div>
                    <p class="text-lg font-bold">Cafe Hagi</p>
                    <p class="text-sm text-stone-300">Admin Dashboard</p>
                </div>
            </div>
            <nav class="space-y-2 text-sm">
                <a href="{{ route('dashboard') }}" class="block rounded-2xl px-4 py-3 hover:bg-white/10">Dashboard</a>
                <a href="{{ route('items.index') }}" class="block rounded-2xl px-4 py-3 hover:bg-white/10">Barang</a>
                <a href="{{ route('categories.index') }}" class="block rounded-2xl px-4 py-3 hover:bg-white/10">Kategori Barang</a>
                <a href="{{ route('suppliers.index') }}" class="block rounded-2xl px-4 py-3 hover:bg-white/10">Supplier</a>
                <a href="{{ route('stock.history') }}" class="block rounded-2xl px-4 py-3 hover:bg-white/10">Riwayat Stok</a>
                <a href="{{ route('menu-categories.index') }}" class="block rounded-2xl px-4 py-3 hover:bg-white/10">Kategori Menu</a>
                <a href="{{ route('menus.index') }}" class="block rounded-2xl px-4 py-3 hover:bg-white/10">Menu Cafe</a>
                <a href="{{ route('orders.index') }}" class="block rounded-2xl px-4 py-3 hover:bg-white/10">Riwayat Pesanan</a>
                <a href="{{ route('reports.index') }}" class="block rounded-2xl px-4 py-3 hover:bg-white/10">Laporan</a>
            </nav>
            <div class="mt-auto rounded-3xl bg-white/10 p-4">
                <p class="text-sm">Login sebagai</p>
                <p class="mt-1 font-semibold">{{ auth()->user()->name }}</p>
                <p class="text-xs uppercase tracking-[0.2em] text-stone-300">{{ auth()->user()->role }}</p>
            </div>
        </aside>

        <div class="flex-1">
            <header class="border-b border-stone-200 bg-white/90 backdrop-blur dark:border-stone-800 dark:bg-stone-950/90">
                <div class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-4 md:px-6">
                    <div class="flex items-center gap-3">
                        <button data-mobile-toggle="#mobile-admin-nav" class="btn-secondary md:hidden">☰</button>
                        <div>
                            <h1 class="text-xl font-semibold">@yield('page-title', 'Dashboard')</h1>
                            <p class="text-sm text-stone-500 dark:text-stone-400">Cafe Hagi X Batman Pools</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <button data-theme-toggle class="btn-secondary">Dark Mode</button>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="btn-primary">Logout</button>
                        </form>
                    </div>
                </div>

                <div id="mobile-admin-nav" class="hidden border-t border-stone-200 px-4 py-4 dark:border-stone-800 md:hidden">
                    <div class="grid gap-2">
                        <a href="{{ route('dashboard') }}" class="rounded-2xl px-4 py-3 hover:bg-stone-100 dark:hover:bg-stone-900">Dashboard</a>
                        <a href="{{ route('items.index') }}" class="rounded-2xl px-4 py-3 hover:bg-stone-100 dark:hover:bg-stone-900">Barang</a>
                        <a href="{{ route('categories.index') }}" class="rounded-2xl px-4 py-3 hover:bg-stone-100 dark:hover:bg-stone-900">Kategori Barang</a>
                        <a href="{{ route('suppliers.index') }}" class="rounded-2xl px-4 py-3 hover:bg-stone-100 dark:hover:bg-stone-900">Supplier</a>
                        <a href="{{ route('stock.history') }}" class="rounded-2xl px-4 py-3 hover:bg-stone-100 dark:hover:bg-stone-900">Riwayat Stok</a>
                        <a href="{{ route('menu-categories.index') }}" class="rounded-2xl px-4 py-3 hover:bg-stone-100 dark:hover:bg-stone-900">Kategori Menu</a>
                        <a href="{{ route('menus.index') }}" class="rounded-2xl px-4 py-3 hover:bg-stone-100 dark:hover:bg-stone-900">Menu Cafe</a>
                        <a href="{{ route('orders.index') }}" class="rounded-2xl px-4 py-3 hover:bg-stone-100 dark:hover:bg-stone-900">Riwayat Pesanan</a>
                        <a href="{{ route('reports.index') }}" class="rounded-2xl px-4 py-3 hover:bg-stone-100 dark:hover:bg-stone-900">Laporan</a>
                    </div>
                </div>
            </header>

            <main class="mx-auto max-w-7xl px-4 py-6 md:px-6">
                @if (session('success'))
                    <div class="mb-4 rounded-2xl bg-green-100 px-4 py-3 text-green-700 dark:bg-green-950 dark:text-green-300">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 rounded-2xl bg-red-100 px-4 py-3 text-red-700 dark:bg-red-950 dark:text-red-300">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
