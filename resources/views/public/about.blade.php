@extends('layouts.public')

@section('title', 'Tentang Cafe')

@section('content')
<section class="mx-auto max-w-5xl px-4 py-16 md:px-6">
    <div class="card-surface p-8 md:p-12">
        <h1 class="text-4xl font-black">Tentang Cafe Hagi X Batman Pools</h1>
        <p class="mt-5 text-lg text-stone-600 dark:text-stone-400">
            Website ini menampilkan daftar menu dan status ketersediaan secara real-time, sekaligus membantu admin cafe mengelola stok barang, supplier, dan laporan harian.
        </p>

        <div class="mt-8 grid gap-4 md:grid-cols-3">
            <div class="rounded-3xl bg-stone-50 p-5 dark:bg-stone-800">
                <h2 class="font-semibold">Responsif</h2>
                <p class="mt-2 text-sm text-stone-600 dark:text-stone-400">Nyaman dibuka dari laptop maupun HP.</p>
            </div>
            <div class="rounded-3xl bg-stone-50 p-5 dark:bg-stone-800">
                <h2 class="font-semibold">Installable</h2>
                <p class="mt-2 text-sm text-stone-600 dark:text-stone-400">Bisa di-install sebagai PWA seperti aplikasi.</p>
            </div>
            <div class="rounded-3xl bg-stone-50 p-5 dark:bg-stone-800">
                <h2 class="font-semibold">Terintegrasi</h2>
                <p class="mt-2 text-sm text-stone-600 dark:text-stone-400">Stok, menu, dan laporan ada di satu sistem.</p>
            </div>
        </div>
    </div>
</section>
@endsection
