@extends('layouts.pegawai')

@section('pegawai-content')
    <div class="flex items-center justify-between mb-4">
        <div class="text-xl font-semibold">Edit Arsip</div>
        <a href="{{ route('pegawai.surat-arsip.index') }}" class="btn">Kembali</a>
    </div>

    <div class="card p-4 sm:p-5">
        <form method="post" action="{{ route('pegawai.surat-arsip.update', $archive) }}" enctype="multipart/form-data" class="grid sm:grid-cols-2 gap-4">
            @csrf
            @method('PUT')
            <div>
                <label class="label">Jenis</label>
                <select name="jenis" class="input w-full" required>
                    @foreach(['SURAT_TUGAS'=>'Surat Tugas','SURAT_KELUAR'=>'Surat Keluar','SK'=>'SK','SOP'=>'SOP','LAINNYA'=>'Lainnya'] as $k=>$v)
                        <option value="{{ $k }}" @selected($archive->jenis===$k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">Nomor Surat (opsional)</label>
                <input name="nomor_surat" class="input w-full" value="{{ old('nomor_surat', $archive->nomor_surat) }}" placeholder="cth: 400.7.7/001">
            </div>
            <div class="sm:col-span-2">
                <label class="label">Perihal</label>
                <input name="perihal" class="input w-full" required value="{{ old('perihal', $archive->perihal) }}">
            </div>
            <div>
                <label class="label">Tanggal Surat</label>
                <input type="date" name="issued_at" class="input w-full" required value="{{ old('issued_at', optional($archive->issued_at)->format('Y-m-d')) }}">
            </div>
            <div>
                <label class="label">Pegawai Terkait (opsional)</label>
                <select name="pegawai_id" class="input w-full">
                    <option value="">-- Pilih Pegawai (opsional) --</option>
                    @foreach($pegawaiList as $p)
                        <option value="{{ $p->id }}" @selected(old('pegawai_id', $archive->pegawai_id) == $p->id)>{{ $p->nama }} @if($p->nip) — NIP: {{ $p->nip }} @endif</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="label">File Saat Ini</label>
                <div class="mb-2"><a class="underline" target="_blank" href="{{ asset('storage/'.$archive->file_path) }}">Unduh file</a></div>
                <label class="label">Ganti File (opsional)</label>
                <input type="file" name="file" class="block" accept="application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/msword,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,image/*">
            </div>
            <div class="sm:col-span-2 flex gap-2">
                <button class="btn btn-primary" type="submit">Simpan</button>
                <a href="{{ route('pegawai.surat-arsip.index') }}" class="btn">Batal</a>
            </div>
        </form>
    </div>
@endsection
