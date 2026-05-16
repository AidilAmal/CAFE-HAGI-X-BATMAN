@extends('layouts.app')
@section('title', 'Supplier')
@section('page-title', 'Supplier')
@section('content')
<div class="mb-4 flex items-center justify-between">
    <h3 class="text-xl font-semibold">Daftar Supplier</h3>
    <a href="{{ route('suppliers.create') }}" class="btn-primary">+ Tambah Supplier</a>
</div>
<div class="card-surface p-5">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead><tr class="border-b border-stone-200 dark:border-stone-800"><th class="px-4 py-3 text-left">No</th><th class="px-4 py-3 text-left">Nama</th><th class="px-4 py-3 text-left">No HP</th><th class="px-4 py-3 text-left">Alamat</th><th class="px-4 py-3 text-right">Aksi</th></tr></thead>
            <tbody>
                @forelse($suppliers as $supplier)
                    <tr class="border-b border-stone-100 dark:border-stone-900">
                        <td class="px-4 py-3">{{ $loop->iteration + ($suppliers->currentPage() - 1) * $suppliers->perPage() }}</td>
                        <td class="px-4 py-3">{{ $supplier->name }}</td>
                        <td class="px-4 py-3">{{ $supplier->phone }}</td>
                        <td class="px-4 py-3 text-stone-500">{{ $supplier->address }}</td>
                        <td class="px-4 py-3"><div class="flex justify-end gap-2"><a href="{{ route('suppliers.edit', $supplier) }}" class="btn-secondary">Edit</a><form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" onsubmit="return confirm('Hapus supplier ini?')">@csrf @method('DELETE')<button class="rounded-2xl bg-red-100 px-4 py-3 text-red-700 dark:bg-red-950 dark:text-red-300">Hapus</button></form></div></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-stone-500">Belum ada supplier.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $suppliers->links() }}</div>
</div>
@endsection
