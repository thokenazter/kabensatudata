@extends('errors.minimal')

@section('title', 'Layanan Tidak Tersedia')
@section('code', '503')
@section('message')
<div class="space-y-4 text-left">
    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Layanan sementara tidak tersedia</h2>
    <p class="text-gray-600 dark:text-gray-400">Aplikasi sedang dalam pemeliharaan atau mengalami beban tinggi.</p>
    <div class="pt-2">
        <a href="{{ url('/dashboard') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition">Kembali ke Dashboard</a>
    </div>
</div>
@endsection
