@extends('errors::minimal')

@section('title', 'Akses Ditolak')
@section('code', '403')
@section('message')
<div class="space-y-4 text-left">
    <h2 class="text-xl font-semibold text-slate-100">Oops, akses anda dibatasi.</h2>
    <p class="text-slate-300">Anda tidak memiliki hak untuk melihat Rekam Medis Pasien. Hanya tenaga kesehatan (nakes) atau super admin yang dapat membuka halaman ini.</p>
    <p class="text-slate-400 text-sm">Jika Anda adalah nakes atau petugas puskesmas, silakan hubungi administrator untuk mengaktifkan akses.</p>
    <div class="pt-2">
        <a href="{{ url()->previous() ?? url('/') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-500 text-white text-sm font-medium hover:bg-blue-600 transition">Kembali</a>
    </div>
</div>
@endsection
