@extends('layouts.pegawai')

@section('pegawai-content')
    <div class="flex items-center justify-between mb-4">
        <div class="text-xl font-semibold">Upload E‑Arsip</div>
        <a href="{{ route('pegawai.surat-arsip.index') }}" class="btn">Kembali</a>
    </div>

    <div class="card p-4 sm:p-5">
        <form method="post" action="{{ route('pegawai.surat-arsip.store') }}" enctype="multipart/form-data" class="grid sm:grid-cols-2 gap-4">
            @csrf
            <div>
                <label class="label">Jenis</label>
                <select name="jenis" class="input w-full" required>
                    @foreach(['SURAT_TUGAS'=>'Surat Tugas','SURAT_KELUAR'=>'Surat Keluar','SK'=>'SK','SOP'=>'SOP','LAINNYA'=>'Lainnya'] as $k=>$v)
                        <option value="{{ $k }}">{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">Nomor Surat (opsional)</label>
                <input name="nomor_surat" class="input w-full" placeholder="cth: 400.7.7/001">
            </div>
            <div class="sm:col-span-2">
                <label class="label">Perihal</label>
                <input name="perihal" class="input w-full" required placeholder="Deskripsi singkat">
            </div>
            <div>
                <label class="label">Tanggal Surat</label>
                <input type="date" name="issued_at" class="input w-full" required value="{{ now()->toDateString() }}">
            </div>
            <div>
                <label class="label">Pegawai Terkait (opsional)</label>
                <select name="pegawai_id" class="input w-full">
                    <option value="">-- Pilih Pegawai (opsional) --</option>
                    @foreach($pegawaiList as $p)
                        <option value="{{ $p->id }}">{{ $p->nama }} @if($p->nip) — NIP: {{ $p->nip }} @endif</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="label">File</label>
                <input type="file" name="file" class="block" accept="application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/msword,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,image/*" required>
            </div>
            <div class="sm:col-span-2">
                <button class="btn btn-primary" type="submit">Upload & Simpan</button>
            </div>
        </form>
    </div>
@endsection
