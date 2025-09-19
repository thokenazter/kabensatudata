@extends('layouts.admin')

@section('admin-content')
<div class="max-w-5xl">
    <h1 class="text-2xl font-bold text-slate-100 mb-4">Tambah Rekam Medis</h1>

    <form method="POST" action="{{ route('panel.medical-records.store') }}" class="space-y-4">
        @csrf
        @include('admin.medical-records._form', ['record' => $record, 'medicines' => $medicines, 'members' => $members])
        <div class="flex items-center gap-2">
            <button class="px-4 py-2 rounded text-white bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500">Simpan</button>
            <a href="{{ route('panel.medical-records.index') }}" class="px-4 py-2 rounded bg-white/10 border border-white/10 text-slate-200">Batal</a>
        </div>
    </form>
</div>
@endsection

