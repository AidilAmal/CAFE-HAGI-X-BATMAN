@extends('layouts.app')
@section('title', 'Kategori Menu')
@section('page-title', 'Kategori Menu')
@section('content')
<div class="mb-4 flex items-center justify-between">
    <h3 class="text-xl font-semibold">Daftar Kategori Menu</h3>
    <a href="{{ route('menu-categories.create') }}" class="btn-primary">+ Tambah Kategori Menu</a>
</div>
<div class="card-surface p-5">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead><tr class="border-b border-stone-200 dark:border-stone-800"><th class="px-4 py-3 text-left">No</th><th class="px-4 py-3 text-left">Nama</th><th class="px-4 py-3 text-left">Slug</th><th class="px-4 py-3 text-right">Aksi</th></tr></thead>
            <tbody>
                @forelse($menuCategories as $menuCategory)
                    <tr class="border-b border-stone-100 dark:border-stone-900">
                        <td class="px-4 py-3">{{ $loop->iteration + ($menuCategories->currentPage() - 1) * $menuCategories->perPage() }}</td>
                        <td class="px-4 py-3">{{ $menuCategory->name }}</td>
                        <td class="px-4 py-3">{{ $menuCategory->slug }}</td>
                        <td class="px-4 py-3"><div class="flex justify-end gap-2"><a href="{{ route('menu-categories.edit', $menuCategory) }}" class="btn-secondary">Edit</a><form action="{{ route('menu-categories.destroy', $menuCategory) }}" method="POST" onsubmit="return confirm('Hapus kategori menu ini?')">@csrf @method('DELETE')<button class="rounded-2xl bg-red-100 px-4 py-3 text-red-700 dark:bg-red-950 dark:text-red-300">Hapus</button></form></div></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-stone-500">Belum ada kategori menu.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $menuCategories->links() }}</div>
</div>
@endsection
