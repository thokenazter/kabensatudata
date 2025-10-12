@extends('layouts.admin')

@section('admin-content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold text-slate-100">Rekam Medis</h1>
    <div class="space-x-2">
        <a href="{{ route('panel.medical-records.analytics') }}" class="px-3 py-2 rounded-lg text-white bg-gradient-to-r from-violet-500 to-fuchsia-600 hover:from-violet-400 hover:to-fuchsia-500">Analitik</a>
        <a href="{{ route('panel.medical-records.create') }}" class="px-3 py-2 rounded-lg text-white bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500">Tambah</a>
        <a href="{{ route('panel.medical-records.export', array_merge(request()->query(), ['format'=>'excel'])) }}" class="px-3 py-2 rounded bg-white/10 border border-white/10 text-slate-200">Export Excel</a>
        <a href="{{ route('panel.medical-records.export', array_merge(request()->query(), ['format'=>'csv'])) }}" class="px-3 py-2 rounded bg-white/10 border border-white/10 text-slate-200">Export CSV</a>
        <a href="{{ route('panel.medical-records.export', array_merge(request()->query(), ['format'=>'pdf'])) }}" class="px-3 py-2 rounded bg-white/10 border border-white/10 text-slate-200">Export PDF</a>
    </div>
    
</div>

<form method="GET" class="mb-4 grid grid-cols-1 md:grid-cols-7 gap-2 p-3 rounded-xl border border-white/10 bg-white/5 backdrop-blur">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari antrian/nama/RM" class="md:col-span-2 bg-white/10 border border-white/10 rounded-lg px-3 py-2 text-slate-200 placeholder:text-slate-400 focus:outline-none">
    <select name="workflow_status" class="bg-white/10 border border-white/10 rounded-lg px-3 py-2 text-slate-200">
        <option value="">Semua Status</option>
        @php $st = [
            'pending_registration' => 'Pendaftaran',
            'pending_nurse' => 'Perawat',
            'pending_doctor' => 'Dokter',
            'pending_pharmacy' => 'Apoteker',
            'completed' => 'Selesai',
        ]; @endphp
        @foreach($st as $k=>$v)
            <option value="{{ $k }}" @selected(request('workflow_status')==$k)>{{ $v }}</option>
        @endforeach
    </select>
    <select name="priority_level" class="bg-white/10 border border-white/10 rounded-lg px-3 py-2 text-slate-200">
        <option value="">Semua Prioritas</option>
        <option value="normal" @selected(request('priority_level')==='normal')>🟢 Normal</option>
        <option value="urgent" @selected(request('priority_level')==='urgent')>🟡 Mendesak</option>
        <option value="emergency" @selected(request('priority_level')==='emergency')>🔴 Darurat</option>
    </select>
    <select name="patient_gender" class="bg-white/10 border border-white/10 rounded-lg px-3 py-2 text-slate-200">
        <option value="">Gender</option>
        <option value="Laki-laki" @selected(request('patient_gender')==='Laki-laki')>Laki-laki</option>
        <option value="Perempuan" @selected(request('patient_gender')==='Perempuan')>Perempuan</option>
    </select>
    <select name="diagnosis_name" class="bg-white/10 border border-white/10 rounded-lg px-3 py-2 text-slate-200">
        <option value="">Diagnosis</option>
        @foreach($diagnoses as $diag)
            <option value="{{ $diag }}" @selected(request('diagnosis_name')===$diag)>{{ $diag }}</option>
        @endforeach
    </select>
    <input type="date" name="visit_from" value="{{ request('visit_from') }}" class="bg-white/10 border border-white/10 rounded-lg px-3 py-2 text-slate-200">
    <input type="date" name="visit_until" value="{{ request('visit_until') }}" class="bg-white/10 border border-white/10 rounded-lg px-3 py-2 text-slate-200">
    <button class="px-3 py-2 rounded-lg text-slate-200 bg-white/10 border border-white/10 hover:bg-white/15">Filter</button>
</form>

<div class="relative overflow-x-auto rounded-xl border border-white/10 bg-white/5 backdrop-blur">
    <table class="min-w-full text-sm text-slate-200">
        <thead class="bg-white/5 text-slate-300">
            <tr>
                @php
                    $columns = [
                        'queue_number' => 'Antrian',
                        'patient_name' => 'Pasien',
                        'visit_date' => 'Tanggal',
                        'diagnosis_name' => 'Diagnosis',
                        'priority_level' => 'Prioritas',
                        'workflow_status' => 'Status',
                        'currentHandler' => 'Ditangani Oleh',
                        'creator' => 'Dibuat Oleh',
                    ];
                    $currentSort = $sort ?? 'queue_number';
                    $currentDir = $dir ?? 'desc';
                @endphp
                @foreach($columns as $key => $label)
                    <th class="text-left px-3 py-2 whitespace-nowrap">
                        @if(in_array($key, ['queue_number','visit_date','patient_name']))
                            @php $nextDir = ($currentSort === $key && $currentDir === 'asc') ? 'desc' : 'asc'; @endphp
                            <a href="?{{ http_build_query(array_merge(request()->query(), ['sort'=>$key,'dir'=>$nextDir])) }}" class="hover:underline">{{ $label }} @if($currentSort===$key)<span class="text-slate-400">{{ $currentDir==='asc'?'▲':'▼' }}</span>@endif</a>
                        @else
                            {{ $label }}
                        @endif
                    </th>
                @endforeach
                <th class="px-3 py-2">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $r)
                <tr class="border-t border-white/10">
                    <td class="px-3 py-2 font-mono font-semibold text-emerald-300">🎯 {{ $r->queue_number }}</td>
                    <td class="px-3 py-2 text-slate-100">{{ $r->patient_name }}</td>
                    <td class="px-3 py-2">{{ optional($r->visit_date)->format('d M Y') }}</td>
                    <td class="px-3 py-2">{{ $r->diagnosis_name }}</td>
                    <td class="px-3 py-2">
                        @php $pc = $r->priority_level; @endphp
                        <span class="px-2 py-1 text-xs rounded {{ $pc==='emergency'?'bg-red-500/15 text-red-200':($pc==='urgent'?'bg-yellow-500/15 text-yellow-200':'bg-emerald-500/15 text-emerald-200') }}">
                            {{ $pc==='emergency' ? '🔴 Darurat' : ($pc==='urgent' ? '🟡 Mendesak' : '🟢 Normal') }}
                        </span>
                    </td>
                    <td class="px-3 py-2">
                        @php $wst = $r->workflow_status; @endphp
                        <span class="px-2 py-1 text-xs rounded bg-white/10 border border-white/10">
                            {{ match($wst){
                                'pending_registration'=>'Menunggu Pendaftaran',
                                'pending_nurse'=>'Menunggu Perawat',
                                'pending_doctor'=>'Menunggu Dokter',
                                'pending_pharmacy'=>'Menunggu Apoteker',
                                'completed'=>'Selesai',
                                default => $wst
                            } }}
                        </span>
                    </td>
                    <td class="px-3 py-2">{{ $r->currentHandler->name ?? '-' }}</td>
                    <td class="px-3 py-2">{{ $r->creator->name ?? '-' }}</td>
                    <td class="px-3 py-2 space-x-2 whitespace-nowrap">
                        <a href="{{ route('panel.medical-records.edit', $r) }}" class="px-2 py-1 rounded text-white bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500">Ubah</a>
                        <form action="{{ route('panel.medical-records.take', $r) }}" method="POST" class="inline">
                            @csrf
                            <button class="px-2 py-1 rounded bg-emerald-500/20 text-emerald-200 border border-emerald-400/20 hover:bg-emerald-500/30">Ambil</button>
                        </form>
                        <form action="{{ route('panel.medical-records.complete', $r) }}" method="POST" class="inline" onsubmit="return confirm('Selesaikan tahap?')">
                            @csrf
                            <button class="px-2 py-1 rounded bg-blue-500/20 text-blue-200 border border-blue-400/20 hover:bg-blue-500/30">Selesai</button>
                        </form>
                        <form action="{{ route('panel.medical-records.destroy', $r) }}" method="POST" class="inline" onsubmit="return confirm('Hapus rekam medis ini?')">
                            @csrf @method('DELETE')
                            <button class="px-2 py-1 rounded bg-red-500/20 text-red-200 border border-red-400/20 hover:bg-red-500/30">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="px-3 py-6 text-center text-slate-400">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4 text-slate-300">
    {{ $records->links() }}
</div>
@endsection
