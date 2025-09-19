@extends('layouts.admin')

@section('admin-content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold text-slate-100">Bangunan</h1>
    @can('create_building')
        <a href="{{ route('panel.buildings.create') }}" class="px-3 py-2 rounded-lg text-white bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 transition">Tambah Bangunan</a>
    @endcan
</div>

<form method="GET" class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-2 p-3 rounded-xl border border-white/10 bg-white/5 backdrop-blur">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nomor/alamat/catatan" class="md:col-span-2 bg-white/10 border border-white/10 rounded-lg px-3 py-2 text-slate-200 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-cyan-400/40" />
    <select name="village_id" class="bg-white/10 border border-white/10 rounded-lg px-3 py-2 text-slate-200">
        <option value="">Semua Desa</option>
        @foreach($villages as $id=>$name)
            <option value="{{ $id }}" @selected(request('village_id')==$id)>{{ $name }}</option>
        @endforeach
    </select>
    <button class="px-3 py-2 rounded-lg text-slate-200 bg-white/10 border border-white/10 hover:bg-white/15 transition">Filter</button>
</form>

<div class="relative overflow-x-auto rounded-xl border border-white/10 bg-white/5 backdrop-blur">
    <table class="min-w-full text-sm text-slate-200">
        <thead class="bg-white/5 text-slate-300">
            <tr>
                <th class="text-left px-3 py-2">No Bangunan</th>
                <th class="text-left px-3 py-2">Desa</th>
                <th class="text-left px-3 py-2">Alamat</th>
                <th class="text-left px-3 py-2">Koordinat</th>
                <th class="text-left px-3 py-2">Keluarga</th>
                <th class="px-3 py-2">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($buildings as $b)
                <tr class="border-t border-white/10">
                    <td class="px-3 py-2 font-medium">{{ $b->building_number }}</td>
                    <td class="px-3 py-2">{{ $b->village->name ?? '-' }}</td>
                    <td class="px-3 py-2">{{ $b->address }}</td>
                    <td class="px-3 py-2 text-xs text-slate-400">{{ $b->latitude }}, {{ $b->longitude }}</td>
                    <td class="px-3 py-2">{{ $b->families_count }}</td>
                    <td class="px-3 py-2 space-x-2 whitespace-nowrap">
                        @can('update_building')
                            <a href="{{ route('panel.buildings.edit', $b) }}" class="px-2 py-1 rounded text-white bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 transition">Ubah</a>
                        @endcan
                        @can('delete_building')
                            <form action="{{ route('panel.buildings.destroy', $b) }}" method="POST" class="inline" onsubmit="return confirm('Hapus bangunan ini?')">
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

<div class="mt-4 text-slate-300">{{ $buildings->links() }}</div>
@endsection
