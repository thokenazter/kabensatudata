@extends('layouts.app')

@section('content')
<div class="min-h-screen">
    <h1 class="text-3xl font-bold text-center mt-12">Dashboard Sederhana</h1>
    <p class="text-center">Halaman ini dibuat untuk menguji integrasi dashboard</p>
    
    <div class="max-w-7xl mx-auto mt-8 p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white shadow-md rounded-lg p-4">
                <h2 class="text-xl font-semibold mb-2">Jumlah Keluarga</h2>
                <p class="text-3xl font-bold">{{ isset($stats['families']) ? $stats['families'] : 0 }}</p>
            </div>
            
            <div class="bg-white shadow-md rounded-lg p-4">
                <h2 class="text-xl font-semibold mb-2">Jumlah Penduduk</h2>
                <p class="text-3xl font-bold">{{ isset($stats['members']) ? $stats['members'] : 0 }}</p>
            </div>
            
            <div class="bg-white shadow-md rounded-lg p-4">
                <h2 class="text-xl font-semibold mb-2">Jumlah Bangunan</h2>
                <p class="text-3xl font-bold">{{ isset($totalBuildings) ? $totalBuildings : 0 }}</p>
            </div>
        </div>
    </div>
</div>
@endsection 