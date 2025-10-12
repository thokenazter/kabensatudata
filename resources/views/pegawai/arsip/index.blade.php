@extends('layouts.pegawai')

@section('pegawai-content')
    <div class="flex items-center justify-between mb-4">
        <div class="text-xl font-semibold">E‑Arsip Surat</div>
        <div class="flex gap-2">
            <a href="{{ route('pegawai.surat.tugas.create') }}" class="btn">Buat Surat</a>
            <a href="{{ route('pegawai.surat-arsip.create') }}" class="btn btn-primary">Upload Arsip</a>
        </div>
    </div>

    <form method="get" class="grid sm:grid-cols-3 gap-3 mb-3">
        <div>
            <label class="label">Jenis</label>
            <select name="jenis" class="input w-full" onchange="this.form.submit()">
                    @foreach(['SURAT_TUGAS' => 'Surat Tugas','SURAT_KELUAR'=>'Surat Keluar','SK'=>'SK','SOP'=>'SOP','LAINNYA'=>'Lainnya'] as $val => $label)
                        <option value="{{ $val }}" @selected($jenis===$val)>{{ $label }}</option>
                    @endforeach
            </select>
        </div>
        <div class="sm:col-span-2">
            <label class="label">Cari</label>
            <input name="q" value="{{ $q }}" class="input w-full" placeholder="Nomor surat / Perihal">
        </div>
    </form>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-white/10">
                    <tr class="text-left">
                        <th class="px-4 py-2">Tanggal</th>
                        <th class="px-4 py-2">Nomor</th>
                        <th class="px-4 py-2">Perihal</th>
                        <th class="px-4 py-2">Pegawai</th>
                        <th class="px-4 py-2">File</th>
                        <th class="px-4 py-2">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($arsip as $s)
                        <tr class="border-t border-white/10">
                            <td class="px-4 py-2">{{ $s->issued_at?->format('d M Y') }}</td>
                            <td class="px-4 py-2">{{ $s->nomor_surat ?: '—' }}</td>
                            <td class="px-4 py-2">{{ $s->perihal ?: '—' }}</td>
                            <td class="px-4 py-2">{{ $s->pegawai?->nama ?: '—' }}</td>
                            <td class="px-4 py-2"><a class="btn" href="{{ route('pegawai.surat-arsip.download', $s) }}">Unduh</a></td>
                            <td class="px-4 py-2">
                                <a class="btn" href="{{ route('pegawai.surat-arsip.edit', $s) }}">Edit</a>
                                @if(auth()->user()->hasRole('super_admin') || (auth()->id() === $s->created_by))
                                <form action="{{ route('pegawai.surat-arsip.destroy', $s) }}" method="post" style="display:inline" onsubmit="return confirm('Hapus arsip ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn">Hapus</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td class="px-4 py-4 text-slate-400" colspan="6">Belum ada arsip.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">{{ $arsip->links() }}</div>
    </div>
@endsection
