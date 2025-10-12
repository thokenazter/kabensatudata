@extends('errors.minimal')

@section('title', 'Terlalu Banyak Permintaan')
@section('code', '429')
@section('message')
<div class="space-y-4 text-left">
    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Terlalu banyak permintaan</h2>
    <p class="text-gray-600 dark:text-gray-400">Silakan tunggu beberapa saat sebelum mencoba lagi.</p>
    <div class="pt-2">
        <a href="{{ url('/dashboard') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition">Kembali ke Dashboard</a>
    </div>
</div>
@endsection
