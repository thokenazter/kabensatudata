@extends('layouts.admin')

@section('admin-content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold text-slate-100">Anggota Keluarga</h1>
    @can('create_family::member')
        <a href="{{ route('panel.family-members.create') }}" class="px-3 py-2 rounded-lg text-white bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 transition">Tambah Anggota</a>
    @endcan
</div>

<form method="GET" class="mb-4 grid grid-cols-1 md:grid-cols-6 gap-2 p-3 rounded-xl border border-white/10 bg-white/5 backdrop-blur">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama/NIK/RM" class="md:col-span-2 bg-white/10 border border-white/10 rounded-lg px-3 py-2 text-slate-200 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-cyan-400/40" />
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
    <select name="family_id" class="bg-white/10 border border-white/10 rounded-lg px-3 py-2 text-slate-200">
        <option value="">Semua Keluarga</option>
        @foreach($families as $id=>$num)
            <option value="{{ $id }}" @selected(request('family_id')==$id)>KK {{ $num }}</option>
        @endforeach
    </select>
    <button class="px-3 py-2 rounded-lg text-slate-200 bg-white/10 border border-white/10 hover:bg-white/15 transition">Filter</button>
</form>

<div class="relative overflow-x-auto rounded-xl border border-white/10 bg-white/5 backdrop-blur">
    <table class="min-w-full text-sm text-slate-200">
        <thead class="bg-white/5 text-slate-300">
            <tr>
                <th class="text-left px-3 py-2">Nama</th>
                <th class="text-left px-3 py-2">NIK</th>
                <th class="text-left px-3 py-2">RM</th>
                <th class="text-left px-3 py-2">KK / Bangunan / Desa</th>
                <th class="text-left px-3 py-2">Gender</th>
                <th class="px-3 py-2">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($members as $m)
                <tr class="border-t border-white/10">
                    <td class="px-3 py-2 text-slate-100">{{ $m->name }}</td>
                    <td class="px-3 py-2 font-mono">{{ $m->nik }}</td>
                    <td class="px-3 py-2 font-mono">{{ $m->rm_number }}</td>
                    <td class="px-3 py-2">
                        KK {{ $m->family->family_number ?? '-' }} / No {{ $m->family->building->building_number ?? '-' }} / {{ $m->family->building->village->name ?? '-' }}
                    </td>
                    <td class="px-3 py-2">{{ $m->gender }}</td>
                    <td class="px-3 py-2 space-x-2 whitespace-nowrap">
                        <a href="{{ route('family-members.show', $m) }}" target="_blank" class="px-2 py-1 rounded text-emerald-200 bg-emerald-500/15 border border-emerald-400/20 hover:bg-emerald-500/25 transition">Detail</a>
                        @can('update_family::member')
                            <a href="{{ route('panel.family-members.edit', $m) }}" class="px-2 py-1 rounded text-white bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 transition">Ubah</a>
                        @endcan
                        @can('delete_family::member')
                            <form action="{{ route('panel.family-members.destroy', $m) }}" method="POST" class="inline" onsubmit="return confirm('Hapus anggota ini?')">
                                @csrf @method('DELETE')
                                <button class="px-2 py-1 rounded bg-red-500/20 text-red-200 border border-red-400/20 hover:bg-red-500/30 transition">Hapus</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-3 py-6 text-center text-slate-400">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4 text-slate-300">{{ $members->links() }}</div>
@endsection
