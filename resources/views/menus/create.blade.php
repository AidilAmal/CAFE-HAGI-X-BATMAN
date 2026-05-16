@extends('layouts.app')
@section('title', 'Tambah Menu')
@section('page-title', 'Tambah Menu')
@section('content')
@include('partials.form-errors')
<div class="card-surface p-6">
    <form action="{{ route('menus.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 gap-4 md:grid-cols-2">
        @csrf
        <div><label class="mb-2 block text-sm font-medium">Nama Menu</label><input type="text" name="name" value="{{ old('name') }}" class="input-ui" required></div>
        <div><label class="mb-2 block text-sm font-medium">Kategori Menu</label><select name="menu_category_id" class="input-ui"><option value="">Pilih kategori</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('menu_category_id') == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
        <div><label class="mb-2 block text-sm font-medium">Harga</label><input type="number" name="price" value="{{ old('price', 0) }}" class="input-ui" min="0" required></div>
        <div><label class="mb-2 block text-sm font-medium">Foto Menu</label><input type="file" name="image" class="input-ui"></div>
        <div><label class="mb-2 block text-sm font-medium">Status Ketersediaan</label><input type="text" value="Otomatis sinkron dari stok bahan" class="input-ui opacity-70" disabled></div>
        <div><label class="mb-2 block text-sm font-medium">Tampilkan ke Publik</label><select name="is_visible" class="input-ui" required><option value="1" @selected((int) old('is_visible', 1) === 1)>Ya</option><option value="0" @selected((int) old('is_visible', 1) === 0)>Tidak</option></select></div>
        <div class="md:col-span-2"><label class="mb-2 block text-sm font-medium">Deskripsi Singkat</label><textarea name="description" rows="3" class="input-ui">{{ old('description') }}</textarea></div>
        <div class="md:col-span-2"><label class="mb-2 block text-sm font-medium">Catatan Resep</label><textarea name="recipe_notes" rows="4" class="input-ui" placeholder="Contoh: Gunakan es batu penuh, tuang espresso terakhir, garnish tipis.">{{ old('recipe_notes') }}</textarea></div>

        <div class="md:col-span-2 rounded-[28px] border border-stone-200 p-5 dark:border-stone-800">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-semibold">Komposisi Bahan</h3>
                    <p class="text-sm text-stone-500 dark:text-stone-400">Pilih bahan yang dipakai dan jumlah pemakaian per 1 porsi.</p>
                </div>
                <button type="button" id="add-ingredient" class="btn-secondary">+ Tambah Bahan</button>
            </div>
            <div id="ingredient-list" class="space-y-3">
                @php $oldIngredients = old('ingredients', [['item_id' => '', 'qty_required' => 1]]); @endphp
                @foreach($oldIngredients as $index => $ingredient)
                    <div class="ingredient-row grid grid-cols-1 gap-3 rounded-2xl bg-stone-50 p-4 dark:bg-stone-950 md:grid-cols-[1fr_180px_auto]">
                        <select name="ingredients[{{ $index }}][item_id]" class="input-ui">
                            <option value="">Pilih bahan</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" @selected(($ingredient['item_id'] ?? '') == $item->id)>{{ $item->name }} (stok {{ $item->stock }} {{ $item->unit }})</option>
                            @endforeach
                        </select>
                        <input type="number" min="1" name="ingredients[{{ $index }}][qty_required]" value="{{ $ingredient['qty_required'] ?? 1 }}" class="input-ui" placeholder="Qty / porsi">
                        <button type="button" class="btn-secondary remove-ingredient">Hapus</button>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="md:col-span-2 flex gap-3"><a href="{{ route('menus.index') }}" class="btn-secondary">Batal</a><button class="btn-primary">Simpan Menu</button></div>
    </form>
</div>

<script>
window.addEventListener('DOMContentLoaded', function () {
    const list = document.getElementById('ingredient-list');
    const addButton = document.getElementById('add-ingredient');
    if (!list || !addButton) return;

    addButton.addEventListener('click', function () {
        const index = list.querySelectorAll('.ingredient-row').length;
        const row = document.createElement('div');
        row.className = 'ingredient-row grid grid-cols-1 gap-3 rounded-2xl bg-stone-50 p-4 dark:bg-stone-950 md:grid-cols-[1fr_180px_auto]';
        row.innerHTML = `
            <select name="ingredients[${index}][item_id]" class="input-ui">
                <option value="">Pilih bahan</option>
                @foreach($items as $item)
                    <option value="{{ $item->id }}">{{ $item->name }} (stok {{ $item->stock }} {{ $item->unit }})</option>
                @endforeach
            </select>
            <input type="number" min="1" name="ingredients[${index}][qty_required]" value="1" class="input-ui" placeholder="Qty / porsi">
            <button type="button" class="btn-secondary remove-ingredient">Hapus</button>`;
        list.appendChild(row);
    });

    list.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-ingredient')) {
            event.target.closest('.ingredient-row')?.remove();
        }
    });
});
</script>
@endsection
