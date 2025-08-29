<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PKM Kaben - Satu Data</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <style>
        .blur-effect { transition: all 0.3s ease-in-out; }
        .health-card { transition: transform 0.2s; }
        .health-card:hover { transform: translateY(-5px); }
        
        .blur-sm {
            filter: blur(4px);
        }
        
        .blur-sm:hover {
            filter: none;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    @include('includes.navbar')

    <!-- Main Content -->
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header with Cover Image -->
            <div class="relative mt-5 mb-8 bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl overflow-hidden">
                <div class="absolute inset-0 bg-blue-900 opacity-20"></div>
                <div class="relative py-8 px-6 sm:px-12">
                    <h2 class="text-3xl font-bold text-white">Detail Anggota Keluarga</h2>
                    <p class="mt-2 text-blue-100">Informasi lengkap mengenai anggota keluarga</p>

                    <div class="mt-6">
                        <a href="{{ route('families.card.member', $familyMember) }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Lihat Kartu Keluarga
                        </a>
                    </div>
                    
                    <!-- Summary Bar -->
                    <div class="mt-6 flex flex-wrap gap-4">
                        <div class="bg-white bg-opacity-20 backdrop-blur-sm px-4 py-2 rounded-full text-white flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                            </svg>
                            {{ auth()->check() ? $familyMember->name : $familyMember->public_name }}
                        </div>
                        
                        <div class="bg-white bg-opacity-20 backdrop-blur-sm px-4 py-2 rounded-full text-white flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm3 1h6v4H7V5zm8 8v2h1v1H4v-1h1v-2a1 1 0 011-1h8a1 1 0 011 1z" clip-rule="evenodd" />
                            </svg>
                            Usia: {{ $familyMember->age }} tahun
                        </div>
                        
                        <div class="bg-white bg-opacity-20 backdrop-blur-sm px-4 py-2 rounded-full text-white flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                            </svg>
                            {{ $familyMember->relationship }}
                        </div>
                        
                        @if($familyMember->family->healthIndex)
                        <div class="bg-white bg-opacity-20 backdrop-blur-sm px-4 py-2 rounded-full text-white flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                            </svg>
                            IKS: {{ number_format($familyMember->family->healthIndex->iks_value * 100, 1) }}% ({{ $familyMember->family->healthIndex->health_status }})
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Guest Warning -->
            @guest
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-8 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            Data sensitif disamarkan. <a href="/admin" class="font-medium underline hover:text-yellow-600">Login</a> untuk akses penuh.
                        </p>
                    </div>
                </div>
            </div>
            @endguest

            <!-- Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Column 1: Basic Information -->
                <div>
                    <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-8">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Informasi Dasar</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            {{-- Menggunakan komponen untuk data sensitif --}}
                            <x-sensitive-data label="Nama" :value="$familyMember->name" :blurredValue="$familyMember->public_name" />
                            <x-sensitive-data label="NIK" :value="$familyMember->nik" :blurredValue="$familyMember->public_nik" />
                            <x-sensitive-data label="Tgl Lahir" :value="$familyMember->formatted_birth_date" :blurredValue="$familyMember->public_birth_date" />
                            <x-sensitive-data label="Hubungan" :value="$familyMember->relationship" />
                            <x-sensitive-data label="Umur" :value="$familyMember->age" :blurredValue="$familyMember->public_age" />
                        </div>
                    </div>

                    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Informasi Tambahan</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <x-sensitive-data label="Agama" :value="$familyMember->religion" />
                            <x-sensitive-data label="Pendidikan" :value="$familyMember->education" />
                            <x-sensitive-data label="Status Pernikahan" :value="$familyMember->marital_status" />
                            <x-sensitive-data label="Pekerjaan" :value="$familyMember->occupation" />
                        </div>
                    </div>
                </div>
                
                <!-- Column 2-3: Health Information (spans 2 columns) -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Informasi Kesehatan -->
                    <x-health-summary-card :familyMember="$familyMember" />
                    
                    <!-- Informasi Keluarga & Lokasi -->
                    <x-location-family-info :familyMember="$familyMember" />
                    
                    <!-- Indeks Keluarga Sehat (IKS) -->
                    <x-family-health-index :family="$familyMember->family" />
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-white border-t py-6 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Copyright -->
            <div class="text-gray-500 text-sm text-center">
                &copy; 2025 Beta|Development. All rights reserved.
            </div>
        </div>
    </footer>
</body>
</html>