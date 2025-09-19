@extends('layouts.admin')

@section('admin-content')
<div class="max-w-5xl">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold text-slate-100">Ubah Rekam Medis</h1>
        <div class="space-x-2">
            <form method="POST" action="{{ route('panel.medical-records.take', $record) }}" class="inline">
                @csrf
                <button class="px-3 py-2 rounded bg-emerald-500/20 text-emerald-200 border border-emerald-400/20 hover:bg-emerald-500/30">Ambil Pasien</button>
            </form>
            <form method="POST" action="{{ route('panel.medical-records.complete', $record) }}" class="inline" onsubmit="return confirm('Selesaikan tahap saat ini?')">
                @csrf
                <button class="px-3 py-2 rounded bg-blue-500/20 text-blue-200 border border-blue-400/20 hover:bg-blue-500/30">Selesai Tahap</button>
            </form>
        </div>
    </div>

    <form method="POST" action="{{ route('panel.medical-records.update', $record) }}" class="space-y-4">
        @csrf
        @method('PUT')
        @include('admin.medical-records._form', ['record' => $record, 'medicines' => $medicines, 'members' => $members])
        <div class="flex items-center gap-2">
            <button class="px-4 py-2 rounded text-white bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500">Perbarui</button>
            <a href="{{ route('panel.medical-records.index') }}" class="px-4 py-2 rounded bg-white/10 border border-white/10 text-slate-200">Batal</a>
        </div>
    </form>
</div>
@endsection

