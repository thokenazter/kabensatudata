<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PKM Kaben - Satu Data</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        },
                        secondary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        }
                    },
                    fontFamily: {
                        'sans': ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'Segoe UI', 'sans-serif'],
                    },
                    boxShadow: {
                        'card': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
                        'card-hover': '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
                    }
                }
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.5/dist/FileSaver.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.3.0/papaparse.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
    
    <!-- CSS untuk Print -->
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #results, #results * {
                visibility: visible;
            }
            #printBtn, #analyzeBtn, #loadingIndicator, .print-hide {
                display: none !important;
            }
            #results {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                box-shadow: none;
                background: white;
                padding: 20px;
            }
            .print-full-width {
                width: 100% !important;
            }
            /* Atur mode landscape */
            @page {
                size: landscape;
            }
            /* Pastikan tabel tidak terpotong */
            table {
                width: 100% !important;
                table-layout: auto !important;
                page-break-inside: avoid;
            }
            /* Hindari pemotongan baris tabel */
            tr {
                page-break-inside: avoid;
            }
            /* Pastikan semua sel terlihat */
            td, th {
                white-space: normal !important;
                overflow: visible !important;
                font-size: 10pt !important;
            }
            /* Atur agar chart terlihat penuh */
            #chartArea {
                max-width: 100% !important;
                width: 100% !important;
                height: auto !important;
                max-height: 500px !important;
                page-break-inside: avoid;
            }
            /* Atur overflow untuk data yang lebar */
            #tableArea {
                overflow: visible !important;
                width: 100% !important;
            }
        }
        .print-only {
            display: none;
        }
        @media print {
            .print-only {
                display: block;
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: #c7d2fe;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #4f46e5;
        }
        
        /* Animations */
        .fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Tooltip Styles */
        .tooltip {
            position: relative;
            display: inline-block;
        }
        .tooltip .tooltip-text {
            visibility: hidden;
            width: 200px;
            background-color: #1e293b;
            color: white;
            text-align: center;
            border-radius: 6px;
            padding: 8px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            margin-left: -100px;
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 0.75rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .tooltip .tooltip-text::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: #1e293b transparent transparent transparent;
        }
        .tooltip:hover .tooltip-text {
            visibility: visible;
            opacity: 1;
        }
        
        /* Loading Spinner */
        .spinner {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 3px solid rgba(99, 102, 241, 0.1);
            border-top-color: #6366f1;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Card Hover Effects */
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        /* Button Effects */
        .btn-hover {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .btn-hover:after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: -100%;
            background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0) 100%);
            transition: all 0.3s ease;
        }
        .btn-hover:hover:after {
            left: 100%;
        }
        
        /* Modal Animations */
        .modal-enter {
            animation: modalEnter 0.3s forwards;
        }
        .modal-exit {
            animation: modalExit 0.3s forwards;
        }
        @keyframes modalEnter {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        @keyframes modalExit {
            from { opacity: 1; transform: scale(1); }
            to { opacity: 0; transform: scale(0.95); }
        }
        
        /* Table Styling */
        .data-table {
            border-collapse: separate;
            border-spacing: 0;
        }
        .data-table th {
            position: sticky;
            top: 0;
            background-color: #f8fafc;
            z-index: 10;
        }
        .data-table tbody tr {
            transition: background-color 0.2s;
        }
        .data-table tbody tr:hover {
            background-color: #f1f5f9;
        }
        
        /* Form Controls */
        .form-input:focus, .form-select:focus {
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
        }
    </style>
</head>
<body class="bg-gray-50 font-sans text-gray-800">
    <!-- Navbar -->
    <nav class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <button class="bg-gradient-to-r from-primary-600 to-secondary-600 rounded-md p-1">
                        <a href="/dashboard" class="flex items-center">
                            <span class="text-xl font-bold text-white px-4 hover:scale-105 transition-all duration-300 flex items-center">
                                <i class="fas fa-chart-line mr-2"></i>
                                Dashboard
                            </span>
                        </a>
                    </button>
                    <h1 class="ml-4 text-xl font-semibold text-gray-700 hidden md:block">PKM Kaben - Satu Data</h1>
                </div>
                <div class="flex items-center space-x-3">
                    <span id="importInfoBadge" class="hidden inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
                        <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-primary-400" fill="currentColor" viewBox="0 0 8 8">
                            <circle cx="4" cy="4" r="3" />
                        </svg>
                        <span id="importInfoText">Data: -</span>
                    </span>
                    <button id="importFileBtn" class="bg-gradient-to-r from-primary-600 to-purple-600 text-white px-4 py-2 rounded-md hover:from-primary-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 btn-hover flex items-center shadow-md">
                        <i class="fas fa-file-import mr-2"></i>
                        Import Data
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <!-- Filter Section -->
        <div class="bg-white rounded-xl shadow-card p-6 mb-6 fade-in transition-all hover:shadow-card-hover">
            <div class="flex items-center mb-4">
                <i class="fas fa-filter text-primary-500 mr-2 text-xl"></i>
                <h2 class="text-lg font-semibold text-gray-800">Filter & Analisis</h2>
            </div>
            
            <!-- Variable Groups -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                @foreach($variableGroups as $groupKey => $group)
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-100 shadow-sm">
                        <h3 class="text-md font-medium text-gray-700 mb-3 flex items-center">
                            <i class="fas fa-layer-group text-primary-400 mr-2"></i>
                            {{ $group['title'] }}
                        </h3>
                        <div class="space-y-3">
                            @foreach($group['variables'] as $key => $variable)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                                        <i class="fas fa-tag text-gray-400 mr-1 text-xs"></i>
                                        {{ $variable['label'] }}
                                    </label>

                                    @if($variable['type'] === 'select')
                                        <select name="filters[{{ $key }}]" 
                                                class="form-select w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 text-sm">
                                            <option value="">Semua</option>
                                            @foreach($variable['options'] as $optionKey => $optionLabel)
                                                <option value="{{ $optionKey }}">{{ $optionLabel }}</option>
                                            @endforeach
                                        </select>

                                    @elseif($variable['type'] === 'boolean')
                                        <div class="space-y-2">
                                            <label class="inline-flex items-center">
                                                <input type="radio" name="filters[{{ $key }}]" 
                                                       value="" class="text-primary-600 focus:ring-primary-500" checked>
                                                <span class="ml-2 text-sm text-gray-700">Semua</span>
                                            </label>
                                            @foreach($variable['options'] as $optionKey => $optionLabel)
                                                <label class="inline-flex items-center">
                                                    <input type="radio" name="filters[{{ $key }}]" 
                                                           value="{{ $optionKey }}" class="text-primary-600 focus:ring-primary-500">
                                                    <span class="ml-2 text-sm text-gray-700">{{ $optionLabel }}</span>
                                                </label>
                                            @endforeach
                                        </div>

                                    @elseif($variable['type'] === 'range')
                                        <select name="filters[{{ $key }}]" 
                                                class="form-select w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 text-sm">
                                            <option value="">Semua</option>
                                            @foreach($variable['options'] as $optionKey => $optionLabel)
                                                <option value="{{ $optionKey }}">{{ $optionLabel }}</option>
                                            @endforeach
                                        </select>

                                    @elseif($variable['type'] === 'number')
                                        <input type="number" name="filters[{{ $key }}]"
                                               class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 text-sm"
                                               placeholder="Masukkan nomor...">
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Buttons for analyze and print -->
            <div class="flex flex-wrap items-center gap-3">
                <button id="analyzeBtn" 
                        class="bg-gradient-to-r from-primary-600 to-primary-700 text-white px-4 py-2 rounded-md hover:from-primary-700 hover:to-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 shadow-md btn-hover flex items-center">
                    <i class="fas fa-chart-bar mr-2"></i>
                    Analisis Data
                </button>
                <button id="exportExcelBtn" 
                        class="bg-gradient-to-r from-green-600 to-green-700 text-white px-4 py-2 rounded-md hover:from-green-700 hover:to-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 shadow-md btn-hover hidden flex items-center">
                    <i class="fas fa-file-excel mr-2"></i>
                    Export Excel
                </button>
                <button id="resetImportBtn" 
                        class="bg-gradient-to-r from-red-600 to-red-700 text-white px-4 py-2 rounded-md hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 shadow-md btn-hover hidden flex items-center">
                    <i class="fas fa-sync-alt mr-2"></i>
                    Reset
                </button>
                <div class="tooltip ml-auto">
                    <button id="helpBtn" class="text-gray-400 hover:text-primary-600 focus:outline-none">
                        <i class="fas fa-question-circle text-xl"></i>
                    </button>
                    <span class="tooltip-text">Gunakan filter untuk menyaring data berdasarkan kriteria yang diinginkan, lalu klik "Analisis Data" untuk melihat hasilnya.</span>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div id="results" class="bg-white rounded-xl shadow-card p-6 transition-all fade-in">
            <!-- Loading Indicator -->
            <div id="loadingIndicator" class="hidden">
                <div class="flex flex-col items-center justify-center p-8">
                    <div class="spinner"></div>
                    <span class="mt-4 text-gray-600">Menganalisis data...</span>
                </div>
            </div>
            
            <!-- Welcome Message (initially shown) -->
            <div id="welcomeMessage" class="text-center py-12">
                <div class="inline-flex rounded-full bg-primary-100 p-4 mb-4">
                    <i class="fas fa-chart-pie text-primary-600 text-2xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Selamat Datang di Satu Data</h2>
                <p class="text-gray-600 max-w-md mx-auto mb-6">
                    Pilih filter yang diinginkan atau impor data untuk memulai analisis. 
                    Hasil akan ditampilkan di sini.
                </p>
                <button id="startAnalysisBtn" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    <i class="fas fa-play mr-2"></i>
                    Mulai Analisis
                </button>
            </div>
            
            <!-- Chart & Table Areas (empty until analysis is run) -->
            <div id="chartArea" class="min-h-[5px]"></div>
            <div id="tableArea" class="overflow-x-auto"></div>
        </div>
    </main><!-- Modal Konfirmasi Export -->
    <div id="exportConfirmationModal" class="fixed inset-0 bg-gray-900 bg-opacity-70 hidden overflow-y-auto h-full w-full backdrop-blur-sm transition-all duration-300 z-50">
        <div class="relative top-20 mx-auto p-0 md:w-[480px] w-[95%] shadow-2xl rounded-xl bg-white transform transition-all duration-300 opacity-0 translate-y-4" id="modalContent">
            <!-- Header dengan grafik -->
            <div class="bg-gradient-to-r from-primary-600 to-primary-700 rounded-t-xl p-5 relative overflow-hidden">
                <div class="absolute -right-8 -top-8 w-32 h-32 bg-primary-500 bg-opacity-50 rounded-full"></div>
                <div class="absolute -right-4 -bottom-8 w-24 h-24 bg-primary-500 bg-opacity-50 rounded-full"></div>
                
                <div class="flex justify-between items-start relative z-10">
                    <div>
                        <h3 class="text-xl font-bold text-white">Konfirmasi Export Data</h3>
                        <p class="text-primary-100 text-sm mt-1">Keamanan dan privasi data</p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center rounded-full bg-white bg-opacity-20">
                        <i class="fas fa-shield-alt text-white text-xl"></i>
                    </div>
                </div>
            </div>
            
            <!-- Body content -->
            <div class="p-6">
                <div class="flex items-start mb-4">
                    <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-full bg-yellow-100">
                        <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-semibold text-gray-800">Data Sensitif</h4>
                        <p class="text-sm text-gray-600 mt-1">
                            Anda akan mengekspor data yang mungkin mengandung informasi sensitif.
                        </p>
                    </div>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <h5 class="font-medium text-gray-700 mb-2">Dengan melanjutkan, Anda menyetujui:</h5>
                    <ul class="space-y-2">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5"></i>
                            <span class="text-sm text-gray-600">Data ini hanya digunakan untuk keperluan resmi</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5"></i>
                            <span class="text-sm text-gray-600">Data akan disimpan dengan aman dan dilindungi</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5"></i>
                            <span class="text-sm text-gray-600">Data tidak akan dibagikan kepada pihak yang tidak berwenang</span>
                        </li>
                    </ul>
                </div>
                
                <!-- Timer progress -->
                <div class="mb-5">
                    <div class="flex justify-between items-center mb-1">
                        <span id="timerText" class="text-sm text-gray-500">Harap tunggu 15 detik...</span>
                        <span id="timerCounter" class="text-sm font-medium text-gray-700">15s</span>
                    </div>
                    <div class="w-full h-1.5 bg-gray-200 rounded-full overflow-hidden">
                        <div id="timerProgressBar" class="h-full bg-primary-600 rounded-full transition-all duration-1000" style="width: 0%"></div>
                    </div>
                </div>
                
                <!-- Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
                    <button id="cancelExportBtn" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                        Batal
                    </button>
                    <button id="confirmExportBtn" disabled class="px-4 py-2 bg-gray-400 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors cursor-not-allowed">
                        <i class="fas fa-check mr-1"></i>
                        Saya Setuju & Lanjutkan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Notifikasi -->
    <div id="welcomeNotificationModal" class="fixed inset-0 bg-gray-600 bg-opacity-70 hidden overflow-y-auto h-full w-full backdrop-blur-sm z-50">
        <div class="relative top-20 mx-auto p-0 shadow-2xl rounded-xl md:w-[480px] w-[95%] bg-white transform transition-all duration-300 modal-enter">
            <div class="p-6">
                <div class="flex justify-between items-center border-b pb-3">
                    <h3 class="text-lg font-semibold text-red-800 flex items-center">
                        <i class="fas fa-bell text-red-600 mr-2"></i>
                        Attention
                    </h3>
                    <button id="closeNotificationBtn" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="mt-4 px-1 py-3">
                    <div class="flex">
                        <div class="flex-shrink-0 bg-primary-100 rounded-full p-3">
                            <i class="fas fa-info-circle text-primary-600 text-xl"></i>
                        </div>
                        
                        <div class="ml-4">
                            <h4 class="text-md font-medium text-gray-800">Harap Bijak Dalam Menggunakan Fitur Yang Tersedia</h4>
                            <div class="mt-2 text-sm text-gray-600 notification-message">
                                <!-- Konten pesan akan diisi melalui JavaScript -->
                                <p class="text-justify">Aplikasi ini digunakan untuk melakukan analisis data kesehatan masyarakat di wilayah kerja Puskesmas Rawat Inap Kabalsiang Benjuring.</p>
                                <p class="mt-2">Versi Beta: <span class="font-semibold text-primary-700">0.0.2</span> (01 Maret 2025)</p>
                                <div class="mt-3 pt-3 border-t border-gray-200">
                                    <p class="font-medium">Pembaruan terbaru:</p>
                                    <ul class="space-y-1 mt-2">
                                        <li class="flex items-start">
                                            <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5 text-sm"></i>
                                            <span>Penambahan fitur export data ke Excel</span>
                                        </li>
                                        <li class="flex items-start">
                                            <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5 text-sm"></i>
                                            <span>Perbaikan filter berdasarkan desa</span>
                                        </li>
                                        <li class="flex items-start">
                                            <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5 text-sm"></i>
                                            <span>Penambahan indikator air bersih & sanitasi</span>
                                        </li></ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="items-center px-1 py-3 mt-2">
                        <div class="flex justify-end">
                            <label class="inline-flex items-center text-sm text-gray-600 mr-4">
                                <input type="checkbox" id="dontShowAgain" class="rounded text-primary-600 focus:ring-primary-500">
                                <span class="ml-2">Jangan tampilkan lagi</span>
                            </label>
                            <button id="okNotificationBtn" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-md shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 flex items-center">
                                <i class="fas fa-check mr-2"></i>
                                Mengerti
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
        <!-- Modal Import File -->
        <div id="importFileModal" class="fixed inset-0 bg-gray-900 bg-opacity-70 hidden overflow-y-auto h-full w-full backdrop-blur-sm transition-all duration-300 z-50">
            <div class="relative top-20 mx-auto p-0 md:w-[480px] w-[95%] shadow-2xl rounded-xl bg-white transform transition-all duration-300 opacity-0 translate-y-4" id="importModalContent">
                <!-- Header -->
                <div class="bg-gradient-to-r from-primary-600 to-purple-700 rounded-t-xl p-5 relative overflow-hidden">
                    <div class="absolute -right-8 -top-8 w-32 h-32 bg-purple-500 bg-opacity-50 rounded-full"></div>
                    <div class="absolute -right-4 -bottom-8 w-24 h-24 bg-primary-500 bg-opacity-50 rounded-full"></div>
                    
                    <div class="flex justify-between items-start relative z-10">
                        <div>
                            <h3 class="text-xl font-bold text-white">Import Data</h3>
                            <p class="text-primary-100 text-sm mt-1">Unggah file Excel atau CSV</p>
                        </div>
                        <div class="w-12 h-12 flex items-center justify-center rounded-full bg-white bg-opacity-20">
                            <i class="fas fa-file-import text-white text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Body content -->
                <div class="p-6">
                    <div class="mb-5">
                        <p class="text-sm text-gray-600 mb-4">
                            Pilih file Excel (.xlsx, .xls) atau CSV untuk diimpor. Data akan dianalisis menggunakan format yang sesuai.
                        </p>
                        
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-primary-500 transition-colors" id="dropZone">
                            <i class="fas fa-file-upload text-gray-400 text-3xl mb-2"></i>
                            <p class="mt-2 text-sm text-gray-600">Drag & drop file di sini, atau <span class="text-primary-600 font-medium">pilih file</span></p>
                            <p class="mt-1 text-xs text-gray-500">Format yang didukung: XLSX, XLS, CSV</p>
                            <input type="file" id="fileInput" class="hidden" accept=".xlsx,.xls,.csv">
                        </div>
                        
                        <div id="selectedFile" class="hidden mt-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                    <div>
                                        <p id="fileName" class="text-sm font-medium text-gray-800">filename.xlsx</p>
                                        <p id="fileSize" class="text-xs text-gray-500">128 KB</p>
                                    </div>
                                </div>
                                <button id="removeFileBtn" class="text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div id="importProgress" class="hidden mt-4">
                            <div class="flex justify-between items-center mb-1">
                                <span id="importStatusText" class="text-sm text-gray-500">Sedang mengimpor...</span>
                                <span id="importPercentage" class="text-sm font-medium text-gray-700">0%</span>
                            </div>
                            <div class="w-full h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                <div id="importProgressBar" class="h-full bg-primary-600 rounded-full transition-all duration-500" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
                        <button id="cancelImportBtn" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                            Batal
                        </button>
                        <button id="confirmImportBtn" disabled class="px-4 py-2 bg-gray-400 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors cursor-not-allowed">
                            <i class="fas fa-check mr-1"></i>
                            Import Data
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Toast Notifications -->
        <div id="toastContainer" class="fixed bottom-4 right-4 z-50 flex flex-col gap-2"></div>
    
        <script>
            $(document).ready(function() {
                // Variabel global untuk data
                window.importedData = null;
                window.usingImportedData = false;
                
                // Sembunyikan tombol export Excel pada awal halaman
                $('#exportExcelBtn').addClass('hidden');
                
                // Handler untuk tombol mulai analisis pada welcome message
                $('#startAnalysisBtn').click(function() {
                    $('#welcomeMessage').addClass('hidden');
                    $('#analyzeBtn').click();
                });
                
                // Sistem Toast Notifications
                function showToast(message, type = 'info', duration = 3000) {
                    const icons = {
                        'success': '<i class="fas fa-check-circle mr-2"></i>',
                        'error': '<i class="fas fa-exclamation-circle mr-2"></i>',
                        'warning': '<i class="fas fa-exclamation-triangle mr-2"></i>',
                        'info': '<i class="fas fa-info-circle mr-2"></i>'
                    };
                    
                    const colors = {
                        'success': 'bg-green-500',
                        'error': 'bg-red-500',
                        'warning': 'bg-yellow-500',
                        'info': 'bg-primary-500'
                    };
                    
                    const toast = `
                        <div class="transform transition-all duration-300 ease-in-out translate-x-full" style="max-width: 24rem;">
                            <div class="rounded-lg shadow-lg overflow-hidden ${colors[type]} text-white">
                                <div class="p-4 flex items-center">
                                    ${icons[type]}
                                    <div class="ml-1">${message}</div>
                                    <button class="ml-auto focus:outline-none text-white hover:text-gray-100 close-toast">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    const $toast = $(toast);
                    $('#toastContainer').append($toast);
                    
                    // Animate in
                    setTimeout(() => {
                        $toast.removeClass('translate-x-full');
                    }, 100);
                    
                    // Auto remove after duration
                    setTimeout(() => {
                        $toast.addClass('translate-x-full');
                        setTimeout(() => {
                            $toast.remove();
                        }, 300);
                    }, duration);
                    
                    // Close button
                    $toast.find('.close-toast').click(function() {
                        $toast.addClass('translate-x-full');
                        setTimeout(() => {
                            $toast.remove();
                        }, 300);
                    });
                }
                
                // Fungsi untuk mendapatkan informasi filter yang aktif
                function getActiveFiltersInfo() {
                    const activeFilters = [];
                    
                    $('select[name^="filters"], input[name^="filters"]:checked, input[type="number"][name^="filters"]').each(function() {
                        const value = $(this).val();
                        if (value !== '' && value !== null) {
                            const name = $(this).attr('name').match(/\[(.*?)\]/)[1];
                            let label = $(`label[for="${$(this).attr('id')}"]`).text().trim();
                            
                            if (!label) {
                                // Coba dapatkan label dari parent label
                                label = $(this).closest('div').find('label').first().text().trim();
                            }
                            
                            // Dapatkan teks opsi untuk select
                            let displayValue = value;
                            if ($(this).is('select')) {
                                displayValue = $(this).find('option:selected').text();
                            }
                            
                            activeFilters.push(`${label}: ${displayValue}`);
                        }
                    });
                    
                    return activeFilters.join(', ');
                }
                
                // Fungsi untuk deteksi tipe kolom
                function detectColumnTypes(data) {
                    const columnTypes = {};
                    const sample = data.slice(0, Math.min(100, data.length));
                    
                    if (sample.length === 0 || !sample[0]) return columnTypes;
                    
                    const headers = Object.keys(sample[0]);
                    
                    headers.forEach(header => {
                        const values = sample.map(row => row[header]).filter(val => val !== null && val !== undefined && val !== '');
                        
                        if (values.length === 0) {
                            columnTypes[header] = 'unknown';
                        } else if (values.every(val => typeof val === 'number' || !isNaN(Number(val)))) {
                            columnTypes[header] = 'number';
                        } else if (values.every(val => typeof val === 'boolean' || val === 'true' || val === 'false' || val === '1' || val === '0')) {
                            columnTypes[header] = 'boolean';
                        } else {
                            columnTypes[header] = 'string';
                        }
                    });
                    
                    console.log("Detected column types:", columnTypes);
                    return columnTypes;
                }// Event handler untuk tombol analisis
            $('#analyzeBtn').click(function() {
                const filters = {};
                
                // Collect all filter values
                $('select[name^="filters"], input[name^="filters"]:checked, input[type="number"][name^="filters"]').each(function() {
                    const name = $(this).attr('name').match(/\[(.*?)\]/)[1];
                    const value = $(this).val();
                    if (value !== '') {
                        filters[name] = value;
                    }
                });

                $('#welcomeMessage').addClass('hidden');
                $('#loadingIndicator').removeClass('hidden');
                $('#chartArea, #tableArea').empty();
                
                // Sembunyikan tombol export saat memulai analisis baru
                $('#exportExcelBtn').addClass('hidden');
                
                // Hapus notifikasi hasil sebelumnya
                $('#results').find('.bg-blue-100').remove();
                $('#results').find('.bg-green-100').remove();
                $('#results').find('.preview-data').remove();
                $('#results').find('#previewDataBtn').remove();

                // Cek jika kita menggunakan data impor
                if (window.usingImportedData && window.importedData) {
                    // Proses data impor langsung di client
                    processImportedDataWithFilters(window.importedData, filters);
                } else {
                    // Gunakan data dari server (kode asli)
                    $.ajax({
                        url: '{{ route("analysis.analyze") }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            filters: filters,
                            visualization_type: 'table' // default ke tabel
                        },
                        success: function(response) {
                            $('#loadingIndicator').addClass('hidden');
                            if (response.success) {
                                renderVisualization(response.data);
                                window.analysisData = response.data;
                                $('#exportExcelBtn').removeClass('hidden');
                                showToast('Analisis data berhasil.', 'success');
                            } else {
                                showToast('Gagal menganalisis data: ' + response.message, 'error');
                                $('#exportExcelBtn').addClass('hidden');
                            }
                        },
                        error: function(xhr) {
                            $('#loadingIndicator').addClass('hidden');
                            console.error('Analysis Error:', xhr.responseJSON);
                            showToast('Mohon Login Terlebih Dahulu: ' + (xhr.responseJSON?.message || 'Unknown error'), 'error');
                            $('#exportExcelBtn').addClass('hidden');
                        }
                    });
                }
            });
            
            // Fungsi untuk mempersiapkan chart untuk printing
            function prepareChartsForPrinting() {
                // Jika menggunakan visualisasi chart
                if ($('#chartArea svg').length > 0) {
                    // Pastikan chart terlihat penuh saat cetak
                    $('#chartArea').addClass('print-full-width');
                    
                    // Jika ada chart yang dirender oleh ApexCharts, pastikan terlihat saat print
                    if (typeof ApexCharts !== 'undefined') {
                        // ApexCharts mungkin perlu dirender ulang untuk ukuran print
                        $('.apexcharts-canvas').css('width', '100%');
                    }
                }
                
                // Jika menggunakan tabel, pastikan tidak terpotong
                if ($('#tableArea table').length > 0) {
                    $('#tableArea').addClass('print-full-width');
                    $('#tableArea table').addClass('print-full-width');
                    
                    // Hapus whitespace-nowrap untuk memungkinkan teks wrap
                    $('#tableArea td, #tableArea th').removeClass('whitespace-nowrap');
                }
            }

            function renderVisualization(data) {
                switch(data.type) {
                    case 'table':
                        renderTable(data);
                        break;
                    case 'bar':
                        renderBarChart(data);
                        break;
                    case 'pie':
                        renderPieChart(data);
                        break;
                    case 'line':
                        renderLineChart(data);
                        break;
                }
            }

            function renderTable(data) {
                console.log("renderTable dipanggil dengan data:", data);
                
                if (!data.headers || !data.rows || data.headers.length === 0) {
                    console.error("Data tidak valid untuk tabel:", data);
                    $('#tableArea').html('<div class="text-center text-red-500 py-4">Data tidak valid untuk ditampilkan.</div>');
                    return;
                }
                
                // Tambahkan kontrol untuk tabel
                const tableControls = `
                    <div class="flex flex-wrap justify-between items-center mb-4">
                        <div class="flex items-center space-x-2 mb-2 sm:mb-0">
                            <select id="tablePageSize" class="bg-white border border-gray-300 text-gray-700 text-sm rounded-md focus:ring-primary-500 focus:border-primary-500 p-2">
                                <option value="10">10 baris</option>
                                <option value="25">25 baris</option>
                                <option value="50">50 baris</option>
                                <option value="100">100 baris</option>
                            </select>
                            <span class="text-sm text-gray-500">Total: ${data.rows.length} baris</span>
                        </div>
                        <div class="relative">
                            <input type="text" id="tableSearch" placeholder="Cari data..." class="bg-white border border-gray-300 text-gray-700 text-sm rounded-md focus:ring-primary-500 focus:border-primary-500 pl-8 p-2 w-full">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                `;
                
                const tableHtml = `
                    <table class="min-w-full divide-y divide-gray-200 data-table">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky top-0 bg-gray-50 shadow-sm z-10 border-b">
                                    No
                                </th>
                                ${data.headers.map(header => `
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky top-0 bg-gray-50 shadow-sm z-10 border-b cursor-pointer hover:bg-gray-100 transition-colors" data-sort="${header}">
                                        <div class="flex items-center">
                                            ${header}
                                            <span class="ml-1 text-gray-400"><i class="fas fa-sort"></i></span>
                                        </div>
                                    </th>
                                `).join('')}
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            ${data.rows.map((row, index) => `
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                        ${index + 1}
                                    </td>
                                    ${row.map(cell => `
                                        <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                            ${cell !== null && cell !== undefined ? cell : '-'}
                                        </td>
                                    `).join('')}
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                `;

                // Tambahkan pagination
                const pagination = `
                    <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 mt-4">
                        <div class="flex flex-1 justify-between sm:hidden">
                            <button id="prevPageMobile" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Previous</button>
                            <button id="nextPageMobile" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Next</button>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing <span id="pageStart" class="font-medium">1</span> to <span id="pageEnd" class="font-medium">10</span> of <span id="totalItems" class="font-medium">${data.rows.length}</span> results
                                </p>
                            </div>
                            <div>
                                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination" id="paginationContainer">
                                    <button id="prevPage" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <!-- Page buttons will be inserted here -->
                                    <div id="pageNumbers" class="flex"></div>
                                    <button id="nextPage" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </nav>
                            </div>
                        </div>
                    </div>
                `;

                // Tampilkan pesan jika tidak ada data
                if (data.rows.length === 0) {
                    $('#tableArea').html(`
                        <div class="text-center py-12 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="mx-auto w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center mb-4">
                                <i class="fas fa-search text-gray-400 text-lg"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada data</h3>
                            <p class="text-gray-500 max-w-md mx-auto">Tidak ada data yang sesuai dengan filter yang dipilih. Coba ubah kriteria filter Anda.</p>
                        </div>
                    `);
                    return;
                }

                // Bersihkan area dan tampilkan tabel dengan pagination
                $('#tableArea').empty();
                $('#tableArea').append(tableControls);
                $('#tableArea').append(`<div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">${tableHtml}</div>`);
                $('#tableArea').append(pagination);
                
                // Implementasi fitur tabel
                initializeTableFeatures(data);
                
                $('#chartArea').empty();
                
                console.log("Tabel berhasil dirender");
                
                // Tambahkan notifikasi hasil
                const resultNotice = `
                    <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4 rounded shadow-sm" role="alert">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-blue-500"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium">
                                    Analisis Selesai
                                </p>
                                <p class="text-sm">
                                    Menampilkan ${data.rows.length} baris data.
                                </p>
                            </div>
                        </div>
                    </div>
                `;
                
                // Tambahkan notifikasi sebelum tabel
                $('#tableArea').before(resultNotice);
            }
            
            // Inisialisasi fitur tabel (pagination, sorting, searching)
            function initializeTableFeatures(data) {
                let tableData = [...data.rows];
                let currentPage = 1;
                let rowsPerPage = 10;
                let sortColumn = null;
                let sortDirection = 'asc';
                
                // Fungsi untuk memfilter data berdasarkan pencarian
                function filterData(searchTerm) {
                    if (!searchTerm) return [...data.rows];
                    
                    searchTerm = searchTerm.toLowerCase();
                    return data.rows.filter(row => {
                        return row.some(cell => {
                            if (cell === null || cell === undefined) return false;
                            return String(cell).toLowerCase().includes(searchTerm);
                        });
                    });
                }
                
                // Fungsi untuk sorting data
                function sortData(column, direction) {
                    const columnIndex = data.headers.indexOf(column);
                    if (columnIndex === -1) return tableData;
                    
                    return [...tableData].sort((a, b) => {
                        const aValue = a[columnIndex];
                        const bValue = b[columnIndex];
                        
                        // Handle null/undefined values
                        if (aValue === null || aValue === undefined) return direction === 'asc' ? -1 : 1;
                        if (bValue === null || bValue === undefined) return direction === 'asc' ? 1 : -1;
                        
                        // Attempt to parse as number if possible
                        const aNum = Number(aValue);
                        const bNum = Number(bValue);
                        
                        if (!isNaN(aNum) && !isNaN(bNum)) {
                            return direction === 'asc' ? aNum - bNum : bNum - aNum;
                        }
                        
                        // Otherwise, compare as strings
                        const aStr = String(aValue).toLowerCase();
                        const bStr = String(bValue).toLowerCase();
                        
                        if (direction === 'asc') {
                            return aStr.localeCompare(bStr);
                        } else {
                            return bStr.localeCompare(aStr);
                        }
                    });
                }// Fungsi untuk merender tabel dengan pagination
                function renderTablePage() {
                    const startIndex = (currentPage - 1) * rowsPerPage;
                    const endIndex = startIndex + rowsPerPage;
                    const paginatedData = tableData.slice(startIndex, endIndex);
                    
                    // Update tampilan
                    const tbody = $('#tableArea tbody');
                    tbody.empty();
                    
                    paginatedData.forEach((row, index) => {
                        const actualIndex = startIndex + index;
                        const tr = $('<tr>').addClass('hover:bg-gray-50 transition-colors');
                        
                        // Add row number
                        tr.append($('<td>').addClass('px-6 py-4 text-sm text-gray-900 whitespace-nowrap').text(actualIndex + 1));
                        
                        // Add data cells
                        row.forEach(cell => {
                            tr.append($('<td>').addClass('px-6 py-4 text-sm text-gray-900 whitespace-nowrap').text(cell !== null && cell !== undefined ? cell : '-'));
                        });
                        
                        tbody.append(tr);
                    });
                    
                    // Update pagination display
                    updatePagination();
                }
                
                // Fungsi untuk update pagination display
                function updatePagination() {
                    const totalPages = Math.ceil(tableData.length / rowsPerPage);
                    const startItem = tableData.length === 0 ? 0 : (currentPage - 1) * rowsPerPage + 1;
                    const endItem = Math.min(currentPage * rowsPerPage, tableData.length);
                    
                    // Update text display
                    $('#pageStart').text(startItem);
                    $('#pageEnd').text(endItem);
                    $('#totalItems').text(tableData.length);
                    
                    // Update page numbers
                    const pageNumbers = $('#pageNumbers');
                    pageNumbers.empty();
                    
                    // Determine range of pages to show
                    let startPage = Math.max(1, currentPage - 2);
                    let endPage = Math.min(totalPages, startPage + 4);
                    
                    // Adjust if we're near the end
                    if (endPage - startPage < 4 && startPage > 1) {
                        startPage = Math.max(1, endPage - 4);
                    }
                    
                    // Add first page and ellipsis if needed
                    if (startPage > 1) {
                        pageNumbers.append(createPageButton(1));
                        if (startPage > 2) {pageNumbers.append(`
                                <span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 focus:outline-offset-0">
                                    ...
                                </span>
                            `);
                        }
                    }
                    
                    // Add page numbers
                    for (let i = startPage; i <= endPage; i++) {
                        pageNumbers.append(createPageButton(i));
                    }
                    
                    // Add last page and ellipsis if needed
                    if (endPage < totalPages) {
                        if (endPage < totalPages - 1) {
                            pageNumbers.append(`
                                <span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 focus:outline-offset-0">
                                    ...
                                </span>
                            `);
                        }
                        pageNumbers.append(createPageButton(totalPages));
                    }
                    
                    // Enable/disable prev/next buttons
                    $('#prevPage, #prevPageMobile').prop('disabled', currentPage === 1)
                        .toggleClass('opacity-50 cursor-not-allowed', currentPage === 1);
                    $('#nextPage, #nextPageMobile').prop('disabled', currentPage === totalPages)
                        .toggleClass('opacity-50 cursor-not-allowed', currentPage === totalPages);
                }
                
                // Create a page button with appropriate styling
                function createPageButton(pageNum) {
                    const isActive = pageNum === currentPage;
                    let btnClass = 'relative inline-flex items-center px-4 py-2 text-sm font-semibold ';
                    
                    if (isActive) {
                        btnClass += 'z-10 bg-primary-600 text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600';
                    } else {
                        btnClass += 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0';
                    }
                    
                    return $('<button>')
                        .addClass(btnClass)
                        .text(pageNum)
                        .attr('data-page', pageNum)
                        .click(function() {
                            if (currentPage !== pageNum) {
                                currentPage = pageNum;
                                renderTablePage();
                            }
                        });
                }
                
                // Handle pagination click events
                $('#prevPage, #prevPageMobile').click(function() {
                    if (currentPage > 1) {
                        currentPage--;
                        renderTablePage();
                    }
                });
                
                $('#nextPage, #nextPageMobile').click(function() {
                    const totalPages = Math.ceil(tableData.length / rowsPerPage);
                    if (currentPage < totalPages) {
                        currentPage++;
                        renderTablePage();
                    }
                });
                
                // Handle page size change
                $('#tablePageSize').change(function() {
                    rowsPerPage = parseInt($(this).val());
                    currentPage = 1; // Reset to first page
                    renderTablePage();
                });
                
                // Handle search
                $('#tableSearch').on('input', debounce(function() {
                    const searchTerm = $(this).val();
                    tableData = filterData(searchTerm);
                    currentPage = 1; // Reset to first page
                    renderTablePage();
                }, 300));
                
                // Handle column sorting
                $('#tableArea th[data-sort]').click(function() {
                    const column = $(this).data('sort');
                    
                    // Update visual indicators
                    $('#tableArea th .fas').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort');
                    
                    if (sortColumn === column) {
                        // Toggle direction if same column
                        sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
                    } else {
                        // Default to ascending for new column
                        sortColumn = column;
                        sortDirection = 'asc';
                    }
                    
                    // Update icon
                    $(this).find('.fas')
                        .removeClass('fa-sort')
                        .addClass(sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
                    
                    // Sort data
                    tableData = sortData(column, sortDirection);
                    renderTablePage();
                });
                
                // Initialize with default state
                renderTablePage();
            }
            
            // Simple debounce function for search input
            function debounce(func, wait) {
                let timeout;
                return function(...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), wait);
                };
            }

            function renderBarChart(data) {
                $('#chartArea').empty();
                $('#tableArea').empty();

                // Tambahkan kontrol chart
                const chartControls = `
                    <div class="flex flex-wrap justify-between items-center mb-4">
                        <div class="flex items-center space-x-4">
                            <h3 class="text-lg font-semibold text-gray-800">${data.datasets[0].label}</h3>
                            <div class="flex items-center space-x-2">
                                <button class="toggle-view bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm py-1 px-3 rounded-l-md active" data-view="bar">
                                    <i class="fas fa-chart-bar mr-1"></i> Bar
                                </button>
                                <button class="toggle-view bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm py-1 px-3" data-view="line">
                                    <i class="fas fa-chart-line mr-1"></i> Line
                                </button>
                                <button class="toggle-view bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm py-1 px-3 rounded-r-md" data-view="table">
                                    <i class="fas fa-table mr-1"></i> Table
                                </button>
                            </div>
                        </div>
                        <button class="download-chart bg-primary-50 hover:bg-primary-100 text-primary-700 text-sm py-1 px-3 rounded flex items-center">
                            <i class="fas fa-download mr-1"></i> Download
                        </button>
                    </div>
                `;
                
                $('#chartArea').append(chartControls);
                $('#chartArea').append('<div id="chart" class="h-96"></div>');
                
                const options = {
                    chart: {
                        type: 'bar',
                        height: 400,
                        fontFamily: 'Inter, sans-serif',
                        toolbar: {
                            show: true,
                            tools: {
                                download: false,
                                selection: true,
                                zoom: true,
                                zoomin: true,
                                zoomout: true,
                                pan: true,
                                reset: true
                            }
                        },
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800,
                            animateGradually: {
                                enabled: true,
                                delay: 150
                            },
                            dynamicAnimation: {
                                enabled: true,
                                speed: 350
                            }
                        }
                    },
                    colors: ['#6366f1'],
                    series: [{
                        name: data.datasets[0].label,
                        data: data.datasets[0].data
                    }],
                    xaxis: {
                        categories: data.labels,
                        labels: {
                            style: {
                                colors: '#64748b',
                                fontFamily: 'Inter, sans-serif'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                colors: '#64748b',
                                fontFamily: 'Inter, sans-serif'
                            }
                        }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '55%',
                            endingShape: 'rounded',
                            borderRadius: 4
                        },
                    },
                    dataLabels: {
                        enabled: true,
                        style: {
                            fontSize: '12px',
                            fontFamily: 'Inter, sans-serif',
                            fontWeight: 500,
                            colors: ['#fff']
                        },
                        background: {
                            enabled: true,
                            foreColor: '#6366f1',
                            padding: 4,
                            borderRadius: 2,
                            borderWidth: 1,
                            borderColor: '#6366f1',
                            opacity: 0.9
                        }
                    },
                    title: {
                        text: data.datasets[0].label,
                        align: 'center',
                        style: {
                            fontSize: '16px',
                            fontWeight: 600,
                            fontFamily: 'Inter, sans-serif',
                            color: '#334155'
                        }
                    },
                    grid: {
                        borderColor: '#e2e8f0',
                        strokeDashArray: 4
                    },
                    tooltip: {
                        theme: 'light',
                        y: {
                            formatter: function (val) {
                                return val.toLocaleString();
                            }
                        }
                    }
                };

                const chart = new ApexCharts(document.querySelector("#chart"), options);
                chart.render();
                
                // Event handler untuk toggle view
                $('.toggle-view').click(function() {
                    $('.toggle-view').removeClass('active');
                    $(this).addClass('active');
                    
                    const view = $(this).data('view');
                    switch(view) {
                        case 'bar':
                            chart.updateOptions({
                                chart: {
                                    type: 'bar'
                                }
                            });
                            break;
                        case 'line':
                            chart.updateOptions({
                                chart: {
                                    type: 'line'
                                }
                            });
                            break;
                        case 'table':
                            // Create and show table view
                            chart.destroy();
                            renderChartDataAsTable(data);
                            break;
                    }
                });
                
                // Event handler untuk download
                $('.download-chart').click(function() {
                    chart.dataURI().then(({ imgURI, blob }) => {
                        const downloadLink = document.createElement('a');
                        downloadLink.href = imgURI;
                        downloadLink.download = `${data.datasets[0].label.replace(/\s+/g, '_')}_chart.png`;
                        downloadLink.click();
                    });
                });
            }
            
            // Render chart data as table
            function renderChartDataAsTable(data) {
                $('#chart').remove();
                const tableHtml = `
                    <div class="overflow-x-auto mt-4 rounded-lg border border-gray-200 shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Kategori
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nilai
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                ${data.labels.map((label, index) => `
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                            ${label}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                            ${data.datasets[0].data[index].toLocaleString()}
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
                $('#chartArea').append(tableHtml);
            }

            function renderPieChart(data) {
                $('#chartArea').empty();
                $('#tableArea').empty();
                
                // Tambahkan kontrol chart
                const chartControls = `
                    <div class="flex flex-wrap justify-between items-center mb-4">
                        <div class="flex items-center space-x-4">
                            <h3 class="text-lg font-semibold text-gray-800">${data.datasets[0].label}</h3>
                            <div class="flex items-center space-x-2">
                                <button class="toggle-view bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm py-1 px-3 rounded-l-md active" data-view="pie">
                                    <i class="fas fa-chart-pie mr-1"></i> Pie
                                </button>
                                <button class="toggle-view bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm py-1 px-3" data-view="donut">
                                    <i class="fas fa-ring mr-1"></i> Donut
                                </button>
                                <button class="toggle-view bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm py-1 px-3 rounded-r-md" data-view="table">
                                    <i class="fas fa-table mr-1"></i> Table
                                </button>
                            </div>
                        </div>
                        <button class="download-chart bg-primary-50 hover:bg-primary-100 text-primary-700 text-sm py-1 px-3 rounded flex items-center">
                            <i class="fas fa-download mr-1"></i> Download
                        </button>
                    </div>
                `;
                
                $('#chartArea').append(chartControls);
                $('#chartArea').append('<div id="chart" class="h-96"></div>');// Generate color palette
                const colors = [
                    '#6366f1', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444',
                    '#8b5cf6', '#06b6d4', '#14b8a6', '#f97316', '#ec4899',
                    '#8b5cf6', '#3b82f6', '#22c55e', '#eab308', '#f43f5e'
                ];
                
                // Ensure we have enough colors
                while (colors.length < data.labels.length) {
                    colors.push(...colors);
                }
                
                const options = {
                    chart: {
                        type: 'pie',
                        height: 400,
                        fontFamily: 'Inter, sans-serif',
                        toolbar: {
                            show: true,
                            tools: {
                                download: false,
                                selection: false,
                                zoom: false,
                                zoomin: false,
                                zoomout: false,
                                pan: false,
                                reset: false
                            }
                        },
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800,
                            animateGradually: {
                                enabled: true,
                                delay: 150
                            },
                            dynamicAnimation: {
                                enabled: true,
                                speed: 350
                            }
                        }
                    },
                    colors: colors.slice(0, data.labels.length),
                    series: data.datasets[0].data,
                    labels: data.labels,
                    title: {
                        text: data.datasets[0].label,
                        align: 'center',
                        style: {
                            fontSize: '16px',
                            fontWeight: 600,
                            fontFamily: 'Inter, sans-serif',
                            color: '#334155'
                        }
                    },
                    legend: {
                        position: 'bottom',
                        fontFamily: 'Inter, sans-serif',
                        fontSize: '14px',
                        markers: {
                            width: 12,
                            height: 12,
                            radius: 12
                        },
                        onItemHover: {
                            highlightDataSeries: true
                        }
                    },
                    plotOptions: {
                        pie: {
                            dataLabels: {
                                offset: -15
                            }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val, opt) {
                            return opt.w.globals.series[opt.seriesIndex] + ' (' + val.toFixed(1) + '%)';
                        },
                        style: {
                            fontSize: '12px',
                            fontFamily: 'Inter, sans-serif',
                            fontWeight: 500,
                            colors: ['#fff']
                        },
                        background: {
                            enabled: true,
                            foreColor: '#333',
                            padding: 4,
                            borderRadius: 2,
                            borderWidth: 1,
                            borderColor: '#fff',
                            opacity: 0.9
                        },
                        dropShadow: {
                            enabled: true,
                            color: '#000',
                            top: 0,
                            left: 0,
                            blur: 3,
                            opacity: 0.3
                        }
                    },
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: 300
                            },
                            legend: {
                                position: 'bottom',
                                floating: false,
                                offsetY: 10
                            }
                        }
                    }],
                    tooltip: {
                        theme: 'light',
                        y: {
                            formatter: function (val) {
                                return val.toLocaleString();
                            }
                        }
                    }
                };

                const chart = new ApexCharts(document.querySelector("#chart"), options);
                chart.render();
                
                // Event handler untuk toggle view
                $('.toggle-view').click(function() {
                    $('.toggle-view').removeClass('active');
                    $(this).addClass('active');
                    
                    const view = $(this).data('view');
                    switch(view) {
                        case 'pie':
                            chart.updateOptions({
                                chart: {
                                    type: 'pie'
                                },
                                plotOptions: {
                                    pie: {
                                        donut: {
                                            size: '0%'
                                        }
                                    }
                                }
                            });
                            break;
                        case 'donut':
                            chart.updateOptions({
                                chart: {
                                    type: 'donut'
                                },
                                plotOptions: {
                                    pie: {
                                        donut: {
                                            size: '50%',
                                            labels: {
                                                show: true,
                                                name: {
                                                    show: true
                                                },
                                                value: {
                                                    show: true,
                                                    formatter: function(val) {
                                                        return val.toLocaleString();
                                                    }
                                                },
                                                total: {
                                                    show: true,
                                                    label: 'Total',
                                                    formatter: function(w) {
                                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString();
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                            break;
                        case 'table':
                            // Create and show table view
                            chart.destroy();
                            renderChartDataAsTable(data);
                            break;
                    }
                });
                
                // Event handler untuk download
                $('.download-chart').click(function() {
                    chart.dataURI().then(({ imgURI, blob }) => {
                        const downloadLink = document.createElement('a');
                        downloadLink.href = imgURI;
                        downloadLink.download = `${data.datasets[0].label.replace(/\s+/g, '_')}_chart.png`;
                        downloadLink.click();
                    });
                });
            }

            function renderLineChart(data) {
                $('#chartArea').empty();
                $('#tableArea').empty();

                // Tambahkan kontrol chart
                const chartControls = `
                    <div class="flex flex-wrap justify-between items-center mb-4">
                        <div class="flex items-center space-x-4">
                            <h3 class="text-lg font-semibold text-gray-800">${data.datasets[0].label}</h3>
                            <div class="flex items-center space-x-2">
                                <button class="toggle-view bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm py-1 px-3 rounded-l-md" data-view="bar">
                                    <i class="fas fa-chart-bar mr-1"></i> Bar
                                </button>
                                <button class="toggle-view bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm py-1 px-3 active" data-view="line">
                                    <i class="fas fa-chart-line mr-1"></i> Line
                                </button>
                                <button class="toggle-view bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm py-1 px-3" data-view="area">
                                    <i class="fas fa-chart-area mr-1"></i> Area
                                </button>
                                <button class="toggle-view bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm py-1 px-3 rounded-r-md" data-view="table">
                                    <i class="fas fa-table mr-1"></i> Table
                                </button>
                            </div>
                        </div>
                        <button class="download-chart bg-primary-50 hover:bg-primary-100 text-primary-700 text-sm py-1 px-3 rounded flex items-center">
                            <i class="fas fa-download mr-1"></i> Download
                        </button>
                    </div>
                `;
                
                $('#chartArea').append(chartControls);
                $('#chartArea').append('<div id="chart" class="h-96"></div>');
                
                const options = {
                    chart: {
                        type: 'line',
                        height: 400,
                        fontFamily: 'Inter, sans-serif',
                        toolbar: {
                            show: true,
                            tools: {
                                download: false,
                                selection: true,
                                zoom: true,
                                zoomin: true,
                                zoomout: true,
                                pan: true,
                                reset: true
                            }
                        },
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800,
                            animateGradually: {
                                enabled: true,
                                delay: 150
                            },
                            dynamicAnimation: {
                                enabled: true,
                                speed: 350
                            }
                        }
                    },
                    colors: ['#6366f1'],
                    series: [{
                        name: data.datasets[0].label,
                        data: data.datasets[0].data
                    }],
                    xaxis: {
                        categories: data.labels,
                        labels: {
                            style: {
                                colors: '#64748b',
                                fontFamily: 'Inter, sans-serif'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                colors: '#64748b',
                                fontFamily: 'Inter, sans-serif'
                            },
                            formatter: function(val) {
                                return val.toLocaleString();
                            }
                        }
                    },
                    title: {
                        text: data.datasets[0].label,
                        align: 'center',
                        style: {
                            fontSize: '16px',
                            fontWeight: 600,
                            fontFamily: 'Inter, sans-serif',
                            color: '#334155'
                        }
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    markers: {
                        size: 5,
                        colors: ['#6366f1'],
                        strokeColors: '#ffffff',
                        strokeWidth: 2,
                        hover: {
                            size: 7
                        }
                    },
                    grid: {
                        borderColor: '#e2e8f0',
                        strokeDashArray: 4
                    },
                    tooltip: {
                        theme: 'light',
                        y: {
                            formatter: function (val) {
                                return val.toLocaleString();
                            }
                        }
                    }
                };

                const chart = new ApexCharts(document.querySelector("#chart"), options);
                chart.render();
                
                // Event handler untuk toggle view
                $('.toggle-view').click(function() {
                    $('.toggle-view').removeClass('active');
                    $(this).addClass('active');
                    
                    const view = $(this).data('view');
                    switch(view) {
                        case 'bar':
                            chart.updateOptions({
                                chart: {
                                    type: 'bar'
                                },
                                stroke: {
                                    width: 0
                                },
                                plotOptions: {
                                    bar: {
                                        borderRadius: 4
                                    }
                                }
                            });
                            break;
                        case 'line':
                            chart.updateOptions({
                                chart: {
                                    type: 'line'
                                },
                                stroke: {
                                    curve: 'smooth',
                                    width: 3
                                },
                                markers: {
                                    size: 5
                                }
                            });
                            break;
                        case 'area':
                            chart.updateOptions({
                                chart: {
                                    type: 'area'
                                },
                                stroke: {
                                    curve: 'smooth',
                                    width: 2
                                },
                                fill: {
                                    type: 'gradient',
                                    gradient: {
                                        shadeIntensity: 1,
                                        opacityFrom: 0.7,
                                        opacityTo: 0.3,
                                        stops: [0, 90, 100]
                                    }
                                }
                            });
                            break;
                        case 'table':
                            // Create and show table view
                            chart.destroy();
                            renderChartDataAsTable(data);
                            break;
                    }
                });
                
                // Event handler untuk download
                $('.download-chart').click(function() {
                    chart.dataURI().then(({ imgURI, blob }) => {
                        const downloadLink = document.createElement('a');
                        downloadLink.href = imgURI;
                        downloadLink.download = `${data.datasets[0].label.replace(/\s+/g, '_')}_chart.png`;
                        downloadLink.click();
                    });
                });
            }// Fungsi untuk mengekspor data ke Excel
            function exportToExcel() {
                console.log("Starting exportToExcel function");
                
                // Pastikan ada data untuk diekspor
                if (!window.analysisData) {
                    showToast('Tidak ada data untuk diekspor!', 'error');
                    return;
                }
                
                try {
                    const data = window.analysisData;
                    const today = new Date();
                    const dateString = today.toISOString().slice(0, 10);
                    const filename = `PKM_Kaben_Analisis_${dateString}.xlsx`;
                    
                    console.log("Preparing Excel data");
                    showToast('Mempersiapkan data untuk ekspor...', 'info');
                    
                    // Buat workbook baru
                    const wb = XLSX.utils.book_new();
                    
                    // Tergantung pada tipe data, buat worksheet yang sesuai
                    if (data.type === 'table') {
                        // Untuk tabel, tambahkan data dari tabel ke worksheet
                        const wsData = [
                            data.headers, // header row
                            ...data.rows // data rows
                        ];
                        
                        const ws = XLSX.utils.aoa_to_sheet(wsData);// Style the header row
                        const headerRange = XLSX.utils.decode_range(ws['!ref']);
                        for (let col = headerRange.s.c; col <= headerRange.e.c; col++) {
                            const cellRef = XLSX.utils.encode_cell({ r: 0, c: col });
                            ws[cellRef].s = {
                                font: { bold: true, color: { rgb: "FFFFFF" } },
                                fill: { fgColor: { rgb: "4F46E5" } },
                                alignment: { horizontal: "center", vertical: "center" }
                            };
                        }
                        
                        // Add cell styles
                        for (let row = 1; row <= data.rows.length; row++) {
                            for (let col = 0; col <= data.headers.length; col++) {
                                const cellRef = XLSX.utils.encode_cell({ r: row, c: col });
                                if (ws[cellRef]) {
                                    ws[cellRef].s = {
                                        font: { color: { rgb: "000000" } },
                                        fill: { fgColor: { rgb: row % 2 === 0 ? "F3F4F6" : "FFFFFF" } },
                                        alignment: { horizontal: "left", vertical: "center" }
                                    };
                                }
                            }
                        }
                        
                        // Adjust column widths
                        const maxWidth = 50;
                        const defaultWidth = 12;
                        const wscols = [];
                        
                        for (let i = 0; i < data.headers.length; i++) {
                            // Check max content length in this column
                            let maxLength = String(data.headers[i]).length;
                            
                            for (let j = 0; j < data.rows.length; j++) {
                                const cellValue = data.rows[j][i];
                                if (cellValue !== null && cellValue !== undefined) {
                                    const cellLength = String(cellValue).length;
                                    maxLength = Math.max(maxLength, cellLength);
                                }
                            }
                            
                            // Add column width (limited to maxWidth)
                            wscols.push({ width: Math.min(maxLength + 2, maxWidth) || defaultWidth });
                        }
                        
                        ws['!cols'] = wscols;
                        
                        XLSX.utils.book_append_sheet(wb, ws, "Data Analisis");
                    } else {
                        // Untuk chart, buat worksheet dari labels dan data
                        const chartData = [];
                        // Add header row
                        chartData.push(['Label', data.datasets[0].label]);
                        
                        // Add data rows
                        for (let i = 0; i < data.labels.length; i++) {
                            chartData.push([data.labels[i], data.datasets[0].data[i]]);
                        }
                        
                        const ws = XLSX.utils.aoa_to_sheet(chartData);
                        
                        // Style headers
                        const headerCellRef1 = XLSX.utils.encode_cell({ r: 0, c: 0 });
                        const headerCellRef2 = XLSX.utils.encode_cell({ r: 0, c: 1 });
                        
                        ws[headerCellRef1].s = ws[headerCellRef2].s = {
                            font: { bold: true, color: { rgb: "FFFFFF" } },
                            fill: { fgColor: { rgb: "4F46E5" } },
                            alignment: { horizontal: "center", vertical: "center" }
                        };
                        
                        // Style data cells
                        for (let i = 1; i <= data.labels.length; i++) {
                            const labelCellRef = XLSX.utils.encode_cell({ r: i, c: 0 });
                            const valueCellRef = XLSX.utils.encode_cell({ r: i, c: 1 });
                            
                            ws[labelCellRef].s = {
                                font: { color: { rgb: "000000" } },
                                fill: { fgColor: { rgb: i % 2 === 0 ? "F3F4F6" : "FFFFFF" } },
                                alignment: { horizontal: "left", vertical: "center" }
                            };
                            
                            ws[valueCellRef].s = {
                                font: { color: { rgb: "000000" } },
                                fill: { fgColor: { rgb: i % 2 === 0 ? "F3F4F6" : "FFFFFF" } },
                                alignment: { horizontal: "right", vertical: "center" },
                                numFmt: "#,##0"
                            };
                        }
                        
                        // Set column widths
                        ws['!cols'] = [
                            { width: 20 }, // Label column
                            { width: 15 }  // Value column
                        ];
                        
                        XLSX.utils.book_append_sheet(wb, ws, "Data Visualisasi");
                    }
                    
                    // Tambahkan informasi filter jika ada
                    const activeFilters = getActiveFiltersInfo();
                    if (activeFilters) {
                        const filterSheet = XLSX.utils.aoa_to_sheet([
                            ['Informasi Export PKM Kaben', ''],
                            ['', ''],
                            ['Filter yang digunakan:', activeFilters],
                            ['Tanggal Export:', new Date().toLocaleString('id-ID')],
                            ['', ''],
                            ['Develope By:', 'Thoken Azter']
                        ]);
                        
                        // Style header
                        const headerCellRef = XLSX.utils.encode_cell({ r: 0, c: 0 });
                        filterSheet[headerCellRef].s = {
                            font: { bold: true, sz: 14, color: { rgb: "4F46E5" } },
                            alignment: { horizontal: "left" }
                        };
                        
                        // Style labels
                        for (let i = 2; i <= 4; i++) {
                            const labelCellRef = XLSX.utils.encode_cell({ r: i, c: 0 });
                            filterSheet[labelCellRef].s = {
                                font: { bold: true },
                                alignment: { horizontal: "left" }
                            };
                        }
                        
                        // Set column widths
                        filterSheet['!cols'] = [
                            { width: 20 },
                            { width: 40 }
                        ];
                        
                        XLSX.utils.book_append_sheet(wb, filterSheet, "Info Export");
                    }
                    
                    console.log("Writing Excel file");
                    
                    // Ekspor file Excel
                    XLSX.writeFile(wb, filename);
                    console.log("Excel file exported successfully");
                    
                    showToast('File Excel berhasil diekspor!', 'success');
                } catch (error) {
                    console.error("Error exporting Excel:", error);
                    showToast("Terjadi kesalahan saat mengekspor data: " + error.message, 'error');
                }
            }

            // Fungsi yang akan menangani export ke Excel
            window.doExcelExport = function() {
                // Panggil fungsi exportToExcel yang sudah ada
                exportToExcel();
            };

            // Timer untuk modal konfirmasi
            let timerCount = 15;
            let timerInterval;

            // Fungsi untuk memulai timer konfirmasi
            function startConfirmationTimer() {
                timerCount = 15;
                $('#timerText').text('Mohon tunggu beberapa saat...');
                $('#timerCounter').text(timerCount + 's');
                $('#confirmExportBtn').prop('disabled', true)
                    .addClass('cursor-not-allowed bg-gray-400')
                    .removeClass('bg-primary-600 hover:bg-primary-700');
                
                $('#timerProgressBar').css('width', '0%');
                
                // Clear any existing timer
                clearInterval(timerInterval);
                
                timerInterval = setInterval(function() {
                    timerCount--;
                    let progressPercent = 100 - (timerCount * 6.66); // 100% dibagi 15 detik
                    $('#timerProgressBar').css('width', progressPercent + '%');
                    $('#timerCounter').text(timerCount > 0 ? timerCount + 's' : '');
                    
                    if (timerCount <= 0) {
                        clearInterval(timerInterval);
                        $('#timerText').text('Anda dapat melanjutkan sekarang');
                        $('#confirmExportBtn').prop('disabled', false)
                            .removeClass('cursor-not-allowed bg-gray-400')
                            .addClass('bg-primary-600 hover:bg-primary-700');
                    } else {
                        $('#timerCounter').text(timerCount + 's');
                    }
                }, 1000);
            }

            // Inisialisasi event handler untuk tombol export
            $('#exportExcelBtn').off('click').on('click', function() {
                // Show modal with animation
                $('#exportConfirmationModal').removeClass('hidden');
                setTimeout(function() {
                    $('#modalContent').removeClass('opacity-0 translate-y-4').addClass('modal-enter');
                }, 50);
                startConfirmationTimer();
            });

            // Event handler untuk tombol konfirmasi
            $('#confirmExportBtn').off('click').on('click', function() {
                if (!$(this).prop('disabled')) {
                    // Hide modal with animation
                    $('#modalContent').removeClass('modal-enter').addClass('modal-exit');
                    setTimeout(function() {
                        $('#exportConfirmationModal').addClass('hidden');
                        $('#modalContent').removeClass('modal-exit');
                    }, 300);
                    
                    clearInterval(timerInterval);
                    // Panggil fungsi export menggunakan timeout untuk menghindari masalah event
                    setTimeout(window.doExcelExport, 400);
                }
            });

            // Event handler untuk tombol batal
            $('#cancelExportBtn').off('click').on('click', function() {
                // Hide modal with animation
                $('#modalContent').removeClass('modal-enter').addClass('modal-exit');
                setTimeout(function() {
                    $('#exportConfirmationModal').addClass('hidden');
                    $('#modalContent').removeClass('modal-exit');
                }, 300);
                
                clearInterval(timerInterval);
            });

            // Event handler untuk tombol import
            $('#importFileBtn').click(function() {
                // Reset form
                $('#selectedFile').addClass('hidden');
                $('#importProgress').addClass('hidden');
                $('#confirmImportBtn').prop('disabled', true)
                    .addClass('cursor-not-allowed bg-gray-400')
                    .removeClass('bg-primary-600 hover:bg-primary-700');
                
                // Tampilkan modal
                $('#importFileModal').removeClass('hidden');
                setTimeout(function() {
                    $('#importModalContent').removeClass('opacity-0 translate-y-4').addClass('modal-enter');
                }, 50);
            });
            
            // Inisialisasi event untuk drop zone dan file input
            const dropZone = document.getElementById('dropZone');
            const fileInput = document.getElementById('fileInput');
            
            // Klik pada drop zone
            if (dropZone && fileInput) {
                dropZone.addEventListener('click', function() {
                    fileInput.click();
                });
                
                // Handle drag & drop
                dropZone.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    dropZone.classList.add('border-primary-500');
                    dropZone.classList.add('bg-primary-50');
                });
                
                dropZone.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    dropZone.classList.remove('border-primary-500');
                    dropZone.classList.remove('bg-primary-50');
                });
                
                dropZone.addEventListener('drop', function(e) {
                    e.preventDefault();
                    dropZone.classList.remove('border-primary-500');
                    dropZone.classList.remove('bg-primary-50');
                    
                    if (e.dataTransfer.files.length) {
                        handleFile(e.dataTransfer.files[0]);
                    }
                });
                
                // Handle file dipilih dari input
                fileInput.addEventListener('change', function() {
                    if (fileInput.files.length) {
                        handleFile(fileInput.files[0]);
                    }
                });
            }// Event handler untuk tombol remove file
            $('#removeFileBtn').click(function(e) {
                e.stopPropagation();
                fileInput.value = '';
                $('#selectedFile').addClass('hidden');
                $('#confirmImportBtn').prop('disabled', true)
                    .addClass('cursor-not-allowed bg-gray-400')
                    .removeClass('bg-primary-600 hover:bg-primary-700');
            });
            
            // Event handler untuk tombol konfirmasi import
            $('#confirmImportBtn').click(function() {
                if (fileInput.files.length) {
                    importFile(fileInput.files[0]);
                }
            });
            
            // Event handler untuk tombol batal import
            $('#cancelImportBtn').click(function() {
                // Sembunyikan modal
                $('#importModalContent').removeClass('modal-enter').addClass('modal-exit');
                setTimeout(function() {
                    $('#importFileModal').addClass('hidden');
                    $('#importModalContent').removeClass('modal-exit');
                }, 300);
            });
            
            // Fungsi untuk menangani file yang dipilih
            function handleFile(file) {
                // Validasi tipe file
                const validTypes = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'];
                if (!validTypes.includes(file.type) && !file.name.endsWith('.csv') && !file.name.endsWith('.xlsx') && !file.name.endsWith('.xls')) {
                    showToast('Format file tidak didukung. Silakan pilih file Excel (.xlsx, .xls) atau CSV.', 'error');
                    return;
                }
                
                // Tampilkan informasi file
                $('#fileName').text(file.name);
                $('#fileSize').text(formatFileSize(file.size));
                $('#selectedFile').removeClass('hidden');
                
                // Aktifkan tombol import
                $('#confirmImportBtn').prop('disabled', false)
                    .removeClass('cursor-not-allowed bg-gray-400')
                    .addClass('bg-primary-600 hover:bg-primary-700');
            }
            
            // Format ukuran file
            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }
            
            // Fungsi untuk mengimpor file
            function importFile(file) {
                // Tampilkan progress bar
                $('#importProgress').removeClass('hidden');
                $('#importStatusText').text('Membaca file...');
                $('#importPercentage').text('0%');
                $('#importProgressBar').css('width', '0%');
                
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const data = e.target.result;
                    
                    try {
                        let importedData;
                        
                        // Update progress
                        $('#importProgressBar').css('width', '30%');
                        $('#importPercentage').text('30%');
                        $('#importStatusText').text('Menganalisis data...');
                        
                        if (file.name.endsWith('.csv')) {
                            // Parse CSV dengan PapaParse
                            Papa.parse(data, {
                                header: true,
                                dynamicTyping: true,
                                skipEmptyLines: true,
                                transformHeader: function(header) {
                                    // Bersihkan header dari karakter aneh
                                    return header.trim().replace(/[\s\uFEFF\xA0]+/g, '_');
                                },
                                complete: function(results) {
                                    if (results.errors && results.errors.length > 0) {
                                        console.warn("CSV parsing warnings:", results.errors);
                                    }
                                    
                                    // Update progress
                                    $('#importProgressBar').css('width', '60%');
                                    $('#importPercentage').text('60%');
                                    $('#importStatusText').text('Memproses data...');
                                    
                                    setTimeout(function() {
                                        if (results.data && results.data.length > 0) {
                                            // Filter out empty rows
                                            const filteredData = results.data.filter(row => {
                                                return Object.values(row).some(val => val !== null && val !== undefined && val !== '');
                                            });
                                            
                                            if (filteredData.length === 0) {
                                                showToast('Tidak ada data yang valid dalam file CSV.', 'error');
                                                return;
                                            }
                                            
                                            // Simpan data yang diimpor
                                            window.importedData = filteredData;
                                            window.usingImportedData = true;
                                            
                                            // Deteksi tipe kolom
                                            const columnTypes = detectColumnTypes(filteredData);
                                            
                                            // Update progress
                                            $('#importProgressBar').css('width', '100%');
                                            $('#importPercentage').text('100%');
                                            $('#importStatusText').text('Selesai!');
                                            
                                            // Sembunyikan modal setelah selesai
                                            setTimeout(function() {
                                                $('#importModalContent').removeClass('modal-enter').addClass('modal-exit');
                                                setTimeout(function() {
                                                    $('#importFileModal').addClass('hidden');
                                                    $('#importModalContent').removeClass('modal-exit');
                                                    
                                                    // Tampilkan notifikasi sukses
                                                    showToast(`File ${file.name} berhasil diimpor! ${filteredData.length} baris data siap dianalisis.`, 'success');
                                                    
                                                    // Reset welcome message jika visible
                                                    $('#welcomeMessage').addClass('hidden');
                                                    
                                                    // Tambahkan notifikasi sukses di area hasil
                                                    const successNotification = `
                                                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm" role="alert">
                                                            <div class="flex">
                                                                <div class="flex-shrink-0">
                                                                    <i class="fas fa-check-circle text-green-500"></i>
                                                                </div>
                                                                <div class="ml-3">
                                                                    <p class="text-sm font-medium">
                                                                        File <span class="font-bold">${file.name}</span> berhasil diimpor!
                                                                    </p>
                                                                    <p class="text-sm">
                                                                        ${filteredData.length} baris data siap untuk dianalisis. Klik "Analisis Data" untuk melihat hasilnya.
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    `;
                                                    $('#loadingIndicator').addClass('hidden');
                                                    $('#chartArea, #tableArea').empty();
                                                    $('#results').html(successNotification);
                                                    
                                                    // Tambahkan tombol preview
                                                    addPreviewButton(filteredData);
                                                    
                                                    // Perbarui tampilan untuk menunjukkan kita menggunakan data impor
                                                    updateUIForImportedData(file.name, filteredData.length);
                                                }, 300);
                                            }, 500);
                                        } else {
                                            showToast('Tidak ada data yang ditemukan dalam file CSV.', 'error');
                                        }
                                    }, 500);
                                },
                                error: function(error) {
                                    showToast('Error saat memproses file CSV: ' + error.message, 'error');
                                }
                            });
                        } else {
                            // Parse Excel
                            try {
                                const workbook = XLSX.read(data, { type: 'array' });
                                const firstSheetName = workbook.SheetNames[0];
                                const worksheet = workbook.Sheets[firstSheetName];
                                
                                // Update progress
                                $('#importProgressBar').css('width', '60%');
                                $('#importPercentage').text('60%');
                                $('#importStatusText').text('Memproses data...');
                                
                                // Konversi ke JSON dengan lebih banyak opsi
                                importedData = XLSX.utils.sheet_to_json(worksheet, { 
                                    header: 'A',  // Gunakan header baris pertama
                                    raw: false,   // Jangan konversi nilai
                                    defval: '',   // Default value untuk sel kosong
                                    blankrows: false // Skip baris kosong
                                });
                                
                                // Jika tidak ada header di baris pertama, coba ini
                                if (importedData.length > 0 && Object.keys(importedData[0]).every(k => k.match(/^[A-Z]+$/))) {
                                    // Kemungkinan tidak ada header, gunakan baris pertama sebagai header
                                    const headers = importedData[0];
                                    importedData = XLSX.utils.sheet_to_json(worksheet, {
                                        header: Object.keys(headers),
                                        range: 1 // Mulai dari baris kedua
                                    });
                                }
                                
                                // Filter out empty rows
                                const filteredData = importedData.filter(row => {
                                    return Object.values(row).some(val => val !== null && val !== undefined && val !== '');
                                });
                                
                                setTimeout(function() {
                                    if (filteredData && filteredData.length > 0) {
                                        // Simpan data yang diimpor
                                        window.importedData = filteredData;
                                        window.usingImportedData = true;
                                        
                                        // Deteksi tipe kolom
                                        const columnTypes = detectColumnTypes(filteredData);
                                        
                                        // Update progress
                                        $('#importProgressBar').css('width', '100%');
                                        $('#importPercentage').text('100%');
                                        $('#importStatusText').text('Selesai!');
                                        
                                        // Sembunyikan modal setelah selesai
                                        setTimeout(function() {
                                            $('#importModalContent').removeClass('modal-enter').addClass('modal-exit');
                                            setTimeout(function() {
                                                $('#importFileModal').addClass('hidden');
                                                $('#importModalContent').removeClass('modal-exit');
                                                
                                                // Tampilkan notifikasi sukses
                                                showToast(`File ${file.name} berhasil diimpor! ${filteredData.length} baris data siap dianalisis.`, 'success');
                                                
                                                // Reset welcome message jika visible
                                                $('#welcomeMessage').addClass('hidden');
                                                
                                                // Tambahkan notifikasi sukses di area hasil
                                                const successNotification = `
                                                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm" role="alert">
                                                        <div class="flex">
                                                            <div class="flex-shrink-0">
                                                                <i class="fas fa-check-circle text-green-500"></i>
                                                            </div>
                                                            <div class="ml-3">
                                                                <p class="text-sm font-medium">
                                                                    File <span class="font-bold">${file.name}</span> berhasil diimpor!
                                                                </p>
                                                                <p class="text-sm">
                                                                    ${filteredData.length} baris data siap untuk dianalisis. Klik "Analisis Data" untuk melihat hasilnya.
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                `;
                                                $('#loadingIndicator').addClass('hidden');
                                                $('#chartArea, #tableArea').empty();
                                                $('#results').html(successNotification);
                                                
                                                // Tambahkan tombol preview
                                                addPreviewButton(filteredData);
                                                
                                                // Perbarui tampilan untuk menunjukkan kita menggunakan data impor
                                                updateUIForImportedData(file.name, filteredData.length);
                                            }, 300);
                                        }, 500);
                                    } else {
                                        showToast('Tidak ada data yang ditemukan dalam file Excel.', 'error');
                                    }
                                }, 500);
                            } catch (excelError) {
                                console.error("Excel parsing error:", excelError);
                                showToast("Error saat membaca file Excel: " + excelError.message, 'error');
                                
                                $('#importProgressBar').addClass('bg-red-600');
                                $('#importStatusText').text('Error parsing Excel!');
                            }
                        }
                    } catch (error) {
                        console.error('Error parsing file:', error);
                        showToast('Error saat memproses file: ' + error.message, 'error');
                        
                        $('#importProgressBar').addClass('bg-red-600');
                        $('#importStatusText').text('Error!');
                    }
                };
                
                reader.onerror = function(ex) {
                    console.error(ex);
                    showToast("Error saat membaca file: " + ex.message, 'error');
                };
                
                if (file.name.endsWith('.csv')) {
                    reader.readAsText(file);
                } else {
                    reader.readAsArrayBuffer(file);
                }
            }// Fungsi untuk menambahkan tombol preview
            function addPreviewButton(data) {
                const previewBtn = `
                    <button id="previewDataBtn" class="mt-4 px-4 py-2 bg-primary-100 text-primary-700 text-sm rounded-md hover:bg-primary-200 flex items-center">
                        <i class="fas fa-eye mr-2"></i>
                        Lihat Preview Data
                    </button>
                `;
                $('#results').append(previewBtn);
                
                // Handler untuk tombol preview
                $(document).off('click', '#previewDataBtn').on('click', '#previewDataBtn', function() {
                    const previewData = data.slice(0, 5); // Ambil 5 baris pertama
                    
                    // Hapus preview yang sudah ada
                    $('.preview-data').remove();
                    
                    let previewHTML = `
                        <div class="preview-data mt-4 overflow-x-auto">
                            <h3 class="text-md font-medium mb-2 flex items-center">
                                <i class="fas fa-table text-primary-500 mr-2"></i>
                                Preview Data (5 baris pertama):
                            </h3>
                            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            ${Object.keys(previewData[0] || {}).map(header => 
                                                `<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">${header}</th>`
                                            ).join('')}
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        ${previewData.map(row => 
                                            `<tr class="hover:bg-gray-50 transition-colors">
                                                ${Object.keys(previewData[0] || {}).map(header => 
                                                    `<td class="px-4 py-2 text-sm text-gray-900 whitespace-nowrap">${row[header] !== null && row[header] !== undefined ? row[header] : '-'}</td>`
                                                ).join('')}
                                            </tr>`
                                        ).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;
                    
                    $('#results').append(previewHTML);
                });
            }
            
            // Fungsi untuk memproses data impor dengan filter
            function processImportedDataWithFilters(data, filters) {// Debug
                console.log("Element tableArea exists:", $('#tableArea').length > 0);
                console.log("Raw imported data (sample):", JSON.stringify(data.slice(0, 2)));
                console.log("Data type:", typeof data);
                console.log("Is array:", Array.isArray(data));
                console.log("Data length:", data.length);
                console.log("First item:", data[0]);
                
                // Pastikan data valid
                if (!Array.isArray(data) || data.length === 0 || !data[0]) {
                    $('#loadingIndicator').addClass('hidden');
                    showToast('Data tidak valid atau format tidak sesuai yang diharapkan.', 'error');
                    $('#results').html(`
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm" role="alert">
                            <p class="font-bold">Data tidak valid</p>
                            <p>Format data yang diimpor tidak sesuai yang diharapkan.</p>
                        </div>
                    `);
                    return;
                }
                
                // Tampilkan loading
                $('#loadingIndicator').removeClass('hidden');
                
                // Hapus welcome message jika masih visible
                $('#welcomeMessage').addClass('hidden');
                
                // Hapus hasil lama
                $('#chartArea').empty();
                $('#tableArea').empty();
                $('#results').find('.bg-green-100').remove(); // Hapus notifikasi sukses jika ada
                $('#results').find('.bg-blue-100').remove(); // Hapus notifikasi hasil sebelumnya
                $('#results').find('.preview-data').remove(); // Hapus preview data jika ada
                $('#results').find('#previewDataBtn').remove(); // Hapus tombol preview jika ada
                
                // Simulasi delay untuk loading effect
                setTimeout(function() {
                    try {
                        // Terapkan filter pada data
                        let filteredData = [...data];
                        
                        // Terapkan filter
                        if (Object.keys(filters).length > 0) {
                            filteredData = filteredData.filter(item => {
                                let passFilter = true;
                                
                                for (const [field, value] of Object.entries(filters)) {
                                    if (item[field] !== undefined) {
                                        const itemValue = String(item[field]).toLowerCase();
                                        const filterValue = String(value).toLowerCase();
                                        
                                        if (itemValue !== filterValue && itemValue.indexOf(filterValue) === -1) {
                                            passFilter = false;
                                            break;
                                        }
                                    }
                                }
                                
                                return passFilter;
                            });
                        }
                        
                        // Format data untuk tabel
                        const headers = Object.keys(data[0] || {});
                        console.log("Headers detected:", headers);
                        
                        // Periksa headers
                        if (headers.length === 0) {
                            $('#loadingIndicator').addClass('hidden');
                            showToast('Header tidak ditemukan dalam data.', 'error');
                            $('#results').html(`
                                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm" role="alert">
                                    <p class="font-bold">Header tidak ditemukan</p>
                                    <p>Data yang diimpor tidak memiliki header yang valid.</p>
                                </div>
                            `);
                            return;
                        }
                        
                        // Buat rows dari data terfilter dengan pengecekan lebih baik
                        const rows = filteredData.map((item, rowIndex) => {
                            return headers.map(header => {
                                const value = item[header];
                                // Handle berbagai tipe data
                                if (value === null || value === undefined) return '-';
                                if (typeof value === 'object') return JSON.stringify(value);
                                return String(value);
                            });
                        });
                        console.log(`Generated ${rows.length} rows from filtered data`);
                        
                        const tableData = {
                            type: 'table',
                            headers: headers,
                            rows: rows
                        };
                        
                        // Render hasil
                        $('#loadingIndicator').addClass('hidden');
                        renderTable(tableData);
                        
                        // Tampilkan juga dalam bentuk grafik jika cocok untuk visualisasi
                        if (rows.length > 0 && rows.length <= 20) {
                            // Coba deteksi kolom yang cocok untuk visualisasi
                            const numericColumns = detectNumericColumns(filteredData, headers);
                            if (numericColumns.length > 0) {
                                // Jika ada kolom numerik, tambahkan opsi visualisasi
                                addVisualizationOptions(filteredData, headers, numericColumns);
                            }
                        }
                        
                        // Tampilkan jumlah baris dan hasil filter
                        const filterInfo = Object.keys(filters).length > 0 
                            ? `dengan ${Object.keys(filters).length} filter aktif` 
                            : 'tanpa filter';
                        
                        showToast(`Analisis selesai. Menampilkan ${rows.length} baris data ${filterInfo}.`, 'success');
                        
                        // Simpan data untuk export
                        window.analysisData = tableData;
                        
                        // Tampilkan tombol export
                        $('#exportExcelBtn').removeClass('hidden');
                        
                        // Scroll ke hasil
                        document.getElementById('results').scrollIntoView({ behavior: 'smooth' });
                        
                    } catch (error) {
                        $('#loadingIndicator').addClass('hidden');
                        console.error('Error processing imported data:', error);
                        showToast('Terjadi kesalahan saat memproses data: ' + error.message, 'error');
                    }
                }, 500);
            }
            
            // Fungsi untuk mendeteksi kolom numerik
            function detectNumericColumns(data, headers) {
                const numericColumns = [];
                
                for (const header of headers) {
                    // Ambil beberapa sampel nilai (hingga 20 baris)
                    const samples = data.slice(0, 20).map(row => row[header]);
                    const validSamples = samples.filter(val => val !== null && val !== undefined && val !== '');
                    
                    // Cek apakah kolom ini numerik (minimal 80% nilai numerik)
                    if (validSamples.length > 0) {
                        const numericCount = validSamples.filter(val => !isNaN(Number(val))).length;
                        const numericRatio = numericCount / validSamples.length;
                        
                        if (numericRatio >= 0.8) {
                            numericColumns.push(header);
                        }
                    }
                }
                
                return numericColumns;
            }
            
            // Fungsi untuk menambahkan opsi visualisasi
            function addVisualizationOptions(data, headers, numericColumns) {
                if (numericColumns.length === 0) return;
                
                // Pilih kolom non-numerik untuk kategori (biasanya kolom pertama jika ada)
                const categoryColumns = headers.filter(h => !numericColumns.includes(h));
                if (categoryColumns.length === 0) return;
                
                const categoryColumn = categoryColumns[0];
                const valueColumn = numericColumns[0];
                
                // Buat data untuk visualisasi
                const categories = {};
                
                // Kumpulkan nilai unik untuk kategori
                data.forEach(row => {
                    const category = row[categoryColumn];
                    if (category !== null && category !== undefined && category !== '') {
                        if (!categories[category]) {
                            categories[category] = 0;
                        }
                        
                        const value = parseFloat(row[valueColumn]);
                        if (!isNaN(value)) {
                            categories[category] += value;
                        }
                    }
                });
                
                // Jika ada cukup kategori, buat visualisasi
                const categoryEntries = Object.entries(categories);
                if (categoryEntries.length > 1 && categoryEntries.length <= 15) {
                    const labels = categoryEntries.map(([cat]) => cat);
                    const values = categoryEntries.map(([, val]) => val);
                    
                    // Pilih tipe chart berdasarkan jumlah kategori
                    const chartType = categoryEntries.length <= 8 ? 'pie' : 'bar';
                    
                    const chartData = {
                        type: chartType,
                        labels: labels,
                        datasets: [{
                            label: `Total ${valueColumn} berdasarkan ${categoryColumn}`,
                            data: values
                        }]
                    };
                    
                    // Tambahkan opsi visualisasi
                    const visualizationOptions = `
                        <div class="mt-6 bg-white rounded-xl shadow-card p-4 border border-gray-100">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="fas fa-chart-pie text-primary-500 mr-2"></i>
                                Visualisasi Data
                            </h3>
                            <div class="flex flex-wrap gap-2 mb-3">
                                <button class="viz-option px-3 py-1.5 bg-primary-100 text-primary-700 rounded-md hover:bg-primary-200 text-sm active" data-type="${chartType}">
                                    <i class="fas fa-chart-${chartType === 'pie' ? 'pie' : 'bar'} mr-1"></i>
                                    ${chartType === 'pie' ? 'Pie Chart' : 'Bar Chart'}
                                </button>
                                <button class="viz-option px-3 py-1.5 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm" data-type="${chartType === 'pie' ? 'bar' : 'pie'}">
                                    <i class="fas fa-chart-${chartType === 'pie' ? 'bar' : 'pie'} mr-1"></i>
                                    ${chartType === 'pie' ? 'Bar Chart' : 'Pie Chart'}
                                </button>
                                <button class="viz-option px-3 py-1.5 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm" data-type="line">
                                    <i class="fas fa-chart-line mr-1"></i>
                                    Line Chart
                                </button>
                            </div>
                            <div id="autoVisualization" class="h-80"></div>
                        </div>
                    `;
                    
                    $('#results').append(visualizationOptions);
                    
                    // Render chart yang dipilih
                    renderAutoVisualization(chartData);
                    
                    // Handle click untuk opsi visualisasi
                    $('.viz-option').click(function() {
                        $('.viz-option').removeClass('active bg-primary-100 text-primary-700').addClass('bg-gray-100 text-gray-700');
                        $(this).removeClass('bg-gray-100 text-gray-700').addClass('active bg-primary-100 text-primary-700');
                        
                        const type = $(this).data('type');
                        chartData.type = type;
                        renderAutoVisualization(chartData);
                    });
                }
            }
            
            // Render visualisasi otomatis
            function renderAutoVisualization(data) {
                $('#autoVisualization').empty();
                
                const options = {
                    chart: {
                        type: data.type,
                        height: 320,
                        fontFamily: 'Inter, sans-serif',
                        toolbar: {
                            show: true,
                            tools: {
                                download: true,
                                selection: true,
                                zoom: true,
                                zoomin: true,
                                zoomout: true,
                                pan: true,
                                reset: true
                            }
                        }
                    },
                    colors: ['#6366f1', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444'],
                    series: data.type === 'pie' ? data.datasets[0].data : [{
                        name: data.datasets[0].label,
                        data: data.datasets[0].data
                    }],
                    labels: data.type === 'pie' ? data.labels : undefined,
                    xaxis: data.type !== 'pie' ? {
                        categories: data.labels,
                        labels: {
                            style: {
                                colors: '#64748b',
                                fontFamily: 'Inter, sans-serif'
                            }
                        }
                    } : undefined,
                    title: {
                        text: data.datasets[0].label,
                        align: 'center',
                        style: {
                            fontSize: '16px',
                            fontWeight: 600,
                            fontFamily: 'Inter, sans-serif',
                            color: '#334155'
                        }
                    },
                    plotOptions: data.type === 'bar' ? {
                        bar: {
                            horizontal: false,
                            columnWidth: '55%',
                            endingShape: 'rounded',
                            borderRadius: 4
                        }
                    } : undefined,
                    dataLabels: {
                        enabled: data.type === 'pie'
                    },
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: 300
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }]
                };
                
                const chart = new ApexCharts(document.querySelector("#autoVisualization"), options);
                chart.render();
            }
            
            // Fungsi untuk memperbarui UI setelah import
            function updateUIForImportedData(fileName, rowCount) {
                // Perbarui tombol analisis untuk menunjukkan kita menggunakan data impor
                $('#analyzeBtn').html(`
                    <span class="flex items-center">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Analisis Data Impor
                    </span>
                `);
                $('#analyzeBtn').removeClass('bg-primary-600 hover:bg-primary-700').addClass('bg-purple-600 hover:bg-purple-700');
                
                // Perbarui badge info
                $('#importInfoBadge').removeClass('hidden');
                $('#importInfoText').text(`File: ${fileName} (${rowCount} baris)`);
                
                // Tampilkan tombol reset jika belum ada
                if ($('#resetImportBtn').hasClass('hidden')) {
                    $('#resetImportBtn').removeClass('hidden');
                    
                    // Event handler untuk tombol reset
                    $('#resetImportBtn').click(function() {
                        resetImportData();
                    });
                }
            }
            
            // Fungsi untuk reset data impor
            function resetImportData() {
                // Reset state
                window.importedData = null;
                window.usingImportedData = false;
                
                // Reset UI
                $('#analyzeBtn').html(`
                    <span class="flex items-center">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Analisis Data
                    </span>
                `);
                $('#analyzeBtn').removeClass('bg-purple-600 hover:bg-purple-700').addClass('bg-primary-600 hover:bg-primary-700');
                $('#importInfoBadge').addClass('hidden');
                $('#resetImportBtn').addClass('hidden');
                
                // Kosongkan hasil
                $('#chartArea, #tableArea').empty();
                $('#exportExcelBtn').addClass('hidden');
                
                // Hapus notifikasi
                $('#results').find('.bg-green-100').remove();
                $('#results').find('.bg-blue-100').remove();
                $('#results').find('.preview-data').remove();
                $('#results').find('#previewDataBtn').remove();
                $('#results').find('#autoVisualization').parent().remove();
                
                // Tampilkan pesan dan welcome message kembali
                $('#welcomeMessage').removeClass('hidden');
                
                showToast('Data impor telah direset. Analisis akan menggunakan data database.', 'info');
            }
            
            // Handler untuk tombol di modal notifikasi
            $('#okNotificationBtn, #closeNotificationBtn').click(function() {
                // Sembunyikan modal
                $('#welcomeNotificationModal').addClass('hidden');
                
                // Jika checkbox dicentang, simpan preferensi untuk tidak menampilkan lagi
                if ($('#dontShowAgain').is(':checked')) {
                    localStorage.setItem('dontShowWelcomeNotification', 'true');
                }
            });
            
            // Periksa apakah notifikasi sudah pernah ditampilkan dan disembunyikan
            const dontShowNotification = localStorage.getItem('dontShowWelcomeNotification');
            
            // Jika pengguna belum memilih untuk tidak menampilkan lagi, tampilkan modal
            if (dontShowNotification !== 'true') {
                // Tampilkan modal setelah halaman dimuat dengan sedikit delay
                setTimeout(function() {
                    $('#welcomeNotificationModal').removeClass('hidden');
                }, 1000); // Modal akan muncul setelah 1 detik
            }
            
            // Handler untuk tombol help
            $('#helpBtn').click(function() {
                const helpModalHTML = `
                    <div id="helpModal" class="fixed inset-0 bg-gray-900 bg-opacity-70 z-50 flex items-center justify-center overflow-y-auto backdrop-blur-sm">
                        <div class="relative mx-auto p-0 md:w-[600px] w-[95%] shadow-2xl rounded-xl bg-white transform transition-all duration-300 modal-enter">
                            <div class="bg-gradient-to-r from-primary-600 to-primary-700 rounded-t-xl p-5 relative overflow-hidden">
                                <div class="absolute -right-8 -top-8 w-32 h-32 bg-primary-500 bg-opacity-50 rounded-full"></div>
                                <div class="absolute -right-4 -bottom-8 w-24 h-24 bg-primary-500 bg-opacity-50 rounded-full"></div>
                                
                                <div class="flex justify-between items-start relative z-10">
                                    <div>
                                        <h3 class="text-xl font-bold text-white">Bantuan Penggunaan</h3>
                                        <p class="text-primary-100 text-sm mt-1">PKM Kaben - Satu Data</p>
                                    </div>
                                    <button id="closeHelpBtn" class="text-white hover:text-primary-200 focus:outline-none">
                                        <i class="fas fa-times text-xl"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="p-6 max-h-[70vh] overflow-y-auto">
                                <div class="space-y-6">
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-800 flex items-center">
                                            <i class="fas fa-file-import text-primary-500 mr-2"></i>
                                            Import Data
                                        </h4>
                                        <p class="mt-2 text-gray-600">
                                            Anda dapat mengimpor data dari file Excel (.xlsx, .xls) atau CSV. Klik tombol "Import Data" di pojok kanan atas,
                                            lalu pilih file atau drag-and-drop file ke area yang ditentukan.
                                        </p>
                                    </div>
                                    
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-800 flex items-center">
                                            <i class="fas fa-filter text-primary-500 mr-2"></i>
                                            Filter Data
                                        </h4>
                                        <p class="mt-2 text-gray-600">
                                            Gunakan panel filter untuk menyaring data berdasarkan berbagai kriteria. Setelah memilih filter, 
                                            klik "Analisis Data" untuk melihat hasil.
                                        </p>
                                    </div>
                                    
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-800 flex items-center">
                                            <i class="fas fa-chart-bar text-primary-500 mr-2"></i>
                                            Visualisasi
                                        </h4>
                                        <p class="mt-2 text-gray-600">
                                            Hasil analisis dapat ditampilkan dalam berbagai bentuk visualisasi seperti tabel, grafik batang, 
                                            grafik lingkaran, dan grafik garis. Pada visualisasi grafik, Anda dapat beralih antar tipe 
                                            dengan tombol di bagian atas grafik.
                                        </p>
                                    </div>
                                    
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-800 flex items-center">
                                            <i class="fas fa-file-excel text-primary-500 mr-2"></i>
                                            Export Excel
                                        </h4>
                                        <p class="mt-2 text-gray-600">
                                            Setelah analisis, Anda dapat mengekspor hasil ke file Excel dengan mengklik tombol "Export Excel". 
                                            File Excel akan menyimpan data hasil analisis beserta informasi filter yang digunakan.
                                        </p>
                                    </div>
                                    
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-800 flex items-center">
                                            <i class="fas fa-keyboard text-primary-500 mr-2"></i>
                                            Shortcuts
                                        </h4>
                                        <div class="mt-2 grid grid-cols-2 gap-2 text-sm">
                                            <div class="bg-gray-50 p-2 rounded">
                                                <span class="font-medium">Ctrl + F</span> - Cari dalam tabel
                                            </div>
                                            <div class="bg-gray-50 p-2 rounded">
                                                <span class="font-medium">Ctrl + P</span> - Print hasil
                                            </div>
                                            <div class="bg-gray-50 p-2 rounded">
                                                <span class="font-medium">F5</span> - Refresh halaman
                                            </div>
                                            <div class="bg-gray-50 p-2 rounded">
                                                <span class="font-medium">Esc</span> - Tutup modal
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-b-xl text-center">
                                <button id="closeHelpOkBtn" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-md shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 flex items-center mx-auto">
                                    <i class="fas fa-check mr-2"></i>
                                    Mengerti
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                
                // Tambahkan modal ke body
                $('body').append(helpModalHTML);
                
                // Handle close button
                $('#closeHelpBtn, #closeHelpOkBtn').click(function() {
                    $('#helpModal').removeClass('modal-enter').addClass('modal-exit');
                    setTimeout(function() {
                        $('#helpModal').remove();
                    }, 300);
                });
                
                // Close dengan ESC
                $(document).on('keydown', function(e) {
                    if (e.key === 'Escape' && $('#helpModal').length) {
                        $('#closeHelpBtn').click();
                    }
                });
            });
        });
    </script>
</body>
</html>