{{-- family-card.blade.php - Fully Responsive with Landscape Print (Part 1) --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Keluarga - {{ $family->head_name ?? 'Nomor Belum Terdaftar' }}</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        /* Base Styles */
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        /* Print Styles */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            @page {
                size: A4 landscape;
                margin: 0.5cm;
            }
            
            .no-print {
                display: none !important;
            }
            
            .print-only {
                display: block !important;
            }
            
            .print-text-xs {
                font-size: 8pt !important;
            }
            
            .print-text-sm {
                font-size: 9pt !important;
            }
            
            table {
                width: 100%;
                page-break-inside: auto;
            }
            
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            
            /* Hide responsive accordions in print */
            .mobile-accordion-content {
                display: block !important;
                height: auto !important;
                overflow: visible !important;
            }
            
            .mobile-accordion-header button svg {
                display: none !important;
            }
            
            /* Footer positioning */
            .page-footer {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                width: 100%;
                border-top: 1px solid #e5e7eb;
                background-color: white;
            }
            
            .content-wrapper {
                margin-bottom: 1.5cm;
            }
            
            /* Force background colors in print */
            .print-bg-white {
                background-color: white !important;
            }
            
            .print-border {
                border: 1px solid #e5e7eb !important;
            }
        }
        
        /* Mobile Table Styles */
        .responsive-table {
            width: 100%;
        }
        
        .print-only {
            display: none;
        }
        
        /* Mobile Accordion Styles */
        .mobile-accordion-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }
        
        .mobile-accordion-content.open {
            max-height: 1000px;
        }
        
        /* Utility Classes */
        .truncate-text {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Blur styles */
        .blur-data {
            filter: blur(4px);
            user-select: none;
        }
        
        .login-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.9);
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @media print {
            .blur-data {
                filter: blur(4px) !important;
                user-select: none !important;
            }
            
            .login-overlay {
                position: fixed !important;
                display: flex !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Floating Action Buttons -->
    <div class="fixed top-4 right-4 z-50 no-print flex flex-col sm:flex-row gap-2">
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Cetak
        </button>
        
        <button onclick="toggleFullScreen()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow flex items-center sm:ml-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5" />
            </svg>
            <span class="hidden sm:inline">Layar Penuh</span>
        </button>
    </div>

    <!-- Back Button -->
    <div class="fixed top-4 left-4 z-50 no-print">
        <a href="javascript:history.back()" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded shadow flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span class="hidden sm:inline">Kembali</span>
        </a>
    </div>{{-- family-card.blade.php - Fully Responsive with Landscape Print (Part 2) --}}

    <div class="px-4 py-16 sm:py-20">
        <div class="max-w-7xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden print:shadow-none print:rounded-none print-bg-white">
            <div class="content-wrapper {{ !auth()->check() ? 'blur-sm' : '' }}">
                <!-- Header Kartu Keluarga - Responsive for both mobile and print -->
                <div class="bg-gradient-to-r from-blue-700 to-blue-900 text-white p-4 md:p-6 print:p-4">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-4">
                        <div class="flex-shrink-0 flex justify-center sm:justify-start">
                            <img src="{{ asset('images/garuda.png') }}" alt="Garuda Pancasila" class="h-16 w-auto print:h-14" onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/9/90/National_emblem_of_Indonesia_Garuda_Pancasila.svg/220px-National_emblem_of_Indonesia_Garuda_Pancasila.svg.png'; this.onerror=null;">
                        </div>
                        <div class="flex-1 text-center sm:text-left">
                            <h1 class="text-2xl font-bold print:text-xl">KARTU KELUARGA</h1>
                            <p class="text-lg print:text-base {{ !auth()->check() ? 'blur-data' : '' }}">
                                No. {{ auth()->check() ? ($family->family_number ?? 'Belum terdaftar') : 'XXXXXXXXXXXX' }}
                            </p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 {{ !auth()->check() ? 'blur-data' : '' }}">
                        <div>
                            <table class="text-sm print-text-sm w-full">
                                <tr>
                                    <td class="pr-2 align-top">Nama Kepala Keluarga</td>
                                    <td class="align-top">: <span class="font-semibold">{{ $family->head_name ?? ($family->members->where('relationship', 'Kepala Keluarga')->first()->name ?? '-') }}</span></td>
                                </tr>
                                <tr>
                                    <td class="pr-2 align-top">Alamat</td>
                                    <td class="align-top">: <span class="font-semibold">{{ $family->building->description ?? 'Alamat tidak tersedia' }}</span></td>
                                </tr>
                                <tr>
                                    <td class="pr-2 align-top">RT/RW</td>
                                    <td class="align-top">: <span class="font-semibold">-/-</span></td>
                                </tr>
                                <tr>
                                    <td class="pr-2 align-top">Kode Pos</td>
                                    <td class="align-top">: <span class="font-semibold">-</span></td>
                                </tr>
                            </table>
                        </div>
                        
                        <div>
                            <table class="text-sm print-text-sm w-full">
                                <tr>
                                    <td class="pr-2 align-top">Desa/Kelurahan</td>
                                    <td class="align-top">: <span class="font-semibold">{{ $family->building->village->name ?? $family->village->name ?? '-' }}</span></td>
                                </tr>
                                <tr>
                                    <td class="pr-2 align-top">Kecamatan</td>
                                    <td class="align-top">: <span class="font-semibold">{{ $family->building->village->district ?? '-' }}</span></td>
                                </tr>
                                <tr>
                                    <td class="pr-2 align-top">Kabupaten/Kota</td>
                                    <td class="align-top">: <span class="font-semibold">{{ $family->building->village->regency ?? '-' }}</span></td>
                                </tr>
                                <tr>
                                    <td class="pr-2 align-top">Provinsi</td>
                                    <td class="align-top">: <span class="font-semibold">{{ $family->building->village->province ?? '-' }}</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Mobile: Collapsible Sections -->
                <div class="p-4 no-print">
                    <!-- Data Anggota Keluarga Accordion -->
                    <div class="mb-4 border border-gray-200 rounded-lg overflow-hidden">
                        <div class="mobile-accordion-header bg-gray-100 p-3">
                            <button onclick="toggleAccordion(this)" class="w-full flex justify-between items-center text-left font-semibold">
                                <span>Data Anggota Keluarga</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </div>
                        <div class="mobile-accordion-content overflow-x-auto">
                            <!-- Mobile Card View for Family Members -->
                            <div class="p-3 sm:hidden space-y-3">
                                @forelse($family->members as $index => $member)
                                <div class="border border-gray-200 rounded p-3 text-sm bg-white">
                                    <div class="flex justify-between">
                                        <h3 class="font-semibold">{{ $member->name }}</h3>
                                        <span class="text-xs px-2 py-1 bg-gray-100 rounded-full">{{ $member->relationship ?? '-' }}</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-x-2 gap-y-1 mt-2">
                                        <div><span class="text-gray-500">NIK:</span> {{ $member->nik ?? '-' }}</div>
                                        <div><span class="text-gray-500">JK:</span> {{ $member->gender == 'Laki-laki' ? 'L' : 'P' }}</div>
                                        <div><span class="text-gray-500">TTL:</span> {{ $member->birth_place ?? '-' }}, {{ $member->birth_date ? $member->birth_date->format('d-m-Y') : '-' }}</div>
                                        <div><span class="text-gray-500">Status:</span> {{ $member->marital_status ?? '-' }}</div>
                                        <div><span class="text-gray-500">Pendidikan:</span> {{ $member->education ?? '-' }}</div>
                                        <div><span class="text-gray-500">Pekerjaan:</span> {{ $member->occupation ?? '-' }}</div>
                                    </div>
                                </div>
                                @empty
                                <p class="p-3 text-center text-gray-500">Tidak ada data anggota keluarga</p>
                                @endforelse
                            </div>{{-- family-card.blade.php - Fully Responsive with Landscape Print (Part 3) --}}
                            
                            <!-- Responsive Table for Tablet/Desktop -->
                            <div class="hidden sm:block overflow-x-auto">
                                <table class="w-full border-collapse">
                                    <thead>
                                        <tr class="bg-gray-100 text-xs">
                                            <th class="p-2 border text-center">No</th>
                                            <th class="p-2 border text-left">Nama</th>
                                            <th class="p-2 border text-center">NIK</th>
                                            <th class="p-2 border text-center">JK</th>
                                            <th class="p-2 border text-left">TTL</th>
                                            <th class="p-2 border text-center">Status</th>
                                            <th class="p-2 border text-center">Hubungan</th>
                                            <th class="p-2 border text-left">Pendidikan</th>
                                            <th class="p-2 border text-left">Pekerjaan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($family->members as $index => $member)
                                        <tr class="hover:bg-gray-50 text-xs">
                                            <td class="p-2 border text-center">{{ $index + 1 }}</td>
                                            <td class="p-2 border font-medium">
                                                <a href="{{ route('family-members.show', $member) }}" class="text-blue-600 hover:underline">
                                                    {{ $member->name }}
                                                </a>
                                            </td>
                                            <td class="p-2 border">{{ $member->nik ?? '-' }}</td>
                                            <td class="p-2 border text-center">{{ $member->gender == 'Laki-laki' ? 'L' : 'P' }}</td>
                                            <td class="p-2 border">{{ $member->birth_place ?? '-' }}, {{ $member->birth_date ? $member->birth_date->format('d-m-Y') : '-' }}</td>
                                            <td class="p-2 border">{{ $member->marital_status ?? '-' }}</td>
                                            <td class="p-2 border">{{ $member->relationship ?? '-' }}</td>
                                            <td class="p-2 border">{{ $member->education ?? '-' }}</td>
                                            <td class="p-2 border">{{ $member->occupation ?? '-' }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="9" class="p-3 text-center text-gray-500 border">Tidak ada data anggota keluarga</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Informasi Kesehatan Accordion -->
                    <div class="mb-4 border border-gray-200 rounded-lg overflow-hidden">
                        <div class="mobile-accordion-header bg-gray-100 p-3">
                            <button onclick="toggleAccordion(this)" class="w-full flex justify-between items-center text-left font-semibold">
                                <span>Informasi Kesehatan Keluarga (PISPK)</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </div>
                        <div class="mobile-accordion-content p-3">
                            <!-- Status Kesehatan Card -->
                            <div class="mb-4 border border-gray-200 rounded-lg p-3 bg-white">
                                <h4 class="font-semibold mb-2">Status Kesehatan Keluarga</h4>
                                @if(isset($family->healthIndex) && $family->healthIndex)
                                    @php
                                        $statusClass = match($family->healthIndex->health_status) {
                                            'Sehat' => 'bg-green-100 text-green-800 border-green-200',
                                            'Pra-Sehat' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                            'Tidak Sehat' => 'bg-red-100 text-red-800 border-red-200',
                                            default => 'bg-gray-100 text-gray-800 border-gray-200'
                                        };
                                    @endphp
                                    <div class="flex flex-wrap gap-2 items-center">
                                        <div class="inline-block px-3 py-1 rounded-full {{ $statusClass }} border font-medium">
                                            {{ $family->healthIndex->health_status ?? 'Belum dinilai' }}
                                        </div>
                                        <div>
                                            IKS: <span class="font-semibold">{{ $family->healthIndex->iks_value ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-3 text-sm">
                                        <div>
                                            <span class="font-medium">Indikator Relevan:</span> 
                                            {{ $family->healthIndex->relevant_indicators ?? '0' }}
                                        </div>
                                        <div>
                                            <span class="font-medium">Indikator Terpenuhi:</span> 
                                            {{ $family->healthIndex->fulfilled_indicators ?? '0' }}
                                        </div>
                                        <div>
                                            <span class="font-medium">Terakhir dihitung:</span> 
                                            {{ $family->healthIndex->calculated_at ? $family->healthIndex->calculated_at->format('d-m-Y') : '-' }}
                                        </div>
                                    </div>
                                @else
                                    <div class="inline-block px-3 py-1 rounded-full bg-gray-100 text-gray-800 border border-gray-200 font-medium">
                                        Belum dinilai
                                    </div>
                                @endif
                            </div>{{-- family-card.blade.php - Fully Responsive with Landscape Print (Part 4) --}}
                            
                            <!-- Sanitasi dan Gaya Hidup -->
                            {{-- <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                <!-- Sanitasi Card -->
                                <div class="border border-gray-200 rounded-lg p-3 bg-white">
                                    <h4 class="font-semibold mb-2">Sanitasi</h4>
                                    <div class="space-y-2">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 w-6 h-6 flex items-center justify-center rounded-full {{ $family->has_clean_water ? 'bg-green-100 text-green-500' : 'bg-red-100 text-red-500' }} mr-2">
                                                @if($family->has_clean_water)
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                @endif
                                            </div>
                                            <div>
                                                <span class="font-medium">Air Bersih:</span> {{ $family->has_clean_water ? 'Tersedia' : 'Tidak Tersedia' }}
                                                @if($family->has_clean_water)
                                                    <span class="text-xs text-gray-500 block sm:inline sm:ml-1">({{ $family->is_water_protected ? 'Terlindungi' : 'Tidak Terlindungi' }})</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 w-6 h-6 flex items-center justify-center rounded-full {{ $family->has_toilet ? 'bg-green-100 text-green-500' : 'bg-red-100 text-red-500' }} mr-2">
                                                @if($family->has_toilet)
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                @endif
                                            </div>
                                            <div>
                                                <span class="font-medium">Jamban:</span> {{ $family->has_toilet ? 'Tersedia' : 'Tidak Tersedia' }}
                                                @if($family->has_toilet)
                                                    <span class="text-xs text-gray-500 block sm:inline sm:ml-1">({{ $family->is_toilet_sanitary ? 'Sehat' : 'Tidak Sehat' }})</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Gaya Hidup Card -->
                                <div class="border border-gray-200 rounded-lg p-3 bg-white">
                                    <h4 class="font-semibold mb-2">Gaya Hidup</h4>
                                    <div class="space-y-2">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 w-6 h-6 flex items-center justify-center rounded-full bg-gray-100 text-gray-700 mr-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <span class="font-medium">Anggota yang merokok:</span> 
                                                {{ $family->members->where('is_smoker', true)->count() }} orang
                                            </div>
                                        </div>
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 w-6 h-6 flex items-center justify-center rounded-full bg-gray-100 text-gray-700 mr-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <span class="font-medium">JKN:</span> 
                                                {{ $family->members->where('has_jkn', true)->count() }} dari {{ $family->members->count() }} orang
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> --}}
                            
                            <!-- Kesehatan Jiwa Card -->
                            {{-- <div class="border border-gray-200 rounded-lg p-3 bg-white">
                                <h4 class="font-semibold mb-2">Kesehatan Jiwa</h4>
                                <div class="space-y-2">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 w-6 h-6 flex items-center justify-center rounded-full {{ $family->has_mental_illness ? 'bg-red-100 text-red-500' : 'bg-green-100 text-green-500' }} mr-2">
                                            @if(!$family->has_mental_illness)
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="font-medium">Ada anggota dengan gangguan jiwa:</span> {{ $family->has_mental_illness ? 'Ya' : 'Tidak' }}
                                        </div>
                                    </div>
                                    
                                    @if($family->has_mental_illness)
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 w-6 h-6 flex items-center justify-center rounded-full {{ $family->takes_medication_regularly ? 'bg-green-100 text-green-500' : 'bg-red-100 text-red-500' }} mr-2">
                                            @if($family->takes_medication_regularly)
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="font-medium">Minum obat secara teratur:</span> {{ $family->takes_medication_regularly ? 'Ya' : 'Tidak' }}
                                        </div>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 w-6 h-6 flex items-center justify-center rounded-full {{ $family->has_restrained_member ? 'bg-red-100 text-red-500' : 'bg-green-100 text-green-500' }} mr-2">
                                            @if(!$family->has_restrained_member)
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="font-medium">Ada anggota dipasung:</span> {{ $family->has_restrained_member ? 'Ya' : 'Tidak' }}
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div> --}}
                        </div>
                    </div>
                </div>{{-- family-card.blade.php - Fully Responsive with Landscape Print (Part 5) --}}

                <!-- Print-only Table View -->
                <div class="print-only p-4">
                    <h3 class="text-md font-semibold mb-2 print-text-sm">Data Anggota Keluarga</h3>
                    <table class="w-full border border-gray-300 compact-table">
                        <thead>
                            <tr class="bg-gray-200 text-gray-700 print-text-xs">
                                <th class="py-1 px-2 text-center border border-gray-300 w-8">No</th>
                                <th class="py-1 px-2 text-center border border-gray-300">Nama Lengkap</th>
                                <th class="py-1 px-2 text-center border border-gray-300">NIK</th>
                                <th class="py-1 px-2 text-center border border-gray-300">JK</th>
                                <th class="py-1 px-2 text-center border border-gray-300">Tempat Lahir</th>
                                <th class="py-1 px-2 text-center border border-gray-300">Tgl Lahir</th>
                                <th class="py-1 px-2 text-center border border-gray-300">Agama</th>
                                <th class="py-1 px-2 text-center border border-gray-300">Pendidikan</th>
                                <th class="py-1 px-2 text-center border border-gray-300">Pekerjaan</th>
                                <th class="py-1 px-2 text-center border border-gray-300">Status</th>
                                <th class="py-1 px-2 text-center border border-gray-300">Hubungan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-300">
                            @forelse($family->members as $index => $member)
                            <tr>
                                <td class="py-1 px-2 text-center border border-gray-300 print-text-xs">{{ $index + 1 }}</td>
                                <td class="py-1 px-2 border border-gray-300 print-text-xs font-medium">{{ $member->name }}</td>
                                <td class="py-1 px-2 border border-gray-300 print-text-xs">{{ $member->nik ?? '-' }}</td>
                                <td class="py-1 px-2 border border-gray-300 print-text-xs text-center">{{ $member->gender == 'Laki-laki' ? 'L' : 'P' }}</td>
                                <td class="py-1 px-2 border border-gray-300 print-text-xs">{{ $member->birth_place ?? '-' }}</td>
                                <td class="py-1 px-2 border border-gray-300 print-text-xs">{{ $member->birth_date ? $member->birth_date->format('d-m-Y') : '-' }}</td>
                                <td class="py-1 px-2 border border-gray-300 print-text-xs">{{ $member->religion ?? '-' }}</td>
                                <td class="py-1 px-2 border border-gray-300 print-text-xs">{{ $member->education ?? '-' }}</td>
                                <td class="py-1 px-2 border border-gray-300 print-text-xs">{{ $member->occupation ?? '-' }}</td>
                                <td class="py-1 px-2 border border-gray-300 print-text-xs">{{ $member->marital_status ?? '-' }}</td>
                                <td class="py-1 px-2 border border-gray-300 print-text-xs">{{ $member->relationship ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="py-2 px-2 text-center border border-gray-300 text-gray-500 print-text-xs">Tidak ada data anggota keluarga</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <!-- Print-only Health Information -->
                    <div class="mt-4 print-border print-bg-white rounded p-3">
                        <h3 class="text-md font-semibold mb-2 print-text-sm">Informasi Kesehatan Keluarga (PISPK)</h3>
                        
                        <div class="grid grid-cols-3 gap-4">
                            <!-- Status Kesehatan -->
                            <div class="print-border print-bg-white rounded p-2">
                                <h4 class="text-xs font-semibold print-text-xs">Status Kesehatan Keluarga</h4>
                                @if(isset($family->healthIndex) && $family->healthIndex)
                                    @php
                                        $statusClass = match($family->healthIndex->health_status) {
                                            'Sehat' => 'bg-green-100 text-green-800 border-green-200',
                                            'Pra-Sehat' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                            'Tidak Sehat' => 'bg-red-100 text-red-800 border-red-200',
                                            default => 'bg-gray-100 text-gray-800 border-gray-200'
                                        };
                                    @endphp
                                    <div class="flex items-center mt-1">
                                        <div class="mr-2 inline-block px-2 py-0.5 rounded-full {{ $statusClass }} border font-medium print-text-xs">
                                            {{ $family->healthIndex->health_status ?? 'Belum dinilai' }}
                                        </div>
                                        <div class="print-text-xs">
                                            IKS: <span class="font-semibold">{{ $family->healthIndex->iks_value ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-1 mt-1">
                                        <div class="print-text-xs">
                                            <span class="font-medium">Indikator Relevan:</span> 
                                            {{ $family->healthIndex->relevant_indicators ?? '0' }}
                                        </div>
                                        <div class="print-text-xs">
                                            <span class="font-medium">Indikator Terpenuhi:</span> 
                                            {{ $family->healthIndex->fulfilled_indicators ?? '0' }}
                                        </div>
                                    </div>
                                @else
                                    <div class="mt-1 print-text-xs">
                                        <span class="font-medium">Status:</span> Belum dinilai
                                    </div>
                                @endif
                            </div>

                            <!-- Sanitasi dan Gaya Hidup -->
                            <div class="print-border print-bg-white rounded p-2">
                                <h4 class="text-xs font-semibold print-text-xs">Sanitasi & Gaya Hidup</h4>
                                <div class="grid grid-cols-2 gap-1 mt-1">
                                    <div class="print-text-xs">
                                        <span class="font-medium">Air Bersih:</span> {{ $family->has_clean_water ? 'Ya' : 'Tidak' }}
                                        @if($family->has_clean_water)
                                            ({{ $family->is_water_protected ? 'Terlindungi' : 'Tidak' }})
                                        @endif
                                    </div>
                                    <div class="print-text-xs">
                                        <span class="font-medium">Jamban:</span> {{ $family->has_toilet ? 'Ya' : 'Tidak' }}
                                        @if($family->has_toilet)
                                            ({{ $family->is_toilet_sanitary ? 'Sehat' : 'Tidak' }})
                                        @endif
                                    </div>
                                    <div class="print-text-xs">
                                        <span class="font-medium">Merokok:</span> 
                                        {{ $family->members->where('is_smoker', true)->count() }} orang
                                    </div>
                                    <div class="print-text-xs">
                                        <span class="font-medium">JKN:</span> 
                                        {{ $family->members->where('has_jkn', true)->count() }}/{{ $family->members->count() }}
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Kesehatan Jiwa -->
                            <div class="print-border print-bg-white rounded p-2">
                                <h4 class="text-xs font-semibold print-text-xs">Kesehatan Jiwa</h4>
                                <div class="mt-1 print-text-xs">
                                    <span class="font-medium">Gangguan jiwa:</span> {{ $family->has_mental_illness ? 'Ya' : 'Tidak' }}
                                </div>
                                @if($family->has_mental_illness)
                                <div class="grid grid-cols-2 gap-1">
                                    <div class="print-text-xs">
                                        <span class="font-medium">Minum obat teratur:</span> {{ $family->takes_medication_regularly ? 'Ya' : 'Tidak' }}
                                    </div>
                                    <div class="print-text-xs">
                                        <span class="font-medium">Dipasung:</span> {{ $family->has_restrained_member ? 'Ya' : 'Tidak' }}
                                    </div>
                                </div>
                                @endif
                                @if(isset($family->healthIndex) && $family->healthIndex)
                                <div class="print-text-xs mt-1">
                                    <span class="font-medium">Update:</span> 
                                    {{ $family->healthIndex->calculated_at ? $family->healthIndex->calculated_at->format('d-m-Y') : '-' }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="page-footer px-4 py-2 border-t border-gray-200 text-xs print-text-xs text-gray-600">
                    <div class="flex justify-between items-center">
                        <div>
                            Dicetak pada: {{ now()->format('d-m-Y H:i:s') }}
                        </div>
                        <div>
                            Program Indonesia Sehat dengan Pendekatan Keluarga (PISPK)
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="relative">
        <!-- Data Sections with Conditional Blur -->
        <div class="{{ !auth()->check() ? 'blur-data' : '' }}">
            <!-- Mobile View -->
            <div class="p-4 no-print">
                <!-- Family Members Section -->
                <div class="mb-4 border border-gray-200 rounded-lg overflow-hidden">
                    <!-- ...existing mobile view code... -->
                </div>

                <!-- Health Info Section -->
                <div class="mb-4 border border-gray-200 rounded-lg overflow-hidden">
                    <!-- ...existing health info code... -->
                </div>
            </div>

            <!-- Print View -->
            <div class="print-only">
                <!-- ...existing print view code... -->
            </div>
        </div>

        <!-- Login Overlay -->
        @guest
        <div class="login-overlay no-print">
            <div class="bg-white p-8 rounded-lg shadow-xl max-w-md w-full mx-4">
                <div class="text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-blue-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <h2 class="text-2xl font-bold mb-4">Data Terlindungi</h2>
                    <p class="text-gray-600 mb-6">
                        Silakan login untuk melihat data kartu keluarga secara lengkap
                    </p>
                    <a href="{{ url('/admin/login') }}" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-200">
                        Login Sekarang
                    </a>
                </div>
            </div>
        </div>
        @endguest
    </div>

    <script>
        // Fungsi untuk toggle accordion pada mobile
        function toggleAccordion(button) {
            const content = button.parentElement.nextElementSibling;
            const icon = button.querySelector('svg');
            
            if (content.classList.contains('open')) {
                content.classList.remove('open');
                icon.classList.remove('rotate-180');
            } else {
                content.classList.add('open');
                icon.classList.add('rotate-180');
            }
        }
        
        // Fungsi untuk toggle fullscreen
        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    alert(`Error attempting to enable full-screen mode: ${err.message}`);
                });
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        }
        
        // Script untuk memastikan tampilan yang benar ketika mencetak
        window.onbeforeprint = function() {
            // Tambahkan kelas khusus untuk mode cetak
            document.body.classList.add('printing');
            
            // Buka semua accordion saat mencetak
            document.querySelectorAll('.mobile-accordion-content').forEach(content => {
                content.classList.add('open');
            });
        };
        
        window.onafterprint = function() {
            // Hapus kelas setelah selesai mencetak
            document.body.classList.remove('printing');
        };
        
        // Auto-expand first accordion on mobile
        document.addEventListener('DOMContentLoaded', function() {
            // Buka accordion pertama secara default pada mobile
            const firstAccordion = document.querySelector('.mobile-accordion-content');
            const firstAccordionButton = document.querySelector('.mobile-accordion-header button');
            
            if (firstAccordion && firstAccordionButton) {
                firstAccordion.classList.add('open');
                const icon = firstAccordionButton.querySelector('svg');
                if (icon) {
                    icon.classList.add('rotate-180');
                }
            }
        });

        // Handle print attempt when not logged in
        @guest
        window.addEventListener('beforeprint', function(e) {
            alert('Silakan login terlebih dahulu untuk mencetak data kartu keluarga.');
            e.preventDefault();
            return false;
        });
        @endguest
    </script>
</body>
</html>