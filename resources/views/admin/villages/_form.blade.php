<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm mb-1">Nama</label>
        <input type="text" name="name" value="{{ old('name', $village->name ?? '') }}" class="w-full border rounded px-3 py-2" required>
    </div>
    <div>
        <label class="block text-sm mb-1">Kode</label>
        <input type="text" name="code" value="{{ old('code', $village->code ?? '') }}" class="w-full border rounded px-3 py-2">
    </div>
    <div>
        <label class="block text-sm mb-1">No Urut</label>
        <input type="number" name="sequence_number" value="{{ old('sequence_number', $village->sequence_number ?? 0) }}" class="w-full border rounded px-3 py-2" min="0">
    </div>
    <div>
        <label class="block text-sm mb-1">Kecamatan</label>
        <input type="text" name="district" value="{{ old('district', $village->district ?? '') }}" class="w-full border rounded px-3 py-2">
    </div>
    <div>
        <label class="block text-sm mb-1">Kabupaten</label>
        <input type="text" name="regency" value="{{ old('regency', $village->regency ?? '') }}" class="w-full border rounded px-3 py-2">
    </div>
    <div>
        <label class="block text-sm mb-1">Provinsi</label>
        <input type="text" name="province" value="{{ old('province', $village->province ?? '') }}" class="w-full border rounded px-3 py-2">
    </div>
</div>

