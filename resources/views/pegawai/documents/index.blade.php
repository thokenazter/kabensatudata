@extends('layouts.pegawai')

@section('pegawai-content')
    <div class="flex items-center justify-between mb-4">
        <div>
            <div class="text-xl font-semibold">Dokumen: {{ $employee->nama }}</div>
            <div class="text-slate-400">Kelola SK, KTP, Foto, dan dokumen lainnya.</div>
        </div>
        <a class="btn btn-primary" href="{{ route('pegawai.employees.documents.create', $employee) }}">Upload Dokumen</a>
    </div>

    @if (session('status'))
        <div class="mb-4 p-3 rounded bg-emerald-500/15 border border-emerald-500/25 text-emerald-300">{{ session('status') }}</div>
    @endif

    <!-- Mobile Cards -->
    <div class="md:hidden grid gap-3">
        @forelse($dokumen as $d)
            <div class="card p-4">
                <div class="flex items-center justify-between">
                    <div class="font-semibold">{{ $d->jenis }}</div>
                    <a class="underline" target="_blank" href="{{ asset('storage/'.$d->file_path) }}">Lihat</a>
                </div>
                @if($d->judul)
                    <div class="text-slate-300 text-sm mt-1">{{ $d->judul }}</div>
                @endif
                <div class="text-slate-400 text-xs mt-1">{{ $d->issued_at?->format('d M Y') ?: '—' }}</div>
                <div class="mt-3 flex gap-2">
                    <a class="btn" href="{{ route('pegawai.employees.documents.edit', [$employee, $d]) }}">Edit</a>
                    <form action="{{ route('pegawai.employees.documents.destroy', [$employee, $d]) }}" method="post" onsubmit="return confirm('Hapus dokumen ini?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn">Hapus</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="text-slate-400">Belum ada dokumen.</div>
        @endforelse
        <div class="px-1">{{ $dokumen->links() }}</div>
    </div>

    <!-- Desktop Table -->
    <div class="hidden md:block card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-white/10">
                    <tr class="text-left">
                        <th class="px-4 py-2">Jenis</th>
                        <th class="px-4 py-2">Judul</th>
                        <th class="px-4 py-2">Tanggal</th>
                        <th class="px-4 py-2">File</th>
                        <th class="px-4 py-2">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dokumen as $d)
                    <tr class="border-t border-white/10">
                        <td class="px-4 py-2">{{ $d->jenis }}</td>
                        <td class="px-4 py-2">{{ $d->judul }}</td>
                        <td class="px-4 py-2">{{ $d->issued_at?->format('d M Y') }}</td>
                        <td class="px-4 py-2"><a class="underline" target="_blank" href="{{ asset('storage/'.$d->file_path) }}">Lihat</a></td>
                        <td class="px-4 py-2 flex gap-2">
                            <a class="btn" href="{{ route('pegawai.employees.documents.edit', [$employee, $d]) }}">Edit</a>
                            <form action="{{ route('pegawai.employees.documents.destroy', [$employee, $d]) }}" method="post" onsubmit="return confirm('Hapus dokumen ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td class="px-4 py-4 text-slate-400" colspan="5">Belum ada dokumen.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">{{ $dokumen->links() }}</div>
    </div>
@endsection
