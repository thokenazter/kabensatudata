<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm mb-1">No Bangunan</label>
        <input type="text" name="building_number" value="{{ old('building_number', $building->building_number ?? '') }}" class="w-full border rounded px-3 py-2" required>
        <p class="text-xs text-gray-500 mt-1">Nomor urut bangunan harus unik untuk desa yang sama.</p>
    </div>
    <div>
        <label class="block text-sm mb-1">Desa</label>
        <select name="village_id" class="w-full border rounded px-3 py-2" required>
            @foreach(($villages ?? []) as $id => $name)
                <option value="{{ $id }}" @selected(old('village_id', $building->village_id ?? '') == $id)>{{ $name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm mb-1">Latitude</label>
        <input type="number" step="any" name="latitude" value="{{ old('latitude', $building->latitude ?? '') }}" class="w-full border rounded px-3 py-2">
    </div>
    <div>
        <label class="block text-sm mb-1">Longitude</label>
        <input type="number" step="any" name="longitude" value="{{ old('longitude', $building->longitude ?? '') }}" class="w-full border rounded px-3 py-2">
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm mb-1">Alamat</label>
        <input type="text" name="address" value="{{ old('address', $building->address ?? '') }}" class="w-full border rounded px-3 py-2">
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm mb-1">Catatan</label>
        <textarea name="notes" rows="3" class="w-full border rounded px-3 py-2">{{ old('notes', $building->notes ?? '') }}</textarea>
    </div>
    <div>
        <label class="block text-sm mb-1">Status</label>
        <input type="text" name="status" value="{{ old('status', $building->status ?? '') }}" class="w-full border rounded px-3 py-2" placeholder="aktif/tidak">
    </div>
</div>

@push('scripts')
<script>
    const vSelect = document.querySelector('select[name="village_id"]');
    const bnInput = document.querySelector('input[name="building_number"]');
    if (vSelect && bnInput) {
        vSelect.addEventListener('change', () => {
            // Reset no bangunan saat desa berubah (meniru afterStateUpdated di Filament)
            bnInput.value = '';
        });
    }
    </script>
@endpush
