@extends('layouts.pegawai')

@section('pegawai-content')
    <div class="flex items-center justify-between mb-4">
        <div class="text-xl font-semibold">Edit Dokumen</div>
        <a class="btn" href="{{ route('pegawai.employees.documents.index', $employee) }}">Kembali</a>
    </div>
    <div class="card p-5">
        <form action="{{ route('pegawai.employees.documents.update', [$employee, $document]) }}" method="post" enctype="multipart/form-data" class="grid md:grid-cols-2 gap-4">
            @csrf
            @method('PUT')
            <div>
                <label class="label">Jenis</label>
                <select name="jenis" class="input w-full" required>
                    @foreach(['SK','KTP','FOTO','LAINNYA'] as $opt)
                        <option value="{{ $opt }}" @selected($document->jenis === $opt)>{{ $opt }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">Judul</label>
                <input class="input w-full" type="text" name="judul" value="{{ old('judul', $document->judul) }}">
            </div>
            <div>
                <label class="label">Tanggal</label>
                <input class="input w-full" type="date" name="issued_at" value="{{ old('issued_at', $document->issued_at?->format('Y-m-d')) }}">
            </div>
            <div class="md:col-span-2">
                <label class="label">Keterangan</label>
                <textarea class="input w-full" name="keterangan" rows="3">{{ old('keterangan', $document->keterangan) }}</textarea>
            </div>
            <div class="md:col-span-2">
                <label class="label">File Saat Ini</label>
                <div class="mb-2"><a class="underline" href="{{ asset('storage/'.$document->file_path) }}" target="_blank">Lihat file</a></div>
                <label class="label">Ganti File (opsional)</label>
                <input class="block" type="file" name="file" accept="application/pdf,image/*">
            </div>
            <div class="md:col-span-2">
                <button class="btn btn-primary" type="submit">Simpan</button>
            </div>
        </form>
    </div>
@endsection

