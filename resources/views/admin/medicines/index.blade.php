@extends('layouts.admin')

@section('admin-content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold text-slate-100">Daftar Obat</h1>
    <div class="space-x-2">
        @can('create_medicine')
            <a href="{{ route('panel.medicines.create') }}" class="px-3 py-2 rounded-lg text-white bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 shadow-[0_0_15px_rgba(59,130,246,0.35)] transition">Tambah Obat</a>
        @endcan
    </div>
</div>

<form method="GET" class="mb-4 grid grid-cols-1 md:grid-cols-5 gap-2 p-3 rounded-xl border border-white/10 bg-white/5 backdrop-blur">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama/generik" class="md:col-span-2 bg-white/10 border border-white/10 rounded-lg px-3 py-2 text-slate-200 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-cyan-400/40">
    <select name="stock_status" class="bg-white/10 border border-white/10 rounded-lg px-3 py-2 text-slate-200">
        <option value="">Semua Stok</option>
        <option value="available" @selected(request('stock_status')==='available')>Tersedia</option>
        <option value="low" @selected(request('stock_status')==='low')>Stok Menipis</option>
        <option value="out" @selected(request('stock_status')==='out')>Habis</option>
    </select>
    <select name="is_active" class="bg-white/10 border border-white/10 rounded-lg px-3 py-2 text-slate-200">
        <option value="">Semua Status</option>
        <option value="1" @selected(request('is_active')==='1')>Aktif</option>
        <option value="0" @selected(request('is_active')==='0')>Tidak Aktif</option>
    </select>
    <button class="px-3 py-2 rounded-lg text-slate-200 bg-white/10 border border-white/10 hover:bg-white/15 transition">Filter</button>
    
</form>

<div class="relative overflow-x-auto rounded-xl border border-white/10 bg-white/5 backdrop-blur">
    <table class="min-w-full text-sm text-slate-200">
        <thead class="bg-white/5 text-slate-300">
            <tr>
                @php
                    $columns = [
                        'name' => 'Nama Obat',
                        'generic_name' => 'Nama Generik',
                        'strength' => 'Kekuatan',
                        'unit' => 'Satuan',
                        'stock_quantity' => 'Stok',
                        'stock_initial' => 'Stok Awal',
                        'minimum_stock' => 'Stok Min',
                        'is_active' => 'Status',
                    ];
                    $currentSort = $sort ?? 'name';
                    $currentDir = $dir ?? 'asc';
                @endphp
                @foreach($columns as $key => $label)
                    <th class="text-left px-3 py-2 whitespace-nowrap">
                        @if(in_array($key, ['name','stock_quantity','unit']))
                            @php
                                $nextDir = ($currentSort === $key && $currentDir === 'asc') ? 'desc' : 'asc';
                            @endphp
                            <a href="?{{ http_build_query(array_merge(request()->query(), ['sort' => $key, 'dir' => $nextDir])) }}" class="hover:underline">
                                {{ $label }}
                                @if($currentSort === $key)
                                    <span class="text-slate-400">{{ $currentDir === 'asc' ? '▲' : '▼' }}</span>
                                @endif
                            </a>
                        @else
                            {{ $label }}
                        @endif
                    </th>
                @endforeach
                <th class="px-3 py-2">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($medicines as $m)
                <tr class="border-t border-white/10">
                    <td class="px-3 py-2 font-medium text-slate-100">{{ $m->name }}</td>
                    <td class="px-3 py-2">{{ $m->generic_name }}</td>
                    <td class="px-3 py-2">{{ $m->strength }}</td>
                    <td class="px-3 py-2"><span class="px-2 py-1 text-xs rounded bg-white/10 text-slate-200">{{ $m->unit }}</span></td>
                    <td class="px-3 py-2">
                        <span class="{{ $m->isOutOfStock() ? 'text-red-400 font-semibold' : ($m->isLowStock() ? 'text-yellow-300 font-semibold' : 'text-emerald-300') }}">{{ $m->stock_quantity }}</span>
                    </td>
                    <td class="px-3 py-2">{{ $m->stock_initial ?? '—' }}</td>
                    <td class="px-3 py-2 text-slate-400">{{ $m->minimum_stock }}</td>
                    <td class="px-3 py-2">
                        @if($m->is_active)
                            <span class="px-2 py-1 text-xs rounded bg-emerald-500/15 text-emerald-300">Aktif</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded bg-red-500/15 text-red-300">Tidak Aktif</span>
                        @endif
                    </td>
                    <td class="px-3 py-2 space-x-2 whitespace-nowrap">
                        @can('update_medicine')
                            <form action="{{ route('panel.medicines.adjust', $m) }}" method="POST" class="inline-flex items-center space-x-1">
                                @csrf
                                <input type="number" name="adjustment" class="w-20 bg-white/10 border border-white/10 rounded px-2 py-1 text-slate-200" placeholder="±" required>
                                <input type="text" name="reason" class="w-48 bg-white/10 border border-white/10 rounded px-2 py-1 text-slate-200" placeholder="Alasan" required>
                                <button class="px-2 py-1 rounded bg-white/10 border border-white/10 text-slate-200 hover:bg-white/15 transition">Sesuaikan</button>
                            </form>
                            <a href="{{ route('panel.medicines.edit', $m) }}" class="px-2 py-1 rounded text-white bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 transition">Ubah</a>
                        @endcan
                        @can('delete_medicine')
                            <form action="{{ route('panel.medicines.destroy', $m) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button class="px-2 py-1 rounded bg-red-500/20 text-red-200 border border-red-400/20 hover:bg-red-500/30 transition" onclick="return confirm('Hapus obat ini?')">Hapus</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-3 py-6 text-center text-slate-400">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4 text-slate-300">
    {{ $medicines->links() }}
    <div class="text-xs text-slate-400 mt-1">Menampilkan {{ $medicines->firstItem() }}-{{ $medicines->lastItem() }} dari {{ $medicines->total() }} data</div>
    
</div>
@endsection
