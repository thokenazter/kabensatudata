<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Riwayat IKS Keluarga - PKM Kaben Satu Data</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    
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

        /* Tambahkan style untuk responsivitas tabel */
        @media (max-width: 640px) {
            .responsive-table {
                display: block;
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
        }
    </style>

    @include('includes.style')
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    @include('includes.navbar')

    <!-- Main Content -->
    <div class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-[38px] lg:mt-2">
            <!-- Header with Cover Image -->
            <div class="relative mt-5 mb-8 bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl overflow-hidden">
                <div class="absolute inset-0 bg-blue-900 opacity-20"></div>
                <div class="relative py-6 sm:py-8 px-4 sm:px-6 md:px-12">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 sm:gap-0">
                        <div>
                            <h2 class="text-2xl sm:text-3xl font-bold text-white">Riwayat IKS: Keluarga {{ $family->head_name }}</h2>
                            <p class="mt-2 text-blue-100 text-sm sm:text-base">Rekam jejak kesehatan keluarga dan rekomendasi intervensi</p>
                        </div>
                        <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                            <button id="generateRecommendationsBtn" class="inline-flex items-center px-3 sm:px-4 py-2 shadow-lg bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:outline-none focus:ring ring-orange-300 disabled:opacity-25 transition ease-in-out duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 sm:mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                Buat Rekomendasi
                            </button>
                        </div>
                    </div>
                    
                    <!-- Summary Bar -->
                    <div class="mt-4 sm:mt-6 flex flex-wrap gap-3">
                        <div class="bg-white bg-opacity-20 backdrop-blur-sm px-3 sm:px-4 py-2 rounded-full text-white flex items-center text-xs sm:text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 mr-1 sm:mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                            </svg>
                            Anggota: {{ $family->members->count() }} orang
                        </div>
                        
                        <div class="bg-white bg-opacity-20 backdrop-blur-sm px-3 sm:px-4 py-2 rounded-full text-white flex items-center text-xs sm:text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 mr-1 sm:mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                            </svg>
                            Desa {{ $family->building->village->name }}, No. {{ $family->building->building_number }}
                        </div>
                        
                        @if($family->healthIndex)
                        <div class="bg-white bg-opacity-20 backdrop-blur-sm px-3 sm:px-4 py-2 rounded-full text-white flex items-center text-xs sm:text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 mr-1 sm:mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                            </svg>
                            IKS: {{ number_format($family->healthIndex->iks_value * 100, 1) }}% ({{ $family->healthIndex->health_status }})
                        </div>
                        @endif
                        
                        @if($family->iks_change != 0)
                        <div class="bg-white bg-opacity-20 backdrop-blur-sm px-3 sm:px-4 py-2 rounded-full text-white flex items-center text-xs sm:text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 mr-1 sm:mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
                            </svg>
                            Perubahan: 
                            <span class="{{ $family->iks_change > 0 ? 'text-green-300' : 'text-red-300' }} ml-1 font-medium">
                                {{ $family->iks_change > 0 ? '+' : '' }}{{ number_format($family->iks_change * 100, 2) }}%
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6 mb-6">
                <!-- Status IKS Terkini -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-4 sm:px-6 py-3 sm:py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900">Status IKS Terkini</h3>
                    </div>
                    <div class="p-4 sm:p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-full flex items-center justify-center {{ $family->healthIndex ? ($family->healthIndex->status_color == 'success' ? 'bg-green-100 text-green-800' : ($family->healthIndex->status_color == 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')) : 'bg-gray-100 text-gray-800' }}">
                                <span class="text-xl sm:text-2xl font-bold">{{ $family->healthIndex ? number_format($family->healthIndex->iks_value * 100) : 0 }}</span>
                            </div>
                            <div class="ml-3 sm:ml-4">
                                <p class="text-base sm:text-lg font-semibold {{ $family->healthIndex ? ($family->healthIndex->status_color == 'success' ? 'text-green-600' : ($family->healthIndex->status_color == 'warning' ? 'text-yellow-600' : 'text-red-600')) : 'text-gray-600' }}">
                                    {{ $family->healthIndex ? $family->healthIndex->health_status : 'Belum dihitung' }}
                                </p>
                                <p class="text-xs sm:text-sm text-gray-500">
                                    {{ $family->healthIndex ? 'Dihitung pada ' . $family->healthIndex->calculated_at->format('d-m-Y') : '' }}
                                </p>
                            </div>
                        </div>
                        @if($family->iks_change != 0)
                            <div class="mt-4 p-3 {{ $family->iks_change > 0 ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }} border rounded-md">
                                <p class="flex items-center text-xs sm:text-sm">
                                    <span class="mr-2">
                                        @if($family->iks_change > 0)
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
                                            </svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1v-5a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586l-4.293-4.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                    </span>
                                    <span class="font-medium">Perubahan:</span>
                                    <span class="{{ $family->iks_change > 0 ? 'text-green-600' : 'text-red-600' }} ml-1 font-medium">
                                        {{ $family->iks_change > 0 ? '+' : '' }}{{ number_format($family->iks_change * 100, 2) }}%
                                    </span>
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Ringkasan Rekomendasi -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-4 sm:px-6 py-3 sm:py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900">Ringkasan Rekomendasi</h3>
                    </div>
                    <div class="p-4 sm:p-6">
                        @if($activeRecommendations->isNotEmpty())
                            <p class="mb-3 sm:mb-4 text-sm"><span class="font-medium">Jumlah rekomendasi aktif:</span> {{ $activeRecommendations->count() }}</p>
                            <div class="grid grid-cols-3 gap-2 sm:gap-3">
                                <div class="bg-red-50 rounded-lg p-2 sm:p-3 text-center">
                                    <div class="text-xl sm:text-2xl font-bold text-red-600">{{ $activeRecommendations->where('priority_level', 'High')->count() }}</div>
                                    <div class="text-xs text-red-600">Prioritas Tinggi</div>
                                </div>
                                <div class="bg-yellow-50 rounded-lg p-2 sm:p-3 text-center">
                                    <div class="text-xl sm:text-2xl font-bold text-yellow-600">{{ $activeRecommendations->where('priority_level', 'Medium')->count() }}</div>
                                    <div class="text-xs text-yellow-600">Prioritas Sedang</div>
                                </div>
                                <div class="bg-green-50 rounded-lg p-2 sm:p-3 text-center">
                                    <div class="text-xl sm:text-2xl font-bold text-green-600">{{ $activeRecommendations->where('priority_level', 'Low')->count() }}</div>
                                    <div class="text-xs text-green-600">Prioritas Rendah</div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-4 text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 sm:h-10 sm:w-10 mx-auto text-gray-400 mb-2 sm:mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                <p class="text-sm sm:text-base">Belum ada rekomendasi aktif</p>
                                <p class="mt-1 sm:mt-2 text-xs sm:text-sm">Klik tombol "Buat Rekomendasi" untuk membuat rekomendasi otomatis</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Prediksi IKS -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-4 sm:px-6 py-3 sm:py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900">Prediksi IKS</h3>
                    </div>
                    <div class="p-4 sm:p-6">
                        @if(!empty($predictions))
                            <div class="border rounded-md p-3 sm:p-4 bg-blue-50 border-blue-200">
                                <div class="flex flex-col sm:flex-row items-center justify-between mb-3 sm:mb-4 gap-2 sm:gap-0">
                                    <div class="text-center sm:text-left">
                                        <span class="text-xs sm:text-sm text-gray-500">Nilai IKS saat ini</span>
                                        <div class="text-base sm:text-xl font-bold">{{ number_format($predictions['current_iks_percentage'], 2) }}%</div>
                                    </div>
                                    <div class="text-xl sm:text-2xl text-blue-500">→</div>
                                    <div class="text-center sm:text-left">
                                        <span class="text-xs sm:text-sm text-gray-500">Prediksi setelah intervensi</span>
                                        <div class="text-base sm:text-xl font-bold text-blue-600">{{ number_format($predictions['predicted_iks_percentage'], 2) }}%</div>
                                    </div>
                                </div>
                                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 sm:gap-0">
                                    <span class="text-xs sm:text-sm {{ $predictions['improvement_percentage'] > 0 ? 'text-green-600' : 'text-gray-600' }}">
                                        Potensi peningkatan: {{ number_format($predictions['improvement_percentage'], 2) }}%
                                    </span>
                                    <span class="text-xs sm:text-sm font-medium px-2 py-1 rounded-full {{ $predictions['predicted_status'] == 'Keluarga Sehat' ? 'bg-green-100 text-green-800' : ($predictions['predicted_status'] == 'Keluarga Pra-Sehat' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ $predictions['predicted_status'] }}
                                    </span>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-4 text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 sm:h-10 sm:w-10 mx-auto text-gray-400 mb-2 sm:mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h10a2 2 0 012 2v14a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-sm sm:text-base">Tidak ada prediksi</p>
                                <p class="mt-1 sm:mt-2 text-xs sm:text-sm">Butuh rekomendasi aktif untuk melihat prediksi peningkatan IKS</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 mb-6">
                <!-- Tabel Riwayat -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-4 sm:px-6 py-3 sm:py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900">Riwayat Perhitungan IKS</h3>
                    </div>
                    <div class="p-4 sm:p-6">
                        @if($histories->count() > 0)
                            <div class="responsive-table">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                            <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai IKS</th>
                                            <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($histories as $item)
                                            <tr>
                                                <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500">
                                                    {{ $item->calculated_at->format('d-m-Y H:i') }}
                                                </td>
                                                <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm font-medium">
                                                    {{ number_format($item->iks_value * 100, 2) }}%
                                                </td>
                                                <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                                    <span class="px-2 py-1 inline-flex text-xs leading-4 sm:leading-5 font-semibold rounded-full 
                                                        {{ $item->status_color == 'success' ? 'bg-green-100 text-green-800' : 
                                                            ($item->status_color == 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                        {{ $item->health_status }}
                                                    </span>
                                                </td>
                                                <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm font-medium">
                                                    <a href="{{ route('families.history.show', ['family' => $family, 'history' => $item]) }}" class="text-blue-600 hover:text-blue-900">
                                                        Detail
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4 text-gray-500">
                                <p class="text-sm">Belum ada riwayat perhitungan IKS</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Rekomendasi Prioritas -->
<div class="bg-white shadow-sm rounded-lg overflow-hidden">
    <div class="px-4 sm:px-6 py-3 sm:py-4 bg-gray-50 border-b border-gray-200">
        <h3 class="text-base sm:text-lg font-semibold text-gray-900">Rekomendasi Prioritas</h3>
    </div>
    <div class="p-4 sm:p-6">
        @if($activeRecommendations->isNotEmpty())
            <div class="space-y-3 sm:space-y-4">
                @foreach($activeRecommendations->take(3) as $recommendation)
                    <div class="border rounded-md p-3 sm:p-4 {{ $recommendation->priority_level == 'High' ? 'border-red-200 bg-red-50' : ($recommendation->priority_level == 'Medium' ? 'border-yellow-200 bg-yellow-50' : 'border-green-200 bg-green-50') }}">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <span class="inline-flex items-center justify-center h-6 w-6 sm:h-8 sm:w-8 rounded-full {{ $recommendation->priority_level == 'High' ? 'bg-red-100' : ($recommendation->priority_level == 'Medium' ? 'bg-yellow-100' : 'bg-green-100') }}">
                                    <svg class="h-4 w-4 sm:h-5 sm:w-5 {{ $recommendation->priority_level == 'High' ? 'text-red-600' : ($recommendation->priority_level == 'Medium' ? 'text-yellow-600' : 'text-green-600') }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </span>
                            </div>
                            <div class="ml-3">
                                <h5 class="text-xs sm:text-sm font-medium {{ $recommendation->priority_level == 'High' ? 'text-red-800' : ($recommendation->priority_level == 'Medium' ? 'text-yellow-800' : 'text-green-800') }}">
                                    {{ $recommendation->title }}
                                </h5>
                                <p class="mt-1 text-xs {{ $recommendation->priority_level == 'High' ? 'text-red-700' : ($recommendation->priority_level == 'Medium' ? 'text-yellow-700' : 'text-green-700') }}">
                                    {{ Str::limit($recommendation->description, 80) }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @if($activeRecommendations->count() > 3)
                <div class="mt-4 text-right">
                    <a href="{{ route('filament.admin.resources.iks-recommendations.index', ['tableFilters[family_id][value]' => $family->id]) }}" class="text-xs sm:text-sm text-blue-600 hover:text-blue-800 flex items-center justify-end">
                        Lihat semua rekomendasi
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            @endif
        @else
            <div class="text-center py-6 sm:py-8 text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 sm:h-10 sm:w-10 mx-auto text-gray-400 mb-2 sm:mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <p class="text-sm sm:text-base">Belum ada rekomendasi aktif</p>
                <p class="mt-1 sm:mt-2 text-xs sm:text-sm">Klik tombol "Buat Rekomendasi" untuk membuat rekomendasi otomatis</p>
            </div>
        @endif
    </div>
</div>

<!-- Perbandingan Indikator -->
</div>
@if($histories->count() >= 2)
<div class="bg-white shadow-sm rounded-lg overflow-hidden mb-6">
    <div class="px-4 sm:px-6 py-3 sm:py-4 bg-gray-50 border-b border-gray-200">
        <h3 class="text-base sm:text-lg font-semibold text-gray-900">Perbandingan Indikator</h3>
    </div>
    <div class="p-4 sm:p-6">
        <div class="responsive-table">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Indikator</th>
                        <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Terkini ({{ $histories->first()->calculated_at->format('d-m-Y') }})
                        </th>
                        <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Awal ({{ $histories->last()->calculated_at->format('d-m-Y') }})
                        </th>
                        <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perubahan</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($indicatorComparison as $code => $indicator)
                        @if($indicator['first']['relevant'] && $indicator['last']['relevant'])
                            <tr>
                                <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm font-medium text-gray-900">
                                    {{ $indicator['name'] }}
                                </td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $indicator['first']['status'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $indicator['first']['status'] ? 'Terpenuhi' : 'Tidak Terpenuhi' }}
                                    </span>
                                </td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $indicator['last']['status'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $indicator['last']['status'] ? 'Terpenuhi' : 'Tidak Terpenuhi' }}
                                    </span>
                                </td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500">
                                    @if($indicator['improved'])
                                        <span class="text-green-600 flex items-center">
                                            <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                                            </svg>
                                            Membaik
                                        </span>
                                    @elseif($indicator['declined'])
                                        <span class="text-red-600 flex items-center">
                                            <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                            </svg>
                                            Memburuk
                                        </span>
                                    @else
                                        <span class="text-gray-500">Tidak berubah</span>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
</div>

<!-- Tambahkan ini di bagian bawah halaman sebelum tag </body> -->
<script>
document.addEventListener('DOMContentLoaded', function() {
const generateBtn = document.getElementById('generateRecommendationsBtn');

if (generateBtn) {
generateBtn.addEventListener('click', function() {
    // Tampilkan indikator loading
    generateBtn.disabled = true;
    generateBtn.innerHTML = `
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Membuat Rekomendasi...
    `;
    
    // Kirim permintaan ke endpoint generateRecommendations
    fetch('{{ route("families.generate-recommendations", $family) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Terjadi kesalahan saat membuat rekomendasi');
        }
        return response.json();
    })
    .then(data => {
        // Tampilkan notifikasi sukses
        if (data.success) {
            // Buat elemen notifikasi
            const notification = document.createElement('div');
            notification.className = 'fixed top-16 sm:top-4 right-4 z-50 bg-green-100 border-l-4 border-green-500 text-green-700 p-3 sm:p-4 rounded shadow-md max-w-xs sm:max-w-sm';
            notification.innerHTML = `
                <div class="flex items-center">
                    <div class="py-1">
                        <svg class="h-5 w-5 sm:h-6 sm:w-6 text-green-500 mr-3 sm:mr-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold text-sm sm:text-base">Berhasil!</p>
                        <p class="text-xs sm:text-sm">Berhasil membuat ${data.data.recommendations_count} rekomendasi</p>
                    </div>
                </div>
            `;
            document.body.appendChild(notification);
            
            // Hilangkan notifikasi setelah 5 detik
            setTimeout(() => {
                notification.remove();
            }, 5000);
            
            // Muat ulang halaman setelah 2 detik untuk menampilkan rekomendasi baru
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Tampilkan notifikasi error
        const notification = document.createElement('div');
        notification.className = 'fixed top-16 sm:top-4 right-4 z-50 bg-red-100 border-l-4 border-red-500 text-red-700 p-3 sm:p-4 rounded shadow-md max-w-xs sm:max-w-sm';
        notification.innerHTML = `
            <div class="flex items-center">
                <div class="py-1">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6 text-red-500 mr-3 sm:mr-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-sm sm:text-base">Gagal!</p>
                    <p class="text-xs sm:text-sm">${error.message}</p>
                </div>
            </div>
        `;
        document.body.appendChild(notification);
        
        // Hilangkan notifikasi setelah 5 detik
        setTimeout(() => {
            notification.remove();
        }, 5000);
    })
    .finally(() => {
        // Kembalikan tombol ke keadaan semula
        setTimeout(() => {
            generateBtn.disabled = false;
            generateBtn.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 mr-1 sm:mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Buat Rekomendasi
            `;
        }, 2000);
    });
});
}
});
</script>
</body>
</html>