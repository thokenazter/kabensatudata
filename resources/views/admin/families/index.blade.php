@extends('layouts.admin')

@section('admin-content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold text-slate-100">Keluarga</h1>
    @can('create_family')
        <a href="{{ route('panel.families.create') }}" class="px-3 py-2 rounded-lg text-white bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 transition">Tambah Keluarga</a>
    @endcan
</div>

<form method="GET" class="mb-4 grid grid-cols-1 md:grid-cols-5 gap-2 p-3 rounded-xl border border-white/10 bg-white/5 backdrop-blur">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari KK / Kepala Keluarga" class="md:col-span-2 bg-white/10 border border-white/10 rounded-lg px-3 py-2 text-slate-200 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-cyan-400/40" />
    <select name="village_id" class="bg-white/10 border border-white/10 rounded-lg px-3 py-2 text-slate-200">
        <option value="">Semua Desa</option>
        @foreach($villages as $id=>$name)
            <option value="{{ $id }}" @selected(request('village_id')==$id)>{{ $name }}</option>
        @endforeach
    </select>
    <select name="building_id" class="bg-white/10 border border-white/10 rounded-lg px-3 py-2 text-slate-200">
        <option value="">Semua Bangunan</option>
        @foreach($buildings as $id=>$num)
            <option value="{{ $id }}" @selected(request('building_id')==$id)>No {{ $num }}</option>
        @endforeach
    </select>
    <button class="px-3 py-2 rounded-lg text-slate-200 bg-white/10 border border-white/10 hover:bg-white/15 transition">Filter</button>
    
</form>

<div class="relative overflow-x-auto rounded-xl border border-white/10 bg-white/5 backdrop-blur">
    <table class="min-w-full text-sm text-slate-200">
        <thead class="bg-white/5 text-slate-300">
            <tr>
                <th class="text-left px-3 py-2">No KK</th>
                <th class="text-left px-3 py-2">Kepala Keluarga</th>
                <th class="text-left px-3 py-2">Bangunan / Desa</th>
                <th class="text-left px-3 py-2">Anggota</th>
                <th class="px-3 py-2">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($families as $f)
                <tr class="border-t border-white/10">
                    <td class="px-3 py-2 font-mono text-slate-100">{{ $f->family_number }}</td>
                    <td class="px-3 py-2 text-slate-100">{{ $f->head_name }}</td>
                    <td class="px-3 py-2">No {{ $f->building->building_number ?? '-' }} / {{ $f->building->village->name ?? '-' }}</td>
                    <td class="px-3 py-2">{{ $f->members_count }}</td>
                    <td class="px-3 py-2 space-x-2 whitespace-nowrap">
                        <a href="{{ route('families.card', $f) }}" target="_blank" class="px-2 py-1 rounded text-emerald-200 bg-emerald-500/15 border border-emerald-400/20 hover:bg-emerald-500/25 transition">Kartu</a>
                        <a href="{{ route('families.history', $f) }}" target="_blank" class="px-2 py-1 rounded text-sky-200 bg-sky-500/15 border border-sky-400/20 hover:bg-sky-500/25 transition">Riwayat IKS</a>
                        @can('update_family')
                            <a href="{{ route('panel.families.edit', $f) }}" class="px-2 py-1 rounded text-white bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 transition">Ubah</a>
                        @endcan
                        @can('delete_family')
                            <form action="{{ route('panel.families.destroy', $f) }}" method="POST" class="inline" onsubmit="return confirm('Hapus keluarga ini?')">
                                @csrf @method('DELETE')
                                <button class="px-2 py-1 rounded bg-red-500/20 text-red-200 border border-red-400/20 hover:bg-red-500/30 transition">Hapus</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-3 py-6 text-center text-slate-400">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4 text-slate-300">{{ $families->links() }}</div>
@endsection
