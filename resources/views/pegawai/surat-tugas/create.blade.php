@extends('layouts.pegawai')

@section('pegawai-content')
    <div class="flex items-center justify-between mb-4">
        <div>
            <div class="text-xl font-semibold">Buat Surat Tugas</div>
            <div class="text-slate-400 text-sm">Untuk: {{ $pegawai->nama }} (NIP: {{ $pegawai->nip ?? '—' }})</div>
        </div>
        <a href="{{ route('pegawai.employees.documents.index', $pegawai) }}" class="btn">Lihat Dokumen</a>
    </div>

    <div class="card p-4 sm:p-5">
        @if(!empty($lastNomor))
            <div class="mb-3 text-xs text-slate-300">
                Nomor surat terakhir: <span class="text-slate-200">{{ $lastNomor }}</span>
                @if(!empty($suggestNomor)) • Saran berikut: <span class="text-cyan-300">{{ $suggestNomor }}</span>@endif
            </div>
        @endif
        <form method="post" action="{{ route('pegawai.employees.surat-tugas.store', $pegawai) }}" class="grid sm:grid-cols-2 gap-4">
            @csrf
            <div class="sm:col-span-2">
                <label class="label">Nomor Surat (opsional)</label>
                <input type="text" name="nomor_surat" class="input w-full" value="{{ old('nomor_surat', $defaults['nomor_surat']) }}" placeholder="cth: 800.1.11.1/004/2025">
            </div>

            <div class="sm:col-span-2">
                <label class="label">Dasar (baris 1) (opsional)</label>
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
                <button type="submit" class="btn btn-primary">Generate Surat & Simpan</button>
                <a href="{{ route('pegawai.employees.edit', $pegawai) }}" class="btn">Batal</a>
            </div>
        </form>
    </div>

    <div class="mt-4 text-xs text-slate-400">
        Catatan: Nilai dalam kurung kurawal pada template (mis. {NAMA}, {NIP}, {PANGKAT_GOL}, {JABATAN}, {DASAR1}, {MAKSUD_TUGAS}, {LAMA_TUGAS}, {TANGGAL_RANGE}, {TANGGAL_SURAT}, {KOTA_TERBIT}, {NOMOR_SURAT}) akan diganti otomatis. Placeholder contoh seperti {IX} juga akan diganti (dipetakan ke Pangkat/Gol).
    </div>
@endsection
