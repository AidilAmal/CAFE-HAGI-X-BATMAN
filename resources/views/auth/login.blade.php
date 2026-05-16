<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[radial-gradient(circle_at_top,#292524,#0c0a09)] text-white">
    <div class="grid min-h-screen lg:grid-cols-2">
        <div class="hidden items-center justify-center p-10 lg:flex">
            <div class="max-w-lg">
                <img src="{{ asset('assets/logo-mark.png') }}" alt="Logo" class="mb-6 h-24 w-24 rounded-[28px]">
                <h1 class="text-5xl font-bold leading-tight">Cafe Hagi X Batman Pools</h1>
                <p class="mt-4 text-lg text-stone-300">Sistem stok barang, monitoring menu, laporan, dan dashboard cafe yang modern.</p>
            </div>
        </div>

        <div class="flex items-center justify-center p-6">
            <div class="w-full max-w-md rounded-[32px] border border-white/10 bg-white/10 p-8 backdrop-blur">
                <a href="{{ route('home') }}"
                    class="mb-6 inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-white/80 transition hover:bg-white/10 hover:text-white">
                     <span>←</span>
                     <span>Kembali ke Beranda</span>
                </a>
                <h2 class="text-3xl font-bold">Login Admin</h2>
                <p class="mt-2 text-sm text-stone-300">Masuk untuk mengelola stok dan dashboard cafe.</p>

                @if ($errors->any())
                    <div class="mt-4 rounded-2xl bg-red-500/20 px-4 py-3 text-sm text-red-200">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('login.attempt') }}" method="POST" class="mt-6 space-y-4">
                    @csrf
                    <div>
                        <label class="mb-2 block text-sm">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="input-ui !bg-white/95 !text-stone-900" required>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm">Password</label>
                        <input type="password" name="password" class="input-ui !bg-white/95 !text-stone-900" required>
                    </div>

                    <label class="flex items-center gap-2 text-sm text-stone-300">
                        <input type="checkbox" name="remember">
                        Ingat saya
                    </label>

                    <button class="w-full rounded-2xl bg-amber-600 px-4 py-3 font-semibold text-white transition hover:bg-amber-500">
                        Masuk
                    </button>
                </form>

                <div class="mt-6 rounded-2xl bg-black/20 p-4 text-sm text-stone-300">
                    <p class="font-semibold text-white">Akun demo</p>
                    <p>admin@cafe.test / password</p>
                    <p>owner@cafe.test / password</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
