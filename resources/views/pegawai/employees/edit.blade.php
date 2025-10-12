@extends('layouts.pegawai')

@section('pegawai-content')
    <div class="flex items-center justify-between mb-4">
        <div>
            <div class="text-xl font-semibold">Edit Profil Pegawai</div>
            <div class="text-slate-400">Perbarui data pegawai.</div>
        </div>
        <div class="flex items-center gap-2">
            <a class="btn" href="{{ route('pegawai.employees.documents.index', $pegawai) }}">Kelola Dokumen</a>
            <a class="btn" href="{{ route('pegawai.dashboard') }}">Kembali</a>
        </div>
    </div>

    @if (session('status'))
        <div class="mb-4 p-3 rounded bg-emerald-500/15 border border-emerald-500/25 text-emerald-300">{{ session('status') }}</div>
    @endif

    <div class="grid md:grid-cols-3 gap-4">
        <div class="card p-5">
            <div class="label mb-2">Foto</div>
            @if($pegawai->foto_path)
                <img src="{{ asset('storage/'.$pegawai->foto_path) }}" class="w-full h-48 object-cover rounded mb-3" alt="Foto Pegawai">
            @else
                <div class="text-slate-400 mb-3">Belum ada foto.</div>
            @endif
            <form action="{{ route('pegawai.employees.update', $pegawai) }}" method="post" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input class="block mb-2" type="file" name="foto" accept="image/*">
                <button class="btn w-full">Unggah Foto</button>
            </form>
        </div>

        <div class="card p-5 md:col-span-2">
            <form action="{{ route('pegawai.employees.update', $pegawai) }}" method="post" enctype="multipart/form-data" class="grid md:grid-cols-2 gap-4">
                @csrf
                @method('PUT')
                @if(auth()->user()->hasRole('super_admin'))
                <div class="md:col-span-2">
                    <label class="label">User (opsional)</label>
                    <select class="input w-full" name="user_id">
                        <option value="">-- Pilih User --</option>
                        @foreach(($users ?? []) as $u)
                            <option value="{{ $u->id }}" @selected(old('user_id', $pegawai->user_id) == $u->id)>{{ $u->name }} @if($u->email) — {{ $u->email }} @endif</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div>
                    <label class="label">Nama</label>
                    <input class="input w-full" type="text" name="nama" value="{{ old('nama', $pegawai->nama) }}" required>
                </div>
                <div>
                    <label class="label">NIP</label>
                    <input class="input w-full" type="text" name="nip" value="{{ old('nip', $pegawai->nip) }}">
                </div>
                <div>
                    <label class="label">NIK</label>
                    <input class="input w-full" type="text" name="nik" value="{{ old('nik', $pegawai->nik) }}">
                </div>
                <div>
                    <label class="label">Jenis Kelamin</label>
                    <select class="input w-full" name="jenis_kelamin" required>
                        <option value="L" @selected(old('jenis_kelamin', $pegawai->jenis_kelamin) === 'L')>Laki-laki</option>
                        <option value="P" @selected(old('jenis_kelamin', $pegawai->jenis_kelamin) === 'P')>Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="label">Jabatan</label>
                    <input class="input w-full" type="text" name="jabatan" value="{{ old('jabatan', $pegawai->jabatan) }}">
                </div>
                <div>
                    <label class="label">Unit</label>
                    <input class="input w-full" type="text" name="unit" value="{{ old('unit', $pegawai->unit) }}">
                </div>
                <div>
                    <label class="label">Pangkat/Gol</label>
                    <input class="input w-full" type="text" name="pangkat_gol" value="{{ old('pangkat_gol', $pegawai->pangkat_gol) }}" placeholder="contoh: III/a">
                </div>
                <div>
                    <label class="label">Pendidikan Terakhir</label>
                    <select class="input w-full" name="pendidikan_terakhir" required>
                        @foreach(['SMA','SMK','D3','S1','S2','Profesi'] as $opt)
                            <option value="{{ $opt }}" @selected(old('pendidikan_terakhir', $pegawai->pendidikan_terakhir) === $opt)>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Profesi</label>
                    <select class="input w-full" name="profesi" required>
                        @foreach(['Kesehatan Masyarakat','Perawat','Bidan','Sanitarian','Dokter','Farmasi','Analis Kesehatan','Administrasi','Lainnya'] as $opt)
                            <option value="{{ $opt }}" @selected(old('profesi', $pegawai->profesi) === $opt)>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Tanggal Lahir</label>
                    <input class="input w-full" type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $pegawai->tanggal_lahir?->format('Y-m-d')) }}">
                </div>
                <div>
                    <label class="label">No. HP</label>
                    <input class="input w-full" type="text" name="no_hp" value="{{ old('no_hp', $pegawai->no_hp) }}">
                </div>
                <div class="md:col-span-2">
                    <label class="label">Alamat</label>
                    <textarea class="input w-full" name="alamat" rows="3">{{ old('alamat', $pegawai->alamat) }}</textarea>
                </div>
                <div>
                    <label class="label">KTP (jpg/png/pdf)</label>
                    @if($pegawai->ktp_path)
                        <div class="mb-2"><a class="underline" href="{{ asset('storage/'.$pegawai->ktp_path) }}" target="_blank">Lihat KTP saat ini</a></div>
                    @endif
                    <input class="block" type="file" name="ktp" accept="image/*,application/pdf">
                </div>
                <div class="md:col-span-2 flex gap-2">
                    <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
@endsection
