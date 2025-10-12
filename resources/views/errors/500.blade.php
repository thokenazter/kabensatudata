@extends('errors.minimal')

@section('title', 'Kesalahan Server')
@section('code', '500')
@section('message')
<div class="space-y-4 text-left">
    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Terjadi kesalahan di server</h2>
    <p class="text-gray-600 dark:text-gray-400">Kami mengalami masalah tak terduga. Silakan coba beberapa saat lagi.</p>
    <ul class="list-disc pl-5 text-gray-600 dark:text-gray-400">
        <li>Jika masalah berlanjut, hubungi administrator.</li>
        <li>Sertakan waktu kejadian dan langkah yang dilakukan.</li>
    </ul>
    <div class="pt-2">
        <a href="{{ url('/dashboard') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition">Kembali ke Dashboard</a>
    </div>
</div>
@endsection
