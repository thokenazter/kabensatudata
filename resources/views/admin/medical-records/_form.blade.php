@php
    $priorities = [
        'normal' => '🟢 Normal',
        'urgent' => '🟡 Mendesak',
        'emergency' => '🔴 Darurat',
    ];
    $statuses = [
        'pending_registration' => 'Menunggu Pendaftaran',
        'pending_nurse' => 'Menunggu Perawat',
        'pending_doctor' => 'Menunggu Dokter',
        'pending_pharmacy' => 'Menunggu Apoteker',
        'completed' => 'Selesai',
    ];
@endphp

<div class="space-y-6">
    <!-- Queue Info -->
    <div class="p-4 rounded-xl border border-white/10 bg-white/5">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <label class="block text-sm text-slate-300 mb-1">Nomor Antrian</label>
                <input type="text" value="{{ old('queue_number', $record->queue_number ?? '') }}" class="w-full bg-white/10 border border-white/10 rounded px-3 py-2 text-emerald-300 font-semibold" disabled>
            </div>
            <div>
                <label class="block text-sm text-slate-300 mb-1">Prioritas</label>
                <select name="priority_level" class="w-full bg-white/10 border border-white/10 rounded px-3 py-2 text-slate-200">
                    @foreach($priorities as $k => $label)
                        <option value="{{ $k }}" @selected(old('priority_level', $record->priority_level ?? 'normal')==$k)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-slate-300 mb-1">Estimasi Waktu (menit)</label>
                <input type="number" name="estimated_service_time" min="5" max="240" value="{{ old('estimated_service_time', $record->estimated_service_time ?? 15) }}" class="w-full bg-white/10 border border-white/10 rounded px-3 py-2 text-slate-200">
            </div>
            <div>
                <label class="block text-sm text-slate-300 mb-1">Status</label>
                <select name="workflow_status" class="w-full bg-white/10 border border-white/10 rounded px-3 py-2 text-slate-200">
                    @foreach($statuses as $k => $label)
                        <option value="{{ $k }}" @selected(old('workflow_status', $record->workflow_status ?? 'pending_registration')==$k)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Patient and visit -->
    <div class="p-4 rounded-xl border border-white/10 bg-white/5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div x-data="patientTypeahead()" x-init="init()">
                <label class="block text-sm text-slate-300 mb-1">Pasien (Ketik nama/NIK/RM)</label>
                <input type="text" id="patientSearch" class="w-full bg-white/10 border border-white/10 rounded px-3 py-2 text-slate-200" placeholder="Mulai ketik nama pasien..." list="patientList" autocomplete="off">
                <datalist id="patientList"></datalist>
                <input type="hidden" name="family_member_id" id="family_member_id" value="{{ old('family_member_id', $record->family_member_id ?? '') }}" required>
                <p class="text-xs text-slate-400 mt-1">Ketik minimal 2 karakter untuk mencari. <a href="{{ route('panel.family-members.create') }}" class="underline">Tambah anggota keluarga baru</a>.</p>
            </div>
            <div>
                <label class="block text-sm text-slate-300 mb-1">Tanggal Kunjungan</label>
                <input type="date" name="visit_date" value="{{ old('visit_date', optional($record->visit_date ?? now())->format('Y-m-d')) }}" class="w-full bg-white/10 border border-white/10 rounded px-3 py-2 text-slate-200" required>
            </div>
        </div>
        <div>
            <label class="block text-sm text-slate-300 mb-1">Sub‑Indikator SPM (opsional, multi‑select)</label>
            @php
                $selected = collect(old('spm_sub_indicator_ids', isset($record) ? $record->spmSubIndicators->pluck('id')->all() : []));
            @endphp
            <select name="spm_sub_indicator_ids[]" multiple size="8" class="w-full bg-white/10 border border-white/10 rounded px-3 py-2 text-slate-200">
                @isset($subIndicators)
                    @foreach($subIndicators as $si)
                        <option value="{{ $si->id }}" @selected($selected->contains($si->id))>{{ $si->code }} — {{ $si->name }} ({{ $si->indicator?->name }})</option>
                    @endforeach
                @endisset
            </select>
            <p class="text-xs text-slate-400 mt-1">Gunakan Ctrl/Cmd untuk memilih lebih dari satu.</p>
        </div>
    </div>

    <!-- Complaint & Anamnesis -->
    <div class="p-4 rounded-xl border border-white/10 bg-white/5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="block text-sm text-slate-300 mb-1">Keluhan Utama</label>
                <textarea name="chief_complaint" rows="3" class="w-full bg-white/10 border border-white/10 rounded px-3 py-2 text-slate-200">{{ old('chief_complaint', $record->chief_complaint ?? '') }}</textarea>
            </div>
            <div>
                <label class="block text-sm text-slate-300 mb-1">Anamnesis</label>
                <textarea name="anamnesis" rows="3" class="w-full bg-white/10 border border-white/10 rounded px-3 py-2 text-slate-200">{{ old('anamnesis', $record->anamnesis ?? '') }}</textarea>
            </div>
        </div>
    </div>

    <!-- Vitals -->
    <div class="p-4 rounded-xl border border-white/10 bg-white/5">
        <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
            <div>
                <label class="block text-sm text-slate-300 mb-1">TD Sistolik (mmHg)</label>
                <input type="number" name="systolic" value="{{ old('systolic', $record->systolic ?? '') }}" class="w-full bg-white/10 border border-white/10 rounded px-3 py-2 text-slate-200">
            </div>
            <div>
                <label class="block text-sm text-slate-300 mb-1">TD Diastolik (mmHg)</label>
                <input type="number" name="diastolic" value="{{ old('diastolic', $record->diastolic ?? '') }}" class="w-full bg-white/10 border border-white/10 rounded px-3 py-2 text-slate-200">
            </div>
            <div>
                <label class="block text-sm text-slate-300 mb-1">Nadi (bpm)</label>
                <input type="number" name="heart_rate" value="{{ old('heart_rate', $record->heart_rate ?? '') }}" class="w-full bg-white/10 border border-white/10 rounded px-3 py-2 text-slate-200">
            </div>
            <div>
                <label class="block text-sm text-slate-300 mb-1">RR (/menit)</label>
                <input type="number" name="respiratory_rate" value="{{ old('respiratory_rate', $record->respiratory_rate ?? '') }}" class="w-full bg-white/10 border border-white/10 rounded px-3 py-2 text-slate-200">
            </div>
            <div>
                <label class="block text-sm text-slate-300 mb-1">Suhu (°C)</label>
                <input type="number" step="0.1" name="body_temperature" value="{{ old('body_temperature', $record->body_temperature ?? '') }}" class="w-full bg-white/10 border border-white/10 rounded px-3 py-2 text-slate-200">
            </div>
            <div>
                <label class="block text-sm text-slate-300 mb-1">BB (kg)</label>
                <input type="number" step="0.1" name="weight" value="{{ old('weight', $record->weight ?? '') }}" class="w-full bg-white/10 border border-white/10 rounded px-3 py-2 text-slate-200">
            </div>
        </div>
    </div>

    <!-- Diagnosis & Therapy -->
    <div class="p-4 rounded-xl border border-white/10 bg-white/5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="block text-sm text-slate-300 mb-1">Kode Diagnosis (ICD)</label>
                <input type="text" name="diagnosis_code" value="{{ old('diagnosis_code', $record->diagnosis_code ?? '') }}" class="w-full bg-white/10 border border-white/10 rounded px-3 py-2 text-slate-200">
            </div>
            <div>
                <label class="block text-sm text-slate-300 mb-1">Nama Diagnosis</label>
                <input type="text" name="diagnosis_name" value="{{ old('diagnosis_name', $record->diagnosis_name ?? '') }}" class="w-full bg-white/10 border border-white/10 rounded px-3 py-2 text-slate-200">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
            <div>
                <label class="block text-sm text-slate-300 mb-1">Terapi</label>
                <textarea name="therapy" rows="3" class="w-full bg-white/10 border border-white/10 rounded px-3 py-2 text-slate-200">{{ old('therapy', $record->therapy ?? '') }}</textarea>
            </div>
            <div>
                <label class="block text-sm text-slate-300 mb-1">Tindakan</label>
                <textarea name="procedure" rows="3" class="w-full bg-white/10 border border-white/10 rounded px-3 py-2 text-slate-200">{{ old('procedure', $record->procedure ?? '') }}</textarea>
            </div>
        </div>
    </div>

    <!-- Medicine repeater -->
    <div class="p-4 rounded-xl border border-white/10 bg-white/5" x-data="medicineRepeater()" x-init="init()">
        <div class="flex items-center justify-between mb-2">
            <div class="text-slate-200 font-semibold">Resep Obat</div>
            <button type="button" @click="add()" class="px-2 py-1 rounded bg-white/10 border border-white/10 text-slate-200 hover:bg-white/15">+ Tambah Baris</button>
        </div>
        <template x-for="(row, idx) in rows" :key="row.key">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-2 items-end mb-2">
                <div class="md:col-span-2">
                    <label class="block text-xs text-slate-300 mb-1">Obat</label>
                    <select :name="`usages[${idx}][medicine_id]`" class="w-full bg-white/10 border border-white/10 rounded px-2 py-1 text-slate-200" @change="compose()">
                        <option value="">Pilih Obat...</option>
                        @foreach(($medicines ?? []) as $id=>$label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-300 mb-1">Jumlah</label>
                    <input type="number" :name="`usages[${idx}][quantity_used]`" class="w-full bg-white/10 border border-white/10 rounded px-2 py-1 text-slate-200" @input="compose()">
                </div>
                <div>
                    <label class="block text-xs text-slate-300 mb-1">Instruksi (3dd1)</label>
                    <input type="text" :name="`usages[${idx}][instruction_text]`" class="w-full bg-white/10 border border-white/10 rounded px-2 py-1 text-slate-200" placeholder="3dd1" @input="compose()">
                </div>
                <div>
                    <label class="block text-xs text-slate-300 mb-1">Frekuensi (3x1)</label>
                    <input type="text" :name="`usages[${idx}][frequency]`" class="w-full bg-white/10 border border-white/10 rounded px-2 py-1 text-slate-200" placeholder="3x1" @input="compose()">
                </div>
                <div class="flex items-center gap-2">
                    <div class="flex-1">
                        <label class="block text-xs text-slate-300 mb-1">Catatan</label>
                        <input type="text" :name="`usages[${idx}][notes]`" class="w-full bg-white/10 border border-white/10 rounded px-2 py-1 text-slate-200" @input="compose()">
                    </div>
                    <button type="button" @click="remove(idx)" class="px-2 py-1 rounded bg-red-500/20 text-red-200 border border-red-400/20 hover:bg-red-500/30">Hapus</button>
                </div>
            </div>
        </template>
        <div class="mt-3">
            <label class="block text-sm text-slate-300 mb-1">Resep Obat (Teks)</label>
            <textarea id="medicationText" name="medication" rows="3" class="w-full bg-white/10 border border-white/10 rounded px-3 py-2 text-slate-200" readonly>{{ old('medication', $record->medication ?? '') }}</textarea>
            <p class="text-xs text-slate-400 mt-1">Otomatis tersusun memakai frekuensi (3x1). Jika kosong, gunakan instruksi (3dd1).</p>
        </div>
    </div>
</div>

@php
    // Data untuk prefill input repeater saat edit
    $usagesForPrefill = (isset($record) && $record->relationLoaded('medicineUsages'))
        ? $record->medicineUsages->map(function ($u) {
            return [
                'medicine_id' => (int) $u->medicine_id,
                'quantity_used' => (int) $u->quantity_used,
                'instruction_text' => $u->instruction_text,
                'frequency' => $u->frequency,
                'notes' => $u->notes,
            ];
        })->values()
        : collect();
@endphp

@push('scripts')
<script>
function debounce(fn, wait){ let t; return function(...args){ clearTimeout(t); t=setTimeout(()=>fn.apply(this,args), wait); } }

function patientTypeahead() {
    return {
        mapLabelToId: {},
        init() {
            const input = document.getElementById('patientSearch');
            const hidden = document.getElementById('family_member_id');
            const list = document.getElementById('patientList');
            if (!input || !hidden || !list) return;

            // Prefill label jika sudah ada id (edit/old)
            const existingId = hidden.value;
            @php
                $prefillLabel = null;
                if (old('family_member_id')) {
                    $oldId = (int) old('family_member_id');
                    $prefillLabel = ($members[$oldId] ?? null) ? ($members[$oldId] . ' — ID ' . $oldId) : null;
                } elseif (isset($record) && isset($record->familyMember)) {
                    $prefillLabel = $record->familyMember->name . ' — ID ' . $record->familyMember->id;
                } elseif (!empty($record->patient_name ?? '')) {
                    $prefillLabel = $record->patient_name;
                }
            @endphp
            const prefill = @json($prefillLabel);
            if (existingId && prefill) input.value = prefill;

            const fetchSuggestions = debounce(async (q) => {
                if (!q || q.length < 2) { list.innerHTML=''; return; }
                try {
                    const res = await fetch(`/api/family-members/search?q=${encodeURIComponent(q)}`);
                    const items = await res.json();
                    list.innerHTML = '';
                    this.mapLabelToId = {};
                    items.forEach(it => {
                        const label = `${it.name} — RM ${it.rm_number ?? '-'} — ${it.nik ?? '-'}`;
                        const opt = document.createElement('option');
                        opt.value = label;
                        list.appendChild(opt);
                        this.mapLabelToId[label] = String(it.id);
                    });
                } catch (e) {
                    console.error(e);
                }
            }, 250);

            input.addEventListener('input', (e) => {
                fetchSuggestions(e.target.value);
            });

            input.addEventListener('change', () => {
                const label = input.value;
                const id = this.mapLabelToId[label];
                if (id) {
                    hidden.value = id;
                } else {
                    // Jika user ketik bebas tanpa memilih, kosongkan id agar validasi server menangkap
                    hidden.value = '';
                }
            });
        }
    }
}

function medicineRepeater() {
    return {
        rows: [],
        init() {
            // Hydrate existing usages for edit page
            @if(isset($record) && $record->relationLoaded('medicineUsages'))
                @foreach($record->medicineUsages as $i => $u)
                    this.rows.push({ key: Date.now() + {{ $i }}, medicine_id: {{ (int) $u->medicine_id }}, quantity_used: {{ (int) $u->quantity_used }}, instruction_text: @json($u->instruction_text), frequency: @json($u->frequency), notes: @json($u->notes) });
                @endforeach
            @endif
            if (this.rows.length === 0) this.add();
            this.$nextTick(() => { this.prefillInputs(); this.compose(); });
        },
        add() { this.rows.push({ key: Date.now() + Math.random() }); },
        remove(i) { this.rows.splice(i, 1); this.compose(); },
        prefillInputs() {
            // Pre-fill inputs for edit (bind by order)
            @if(isset($record) && $record->relationLoaded('medicineUsages'))
                const usages = @json($usagesForPrefill);
                document.querySelectorAll('[name^="usages"]').forEach((el) => {
                    const m = el.name.match(/usages\[(\d+)\]\[(.+)\]/);
                    if (!m) return; const idx = +m[1]; const key = m[2];
                    if (usages[idx] && usages[idx][key] !== null) el.value = usages[idx][key];
                });
            @endif
        },
        compose() {
            const rows = [];
            const selects = document.querySelectorAll('select[name^="usages["]');
            const mapMed = {};
            // Build medicine label map from select options (first select holds them)
            if (selects.length > 0) {
                selects[0].querySelectorAll('option').forEach(opt => { if (opt.value) mapMed[opt.value] = opt.textContent.trim(); });
            }
            const container = document.querySelectorAll('[name^="usages["][name$="[medicine_id]"]').forEach((sel, i) => {
                const idx = [...document.querySelectorAll('[name^="usages["][name$="[medicine_id]"]').values()].indexOf(sel);
                const get = (suffix) => document.querySelector(`[name="usages[${idx}][${suffix}]"]`);
                const mid = sel.value || '';
                if (!mid) return;
                const medLabel = mapMed[mid] || `Obat #${mid}`;
                let line = medLabel;
                const qty = get('quantity_used')?.value || '';
                if (qty) {
                    // Extract unit from label (after •)
                    const unit = medLabel.includes('•') ? medLabel.split('•').pop().trim() : '';
                    line += ` - ${qty} ${unit}`;
                }
                const freq = get('frequency')?.value?.trim();
                const instr = get('instruction_text')?.value?.trim();
                let display = freq || '';
                if (!display && instr) {
                    const m1 = instr.match(/^\s*(\d+)\s*dd\s*(\d+)\s*$/i);
                    const m2 = instr.match(/(\d+)\s*d+\s*(\d+)/i);
                    display = m1 ? `${m1[1]}x${m1[2]}` : (m2 ? `${m2[1]}x${m2[2]}` : instr);
                }
                if (display) line += ` (${display})`;
                const notes = get('notes')?.value?.trim();
                if (notes) line += ` - ${notes}`;
                rows.push(line);
            });
            const ta = document.getElementById('medicationText');
            if (ta) ta.value = rows.join('\n');
        }
    }
}
</script>
@endpush
