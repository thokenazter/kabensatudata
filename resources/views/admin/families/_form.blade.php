<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm mb-1">Desa</label>
        <select id="villageSelect" class="w-full border rounded px-3 py-2">
            <option value="">Pilih Desa</option>
            @foreach(($villages ?? []) as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>
        <p class="text-xs text-gray-500 mt-1">Opsional untuk memudahkan memilih bangunan</p>
    </div>
    <div>
        <label class="block text-sm mb-1">Bangunan</label>
        <select name="building_id" id="buildingSelect" class="w-full border rounded px-3 py-2" required>
            @foreach(($buildings ?? []) as $id => $num)
                <option value="{{ $id }}" @selected(old('building_id', $family->building_id ?? '') == $id)>No {{ $num }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm mb-1">No KK</label>
        <input type="text" name="family_number" value="{{ old('family_number', $family->family_number ?? '') }}" class="w-full border rounded px-3 py-2" required>
    </div>
    <div>
        <label class="block text-sm mb-1">No Urut di Bangunan</label>
        <input type="number" name="sequence_number_in_building" value="{{ old('sequence_number_in_building', $family->sequence_number_in_building ?? 0) }}" class="w-full border rounded px-3 py-2" min="0">
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm mb-1">Nama Kepala Keluarga</label>
        <input type="text" name="head_name" value="{{ old('head_name', $family->head_name ?? '') }}" class="w-full border rounded px-3 py-2" required>
    </div>

    <div class="md:col-span-2 grid grid-cols-2 md:grid-cols-3 gap-3">
        @php
            $flags = [
                'has_clean_water' => 'Air Bersih',
                'is_water_protected' => 'Air Terlindungi',
                'has_toilet' => 'Jamban',
                'is_toilet_sanitary' => 'Jamban Saniter',
                'has_mental_illness' => 'Gangguan Jiwa',
                'takes_medication_regularly' => 'Minum Obat Teratur',
                'has_restrained_member' => 'Kasus Pasung',
            ];
        @endphp
        <label class="inline-flex items-center space-x-2">
            <input type="checkbox" id="has_clean_water" name="has_clean_water" value="1" @checked(old('has_clean_water', $family->has_clean_water ?? false))>
            <span>{{ $flags['has_clean_water'] }}</span>
        </label>
        <label class="inline-flex items-center space-x-2" id="wrap_is_water_protected">
            <input type="checkbox" id="is_water_protected" name="is_water_protected" value="1" @checked(old('is_water_protected', $family->is_water_protected ?? false))>
            <span>{{ $flags['is_water_protected'] }}</span>
        </label>
        <label class="inline-flex items-center space-x-2">
            <input type="checkbox" id="has_toilet" name="has_toilet" value="1" @checked(old('has_toilet', $family->has_toilet ?? false))>
            <span>{{ $flags['has_toilet'] }}</span>
        </label>
        <label class="inline-flex items-center space-x-2" id="wrap_is_toilet_sanitary">
            <input type="checkbox" id="is_toilet_sanitary" name="is_toilet_sanitary" value="1" @checked(old('is_toilet_sanitary', $family->is_toilet_sanitary ?? false))>
            <span>{{ $flags['is_toilet_sanitary'] }}</span>
        </label>
        <label class="inline-flex items-center space-x-2">
            <input type="checkbox" name="has_mental_illness" value="1" @checked(old('has_mental_illness', $family->has_mental_illness ?? false))>
            <span>{{ $flags['has_mental_illness'] }}</span>
        </label>
        <label class="inline-flex items-center space-x-2">
            <input type="checkbox" name="takes_medication_regularly" value="1" @checked(old('takes_medication_regularly', $family->takes_medication_regularly ?? false))>
            <span>{{ $flags['takes_medication_regularly'] }}</span>
        </label>
        <label class="inline-flex items-center space-x-2">
            <input type="checkbox" name="has_restrained_member" value="1" @checked(old('has_restrained_member', $family->has_restrained_member ?? false))>
            <span>{{ $flags['has_restrained_member'] }}</span>
        </label>
    </div>
</div>

@push('scripts')
<script>
    const vSel = document.getElementById('villageSelect');
    const bSel = document.getElementById('buildingSelect');
    if (vSel && bSel) {
        vSel.addEventListener('change', async function () {
            const id = this.value;
            if (!id) return;
            const res = await fetch(`/api/buildings?village_id=${id}`);
            const data = await res.json();
            bSel.innerHTML = '';
            data.forEach(it => {
                const opt = document.createElement('option');
                opt.value = it.id;
                opt.textContent = `No ${it.building_number}`;
                bSel.appendChild(opt);
            });
        });
    }

    // Reactive visibility like Filament toggles
    const wrapWaterProtected = document.getElementById('wrap_is_water_protected');
    const wrapToiletSanitary = document.getElementById('wrap_is_toilet_sanitary');
    const cbHasCleanWater = document.getElementById('has_clean_water');
    const cbHasToilet = document.getElementById('has_toilet');

    function syncVisibility() {
        if (wrapWaterProtected && cbHasCleanWater) wrapWaterProtected.style.display = cbHasCleanWater.checked ? '' : 'none';
        if (wrapToiletSanitary && cbHasToilet) wrapToiletSanitary.style.display = cbHasToilet.checked ? '' : 'none';
    }
    if (cbHasCleanWater && cbHasToilet) {
        cbHasCleanWater.addEventListener('change', syncVisibility);
        cbHasToilet.addEventListener('change', syncVisibility);
        syncVisibility();
    }
</script>
@endpush
