@extends('errors.minimal')

@section('title', 'Akses Panel Ditolak')
@section('code', '403')
@section('message')
<div class="space-y-4 text-left">
    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Anda tidak memiliki akses ke Panel</h2>
    <p class="text-gray-600 dark:text-gray-400">Halaman ini hanya dapat diakses oleh pengguna yang sudah masuk dengan hak akses yang sesuai.</p>
    <ul class="list-disc pl-5 text-gray-600 dark:text-gray-400">
        <li>Jika Anda seharusnya memiliki akses, hubungi administrator.</li>
        <li>Anda dapat kembali ke halaman utama untuk melanjutkan.</li>
    </ul>
    <div class="pt-2">
        <a href="{{ url('/dashboard') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition">Kembali ke Dashboard</a>
    </div>
    <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">Kode: 403 • Panel Access Forbidden</p>
    {{-- Tidak menampilkan tautan login sesuai permintaan --}}
</div>
@endsection
