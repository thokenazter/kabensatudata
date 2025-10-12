@extends('layouts.pegawai')

@section('pegawai-content')
    <div class="flex items-center justify-between mb-4">
        <div>
            <div class="text-xl font-semibold">Buat Profil Pegawai</div>
            <div class="text-slate-400">Lengkapi data pegawai dan unggah KTP/foto.</div>
        </div>
    </div>
    <div class="card p-5">
        <form action="{{ route('pegawai.employees.store') }}" method="post" enctype="multipart/form-data" class="grid md:grid-cols-2 gap-4">
            @csrf
            @if(auth()->user()->hasRole('super_admin'))
            <div class="col-span-2">
                <label class="label">User (opsional)</label>
                <select class="input w-full" name="user_id">
                    <option value="">-- Pilih User --</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} @if($u->email) — {{ $u->email }} @endif</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div>
                <label class="label">Nama</label>
                <input class="input w-full" type="text" name="nama" required>
            </div>
            <div>
                <label class="label">NIP</label>
                <input class="input w-full" type="text" name="nip">
            </div>
            <div>
                <label class="label">NIK</label>
                <input class="input w-full" type="text" name="nik">
            </div>
            <div>
                <label class="label">Jenis Kelamin</label>
                <select class="input w-full" name="jenis_kelamin" required>
                    <option value="">-- Pilih --</option>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>
            </div>
            <div>
                <label class="label">Jabatan</label>
                <input class="input w-full" type="text" name="jabatan">
            </div>
            <div>
                <label class="label">Unit</label>
                <input class="input w-full" type="text" name="unit">
            </div>
            <div>
                <label class="label">Pangkat/Gol</label>
                <input class="input w-full" type="text" name="pangkat_gol" placeholder="contoh: III/a">
            </div>
            <div>
                <label class="label">Pendidikan Terakhir</label>
                <select class="input w-full" name="pendidikan_terakhir" required>
                    <option value="">-- Pilih --</option>
                    <option value="SMA">SMA</option>
                    <option value="SMK">SMK</option>
                    <option value="D3">D3</option>
                    <option value="S1">S1</option>
                    <option value="S2">S2</option>
                    <option value="Profesi">Profesi</option>
                </select>
            </div>
            <div>
                <label class="label">Profesi</label>
                <select class="input w-full" name="profesi" required>
                    <option value="">-- Pilih --</option>
                    <option value="Kesehatan Masyarakat">Kesehatan Masyarakat</option>
                    <option value="Perawat">Perawat</option>
                    <option value="Bidan">Bidan</option>
                    <option value="Sanitarian">Sanitarian</option>
                    <option value="Dokter">Dokter</option>
                    <option value="Farmasi">Farmasi</option>
                    <option value="Analis Kesehatan">Analis Kesehatan</option>
                    <option value="Administrasi">Administrasi</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>
            <div>
                <label class="label">Tanggal Lahir</label>
                <input class="input w-full" type="date" name="tanggal_lahir">
            </div>
            <div>
                <label class="label">No. HP</label>
                <input class="input w-full" type="text" name="no_hp">
            </div>
            <div class="md:col-span-2">
                <label class="label">Alamat</label>
                <textarea class="input w-full" name="alamat" rows="3"></textarea>
            </div>
            <div>
                <label class="label">Foto</label>
                <input class="block" type="file" name="foto" accept="image/*">
            </div>
            <div>
                <label class="label">KTP (jpg/png/pdf)</label>
                <input class="block" type="file" name="ktp" accept="image/*,application/pdf">
            </div>
            <div class="col-span-2 flex gap-2">
                <button class="btn btn-primary" type="submit">Simpan</button>
                <a class="btn" href="{{ route('pegawai.employees.index') }}">Batal</a>
            </div>
        </form>
    </div>
@endsection
