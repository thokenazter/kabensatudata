@extends('layouts.pegawai')

@section('pegawai-content')
    <div class="flex items-center justify-between mb-4">
        <div>
            <div class="text-xl font-semibold">Buat Surat Tugas</div>
            <div class="text-slate-400 text-sm">Pilih pegawai pelaksana tugas, isi rincian, dan generate surat.</div>
        </div>
        <a href="{{ route('pegawai.surat-arsip.index') }}" class="btn">E‑Arsip</a>
    </div>

    <div class="card p-4 sm:p-5">
        @if(!empty($last))
            <div class="mb-3 text-xs text-slate-300">
                Nomor surat terakhir: <span class="text-slate-200">{{ $last }}</span>
                @if(!empty($suggest)) • Saran berikut: <span class="text-cyan-300">{{ $suggest }}</span>@endif
            </div>
        @endif
        <form method="post" action="{{ route('pegawai.surat.tugas.store') }}" class="grid sm:grid-cols-2 gap-4">
            @csrf

            <div class="sm:col-span-2">
                <label class="label">Pegawai</label>
                <select name="pegawai_id" class="input w-full" required>
                    <option value="">-- Pilih Pegawai --</option>
                    @foreach($pegawaiList as $p)
                        <option value="{{ $p->id }}">{{ $p->nama }} @if($p->nip) — NIP: {{ $p->nip }} @endif</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="label">Nomor Surat (otomatis bila dikosongkan)</label>
                <input type="text" name="nomor_surat" class="input w-full" value="{{ old('nomor_surat', $defaults['nomor_surat']) }}" placeholder="cth: 800.1.11.1/004/{{ now()->year }}">
            </div>

            <div class="sm:col-span-2">
                <label class="label">Dasar (opsional)</label>
                <textarea name="dasar1" rows="2" class="input w-full" placeholder="Berdasarkan ...">{{ old('dasar1', $defaults['dasar1']) }}</textarea>
            </div>

            <div class="sm:col-span-2">
                <label class="label">Maksud Tugas</label>
                <textarea name="maksud_tugas" rows="3" class="input w-full" required placeholder="Mengikuti ...">{{ old('maksud_tugas', $defaults['maksud_tugas']) }}</textarea>
            </div>

            <div>
                <label class="label">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" class="input w-full" value="{{ old('tanggal_mulai', $defaults['tanggal_mulai']) }}" required>
            </div>
            <div>
                <label class="label">Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" class="input w-full" value="{{ old('tanggal_selesai', $defaults['tanggal_selesai']) }}" required>
            </div>

            <div>
                <label class="label">Kota Dikeluarkan</label>
                <input type="text" name="kota_terbit" class="input w-full" value="{{ old('kota_terbit', $defaults['kota_terbit']) }}" required>
            </div>
            <div>
                <label class="label">Tanggal Surat</label>
                <input type="date" name="tanggal_surat" class="input w-full" value="{{ old('tanggal_surat', $defaults['tanggal_surat']) }}" required>
            </div>

            <div class="sm:col-span-2 flex gap-2">
                <button type="submit" class="btn btn-primary">Generate & Simpan</button>
                <a href="{{ route('pegawai.dashboard') }}" class="btn">Batal</a>
            </div>
        </form>
    </div>
@endsection

