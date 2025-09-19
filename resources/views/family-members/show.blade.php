<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KabenSatuData - Detail Anggota</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <style>
        @media print {
            .no-print { display: none !important; }
        }
        .info-card { transition: all 0.2s ease; }
        .info-card:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }
        
        .blur-sm { filter: blur(4px); }
        .blur-sm:hover { filter: none; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <div class="no-print">
        @include('includes.navbar')
    </div>

    <!-- Main Content -->
    <div class="py-12 mt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header - Simpel dan Informatif -->
            <header class="bg-white shadow-sm rounded-lg overflow-hidden mb-6">
                <div class="p-4 sm:p-6 bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-2xl font-bold">{{ auth()->check() ? $familyMember->name : $familyMember->public_name }}</h1>
                            <p class="text-blue-100 mt-1">{{ $familyMember->relationship }} · {{ $familyMember->age }} tahun · {{ $familyMember->gender }}</p>
                        </div>
                        
                        <div class="flex flex-wrap gap-2 mt-4 sm:mt-0">
                            @if($familyMember->family->healthIndex)
                                <div class="bg-white bg-opacity-25 px-3 py-1 rounded-full text-sm">
                                    IKS: {{ number_format($familyMember->family->healthIndex->iks_value * 100, 1) }}%
                                </div>
                            @endif
                            
                            @if($familyMember->rm_number)
                            <div class="bg-white bg-opacity-25 px-3 py-1 rounded-full text-sm">
                                RM: {{ $familyMember->rm_number }}
                            </div>
                            @endif
                            
                            <div class="bg-white bg-opacity-25 px-3 py-1 rounded-full text-sm">
                                NIK: {{ auth()->check() ? ($familyMember->nik ?? '-') : $familyMember->public_nik }}
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons - Compact -->
                <div class="p-3 bg-gray-50 border-t border-gray-200 flex flex-wrap gap-2">
                    @php
                        $building = optional($familyMember->family)->building;
                        $lat = $building->latitude ?? null;
                        $lon = $building->longitude ?? null;
                        $hasCoords = is_numeric($lat) && is_numeric($lon);
                        $canViewMedicalHistory = auth()->check() && auth()->user()->hasAnyRole(['nakes', 'super_admin']);
                    @endphp

                    <a href="{{ route('families.card.member', $familyMember) }}" 
                       class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Kartu Keluarga
                    </a>
                    
                    <a href="{{ route('families.qrcode.member', $familyMember) }}" 
                       class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M3 4a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm2 2V5h1v1H5zM3 13a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1v-3zm2 2v-1h1v1H5z" />
                            <path d="M11 4a1 1 0 10-2 0v1a1 1 0 102 0V4zM10 7a1 1 0 011 1v1h2a1 1 0 110 2h-3a1 1 0 01-1-1V8a1 1 0 011-1zM16 9a1 1 0 100 2 1 1 0 000-2z" />
                        </svg>
                        QR Code
                    </a>
                    
                    <button onclick="window.print()" 
                           class="inline-flex items-center px-3 py-2 bg-gray-600 text-white text-sm rounded-md hover:bg-gray-700 transition ml-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2z" />
                        </svg>
                        Cetak
                    </button>

                    @if($building)
                        @if($hasCoords)
                            <a href="{{ url('/map-vue') }}?lat={{ $lat }}&lon={{ $lon }}&zoom=20&id={{ $building->id }}" target="_blank"
                               class="inline-flex items-center px-3 py-2 bg-emerald-600 text-white text-sm rounded-md hover:bg-emerald-700 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 1 1 18 0Z" />
                                    <circle cx="12" cy="10" r="3" />
                                </svg>
                                Lihat Lokasi di Peta
                            </a>
                        @else
                            <button type="button"
                                    onclick="alert('Lokasi rumah belum memiliki koordinat. Mohon lengkapi latitude/longitude di data bangunan.')"
                                    class="inline-flex items-center px-3 py-2 bg-gray-300 text-gray-600 text-sm rounded-md cursor-not-allowed"
                                    title="Koordinat belum tersedia">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 1 1 18 0Z" />
                                    <line x1="6" y1="6" x2="18" y2="18" />
                                </svg>
                                Lihat Lokasi di Peta
                            </button>
                        @endif
                    @endif
                </div>
            </header>

            <!-- Guest Warning - Lebih Ringkas -->
            @guest
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 mb-4 rounded-r-md text-sm">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/>
                    </svg>
                    <p class="text-yellow-700">
                        Data sensitif disamarkan. <a href="{{ url('/admin/login') }}" class="font-medium underline hover:text-yellow-600">Login</a> untuk akses penuh.
                    </p>
                </div>
            </div>
            @endguest

            <!-- Content Cards - Layout Lebih Efisien -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 {{ !auth()->check() ? 'blur-sm' : '' }}">
                <!-- Informasi Pribadi -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden info-card">
                    <div class="px-4 py-3 bg-blue-50 border-b border-blue-100">
                        <h3 class="text-lg font-medium text-blue-800 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                            </svg>
                            Data Pribadi
                        </h3>
                        <a href="{{ route('medical-records.index', $familyMember) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border-transparent rounded-md text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V5zm11 1H6v8l4-2 4 2V6z" clip-rule="evenodd" />
                            </svg>
                            Riwayat Medis
                        </a>
                    </div>
                    <div class="p-4">
                        <dl class="grid grid-cols-3 gap-y-2 text-sm">
                            <dt class="col-span-1 text-gray-500">Nama</dt>
                            <dd class="col-span-2 font-medium">{{ auth()->check() ? $familyMember->name : $familyMember->public_name }}</dd>
                            
                            @if($familyMember->rm_number)
                            <dt class="col-span-1 text-gray-500">No. RM</dt>
                            <dd class="col-span-2 font-medium text-blue-600">{{ $familyMember->rm_number }}</dd>
                            @endif
                            
                            <dt class="col-span-1 text-gray-500">NIK</dt>
                            <dd class="col-span-2">{{ auth()->check() ? ($familyMember->nik ?? '-') : $familyMember->public_nik }}</dd>
                            
                            <dt class="col-span-1 text-gray-500">Tempat Lahir</dt>
                            <dd class="col-span-2">{{ auth()->check() ? $familyMember->birth_place : $familyMember->public_birth_place }}</dd>
                            
                            <dt class="col-span-1 text-gray-500">Tgl Lahir</dt>
                            <dd class="col-span-2">{{ auth()->check() ? $familyMember->formatted_birth_date : $familyMember->public_birth_date }}</dd>
                            
                            <dt class="col-span-1 text-gray-500">Jenis Kelamin</dt>
                            <dd class="col-span-2">{{ $familyMember->gender }}</dd>
                            
                            <dt class="col-span-1 text-gray-500">Status</dt>
                            <dd class="col-span-2">{{ $familyMember->marital_status ?? '-' }}</dd>
                            
                            <dt class="col-span-1 text-gray-500">Pendidikan</dt>
                            <dd class="col-span-2">{{ $familyMember->education ?? '-' }}</dd>
                            
                            <dt class="col-span-1 text-gray-500">Pekerjaan</dt>
                            <dd class="col-span-2">{{ $familyMember->occupation ?? '-' }}</dd>
                        </dl>
                    </div>
                </div>
                
                <!-- Informasi Keluarga & Lokasi -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden info-card">
                    <div class="px-4 py-3 bg-green-50 border-b border-green-100">
                        <h3 class="text-lg font-medium text-green-800 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                            </svg>
                            Keluarga & Lokasi
                        </h3>
                    </div>
                    <div class="p-4">
                        <dl class="grid grid-cols-3 gap-y-2 text-sm">
                            <dt class="col-span-1 text-gray-500">Kepala Keluarga</dt>
                            <dd class="col-span-2 font-medium">{{ $familyMember->family->head_name }}</dd>
                            
                            <dt class="col-span-1 text-gray-500">Hubungan</dt>
                            <dd class="col-span-2">{{ $familyMember->relationship }}</dd>
                            
                            <dt class="col-span-1 text-gray-500">Desa</dt>
                            <dd class="col-span-2">{{ $familyMember->family->building->village->name }}</dd>
                            
                            <dt class="col-span-1 text-gray-500">No. Bangunan</dt>
                            <dd class="col-span-2">{{ $familyMember->family->building->building_number }}</dd>
                            
                            <dt class="col-span-1 text-gray-500">No. Keluarga</dt>
                            <dd class="col-span-2">{{ $familyMember->family->family_number }}</dd>
                            
                            <dt class="col-span-1 text-gray-500">Jml Anggota</dt>
                            <dd class="col-span-2">{{ $familyMember->family->members->count() }} orang</dd>
                            
                            @if($familyMember->family->healthIndex)
                            <dt class="col-span-1 text-gray-500">Status IKS</dt>
                            <dd class="col-span-2">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    @if($familyMember->family->healthIndex->health_status == 'Sehat')
                                        bg-green-100 text-green-800
                                    @elseif($familyMember->family->healthIndex->health_status == 'Pra-Sehat')
                                        bg-yellow-100 text-yellow-800
                                    @else
                                        bg-red-100 text-red-800
                                    @endif">
                                    {{ $familyMember->family->healthIndex->health_status }}
                                </span>
                            </dd>
                            @endif
                        </dl>
                    </div>
                </div>
                
                <!-- Informasi Kesehatan -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden info-card md:col-span-2">
                    <div class="px-4 py-3 bg-red-50 border-b border-red-100">
                        <h3 class="text-lg font-medium text-red-800 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-red-600" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                            </svg>
                            Informasi Kesehatan
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Kolom 1: Status Kesehatan -->
                            <div>
                                <h4 class="font-medium text-gray-700 mb-2">Status Kesehatan</h4>
                                <ul class="space-y-1 text-sm">
                                    <li class="flex items-center">
                                        <span class="w-6 h-6 flex-shrink-0 mr-2 {{ $familyMember->has_jkn ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500' }} rounded-full flex items-center justify-center text-xs font-medium">
                                            {{ $familyMember->has_jkn ? '✓' : '✕' }}
                                        </span>
                                        <span>Memiliki JKN</span>
                                    </li>
                                    <li class="flex items-center">
                                        <span class="w-6 h-6 flex-shrink-0 mr-2 {{ $familyMember->is_smoker ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }} rounded-full flex items-center justify-center text-xs font-medium">
                                            {{ $familyMember->is_smoker ? '✕' : '✓' }}
                                        </span>
                                        <span>{{ $familyMember->is_smoker ? 'Perokok' : 'Tidak Merokok' }}</span>
                                    </li>
                                    <li class="flex items-center">
                                        @if($canViewMedicalHistory)
                                            <span class="w-6 h-6 flex-shrink-0 mr-2 {{ $familyMember->has_tuberculosis ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }} rounded-full flex items-center justify-center text-xs font-medium">
                                                {{ $familyMember->has_tuberculosis ? '!' : '✓' }}
                                            </span>
                                            <span>
                                                @if($familyMember->has_tuberculosis)
                                                    TBC {{ $familyMember->takes_tb_medication_regularly ? '(Minum Obat Teratur)' : '(Tidak Teratur)' }}
                                                @else
                                                    Tidak TBC
                                                @endif
                                            </span>
                                        @else
                                            <span class="w-6 h-6 flex-shrink-0 mr-2 bg-gray-100 text-gray-500 rounded-full flex items-center justify-center text-xs font-medium">?</span>
                                            <span class="text-sm text-gray-500 italic">Informasi TBC khusus untuk tenaga kesehatan</span>
                                        @endif
                                    </li>
                                    <li class="flex items-center">
                                        @if($canViewMedicalHistory)
                                            <span class="w-6 h-6 flex-shrink-0 mr-2 {{ $familyMember->has_hypertension ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }} rounded-full flex items-center justify-center text-xs font-medium">
                                                {{ $familyMember->has_hypertension ? '!' : '✓' }}
                                            </span>
                                            <span>
                                                @if($familyMember->has_hypertension)
                                                    Hipertensi {{ $familyMember->takes_hypertension_medication_regularly ? '(Minum Obat Teratur)' : '(Tidak Teratur)' }}
                                                @else
                                                    Tidak Hipertensi
                                                @endif
                                            </span>
                                        @else
                                            <span class="w-6 h-6 flex-shrink-0 mr-2 bg-gray-100 text-gray-500 rounded-full flex items-center justify-center text-xs font-medium">?</span>
                                            <span class="text-sm text-gray-500 italic">Informasi hipertensi khusus untuk tenaga kesehatan</span>
                                        @endif
                                    </li>
                                </ul>
                            </div>
                            
                            <!-- Kolom 2: Status Kesehatan Khusus -->
                            <div>
                                <h4 class="font-medium text-gray-700 mb-2">Status Khusus</h4>
                                <ul class="space-y-1 text-sm">
                                    @if($familyMember->gender === 'Perempuan' && $familyMember->is_pregnant)
                                    <li class="flex items-center">
                                        <span class="w-6 h-6 flex-shrink-0 mr-2 bg-blue-100 text-blue-800 rounded-full flex items-center justify-center text-xs font-medium">!</span>
                                        <span>Sedang Hamil</span>
                                    </li>
                                    @endif
                                    
                                    @if($familyMember->uses_contraception)
                                    <li class="flex items-center">
                                        <span class="w-6 h-6 flex-shrink-0 mr-2 bg-green-100 text-green-800 rounded-full flex items-center justify-center text-xs font-medium">✓</span>
                                        <span>Menggunakan KB</span>
                                    </li>
                                    @endif
                                    
                                    @if($familyMember->gave_birth_in_health_facility)
                                    <li class="flex items-center">
                                        <span class="w-6 h-6 flex-shrink-0 mr-2 bg-green-100 text-green-800 rounded-full flex items-center justify-center text-xs font-medium">✓</span>
                                        <span>Melahirkan di Fasilitas Kesehatan</span>
                                    </li>
                                    @endif
                                    
                                    @if($familyMember->exclusive_breastfeeding)
                                    <li class="flex items-center">
                                        <span class="w-6 h-6 flex-shrink-0 mr-2 bg-green-100 text-green-800 rounded-full flex items-center justify-center text-xs font-medium">✓</span>
                                        <span>ASI Eksklusif</span>
                                    </li>
                                    @endif
                                    
                                    @if($familyMember->complete_immunization)
                                    <li class="flex items-center">
                                        <span class="w-6 h-6 flex-shrink-0 mr-2 bg-green-100 text-green-800 rounded-full flex items-center justify-center text-xs font-medium">✓</span>
                                        <span>Imunisasi Lengkap</span>
                                    </li>
                                    @endif
                                    
                                    @if($familyMember->growth_monitoring)
                                    <li class="flex items-center">
                                        <span class="w-6 h-6 flex-shrink-0 mr-2 bg-green-100 text-green-800 rounded-full flex items-center justify-center text-xs font-medium">✓</span>
                                        <span>Pemantauan Pertumbuhan</span>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                            
                            <!-- Kolom 3: Status Sanitasi -->
                            <div>
                                <h4 class="font-medium text-gray-700 mb-2">Sanitasi</h4>
                                <ul class="space-y-1 text-sm">
                                    <li class="flex items-center">
                                        <span class="w-6 h-6 flex-shrink-0 mr-2 {{ $familyMember->family->has_clean_water ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} rounded-full flex items-center justify-center text-xs font-medium">
                                            {{ $familyMember->family->has_clean_water ? '✓' : '✕' }}
                                        </span>
                                        <span>
                                            @if($familyMember->family->has_clean_water)
                                                Air Bersih {{ $familyMember->family->is_water_protected ? '(Terlindungi)' : '(Tidak Terlindungi)' }}
                                            @else
                                                Tidak Ada Air Bersih
                                            @endif
                                        </span>
                                    </li>
                                    <li class="flex items-center">
                                        <span class="w-6 h-6 flex-shrink-0 mr-2 {{ $familyMember->family->has_toilet ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} rounded-full flex items-center justify-center text-xs font-medium">
                                            {{ $familyMember->family->has_toilet ? '✓' : '✕' }}
                                        </span>
                                        <span>
                                            @if($familyMember->family->has_toilet)
                                                Jamban {{ $familyMember->family->is_toilet_sanitary ? '(Saniter)' : '(Tidak Saniter)' }}
                                            @else
                                                Tidak Ada Jamban
                                            @endif
                                        </span>
                                    </li>
                                    <li class="flex items-center">
                                        <span class="w-6 h-6 flex-shrink-0 mr-2 {{ $familyMember->family->has_mental_illness ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }} rounded-full flex items-center justify-center text-xs font-medium">
                                            {{ $familyMember->family->has_mental_illness ? '!' : '✓' }}
                                        </span>
                                        <span>
                                            @if($familyMember->family->has_mental_illness)
                                                Ada Gangguan Jiwa {{ $familyMember->family->takes_medication_regularly ? '(Berobat Teratur)' : '(Tidak Teratur)' }}
                                            @else
                                                Tidak Ada Gangguan Jiwa
                                            @endif
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Indikator IKS -->
            </div>

            <x-family-indicator-card :indicators="$familyIndicators" :family="$familyMember->family" />
        </div>
    </div>

    <footer class="bg-white border-t py-4 mt-6 no-print">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <div class="text-gray-500 text-xs text-center">
                &copy; 2025 BetaDevelopment. All rights reserved.
            </div>
        </div>
    </footer>
</body>
</html>
