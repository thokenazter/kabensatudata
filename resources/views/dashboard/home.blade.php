@extends('layouts.app')

@section('content')
    <div class="min-h-screen">
        <!-- Navbar -->
        @include('includes.navbar')

        <!-- Main Content -->
        <main>

            <div class="mt-[80px] px-4">
                @include('home.iks-dashboard')
            </div>
            
            <!-- Filter Section -->
            {{-- @include('includes.filter') --}}

            {{-- Person Info Detail --}}
            @include('includes.detailinfoperson')

            <!-- Stats Cards -->
            @include('includes.stats')

            @include('includes.kia')

            {{-- JKN --}}
            @include('includes.jkn')

            <!-- Statistik Air Bersih dan Sanitasi Cards -->
            @include('includes.sanitation')

            <!-- Tambahkan visualisasi sanitasi di bagian Charts Section -->
            @include('includes.distribution')
            
            <!-- Tambahkan visualisasi peta setelah bagian charts -->
            @include('includes.sebaran')

            <!-- Table Section -->
            {{-- @include('includes.penduduk') --}}
        </main>
        @include('includes.footer')
    </div>
@endsection


