<!DOCTYPE html>
<html lang="id" class="min-h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Cafe Hagi X Batman Pools')</title>
    <meta name="theme-color" content="#1c1917">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Cafe Hagi">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('assets/pwa/icon-192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/pwa/apple-touch-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f8f5f0] dark:bg-stone-950">
    <div id="page-splash" class="fixed inset-0 z-[100] flex items-center justify-center bg-stone-950 text-white transition duration-500">
        <div class="text-center">
            <img src="{{ asset('assets/logo-mark.png') }}" alt="Logo" class="mx-auto mb-4 h-24 w-24 rounded-[28px] shadow-2xl">
            <h1 class="text-3xl font-bold">Cafe Hagi X Batman Pools</h1>
            <p class="mt-2 text-stone-300">Loading your cafe experience...</p>
        </div>
    </div>

    <nav class="sticky top-0 z-50 border-b border-stone-200 bg-white/90 backdrop-blur dark:border-stone-800 dark:bg-stone-950/90">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 md:px-6">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <img src="{{ asset('assets/logo-mark.png') }}" alt="Logo" class="h-12 w-12 rounded-2xl">
                <div>
                    <p class="text-lg font-bold">Cafe Hagi</p>
                    <p class="text-xs text-stone-500 dark:text-stone-400">X Batman Pools</p>
                </div>
            </a>

            <div class="hidden items-center gap-4 md:flex">
                <a href="{{ route('home') }}" class="hover:text-amber-700">Home</a>
                <a href="{{ route('public.menu') }}" class="hover:text-amber-700">Menu</a>
                <a href="{{ route('about') }}" class="hover:text-amber-700">About</a>
                <button data-theme-toggle class="btn-secondary text-sm">Mode Gelap</button>
                <button data-install-app class="btn-secondary text-sm">Install App</button>
                <a href="{{ route('login') }}" class="btn-secondary">Login Admin</a>
            </div>

            <button data-mobile-toggle="#mobile-public-nav" class="btn-secondary md:hidden">☰</button>
        </div>

        <div id="mobile-public-nav" class="hidden border-t border-stone-200 px-4 py-4 dark:border-stone-800 md:hidden">
            <div class="grid gap-2">
                <a href="{{ route('home') }}" class="rounded-2xl px-4 py-3 hover:bg-stone-100 dark:hover:bg-stone-900">Home</a>
                <a href="{{ route('public.menu') }}" class="rounded-2xl px-4 py-3 hover:bg-stone-100 dark:hover:bg-stone-900">Menu</a>
                <a href="{{ route('about') }}" class="rounded-2xl px-4 py-3 hover:bg-stone-100 dark:hover:bg-stone-900">About</a>
                <button data-theme-toggle class="btn-secondary text-left">Mode Gelap</button>
                <button data-install-app class="btn-secondary text-left">Install App</button>
                <a href="{{ route('login') }}" class="rounded-2xl px-4 py-3 hover:bg-stone-100 dark:hover:bg-stone-900">Login Admin</a>
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>
</body>
</html>
