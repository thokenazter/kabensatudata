@extends('layouts.admin')

@section('admin-content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold text-slate-100">Analitik Rekam Medis</h1>
    <div class="text-slate-300 text-sm">Visualisasi kunjungan dan distribusi pasien</div>
    
</div>

<div class="mb-4 grid grid-cols-1 md:grid-cols-5 gap-2 p-3 rounded-xl border border-white/10 bg-white/5 backdrop-blur">
    <div class="md:col-span-1">
        <label class="block text-xs text-slate-300 mb-1">Dari Tanggal</label>
        <input type="date" id="visit_from" value="{{ $defaultFrom }}" class="w-full bg-white/10 border border-white/10 rounded-lg px-3 py-2 text-slate-200 placeholder:text-slate-400 focus:outline-none">
    </div>
    <div class="md:col-span-1">
        <label class="block text-xs text-slate-300 mb-1">Sampai Tanggal</label>
        <input type="date" id="visit_until" value="{{ $defaultUntil }}" class="w-full bg-white/10 border border-white/10 rounded-lg px-3 py-2 text-slate-200 placeholder:text-slate-400 focus:outline-none">
    </div>
    <div class="md:col-span-1">
        <label class="block text-xs text-slate-300 mb-1">Jenis Kelamin</label>
        <select id="patient_gender" class="w-full bg-white/10 border border-white/10 rounded-lg px-3 py-2 text-slate-200">
            <option value="">Semua</option>
            <option value="Laki-laki">Laki-laki</option>
            <option value="Perempuan">Perempuan</option>
        </select>
    </div>
    <div class="md:col-span-1">
        <label class="block text-xs text-slate-300 mb-1">Diagnosis</label>
        <select id="diagnosis_name" class="w-full bg-white/10 border border-white/10 rounded-lg px-3 py-2 text-slate-200">
            <option value="">Semua</option>
            @foreach($diagnoses as $diag)
                <option value="{{ $diag }}">{{ $diag }}</option>
            @endforeach
        </select>
    </div>
    <div class="md:col-span-1">
        <label class="block text-xs text-slate-300 mb-1">Desa</label>
        <select id="village_id" class="w-full bg-white/10 border border-white/10 rounded-lg px-3 py-2 text-slate-200">
            <option value="">Semua</option>
            @foreach($villages as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>
    </div>

    <div class="md:col-span-5 flex items-center gap-2">
        <button id="applyFilters" class="px-4 py-2 rounded-lg text-white bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500">Terapkan</button>
        <button id="resetFilters" class="px-3 py-2 rounded bg-white/10 border border-white/10 text-slate-200">Reset</button>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="rounded-xl border border-white/10 bg-white/5 backdrop-blur p-4">
        <div class="text-slate-200 font-semibold mb-2">Kunjungan per Hari</div>
        <div class="h-64"><canvas id="chart-visits"></canvas></div>
    </div>
    <div class="rounded-xl border border-white/10 bg-white/5 backdrop-blur p-4">
        <div class="text-slate-200 font-semibold mb-2">Distribusi Diagnosa (Top 10)</div>
        <div class="h-64"><canvas id="chart-diagnosis"></canvas></div>
    </div>
    <div class="rounded-xl border border-white/10 bg-white/5 backdrop-blur p-4">
        <div class="text-slate-200 font-semibold mb-2">Komposisi Jenis Kelamin</div>
        <div class="h-64"><canvas id="chart-gender"></canvas></div>
    </div>
    <div class="rounded-xl border border-white/10 bg-white/5 backdrop-blur p-4">
        <div class="text-slate-200 font-semibold mb-2">Kunjungan per Desa (Top 15)</div>
        <div class="h-64"><canvas id="chart-village"></canvas></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let chartVisits, chartDiagnosis, chartGender, chartVillage;

    function buildQuery() {
        const params = new URLSearchParams();
        const from = document.getElementById('visit_from').value;
        const until = document.getElementById('visit_until').value;
        const gender = document.getElementById('patient_gender').value;
        const diag = document.getElementById('diagnosis_name').value;
        const village = document.getElementById('village_id').value;

        if (from) params.set('visit_from', from);
        if (until) params.set('visit_until', until);
        if (gender) params.set('patient_gender', gender);
        if (diag) params.set('diagnosis_name', diag);
        if (village) params.set('village_id', village);
        return params.toString();
    }

    async function loadCharts() {
        const url = `{{ route('panel.medical-records.analytics-data') }}?${buildQuery()}`;
        const res = await fetch(url, { headers: { 'Accept': 'application/json' }});
        const json = await res.json();
        if (!json.success) return;
        const data = json.data;

        // Destroy previous
        [chartVisits, chartDiagnosis, chartGender, chartVillage].forEach(ch => { if (ch) ch.destroy(); });

        const ctxVisits = document.getElementById('chart-visits').getContext('2d');
        chartVisits = new Chart(ctxVisits, {
            type: data.visitsPerDay.type || 'line',
            data: { labels: data.visitsPerDay.labels, datasets: data.visitsPerDay.datasets },
            options: { responsive: true, maintainAspectRatio: false }
        });

        const ctxDiag = document.getElementById('chart-diagnosis').getContext('2d');
        // add colors
        const diagDs = (data.byDiagnosis.datasets || []).map((d, i) => ({
            ...d,
            backgroundColor: 'rgba(34, 211, 238, 0.6)',
            borderColor: 'rgb(34, 211, 238)'
        }));
        chartDiagnosis = new Chart(ctxDiag, {
            type: data.byDiagnosis.type || 'bar',
            data: { labels: data.byDiagnosis.labels, datasets: diagDs },
            options: { responsive: true, maintainAspectRatio: false }
        });

        const ctxGender = document.getElementById('chart-gender').getContext('2d');
        const genderDs = (data.byGender.datasets || []).map((d, i) => ({
            ...d,
            backgroundColor: ['#38bdf8', '#f472b6', '#94a3b8'].slice(0, (d.data||[]).length)
        }));
        chartGender = new Chart(ctxGender, {
            type: data.byGender.type || 'pie',
            data: { labels: data.byGender.labels, datasets: genderDs },
            options: { responsive: true, maintainAspectRatio: false }
        });

        const ctxVillage = document.getElementById('chart-village').getContext('2d');
        const villageDs = (data.byVillage.datasets || []).map((d, i) => ({
            ...d,
            backgroundColor: 'rgba(168, 85, 247, 0.6)',
            borderColor: 'rgb(168, 85, 247)'
        }));
        chartVillage = new Chart(ctxVillage, {
            type: data.byVillage.type || 'bar',
            data: { labels: data.byVillage.labels, datasets: villageDs },
            options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y' }
        });
    }

    document.getElementById('applyFilters').addEventListener('click', loadCharts);
    document.getElementById('resetFilters').addEventListener('click', () => {
        document.getElementById('visit_from').value = '{{ $defaultFrom }}';
        document.getElementById('visit_until').value = '{{ $defaultUntil }}';
        document.getElementById('patient_gender').value = '';
        document.getElementById('diagnosis_name').value = '';
        document.getElementById('village_id').value = '';
        loadCharts();
    });

    // initial load
    document.addEventListener('DOMContentLoaded', loadCharts);
</script>
@endsection

