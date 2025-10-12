@extends('layouts.pegawai')

@section('pegawai-content')
    <div class="flex items-center justify-between mb-4">
        <div class="text-xl font-semibold">Upload Dokumen</div>
        <a class="btn" href="{{ route('pegawai.employees.documents.index', $employee) }}">Kembali</a>
    </div>
    <div class="card p-5">
        <form action="{{ route('pegawai.employees.documents.store', $employee) }}" method="post" enctype="multipart/form-data" class="grid md:grid-cols-2 gap-4">
            @csrf
            <div>
                <label class="label">Jenis</label>
                <select name="jenis" class="input w-full" required>
                    <option value="SK">SK</option>
                    <option value="KTP">KTP</option>
                    <option value="FOTO">FOTO</option>
                    <option value="LAINNYA">LAINNYA</option>
                </select>
            </div>
            <div>
                <label class="label">Judul</label>
                <input class="input w-full" type="text" name="judul" placeholder="Opsional">
            </div>
            <div>
                <label class="label">Tanggal</label>
                <input class="input w-full" type="date" name="issued_at">
            </div>
            <div class="md:col-span-2">
                <label class="label">Keterangan</label>
                <textarea class="input w-full" name="keterangan" rows="3" placeholder="Opsional"></textarea>
            </div>
            <div class="md:col-span-2">
                <label class="label">File (pdf/jpg/png)</label>
                <input class="block" type="file" name="file" accept="application/pdf,image/*" required>
            </div>
            <div class="md:col-span-2">
                <button class="btn btn-primary" type="submit">Unggah</button>
            </div>
        </form>
    </div>
@endsection

