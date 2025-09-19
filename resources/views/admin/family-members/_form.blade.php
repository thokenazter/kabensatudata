@php
    $genderOptions = ['Laki-laki' => 'Laki-laki', 'Perempuan' => 'Perempuan'];
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm mb-1">Keluarga (KK)</label>
        <select name="family_id" class="w-full border rounded px-3 py-2" required>
            @foreach(($families ?? []) as $id => $num)
                <option value="{{ $id }}" @selected(old('family_id', $familyMember->family_id ?? '') == $id)>KK {{ $num }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm mb-1">Nama</label>
        <input type="text" name="name" value="{{ old('name', $familyMember->name ?? '') }}" class="w-full border rounded px-3 py-2" required>
    </div>
    <div>
        <label class="block text-sm mb-1">NIK</label>
        <input type="text" name="nik" value="{{ old('nik', $familyMember->nik ?? '') }}" class="w-full border rounded px-3 py-2" maxlength="16">
    </div>
    <div>
        <label class="block text-sm mb-1">No RM</label>
        <input type="text" name="rm_number" value="{{ old('rm_number', $familyMember->rm_number ?? '') }}" class="w-full border rounded px-3 py-2">
    </div>
    <div>
        <label class="block text-sm mb-1">Hubungan dengan Kepala Keluarga</label>
        <select name="relationship" class="w-full border rounded px-3 py-2" required>
            @php
                $rels = ['Kepala Keluarga','Istri','Suami','Anak','Menantu','Cucu','Orang Tua','Mertua','Pembantu','Lainnya'];
            @endphp
            <option value="">-</option>
            @foreach($rels as $rel)
                <option value="{{ $rel }}" @selected(old('relationship', $familyMember->relationship ?? '')==$rel)>{{ $rel }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm mb-1">Tempat Lahir</label>
        <input type="text" name="birth_place" value="{{ old('birth_place', $familyMember->birth_place ?? '') }}" class="w-full border rounded px-3 py-2">
    </div>
    <div>
        <label class="block text-sm mb-1">Tanggal Lahir</label>
        <input type="date" name="birth_date" value="{{ old('birth_date', isset($familyMember->birth_date) ? $familyMember->birth_date->format('Y-m-d') : '') }}" class="w-full border rounded px-3 py-2">
    </div>
    <div>
        <label class="block text-sm mb-1">Jenis Kelamin</label>
        <select name="gender" class="w-full border rounded px-3 py-2">
            <option value="">-</option>
            @foreach($genderOptions as $key => $label)
                <option value="{{ $key }}" @selected(old('gender', $familyMember->gender ?? '')==$key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm mb-1">Agama</label>
        <input type="text" name="religion" value="{{ old('religion', $familyMember->religion ?? '') }}" class="w-full border rounded px-3 py-2">
    </div>
    <div id="wrap_education">
        <label class="block text-sm mb-1">Pendidikan</label>
        <select name="education" class="w-full border rounded px-3 py-2">
            @php
                $eduOpts=['Tidak Pernah Sekolah','Tidak Tamat SD/MI','Tamat SD/MI','Tamat SMP/MTs','Tamat SMA/MA/SMK','Tamat D1/D2/D3','Tamat D4/S1','Tamat S2/S3'];
            @endphp
            <option value="">-</option>
            @foreach($eduOpts as $opt)
                <option value="{{ $opt }}" @selected(old('education', $familyMember->education ?? '')==$opt)>{{ $opt }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm mb-1">Status Kawin</label>
        <input type="text" name="marital_status" value="{{ old('marital_status', $familyMember->marital_status ?? '') }}" class="w-full border rounded px-3 py-2">
    </div>
    <div id="wrap_occupation">
        <label class="block text-sm mb-1">Pekerjaan</label>
        <select name="occupation" class="w-full border rounded px-3 py-2">
            @php
                $occOpts=['Tidak Kerja','Sekolah','ASN','TNI/Polri','Honorer','Pegawai Swasta','Nelayan','Petani','IRT','Lainnya'];
            @endphp
            <option value="">-</option>
            @foreach($occOpts as $opt)
                <option value="{{ $opt }}" @selected(old('occupation', $familyMember->occupation ?? '')==$opt)>{{ $opt }}</option>
            @endforeach
        </select>
    </div>
    <div class="md:col-span-2 grid grid-cols-2 md:grid-cols-3 gap-3">
        @php
            $flags = [
                'is_pregnant' => 'Hamil',
                'has_jkn' => 'Memiliki JKN',
                'is_smoker' => 'Perokok',
                'use_toilet' => 'Pakai Jamban',
                'has_tuberculosis' => 'Pernah TBC',
                'takes_tb_medication_regularly' => 'Minum Obat TBC',
                'has_chronic_cough' => 'Batuk Kronis',
                'has_hypertension' => 'Hipertensi',
                'takes_hypertension_medication_regularly' => 'Minum Obat HT',
                'uses_contraception' => 'KB',
                'gave_birth_in_health_facility' => 'Bersalin di Faskes',
                'exclusive_breastfeeding' => 'ASI Eksklusif',
                'complete_immunization' => 'Imunisasi Lengkap',
                'growth_monitoring' => 'Pemantauan Tumbuh',
            ];
        @endphp
        <label class="inline-flex items-center space-x-2" id="wrap_is_pregnant">
            <input type="checkbox" name="is_pregnant" id="is_pregnant" value="1" @checked(old('is_pregnant', $familyMember->is_pregnant ?? false))>
            <span>Hamil</span>
        </label>
        <label class="inline-flex items-center space-x-2">
            <input type="checkbox" name="has_jkn" value="1" @checked(old('has_jkn', $familyMember->has_jkn ?? false))>
            <span>Memiliki JKN</span>
        </label>
        <label class="inline-flex items-center space-x-2" id="wrap_is_smoker">
            <input type="checkbox" name="is_smoker" id="is_smoker" value="1" @checked(old('is_smoker', $familyMember->is_smoker ?? false))>
            <span>Perokok</span>
        </label>
        <label class="inline-flex items-center space-x-2">
            <input type="checkbox" name="use_toilet" value="1" @checked(old('use_toilet', $familyMember->use_toilet ?? false))>
            <span>Pakai Jamban</span>
        </label>
        <label class="inline-flex items-center space-x-2">
            <input type="checkbox" name="use_water" value="1" @checked(old('use_water', $familyMember->use_water ?? false))>
            <span>Menggunakan Air Bersih</span>
        </label>
        <label class="inline-flex items-center space-x-2">
            <input type="checkbox" name="has_tuberculosis" value="1" @checked(old('has_tuberculosis', $familyMember->has_tuberculosis ?? false))>
            <span>Pernah TBC</span>
        </label>
        <label class="inline-flex items-center space-x-2">
            <input type="checkbox" name="takes_tb_medication_regularly" value="1" @checked(old('takes_tb_medication_regularly', $familyMember->takes_tb_medication_regularly ?? false))>
            <span>Minum Obat TBC</span>
        </label>
        <label class="inline-flex items-center space-x-2">
            <input type="checkbox" name="has_chronic_cough" value="1" @checked(old('has_chronic_cough', $familyMember->has_chronic_cough ?? false))>
            <span>Batuk Kronis</span>
        </label>
        <label class="inline-flex items-center space-x-2">
            <input type="checkbox" name="has_hypertension" value="1" @checked(old('has_hypertension', $familyMember->has_hypertension ?? false))>
            <span>Hipertensi</span>
        </label>
        <label class="inline-flex items-center space-x-2">
            <input type="checkbox" name="takes_hypertension_medication_regularly" value="1" @checked(old('takes_hypertension_medication_regularly', $familyMember->takes_hypertension_medication_regularly ?? false))>
            <span>Minum Obat HT</span>
        </label>
        <label class="inline-flex items-center space-x-2">
            <input type="checkbox" name="uses_contraception" value="1" @checked(old('uses_contraception', $familyMember->uses_contraception ?? false))>
            <span>KB</span>
        </label>
        <label class="inline-flex items-center space-x-2">
            <input type="checkbox" name="gave_birth_in_health_facility" value="1" @checked(old('gave_birth_in_health_facility', $familyMember->gave_birth_in_health_facility ?? false))>
            <span>Bersalin di Faskes</span>
        </label>
        <label class="inline-flex items-center space-x-2">
            <input type="checkbox" name="exclusive_breastfeeding" value="1" @checked(old('exclusive_breastfeeding', $familyMember->exclusive_breastfeeding ?? false))>
            <span>ASI Eksklusif</span>
        </label>
        <label class="inline-flex items-center space-x-2">
            <input type="checkbox" name="complete_immunization" value="1" @checked(old('complete_immunization', $familyMember->complete_immunization ?? false))>
            <span>Imunisasi Lengkap</span>
        </label>
        <label class="inline-flex items-center space-x-2">
            <input type="checkbox" name="growth_monitoring" value="1" @checked(old('growth_monitoring', $familyMember->growth_monitoring ?? false))>
            <span>Pemantauan Tumbuh</span>
        </label>
    </div>

@push('scripts')
<script>
    const birthInput = document.querySelector('input[name="birth_date"]');
    const genderSelect = document.querySelector('select[name="gender"]');
    const wrapPreg = document.getElementById('wrap_is_pregnant');
    const wrapSmoker = document.getElementById('wrap_is_smoker');
    const wrapOcc = document.getElementById('wrap_occupation');
    const wrapEdu = document.getElementById('wrap_education');

    function getAge() {
        const v = birthInput?.value;
        if (!v) return null;
        const bd = new Date(v);
        if (isNaN(bd)) return null;
        const diff = Date.now() - bd.getTime();
        const ageDate = new Date(diff);
        return Math.abs(ageDate.getUTCFullYear() - 1970);
    }

    function syncMemberVisibility() {
        const age = getAge();
        const gender = genderSelect?.value;
        if (wrapPreg) wrapPreg.style.display = (gender === 'Perempuan' && age !== null && age >= 10 && age <= 54) ? '' : 'none';
        if (wrapSmoker) wrapSmoker.style.display = (age !== null && age > 15) ? '' : 'none';
        if (wrapOcc) wrapOcc.style.display = (age !== null && age > 10) ? '' : 'none';
        if (wrapEdu) wrapEdu.style.display = (age !== null && age >= 6) ? '' : 'none';
    }
    [birthInput, genderSelect].forEach(el => el && el.addEventListener('change', syncMemberVisibility));
    syncMemberVisibility();
</script>
@endpush
</div>
