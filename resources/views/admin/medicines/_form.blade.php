@php
    $units = [
        'tablet' => 'Tablet',
        'kapsul' => 'Kapsul',
        'botol' => 'Botol',
        'tube' => 'Tube',
        'ampul' => 'Ampul',
        'vial' => 'Vial',
        'sachet' => 'Sachet',
        'strip' => 'Strip',
    ];
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm mb-1">Nama Obat</label>
        <input type="text" name="name" value="{{ old('name', $medicine->name ?? '') }}" class="w-full border rounded px-3 py-2" required>
    </div>
    <div>
        <label class="block text-sm mb-1">Nama Generik</label>
        <input type="text" name="generic_name" value="{{ old('generic_name', $medicine->generic_name ?? '') }}" class="w-full border rounded px-3 py-2">
    </div>
    <div>
        <label class="block text-sm mb-1">Kekuatan</label>
        <input type="text" name="strength" value="{{ old('strength', $medicine->strength ?? '') }}" class="w-full border rounded px-3 py-2" placeholder="500mg, 250mg, dll">
    </div>
    <div>
        <label class="block text-sm mb-1">Satuan</label>
        <select name="unit" class="w-full border rounded px-3 py-2" required>
            @foreach($units as $key => $label)
                <option value="{{ $key }}" @selected(old('unit', $medicine->unit ?? 'tablet') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm mb-1">Deskripsi</label>
        <textarea name="description" class="w-full border rounded px-3 py-2" rows="3">{{ old('description', $medicine->description ?? '') }}</textarea>
    </div>
    <div>
        <label class="block text-sm mb-1">Jumlah Stok</label>
        <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $medicine->stock_quantity ?? 0) }}" class="w-full border rounded px-3 py-2" min="0" required>
    </div>
    <div>
        <label class="block text-sm mb-1">Stok Awal</label>
        <input type="number" name="stock_initial" value="{{ old('stock_initial', $medicine->stock_initial ?? $medicine->stock_quantity ?? 0) }}" class="w-full border rounded px-3 py-2" min="0" placeholder="Biarkan kosong untuk sama dengan stok saat ini">
    </div>
    <div>
        <label class="block text-sm mb-1">Stok Minimum</label>
        <input type="number" name="minimum_stock" value="{{ old('minimum_stock', $medicine->minimum_stock ?? 10) }}" class="w-full border rounded px-3 py-2" min="0" required>
    </div>
    <div class="md:col-span-2">
        <label class="inline-flex items-center space-x-2">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $medicine->is_active ?? true))>
            <span>Aktif</span>
        </label>
    </div>
</div>
