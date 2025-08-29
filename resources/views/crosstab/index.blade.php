<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PKM Kaben - Analisis Crosstab</title>
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
    
    <!-- CSS untuk Print dan lainnya -->
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #crosstabResults, #crosstabResults * {
                visibility: visible;
            }
            #exportCrosstabBtn, #generateCrosstabBtn, .print-hide {
                display: none !important;
            }
            #crosstabResults {
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
            /* Atur overflow untuk data yang lebar */
            .cross-table-container {
                overflow: visible !important;
                width: 100% !important;
            }
            /* Atur warna latar yang dicetak */
            .cell-highlight {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
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
        
        /* Cell highlight coloring for crosstab */
        .cell-highlight-0 { background-color: #ffffff; }
        .cell-highlight-1 { background-color: #f0f9ff; }
        .cell-highlight-2 { background-color: #e0f2fe; }
        .cell-highlight-3 { background-color: #bae6fd; }
        .cell-highlight-4 { background-color: #7dd3fc; }
        .cell-highlight-5 { background-color: #38bdf8; color: white; }
        .cell-highlight-6 { background-color: #0ea5e9; color: white; }
        .cell-highlight-7 { background-color: #0284c7; color: white; }
        .cell-highlight-8 { background-color: #0369a1; color: white; }
        .cell-highlight-9 { background-color: #075985; color: white; }
        .cell-highlight-10 { background-color: #0c4a6e; color: white; }
        
        /* Percentage Display */
        .percentage-display {
            font-size: 0.75rem;
            color: #64748b;
            display: block;
        }
        
        /* Sticky Header and First Column */
        .sticky-header {
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .sticky-first-col {
            position: sticky;
            left: 0;
            z-index: 9;
        }
        
        /* Chart container */
        .chart-container {
            min-height: 400px;
        }
        
        /* Modal animations */
        .modal-enter {
            opacity: 1 !important;
            transform: translateY(0) !important;
        }
        
        .modal-exit {
            opacity: 0 !important;
            transform: translateY(4px) !important;
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
                    <div class="ml-6 flex space-x-8">
                        <a href="/dashboard" class="text-gray-500 hover:text-primary-600 px-3 py-2 text-sm font-medium border-b-2 border-transparent hover:border-primary-600 transition-all">
                            <i class="fas fa-home mr-1"></i>
                            Dashboard
                        </a>
                        <a href="/crosstab" class="text-primary-600 px-3 py-2 text-sm font-medium border-b-2 border-primary-600">
                            <i class="fas fa-table-cells mr-1"></i>
                            Analisis Crosstab
                        </a>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <span id="importInfoBadge" class="hidden items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
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
        <!-- Intro Section -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-2 flex items-center">
                <i class="fas fa-table-cells text-primary-500 mr-3"></i>
                Analisis Tabulasi Silang (Crosstab)
            </h1>
            <p class="text-gray-600 max-w-3xl">
                Tabulasi silang memungkinkan Anda untuk menganalisis hubungan antara dua atau lebih variabel. 
                Cukup pilih variabel baris, kolom, dan nilai yang ingin diukur untuk melihat distribusi datanya.
            </p>
        </div>

        <!-- Settings Card -->
        <div class="bg-white rounded-xl shadow-card p-6 mb-6 fade-in transition-all hover:shadow-card-hover">
            <div class="flex items-center mb-4">
                <i class="fas fa-sliders text-primary-500 mr-2 text-xl"></i>
                <h2 class="text-lg font-semibold text-gray-800">Konfigurasi Tabulasi Silang</h2>
            </div>
            
            <!-- Variable Selection -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Row Variable -->
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-100 shadow-sm">
                    <h3 class="text-md font-medium text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-arrows-up-down text-primary-400 mr-2"></i>
                        Variabel Baris
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Pilih Variabel Baris
                            </label>
                            <select id="rowVariable" class="form-select w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 text-sm">
                                <option value="">Pilih variabel...</option>
                            </select>
                        </div>
                        <div id="rowValueFilters" class="hidden space-y-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Filter Nilai (opsional)
                            </label>
                            <div class="flex items-center space-x-2">
                                <button id="selectAllRowValues" class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 py-1 px-2 rounded">
                                    Pilih Semua
                                </button>
                                <button id="deselectAllRowValues" class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 py-1 px-2 rounded">
                                    Hapus Semua
                                </button>
                            </div>
                            <div id="rowValueCheckboxes" class="max-h-32 overflow-y-auto p-2 bg-white rounded border border-gray-200">
                                <!-- Checkboxes will be generated here -->
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Column Variable -->
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-100 shadow-sm">
                    <h3 class="text-md font-medium text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-arrows-left-right text-primary-400 mr-2"></i>
                        Variabel Kolom
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Pilih Variabel Kolom
                            </label>
                            <select id="columnVariable" class="form-select w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 text-sm">
                                <option value="">Pilih variabel...</option>
                            </select>
                        </div>
                        <div id="columnValueFilters" class="hidden space-y-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Filter Nilai (opsional)
                            </label>
                            <div class="flex items-center space-x-2">
                                <button id="selectAllColumnValues" class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 py-1 px-2 rounded">
                                    Pilih Semua
                                </button>
                                <button id="deselectAllColumnValues" class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 py-1 px-2 rounded">
                                    Hapus Semua
                                </button>
                            </div>
                            <div id="columnValueCheckboxes" class="max-h-32 overflow-y-auto p-2 bg-white rounded border border-gray-200">
                                <!-- Checkboxes will be generated here -->
                            </div>
                        </div>
                    </div>
                </div><!-- Measure Settings -->
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-100 shadow-sm">
                    <h3 class="text-md font-medium text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-calculator text-primary-400 mr-2"></i>
                        Pengukuran
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Fungsi Pengukuran
                            </label>
                            <select id="aggregationFunction" class="form-select w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 text-sm">
                                <option value="count">Count (Jumlah)</option>
                                <option value="sum">Sum (Total)</option>
                                <option value="avg">Average (Rata-rata)</option>
                                <option value="min">Minimum</option>
                                <option value="max">Maximum</option>
                            </select>
                        </div>
                        <div id="valueFieldContainer">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Variabel Nilai
                            </label>
                            <select id="valueVariable" class="form-select w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 text-sm">
                                <option value="">Pilih variabel nilai...</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">
                                Diperlukan untuk Sum, Average, Min, dan Max
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Display Options -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Display Settings -->
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-100 shadow-sm">
                    <h3 class="text-md font-medium text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-eye text-primary-400 mr-2"></i>
                        Opsi Tampilan
                    </h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Tampilkan Persentase
                            </label>
                            <div class="flex items-center space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="percentageType" value="none" checked class="text-primary-600 focus:ring-primary-500">
                                    <span class="ml-2 text-sm text-gray-700">Tidak ada</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="percentageType" value="row" class="text-primary-600 focus:ring-primary-500">
                                    <span class="ml-2 text-sm text-gray-700">Baris</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="percentageType" value="column" class="text-primary-600 focus:ring-primary-500">
                                    <span class="ml-2 text-sm text-gray-700">Kolom</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="percentageType" value="total" class="text-primary-600 focus:ring-primary-500">
                                    <span class="ml-2 text-sm text-gray-700">Total</span>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Highlight Cell
                            </label>
                            <div class="flex items-center space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="highlightType" value="none" class="text-primary-600 focus:ring-primary-500">
                                    <span class="ml-2 text-sm text-gray-700">Tidak ada</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="highlightType" value="row" checked class="text-primary-600 focus:ring-primary-500">
                                    <span class="ml-2 text-sm text-gray-700">Per Baris</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="highlightType" value="column" class="text-primary-600 focus:ring-primary-500">
                                    <span class="ml-2 text-sm text-gray-700">Per Kolom</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="highlightType" value="all" class="text-primary-600 focus:ring-primary-500">
                                    <span class="ml-2 text-sm text-gray-700">Global</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Filters -->
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-100 shadow-sm">
                    <h3 class="text-md font-medium text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-filter text-primary-400 mr-2"></i>
                        Filter Tambahan
                    </h3>
                    <div id="additionalFilters" class="space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-gray-700">
                                Tambah Filter
                            </label>
                            <button id="addFilterBtn" class="text-xs bg-primary-100 hover:bg-primary-200 text-primary-700 py-1 px-2 rounded flex items-center">
                                <i class="fas fa-plus mr-1"></i>
                                Tambah
                            </button>
                        </div>
                        <div id="filtersContainer">
                            <!-- Additional filters will be added here -->
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-3">
                <button id="generateCrosstabBtn" class="bg-gradient-to-r from-primary-600 to-primary-700 text-white px-5 py-2 rounded-md hover:from-primary-700 hover:to-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 shadow-md btn-hover flex items-center">
                    <i class="fas fa-table-cells mr-2"></i>
                    Buat Tabulasi Silang
                </button>
                <button id="resetBtn" class="bg-white text-gray-700 border border-gray-300 px-4 py-2 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 shadow-sm flex items-center">
                    <i class="fas fa-undo mr-2"></i>
                    Reset
                </button>
                <div class="tooltip ml-auto">
                    <button id="helpBtn" class="text-gray-400 hover:text-primary-600 focus:outline-none">
                        <i class="fas fa-question-circle text-xl"></i>
                    </button>
                    <span class="tooltip-text">Tabulasi silang (crosstab) digunakan untuk melihat hubungan antara dua variabel kategori. Pilih variabel baris dan kolom, lalu pilih fungsi pengukuran yang sesuai.</span>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div id="crosstabResults" class="bg-white rounded-xl shadow-card p-6 transition-all fade-in">
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
                    <i class="fas fa-table-cells text-primary-600 text-2xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Analisis Tabulasi Silang</h2>
                <p class="text-gray-600 max-w-md mx-auto mb-6">
                    Import data atau gunakan data yang tersedia, pilih variabel baris dan kolom 
                    pada panel konfigurasi di atas, lalu klik "Buat Tabulasi Silang".
                </p>
                <div class="space-y-4 max-w-md mx-auto">
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-100 text-left">
                        <h3 class="font-medium text-blue-800 flex items-center mb-1">
                            <i class="fas fa-lightbulb mr-2 text-blue-500"></i>
                            Contoh Penggunaan
                        </h3>
                        <p class="text-sm text-blue-700">
                            Anda dapat melihat hubungan antara desa dan ketersediaan jamban, atau usia dan status JKN, 
                            lalu lihat distribusi dan persentasenya.
                        </p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg border border-green-100 text-left">
                        <h3 class="font-medium text-green-800 flex items-center mb-1">
                            <i class="fas fa-check-circle mr-2 text-green-500"></i>
                            Tip
                        </h3>
                        <p class="text-sm text-green-700">
                            Untuk analisis kesehatan, variabel baris bisa berupa desa, usia, atau jenis kelamin, sementara variabel kolom
                            bisa berupa status kesehatan, kepemilikan JKN, dan sebagainya.
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Crosstab Area (empty until analysis is run) -->
            <div id="crosstabTable" class="min-h-[5px]"></div>
            
            <!-- Chart Area -->
            <div id="chartArea" class="mt-6 hidden">
                <div class="border-t pt-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-chart-bar text-primary-500 mr-2"></i>
                        Visualisasi
                    </h3>
                    <div class="flex gap-2 mb-3">
                        <button class="viz-option px-3 py-1.5 bg-primary-100 text-primary-700 rounded-md hover:bg-primary-200 text-sm active" data-type="bar">
                            <i class="fas fa-chart-bar mr-1"></i>
                            Bar Chart
                        </button>
                        <button class="viz-option px-3 py-1.5 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm" data-type="heatmap">
                            <i class="fas fa-th mr-1"></i>
                            Heatmap
                        </button>
                        <button class="viz-option px-3 py-1.5 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm" data-type="stacked">
                            <i class="fas fa-layer-group mr-1"></i>
                            Stacked Bar
                        </button>
                        <button class="viz-option px-3 py-1.5 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm" data-type="grouped">
                            <i class="fas fa-bars mr-1"></i>
                            Grouped Bar
                        </button>
                    </div>
                    <div id="crosstabChart" class="chart-container border border-gray-200 rounded-lg overflow-hidden bg-gray-50"></div>
                </div>
            </div>
            
            <!-- Additional Statistics -->
            <div id="statisticsArea" class="mt-6 hidden">
                <div class="border-t pt-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-square-root-variable text-primary-500 mr-2"></i>
                        Statistik Lanjutan
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Chi-Square Test -->
                        <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm">
                            <h4 class="font-medium text-gray-800 mb-2 text-sm">Chi-Square Test</h4>
                            <div id="chiSquareResult" class="text-sm"></div>
                        </div>
                        
                        <!-- Correlation -->
                        <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm">
                            <h4 class="font-medium text-gray-800 mb-2 text-sm">Korelasi</h4>
                            <div id="correlationResult" class="text-sm"></div>
                        </div>
                        
                        <!-- Summary -->
                        <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm">
                            <h4 class="font-medium text-gray-800 mb-2 text-sm">Ringkasan</h4>
                            <div id="summaryResult" class="text-sm"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

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
                        Pilih file Excel (.xlsx, .xls) atau CSV untuk diimpor. Data akan digunakan untuk analisis crosstab.
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

    <!-- JavaScript for Crosstab Analysis -->
<script>
    $(document).ready(function() {
        // Variables for storing data
        let dataSource = null;
        let crosstabData = null;
        let usingImportedData = false;
        
        // Variable groups
        const variableGroups = {
            demographic: {
                title: "Demografi",
                variables: {
                    gender: {
                        label: "Jenis Kelamin",
                        type: "select",
                        options: {
                            "Laki-laki": "Laki-laki",
                            "Perempuan": "Perempuan"
                        }
                    },
                    age_group: {
                        label: "Kelompok Usia",
                        type: "select",
                        options: {
                            "0-5": "0-5 tahun",
                            "6-11": "6-11 tahun",
                            "12-18": "12-18 tahun",
                            "19-35": "19-35 tahun",
                            "36-50": "36-50 tahun",
                            "51-65": "51-65 tahun",
                            "65+": "65+ tahun"
                        }
                    },
                    education: {
                        label: "Pendidikan",
                        type: "select",
                        options: {
                            "Tidak Sekolah": "Tidak Sekolah",
                            "SD": "SD/Sederajat",
                            "SMP": "SMP/Sederajat",
                            "SMA": "SMA/Sederajat",
                            "D3": "Diploma",
                            "S1": "Sarjana",
                            "S2": "Magister",
                            "S3": "Doktor"
                        }
                    },
                    occupation: {
                        label: "Pekerjaan",
                        type: "select",
                        options: {
                            "Tidak Bekerja": "Tidak Bekerja",
                            "Petani": "Petani",
                            "Nelayan": "Nelayan",
                            "Buruh": "Buruh",
                            "Wiraswasta": "Wiraswasta",
                            "PNS": "PNS",
                            "TNI/Polri": "TNI/Polri",
                            "Pensiunan": "Pensiunan",
                            "Lainnya": "Lainnya"
                        }
                    },
                    marital_status: {
                        label: "Status Perkawinan",
                        type: "select",
                        options: {
                            "Belum Kawin": "Belum Kawin",
                            "Kawin": "Kawin",
                            "Cerai Hidup": "Cerai Hidup",
                            "Cerai Mati": "Cerai Mati"
                        }
                    }
                }
            },
            location: {
                title: "Lokasi",
                variables: {
                    village: {
                        label: "Desa/Kelurahan",
                        type: "select",
                        options: {
                            "Desa A": "Desa A",
                            "Desa B": "Desa B",
                            "Desa C": "Desa C",
                            "Desa D": "Desa D",
                            "Desa E": "Desa E"
                        }
                    },
                    district: {
                        label: "Kecamatan",
                        type: "select",
                        options: {
                            "Kecamatan X": "Kecamatan X",
                            "Kecamatan Y": "Kecamatan Y",
                            "Kecamatan Z": "Kecamatan Z"
                        }
                    }
                }
            },
            health: {
                title: "Kesehatan",
                variables: {
                    has_jkn: {
                        label: "Kepemilikan JKN",
                        type: "boolean",
                        options: {
                            "1": "Ya",
                            "0": "Tidak"
                        }
                    },
                    has_clean_water: {
                        label: "Memiliki Air Bersih",
                        type: "boolean",
                        options: {
                            "1": "Ya",
                            "0": "Tidak"
                        }
                    },
                    is_water_protected: {
                        label: "Air Terlindungi",
                        type: "boolean",
                        options: {
                            "1": "Ya",
                            "0": "Tidak"
                        }
                    },
                    has_toilet: {
                        label: "Memiliki Jamban",
                        type: "boolean",
                        options: {
                            "1": "Ya",
                            "0": "Tidak"
                        }
                    },
                    is_toilet_sanitary: {
                        label: "Jamban Sehat",
                        type: "boolean",
                        options: {
                            "1": "Ya",
                            "0": "Tidak"
                        }
                    },
                    is_smoker: {
                        label: "Merokok",
                        type: "boolean",
                        options: {
                            "1": "Ya",
                            "0": "Tidak"
                        }
                    },
                    has_mental_illness: {
                        label: "Gangguan Jiwa",
                        type: "boolean",
                        options: {
                            "1": "Ya",
                            "0": "Tidak"
                        }
                    },
                    has_restrained_member: {
                        label: "Dipasung",
                        type: "boolean",
                        options: {
                            "1": "Ya",
                            "0": "Tidak"
                        }
                    },
                    health_status: {
                        label: "Status Kesehatan",
                        type: "select",
                        options: {
                            "Sehat": "Sehat",
                            "Pra-Sehat": "Pra-Sehat",
                            "Tidak Sehat": "Tidak Sehat"
                        }
                    }
                }
            }
        };
        
        // Function to populate variable selectors
        function populateVariableSelectors() {
            const rowSelect = $('#rowVariable');
            const colSelect = $('#columnVariable');
            const valueSelect = $('#valueVariable');
            
            // Clear previous options
            rowSelect.empty();
            colSelect.empty();
            valueSelect.empty();
            
            // Add default options
            rowSelect.append('<option value="">Pilih variabel...</option>');
            colSelect.append('<option value="">Pilih variabel...</option>');
            valueSelect.append('<option value="">Pilih variabel nilai...</option>');
            
            // Add options from variable groups
            for (const groupKey in variableGroups) {
                const group = variableGroups[groupKey];
                
                const rowOptgroup = $('<optgroup>').attr('label', group.title);
                const colOptgroup = $('<optgroup>').attr('label', group.title);
                const valOptgroup = $('<optgroup>').attr('label', group.title);
                
                let hasValueOptions = false;
                
                for (const varKey in group.variables) {
                    const variable = group.variables[varKey];
                    
                    // Add to row and column selectors
                    rowOptgroup.append(`<option value="${varKey}">${variable.label}</option>`);
                    colOptgroup.append(`<option value="${varKey}">${variable.label}</option>`);
                    
                    // Add numeric variables to value selector
                    if (variable.type === 'number' || varKey === 'age' || variable.type === 'range') {
                        valOptgroup.append(`<option value="${varKey}">${variable.label}</option>`);
                        hasValueOptions = true;
                    }
                }
                
                rowSelect.append(rowOptgroup);
                colSelect.append(colOptgroup);
                
                if (hasValueOptions) {
                    valueSelect.append(valOptgroup);
                }
            }
            
            // If we have imported data, use that for options
            if (dataSource && usingImportedData) {
                populateFromImportedData();
            }
        }
        
        // Function to populate selectors from imported data
        function populateFromImportedData() {
            if (!dataSource || dataSource.length === 0) return;
            
            const rowSelect = $('#rowVariable');
            const colSelect = $('#columnVariable');
            const valueSelect = $('#valueVariable');
            
            // Clear previous options
            rowSelect.empty();
            colSelect.empty();
            valueSelect.empty();
            
            // Add default options
            rowSelect.append('<option value="">Pilih variabel...</option>');
            colSelect.append('<option value="">Pilih variabel...</option>');
            valueSelect.append('<option value="">Pilih variabel nilai...</option>');
            
            // Get column types
            const columnTypes = detectColumnTypes(dataSource);
            
            // Get column headers
            const headers = Object.keys(dataSource[0] || {});
            
            // Add options for each header
            headers.forEach(header => {
                rowSelect.append(`<option value="${header}">${header}</option>`);
                colSelect.append(`<option value="${header}">${header}</option>`);
                
                // Add numeric columns to value selector
                if (columnTypes[header] === 'number') {
                    valueSelect.append(`<option value="${header}">${header}</option>`);
                }
            });
        }
        
        // Function to detect column types
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
            
            return columnTypes;
        }
        
        // Initialize variable selectors
        populateVariableSelectors();
        
        // Handle aggregation function change
        $('#aggregationFunction').change(function() {
            const selectedFunction = $(this).val();
            
            // Show/hide value field based on selection
            if (selectedFunction === 'count') {
                $('#valueFieldContainer').addClass('opacity-50');
                $('#valueVariable').prop('required', false);
            } else {
                $('#valueFieldContainer').removeClass('opacity-50');
                $('#valueVariable').prop('required', true);
            }
        });
        // Handle row and column variable selection
        $('#rowVariable, #columnVariable').change(function() {
            const selectId = $(this).attr('id');
            const selectedVar = $(this).val();
            
            if (!selectedVar) {
                // Hide filter
                $(`#${selectId === 'rowVariable' ? 'rowValueFilters' : 'columnValueFilters'}`).addClass('hidden');
                return;
            }
            
            // Get unique values for the selected variable
            populateValueFilters(selectId, selectedVar);
        });
        
        // Function to populate value filters
        function populateValueFilters(selectId, selectedVar) {
            const isRow = selectId === 'rowVariable';
            const filterContainer = isRow ? '#rowValueFilters' : '#columnValueFilters';
            const checkboxContainer = isRow ? '#rowValueCheckboxes' : '#columnValueCheckboxes';
            
            // Show filter container
            $(filterContainer).removeClass('hidden');
            
            // Clear previous checkboxes
            $(checkboxContainer).empty();
            
            // Get unique values
            let uniqueValues = [];
            
            if (usingImportedData && dataSource) {
                // From imported data
                uniqueValues = [...new Set(dataSource.map(item => item[selectedVar]))].filter(Boolean);
            } else {
                // From predefined variables
                for (const groupKey in variableGroups) {
                    const group = variableGroups[groupKey];
                    if (selectedVar in group.variables) {
                        const variable = group.variables[selectedVar];
                        if (variable.type === 'select' || variable.type === 'boolean') {
                            uniqueValues = Object.keys(variable.options);
                        }
                        break;
                    }
                }
            }
            
            // Sort values
            uniqueValues.sort();
            
            // Create checkboxes for each value
            uniqueValues.forEach(value => {
                const checkboxId = `${isRow ? 'row' : 'col'}-value-${value.toString().replace(/\s+/g, '-').toLowerCase()}`;
                const displayValue = getDisplayValue(selectedVar, value);
                
                const checkbox = `
                    <div class="flex items-center mb-1 last:mb-0">
                        <input type="checkbox" id="${checkboxId}" name="${isRow ? 'rowValues' : 'colValues'}" 
                               value="${value}" class="text-primary-600 focus:ring-primary-500" checked>
                        <label for="${checkboxId}" class="ml-2 text-sm text-gray-700">
                            ${displayValue}
                        </label>
                    </div>
                `;
                
                $(checkboxContainer).append(checkbox);
            });
        }
        
        // Function to get display value for a variable
        function getDisplayValue(variable, value) {
            for (const groupKey in variableGroups) {
                const group = variableGroups[groupKey];
                if (variable in group.variables) {
                    const varDef = group.variables[variable];
                    if (varDef.type === 'select' || varDef.type === 'boolean') {
                        return varDef.options[value] || value;
                    }
                    break;
                }
            }
            return value;
        }
        
        // Handle select/deselect all buttons
        $('#selectAllRowValues').click(function() {
            $('#rowValueCheckboxes input[type="checkbox"]').prop('checked', true);
        });
        
        $('#deselectAllRowValues').click(function() {
            $('#rowValueCheckboxes input[type="checkbox"]').prop('checked', false);
        });
        
        $('#selectAllColumnValues').click(function() {
            $('#columnValueCheckboxes input[type="checkbox"]').prop('checked', true);
        });
        
        $('#deselectAllColumnValues').click(function() {
            $('#columnValueCheckboxes input[type="checkbox"]').prop('checked', false);
        });
        
        // Add filter button handler
        $('#addFilterBtn').click(function() {
            addFilterRow();
        });
        
        // Function to add additional filter row
        function addFilterRow() {
            const filterCount = $('#filtersContainer .filter-row').length + 1;
            
            const filterRow = `
                <div class="filter-row flex flex-wrap items-end gap-2 pb-2 mb-2 border-b border-gray-200">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">
                            Variabel
                        </label>
                        <select class="filter-variable form-select rounded-md border-gray-300 shadow-sm text-sm" style="min-width: 150px;">
                            <option value="">Pilih variabel...</option>
                            ${getFilterVariableOptions()}
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">
                            Operator
                        </label>
                        <select class="filter-operator form-select rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="eq">Sama dengan</option>
                            <option value="neq">Tidak sama dengan</option>
                            <option value="contains">Mengandung</option>
                            <option value="gt">Lebih besar dari</option>
                            <option value="lt">Lebih kecil dari</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">
                            Nilai
                        </label>
                        <input type="text" class="filter-value form-input rounded-md border-gray-300 shadow-sm text-sm" placeholder="Nilai">
                    </div>
                    <div>
                        <button class="remove-filter bg-red-100 text-red-700 p-2 rounded-md hover:bg-red-200">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            `;
            
            $('#filtersContainer').append(filterRow);
            
            // Attach event to remove filter button
            $('.remove-filter').off('click').on('click', function() {
                $(this).closest('.filter-row').remove();
            });
            
            // Handle filter variable change
            $('.filter-variable').off('change').on('change', function() {
                const selectedVar = $(this).val();
                const operatorSelect = $(this).closest('.filter-row').find('.filter-operator');
                const valueInput = $(this).closest('.filter-row').find('.filter-value');
                
                // Reset value input
                valueInput.val('');
                
                // Update operator options based on variable type
                updateFilterOperators(selectedVar, operatorSelect);
            });
        }
        
        // Function to get filter variable options as HTML
        function getFilterVariableOptions() {
            let optionsHtml = '';
            
            if (usingImportedData && dataSource) {
                // From imported data
                const headers = Object.keys(dataSource[0] || {});
                headers.forEach(header => {
                    optionsHtml += `<option value="${header}">${header}</option>`;
                });
            } else {
                // From predefined variables
                for (const groupKey in variableGroups) {
                    const group = variableGroups[groupKey];
                    optionsHtml += `<optgroup label="${group.title}">`;
                    
                    for (const varKey in group.variables) {
                        const variable = group.variables[varKey];
                        optionsHtml += `<option value="${varKey}">${variable.label}</option>`;
                    }
                    
                    optionsHtml += `</optgroup>`;
                }
            }
            
            return optionsHtml;
        }
        
        // Function to update filter operators based on variable type
        function updateFilterOperators(selectedVar, operatorSelect) {
            // Reset operators
            operatorSelect.empty();
            
            let varType = 'string';
            
            if (usingImportedData && dataSource) {
                // From imported data
                const columnTypes = detectColumnTypes(dataSource);
                varType = columnTypes[selectedVar] || 'string';
            } else {
                // From predefined variables
                for (const groupKey in variableGroups) {
                    const group = variableGroups[groupKey];
                    if (selectedVar in group.variables) {
                        const variable = group.variables[selectedVar];
                        
                        if (variable.type === 'number' || variable.type === 'range') {
                            varType = 'number';
                        }} else if (variable.type === 'boolean') {
                            varType = 'boolean';
                        }
                        break;
                    }
                }
            }
            
            // Add appropriate operators based on type
            if (varType === 'number') {
                operatorSelect.append(`
                    <option value="eq">Sama dengan</option>
                    <option value="neq">Tidak sama dengan</option>
                    <option value="gt">Lebih besar dari</option>
                    <option value="lt">Lebih kecil dari</option>
                    <option value="gte">Lebih besar atau sama dengan</option>
                    <option value="lte">Lebih kecil atau sama dengan</option>
                `);
            } else if (varType === 'boolean') {
                operatorSelect.append(`
                    <option value="eq">Sama dengan</option>
                    <option value="neq">Tidak sama dengan</option>
                `);
            } else {
                operatorSelect.append(`
                    <option value="eq">Sama dengan</option>
                    <option value="neq">Tidak sama dengan</option>
                    <option value="contains">Mengandung</option>
                    <option value="starts">Dimulai dengan</option>
                    <option value="ends">Diakhiri dengan</option>
                `);
            }
        }
        
        // Reset button handler
        $('#resetBtn').click(function() {
            // Reset all form fields
            $('#rowVariable, #columnVariable, #valueVariable').val('');
            $('#aggregationFunction').val('count');
            $('input[name="percentageType"]').filter('[value="none"]').prop('checked', true);
            $('input[name="highlightType"]').filter('[value="row"]').prop('checked', true);
            
            // Hide value filters
            $('#rowValueFilters, #columnValueFilters').addClass('hidden');
            
            // Clear filter rows
            $('#filtersContainer').empty();
            
            // Clear results
            $('#crosstabTable').empty();
            $('#chartArea').addClass('hidden');
            $('#statisticsArea').addClass('hidden');
            $('#welcomeMessage').removeClass('hidden');
            
            // Show toast
            showToast('Formulir telah direset', 'info');
        });
        
        // Generate Crosstab button handler
        $('#generateCrosstabBtn').click(function() {
            const rowVariable = $('#rowVariable').val();
            const columnVariable = $('#columnVariable').val();
            const valueVariable = $('#valueVariable').val();
            const aggregationFunction = $('#aggregationFunction').val();
            const percentageType = $('input[name="percentageType"]:checked').val();
            const highlightType = $('input[name="highlightType"]:checked').val();
            
            // Validate inputs
            if (!rowVariable || !columnVariable) {
                showToast('Pilih variabel baris dan kolom terlebih dahulu', 'warning');
                return;
            }
            
            if (aggregationFunction !== 'count' && !valueVariable) {
                showToast('Pilih variabel nilai untuk fungsi ' + aggregationFunction, 'warning');
                return;
            }
            
            // Get selected row and column values (if filtered)
            const selectedRowValues = [];
            const selectedColumnValues = [];
            
            $('#rowValueCheckboxes input:checked').each(function() {
                selectedRowValues.push($(this).val());
            });
            
            $('#columnValueCheckboxes input:checked').each(function() {
                selectedColumnValues.push($(this).val());
            });
            
            // Get additional filters
            const additionalFilters = [];
            $('.filter-row').each(function() {
                const variable = $(this).find('.filter-variable').val();
                const operator = $(this).find('.filter-operator').val();
                const value = $(this).find('.filter-value').val();
                
                if (variable && value) {
                    additionalFilters.push({
                        variable: variable,
                        operator: operator,
                        value: value
                    });
                }
            });
            
            // Hide welcome message and show loading
            $('#welcomeMessage').addClass('hidden');
            $('#loadingIndicator').removeClass('hidden');
            $('#crosstabTable').empty();
            $('#chartArea').addClass('hidden');
            $('#statisticsArea').addClass('hidden');
            
            // Generate crosstab
            setTimeout(function() {
                try {
                    // Check if we have imported data or need to generate sample data
                    let dataToProcess = null;
                    
                    if (usingImportedData && dataSource) {
                        dataToProcess = dataSource;
                    } else {
                        // Generate sample data
                        dataToProcess = generateSampleData();
                    }
                    
                    // Generate crosstab
                    crosstabData = generateCrosstab(
                        dataToProcess,
                        rowVariable,
                        columnVariable,
                        valueVariable,
                        aggregationFunction,
                        selectedRowValues,
                        selectedColumnValues,
                        additionalFilters
                    );
                    
                    // Render crosstab
                    renderCrosstabTable(crosstabData, percentageType, highlightType);
                    
                    // Show chart area and render visualization
                    $('#chartArea').removeClass('hidden');
                    renderCrosstabVisualization(crosstabData, 'bar');
                    
                    // Show statistics area and calculate statistics
                    $('#statisticsArea').removeClass('hidden');
                    calculateStatistics(crosstabData);
                    
                    // Hide loading
                    $('#loadingIndicator').addClass('hidden');
                    
                    // Show success toast
                    showToast('Tabulasi silang berhasil dibuat', 'success');
                    
                } catch (error) {
                    console.error('Error generating crosstab:', error);
                    $('#loadingIndicator').addClass('hidden');
                    showToast('Error saat membuat tabulasi silang: ' + error.message, 'error');
                }
            }, 800);
        });
        
        // Function to generate sample data if no imported data available
        function generateSampleData() {
            const sampleData = [];
            const rowVar = $('#rowVariable').val();
            const colVar = $('#columnVariable').val();
            
            // Get possible values for both variables
            let rowValues = [];
            let colValues = [];
            
            for (const groupKey in variableGroups) {
                const group = variableGroups[groupKey];
                
                if (rowVar in group.variables) {
                    const variable = group.variables[rowVar];
                    if (variable.type === 'select' || variable.type === 'boolean') {
                        rowValues = Object.keys(variable.options);
                    }
                }
                
                if (colVar in group.variables) {
                    const variable = group.variables[colVar];
                    if (variable.type === 'select' || variable.type === 'boolean') {
                        colValues = Object.keys(variable.options);
                    }
                }
            }
            
            // Generate 200-500 random records
            const recordCount = Math.floor(Math.random() * 300) + 200;
            
            for (let i = 0; i < recordCount; i++) {
                const record = {};
                
                // Set values for all variables
                for (const groupKey in variableGroups) {
                    const group = variableGroups[groupKey];
                    
                    for (const varKey in group.variables) {
                        const variable = group.variables[varKey];
                        
                        if (variable.type === 'select') {
                            const options = Object.keys(variable.options);
                            record[varKey] = options[Math.floor(Math.random() * options.length)];
                        } else if (variable.type === 'boolean') {
                            record[varKey] = Math.random() > 0.5 ? '1' : '0';
                        } else if (variable.type === 'number' || variable.type === 'range') {
                            record[varKey] = Math.floor(Math.random() * 100);
                        }
                    }
                }
                
                // Add age as a numeric field
                record['age'] = Math.floor(Math.random() * 80) + 1;
                
                sampleData.push(record);
            }
            
            return sampleData;
        }
        // Function to generate crosstab data
        function generateCrosstab(data, rowField, columnField, valueField, aggregationFunction, selectedRowValues, selectedColumnValues, additionalFilters) {
            // Filter data based on additional filters
            let filteredData = [...data];
            
            if (additionalFilters && additionalFilters.length > 0) {
                filteredData = filteredData.filter(item => {
                    return additionalFilters.every(filter => {
                        const itemValue = item[filter.variable];
                        const filterValue = filter.value;
                        
                        switch (filter.operator) {
                            case 'eq':
                                return String(itemValue) === String(filterValue);
                            case 'neq':
                                return String(itemValue) !== String(filterValue);
                            case 'contains':
                                return String(itemValue).includes(filterValue);
                            case 'starts':
                                return String(itemValue).startsWith(filterValue);
                            case 'ends':
                                return String(itemValue).endsWith(filterValue);
                            case 'gt':
                                return Number(itemValue) > Number(filterValue);
                            case 'lt':
                                return Number(itemValue) < Number(filterValue);
                            case 'gte':
                                return Number(itemValue) >= Number(filterValue);
                            case 'lte':
                                return Number(itemValue) <= Number(filterValue);
                            default:
                                return true;
                        }
                    });
                });
            }
            
            // Get all unique values for row and column
            let uniqueRows = [...new Set(filteredData.map(item => item[rowField]))].filter(Boolean);
            let uniqueColumns = [...new Set(filteredData.map(item => item[columnField]))].filter(Boolean);
            
            // Filter by selected values if provided
            if (selectedRowValues && selectedRowValues.length > 0) {
                uniqueRows = uniqueRows.filter(value => selectedRowValues.includes(value));
            }
            
            if (selectedColumnValues && selectedColumnValues.length > 0) {
                uniqueColumns = uniqueColumns.filter(value => selectedColumnValues.includes(value));
            }
            
            // Sort values
            uniqueRows.sort();
            uniqueColumns.sort();
            
            // Initialize matrix for results
            const resultMatrix = {};
            const rowTotals = {};
            const columnTotals = {};
            let grandTotal = 0;
            
            // Initialize with zeros/defaults
            uniqueRows.forEach(row => {
                resultMatrix[row] = {};
                rowTotals[row] = 0;
                
                uniqueColumns.forEach(col => {
                    resultMatrix[row][col] = 0;
                });
            });
            
            uniqueColumns.forEach(col => {
                columnTotals[col] = 0;
            });
            
            // Helper function to get value based on aggregation function
            function getAggregationValue(records, field) {
                if (!records || records.length === 0) return 0;
                
                switch (aggregationFunction) {
                    case 'count':
                        return records.length;
                    case 'sum':
                        return records.reduce((sum, record) => {
                            const val = parseFloat(record[field]);
                            return sum + (isNaN(val) ? 0 : val);
                        }, 0);
                    case 'avg':
                        const sum = records.reduce((sum, record) => {
                            const val = parseFloat(record[field]);
                            return sum + (isNaN(val) ? 0 : val);
                        }, 0);
                        return records.length > 0 ? sum / records.length : 0;
                    case 'min':
                        return Math.min(...records.map(record => {
                            const val = parseFloat(record[field]);
                            return isNaN(val) ? Infinity : val;
                        }));
                    case 'max':
                        return Math.max(...records.map(record => {
                            const val = parseFloat(record[field]);
                            return isNaN(val) ? -Infinity : val;
                        }));
                    default:
                        return records.length;
                }
            }
            
            // Group data by row and column fields
            const groupedData = {};
            
            uniqueRows.forEach(row => {
                groupedData[row] = {};
                
                uniqueColumns.forEach(col => {
                    groupedData[row][col] = filteredData.filter(item => 
                        item[rowField] === row && item[columnField] === col
                    );
                });
            });
            
            // Calculate values
            uniqueRows.forEach(row => {
                uniqueColumns.forEach(col => {
                    const cellRecords = groupedData[row][col];
                    const value = getAggregationValue(cellRecords, valueField);
                    
                    resultMatrix[row][col] = value;
                    rowTotals[row] += value;
                    columnTotals[col] += value;
                    grandTotal += value;
                });
            });
            
            return {
                rows: uniqueRows,
                columns: uniqueColumns,
                data: resultMatrix,
                rowTotals: rowTotals,
                columnTotals: columnTotals,
                grandTotal: grandTotal,
                rowField: rowField,
                columnField: columnField,
                valueField: valueField,
                aggregationType: aggregationFunction,
                rowLabels: uniqueRows.map(val => getDisplayValue(rowField, val)),
                columnLabels: uniqueColumns.map(val => getDisplayValue(columnField, val))
            };
        }
        
        // Function to render crosstab table
        function renderCrosstabTable(crosstabData, percentageType, highlightType) {
            const { rows, columns, data, rowTotals, columnTotals, grandTotal, 
                  aggregationType, rowField, columnField, valueField, rowLabels, columnLabels } = crosstabData;
            
            // Format value function
            function formatValue(value) {
                if (typeof value !== 'number') return value;
                
                if (aggregationType === 'avg') {
                    return value.toFixed(2);
                } else if (aggregationType === 'count' || aggregationType === 'sum') {
                    return value.toLocaleString();
                }
                return value.toLocaleString();
            }
            
            // Get percentage function
            function getPercentage(value, total) {
                if (typeof value !== 'number' || typeof total !== 'number' || total === 0) return '';
                return ((value / total) * 100).toFixed(1) + '%';
            }
            
            // Get class for cell highlighting
            function getCellHighlightClass(value, row, col) {
                if (highlightType === 'none' || typeof value !== 'number') return '';
                
                let maxValue = 1;
                let intensity = 0;
                
                if (highlightType === 'row') {
                    // Compare with max in row
                    maxValue = Math.max(...columns.map(c => data[row][c]));
                    intensity = maxValue > 0 ? Math.round((value / maxValue) * 10) : 0;
                } else if (highlightType === 'column') {
                    // Compare with max in column
                    maxValue = Math.max(...rows.map(r => data[r][col]));
                    intensity = maxValue > 0 ? Math.round((value / maxValue) * 10) : 0;
                } else if (highlightType === 'all') {
                    // Compare with global max
                    maxValue = Math.max(...rows.flatMap(r => columns.map(c => data[r][c])));
                    intensity = maxValue > 0 ? Math.round((value / maxValue) * 10) : 0;
                }
                
                return `cell-highlight-${intensity}`;
            }
            
            // Get row and column labels from original variable if possible
            const rowHeader = getVariableLabel(rowField);
            const colHeader = getVariableLabel(columnField);
            
            // Build table HTML
            let tableHtml = `
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-table-cells text-primary-500 mr-2"></i>
                        Hasil Tabulasi Silang
                    </h3>
                    <p class="text-sm text-gray-600 mb-3">
                        Menampilkan tabulasi silang antara <span class="font-semibold">${rowHeader}</span> 
                        dan <span class="font-semibold">${colHeader}</span> 
                        dengan perhitungan <span class="font-semibold">${getAggregationLabel(aggregationType, valueField)}</span>.
                    </p>
                </div>
                <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm cross-table-container">
                    <table class="min-w-full divide-y divide-gray-200 border-collapse">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border sticky-header sticky-first-col bg-gray-50 z-20">
                                    ${rowHeader} / ${colHeader}
                                </th>
            `;
            
            // Add column headers
            columns.forEach((col, index) => {
                tableHtml += `
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border sticky-header bg-gray-50 z-10">
                        ${columnLabels[index] || col}
                    </th>
                `;
            });
            // Add total header
            tableHtml += `
                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border sticky-header bg-gray-50 z-10">
                    Total
                </th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            `;
            
            // Add data rows
            rows.forEach((row, rowIndex) => {
                tableHtml += `
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900 border sticky-first-col bg-gray-50 z-10">
                            ${rowLabels[rowIndex] || row}
                        </td>
                `;
                
                // Add cells for each column
                columns.forEach((col, colIndex) => {
                    const value = data[row][col];
                    const cellClass = getCellHighlightClass(value, row, col);
                    let percentageText = '';
                    
                    if (percentageType === 'row') {
                        percentageText = getPercentage(value, rowTotals[row]);
                    } else if (percentageType === 'column') {
                        percentageText = getPercentage(value, columnTotals[col]);
                    } else if (percentageType === 'total') {
                        percentageText = getPercentage(value, grandTotal);
                    }
                    
                    tableHtml += `
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 text-center border ${cellClass}">
                            ${formatValue(value)}
                            ${percentageText ? `<span class="percentage-display">${percentageText}</span>` : ''}
                        </td>
                    `;
                });
                
                // Add row total
                tableHtml += `
                    <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900 bg-gray-100 text-center border">
                        ${formatValue(rowTotals[row])}
                    </td>
                </tr>
                `;
            });
            
            // Add totals row
            tableHtml += `
                <tr class="bg-gray-100 font-medium">
                    <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900 border sticky-first-col bg-gray-100 z-10">
                        Total
                    </td>
            `;
            
            // Add column totals
            columns.forEach(col => {
                tableHtml += `
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 text-center border">
                        ${formatValue(columnTotals[col])}
                    </td>
                `;
            });
            
            // Add grand total
            tableHtml += `
                    <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900 text-center border">
                        ${formatValue(grandTotal)}
                    </td>
                </tr>
            </tbody>
            </table>
            </div>
            `;
            
            // Add export button
            tableHtml += `
                <div class="flex justify-end mt-4">
                    <button id="printCrosstabBtn" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-1.5 rounded-md text-sm shadow-sm mr-2">
                        <i class="fas fa-print mr-1"></i>
                        Cetak
                    </button>
                    <button id="exportCrosstabBtn" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-md text-sm shadow-sm">
                        <i class="fas fa-file-excel mr-1"></i>
                        Export Excel
                    </button>
                </div>
            `;
            
            // Display the table
            $('#crosstabTable').html(tableHtml);
            
            // Add event handler for export and print
            $('#exportCrosstabBtn').click(function() {
                exportCrosstabToExcel(crosstabData, percentageType);
            });
            
            $('#printCrosstabBtn').click(function() {
                window.print();
            });
        }
        
        // Function to get variable label from its key
        function getVariableLabel(varKey) {
            for (const groupKey in variableGroups) {
                const group = variableGroups[groupKey];
                if (varKey in group.variables) {
                    return group.variables[varKey].label;
                }
            }
            return varKey;
        }
        
        // Function to get aggregation label
        function getAggregationLabel(aggType, valueField) {
            let valueLabel = valueField;
            
            // Try to get proper label for value field
            for (const groupKey in variableGroups) {
                const group = variableGroups[groupKey];
                if (valueField in group.variables) {
                    valueLabel = group.variables[valueField].label;
                    break;
                }
            }
            
            switch (aggType) {
                case 'count':
                    return 'Jumlah (Count)';
                case 'sum':
                    return `Jumlah Total (Sum) dari ${valueLabel}`;
                case 'avg':
                    return `Rata-rata (Average) dari ${valueLabel}`;
                case 'min':
                    return `Nilai Minimum dari ${valueLabel}`;
                case 'max':
                    return `Nilai Maximum dari ${valueLabel}`;
                default:
                    return 'Jumlah (Count)';
            }
        }
        
        // Function to render visualization
        function renderCrosstabVisualization(crosstabData, chartType) {
            const { rows, columns, data, rowTotals, columnTotals, grandTotal, 
                  rowField, columnField, valueField, rowLabels, columnLabels } = crosstabData;
            
            // Clear previous chart
            $('#crosstabChart').empty();
            
            // Prepare chart options based on type
            let options;
            
            if (chartType === 'bar') {
                // Bar chart - series by columns
                const series = columns.map((col, colIndex) => ({
                    name: columnLabels[colIndex] || col,
                    data: rows.map(row => data[row][col])
                }));
                
                options = {
                    chart: {
                        type: 'bar',
                        height: 400,
                        fontFamily: 'Inter, sans-serif',
                        toolbar: {
                            show: true
                        }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '70%',
                            endingShape: 'rounded',
                            borderRadius: 4
                        },
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        show: true,
                        width: 2,
                        colors: ['transparent']
                    },
                    series: series,
                    xaxis: {
                        categories: rows.map((row, rowIndex) => rowLabels[rowIndex] || row),
                        labels: {
                            style: {
                                colors: '#64748b',
                                fontFamily: 'Inter, sans-serif'
                            }
                        }
                    },
                    yaxis: {
                        title: {
                            text: getAggregationLabel(crosstabData.aggregationType, valueField),
                            style: {
                                fontSize: '12px',
                                fontWeight: 500,
                                fontFamily: 'Inter, sans-serif',
                                color: '#64748b'
                            }
                        },
                        labels: {
                            style: {
                                colors: '#64748b',
                                fontFamily: 'Inter, sans-serif'
                            }
                        }
                    },
                    fill: {
                        opacity: 1
                    },
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return val.toLocaleString();
                            }
                        }
                    },
                    legend: {
                        position: 'top',
                        fontFamily: 'Inter, sans-serif',
                        fontSize: '13px',
                        markers: {
                            width: 12,
                            height: 12,
                            radius: 12
                        }
                    }
                };
            }else if (chartType === 'heatmap') {
                // Heatmap visualization
                const series = rows.map((row, rowIndex) => ({
                    name: rowLabels[rowIndex] || row,
                    data: columns.map((col, colIndex) => ({
                        x: columnLabels[colIndex] || col,
                        y: data[row][col]
                    }))
                }));
                
                options = {
                    chart: {
                        type: 'heatmap',
                        height: 400,
                        fontFamily: 'Inter, sans-serif',
                        toolbar: {
                            show: true
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        style: {
                            colors: ['#fff']
                        }
                    },
                    colors: ["#0369a1"],
                    series: series,
                    title: {
                        text: getAggregationLabel(crosstabData.aggregationType, valueField),
                        align: 'center',
                        style: {
                            fontSize: '14px',
                            fontWeight: 500,
                            fontFamily: 'Inter, sans-serif',
                            color: '#334155'
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return val.toLocaleString();
                            }
                        }
                    },
                    theme: {
                        mode: 'light',
                        palette: 'palette1'
                    }
                };
            } else if (chartType === 'stacked') {
                // Stacked Bar Chart
                const series = columns.map((col, colIndex) => ({
                    name: columnLabels[colIndex] || col,
                    data: rows.map(row => data[row][col])
                }));
                
                options = {
                    chart: {
                        type: 'bar',
                        height: 400,
                        stacked: true,
                        fontFamily: 'Inter, sans-serif',
                        toolbar: {
                            show: true
                        }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '70%',
                            endingShape: 'rounded',
                            borderRadius: 4},
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        show: true,
                        width: 2,
                        colors: ['transparent']
                    },
                    series: series,
                    xaxis: {
                        categories: rows.map((row, rowIndex) => rowLabels[rowIndex] || row),
                        labels: {
                            style: {
                                colors: '#64748b',
                                fontFamily: 'Inter, sans-serif'
                            }
                        }
                    },
                    yaxis: {
                        title: {
                            text: getAggregationLabel(crosstabData.aggregationType, valueField),
                            style: {
                                fontSize: '12px',
                                fontWeight: 500,
                                fontFamily: 'Inter, sans-serif',
                                color: '#64748b'
                            }
                        },
                        labels: {
                            style: {
                                colors: '#64748b',
                                fontFamily: 'Inter, sans-serif'
                            }
                        }
                    },
                    fill: {
                        opacity: 1
                    },
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return val.toLocaleString();
                            }
                        }
                    },
                    legend: {
                        position: 'top',
                        fontFamily: 'Inter, sans-serif',
                        fontSize: '13px',
                        markers: {
                            width: 12,
                            height: 12,
                            radius: 12
                        }
                    }
                };
            } else if (chartType === 'grouped') {
                // Grouped Bar Chart (horizontal)
                const series = columns.map((col, colIndex) => ({
                    name: columnLabels[colIndex] || col,
                    data: rows.map(row => data[row][col])
                }));
                
                options = {
                    chart: {
                        type: 'bar',
                        height: 400,
                        fontFamily: 'Inter, sans-serif',
                        toolbar: {
                            show: true
                        }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: true,
                            columnWidth: '70%',
                            endingShape: 'rounded',
                            borderRadius: 4
                        },
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        show: true,
                        width: 2,
                        colors: ['transparent']
                    },
                    series: series,
                    xaxis: {
                        title: {
                            text: getAggregationLabel(crosstabData.aggregationType, valueField),
                            style: {
                                fontSize: '12px',
                                fontWeight: 500,
                                fontFamily: 'Inter, sans-serif',
                                color: '#64748b'
                            }
                        },
                        labels: {
                            style: {
                                colors: '#64748b',
                                fontFamily: 'Inter, sans-serif'
                            }
                        }
                    },
                    yaxis: {
                        categories: rows.map((row, rowIndex) => rowLabels[rowIndex] || row),
                        labels: {
                            style: {
                                colors: '#64748b',
                                fontFamily: 'Inter, sans-serif'
                            }
                        }
                    },
                    fill: {
                        opacity: 1
                    },
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return val.toLocaleString();
                            }
                        }
                    },
                    legend: {
                        position: 'top',
                        fontFamily: 'Inter, sans-serif',
                        fontSize: '13px',
                        markers: {
                            width: 12,
                            height: 12,
                            radius: 12
                        }
                    }
                };
            }
            
            // Create and render chart
            const chart = new ApexCharts(document.querySelector("#crosstabChart"), options);
            chart.render();
            
            // Add event listeners for visualization type switches
            $('.viz-option').off('click').on('click', function() {
                const vizType = $(this).data('type');
                
                // Update active button
                $('.viz-option').removeClass('active bg-primary-100 text-primary-700').addClass('bg-gray-100 text-gray-700');
                $(this).removeClass('bg-gray-100 text-gray-700').addClass('active bg-primary-100 text-primary-700');
                
                // Re-render visualization
                renderCrosstabVisualization(crosstabData, vizType);
            });
        }
        
        // Function to calculate statistics
        function calculateStatistics(crosstabData) {
            const { rows, columns, data, rowTotals, columnTotals, grandTotal } = crosstabData;
            
            // Chi-square test (for categorical variables)
            let chiSquareValue = 0;
            let degreesOfFreedom = (rows.length - 1) * (columns.length - 1);
            
            // Calculate expected values and chi-square
            rows.forEach(row => {
                columns.forEach(col => {
                    const observed = data[row][col];
                    const expected = (rowTotals[row] * columnTotals[col]) / grandTotal;
                    
                    if (expected > 0) {
                        chiSquareValue += Math.pow(observed - expected, 2) / expected;
                    }
                });
            });
            
            // Format chi-square result
            let chiSquareResult = '';
            if (degreesOfFreedom > 0) {
                const pValue = calculateChiSquarePValue(chiSquareValue, degreesOfFreedom);
                const significance = pValue < 0.05 ? 'signifikan' : 'tidak signifikan';
                
                chiSquareResult = `
                    <p>Chi-Square: <span class="font-medium">${chiSquareValue.toFixed(4)}</span></p>
                    <p>Degrees of Freedom: <span class="font-medium">${degreesOfFreedom}</span></p>
                    <p>p-value: <span class="font-medium">${pValue.toFixed(4)}</span></p>
                    <p class="mt-1">Hubungan antar variabel <span class="font-medium ${pValue < 0.05 ? 'text-green-600' : 'text-gray-600'}">${significance}</span> (α = 0.05)</p>
                `;
            } else {
                chiSquareResult = '<p>Tidak cukup data untuk menghitung Chi-Square</p>';
            }
            
            // Display chi-square result
            $('#chiSquareResult').html(chiSquareResult);
            
            // Calculate correlation (if applicable)
            let correlationResult = '';
            if (rows.length >= 2 && columns.length >= 2) {
                // For simplicity, we'll calculate Cramer's V for categorical variables
                const cramersV = Math.sqrt(chiSquareValue / (grandTotal * Math.min(rows.length - 1, columns.length - 1)));
                
                correlationResult = `
                    <p>Cramer's V: <span class="font-medium">${cramersV.toFixed(4)}</span></p>
                    <p>Kekuatan hubungan: <span class="font-medium">${getCramersVInterpretation(cramersV)}</span></p>
                `;
            } else {
                correlationResult = '<p>Tidak cukup data untuk menghitung korelasi</p>';
            }
            
            // Display correlation result
            $('#correlationResult').html(correlationResult);
            
            // Calculate summary
            const summaryResult = `
                <p>Jumlah baris: <span class="font-medium">${rows.length}</span></p>
                <p>Jumlah kolom: <span class="font-medium">${columns.length}</span></p>
                <p>Total data: <span class="font-medium">${grandTotal.toLocaleString()}</span></p>
                <p>Nilai tertinggi: <span class="font-medium">${Math.max(...rows.flatMap(r => columns.map(c => data[r][c]))).toLocaleString()}</span></p>
            `;
            
            // Display summary
            $('#summaryResult').html(summaryResult);
        }
        // Function to calculate chi-square p-value (approximation)
        function calculateChiSquarePValue(chiSquare, df) {
            // Simple approximation for p-value calculation
            // For more accurate values, you'd need a more comprehensive statistical library
            if (df === 1) {
                return 1 - Math.exp(-0.5 * chiSquare);
            } else {
                const k = df / 2;
                return Math.exp(-0.5 * chiSquare) * Math.pow(chiSquare, k - 1) / (Math.pow(2, k - 1) * gamma(k));
            }
        }
        
        // Helper function for p-value calculation
        function gamma(n) {
            // Simple approximation of gamma function
            if (n === 1) return 1;
            if (n === 0.5) return Math.sqrt(Math.PI);
            return (n - 1) * gamma(n - 1);
        }
        
        // Function to interpret Cramer's V
        function getCramersVInterpretation(v) {
            if (v < 0.1) return "Sangat lemah";
            if (v < 0.2) return "Lemah";
            if (v < 0.3) return "Sedang";
            if (v < 0.4) return "Kuat";
            return "Sangat kuat";
        }
        
        // Function to export crosstab to Excel
        function exportCrosstabToExcel(crosstabData, percentageType) {
            const { rows, columns, data, rowTotals, columnTotals, grandTotal, 
                   rowField, columnField, valueField, aggregationType, rowLabels, columnLabels } = crosstabData;
            
            try {
                // Create workbook
                const wb = XLSX.utils.book_new();
                
                // Prepare data for the crosstab sheet
                const wsData = [];
                
                // Get row and column headers
                const rowHeader = getVariableLabel(rowField);
                const colHeader = getVariableLabel(columnField);
                
                // Add header row
                const headerRow = [`${rowHeader} / ${colHeader}`];
                columns.forEach((col, colIndex) => {
                    headerRow.push(columnLabels[colIndex] || col);
                });
                headerRow.push('Total');
                wsData.push(headerRow);
                
                // Add data rows
                rows.forEach((row, rowIndex) => {
                    const dataRow = [rowLabels[rowIndex] || row];
                    
                    // Add values for each column
                    columns.forEach(col => {
                        const value = data[row][col];
                        
                        if (percentageType === 'none') {
                            dataRow.push(value);
                        } else {
                            let percentage = '';
                            
                            if (percentageType === 'row') {
                                percentage = rowTotals[row] > 0 ? (value / rowTotals[row]) * 100 : 0;
                            } else if (percentageType === 'column') {
                                percentage = columnTotals[col] > 0 ? (value / columnTotals[col]) * 100 : 0;
                            } else if (percentageType === 'total') {
                                percentage = grandTotal > 0 ? (value / grandTotal) * 100 : 0;
                            }
                            
                            dataRow.push({
                                v: value,
                                t: 'n',
                                z: '#,##0',
                                c: [
                                    { v: value, t: 'n' },
                                    { v: percentage.toFixed(1) + '%', t: 's' }
                                ]
                            });
                        }
                    });
                    
                    // Add row total
                    dataRow.push(rowTotals[row]);
                    
                    wsData.push(dataRow);
                });
                
                // Add total row
                const totalRow = ['Total'];
                columns.forEach(col => {
                    totalRow.push(columnTotals[col]);
                });
                totalRow.push(grandTotal);
                wsData.push(totalRow);
                
                // Create worksheet
                const ws = XLSX.utils.aoa_to_sheet(wsData);
                
                // Style the header row
                const headerRange = XLSX.utils.decode_range(ws['!ref']);
                for (let col = headerRange.s.c; col <= headerRange.e.c; col++) {
                    const cellRef = XLSX.utils.encode_cell({ r: 0, c: col });
                    ws[cellRef].s = {
                        font: { bold: true, color: { rgb: "FFFFFF" } },
                        fill: { fgColor: { rgb: "4F46E5" } },
                        alignment: { horizontal: "center", vertical: "center" }
                    };
                }
                
                // Add to workbook
                XLSX.utils.book_append_sheet(wb, ws, "Tabulasi Silang");
                
                // Add info sheet
                const infoData = [
                    ['Informasi Tabulasi Silang', ''],
                    ['', ''],
                    ['Variabel Baris:', getVariableLabel(rowField)],
                    ['Variabel Kolom:', getVariableLabel(columnField)],
                    ['Pengukuran:', getAggregationLabel(aggregationType, valueField)],
                    ['Tanggal Export:', new Date().toLocaleString('id-ID')],
                    ['', ''],
                    ['Jumlah Baris:', rows.length],
                    ['Jumlah Kolom:', columns.length],
                    ['Total Data:', grandTotal],
                    ['', ''],
                    ['Dibuat dengan:', 'PKM Kaben - Analisis Crosstab']
                ];
                
                const infoWs = XLSX.utils.aoa_to_sheet(infoData);
                XLSX.utils.book_append_sheet(wb, infoWs, "Info");
                
                // Export file
                const today = new Date();
                const dateString = today.toISOString().slice(0, 10);
                const filename = `PKM_Kaben_Crosstab_${rowField}_${columnField}_${dateString}.xlsx`;
                
                XLSX.writeFile(wb, filename);
                
                showToast('File Excel berhasil diekspor!', 'success');
            } catch (error) {
                console.error('Error exporting to Excel:', error);
                showToast('Terjadi kesalahan saat mengekspor: ' + error.message, 'error');
            }
        }
        
        // Function to create help modal
        function createHelpModal() {
            // Create and show help modal
            const helpModalHTML = `
                <div id="helpModal" class="fixed inset-0 bg-gray-900 bg-opacity-70 z-50 flex items-center justify-center overflow-y-auto backdrop-blur-sm">
                    <div class="relative mx-auto p-0 md:w-[600px] w-[95%] shadow-2xl rounded-xl bg-white transform transition-all duration-300 modal-enter">
                        <div class="bg-gradient-to-r from-primary-600 to-primary-700 rounded-t-xl p-5 relative overflow-hidden">
                            <div class="absolute -right-8 -top-8 w-32 h-32 bg-primary-500 bg-opacity-50 rounded-full"></div>
                            <div class="absolute -right-4 -bottom-8 w-24 h-24 bg-primary-500 bg-opacity-50 rounded-full"></div>
                            
                            <div class="flex justify-between items-start relative z-10">
                                <div>
                                    <h3 class="text-xl font-bold text-white">Bantuan Analisis Crosstab</h3>
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
                                        <i class="fas fa-table-cells text-primary-500 mr-2"></i>
                                        Tentang Analisis Crosstab
                                    </h4>
                                    <p class="mt-2 text-gray-600">
                                        Analisis Tabulasi Silang (Crosstab) digunakan untuk menampilkan hubungan antara dua variabel kategori. 
                                        Ini menyajikan data dalam bentuk tabel dengan baris dan kolom yang menunjukkan distribusi frekuensi dari data.
                                    </p>
                                </div>
                                
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-800 flex items-center">
                                        <i class="fas fa-sliders text-primary-500 mr-2"></i>
                                        Cara Penggunaan
                                    </h4>
                                    <ol class="mt-2 text-gray-600 list-decimal list-inside space-y-1">
                                        <li>Pilih <strong>Variabel Baris</strong> dan <strong>Variabel Kolom</strong> yang ingin dianalisis.</li>
                                        <li>Pilih <strong>Fungsi Pengukuran</strong> (Count, Sum, Average, dll).</li>
                                        <li>Jika memilih Sum, Average, Min, atau Max, pilih juga <strong>Variabel Nilai</strong>.</li>
                                        <li>Opsional: Pilih <strong>Filter Nilai</strong> untuk membatasi nilai yang ditampilkan.</li>
                                        <li>Opsional: Tentukan <strong>Opsi Tampilan</strong> untuk persentase dan highlight cell.</li>
                                        <li>Klik tombol <strong>Buat Tabulasi Silang</strong> untuk melihat hasil.</li>
                                    </ol>
                                </div>
                                
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-800 flex items-center">
                                        <i class="fas fa-lightbulb text-primary-500 mr-2"></i>
                                        Tips Penggunaan
                                    </h4>
                                    <ul class="mt-2 text-gray-600 list-disc list-inside space-y-1">
                                        <li>Gunakan <strong>Count</strong> untuk melihat frekuensi atau jumlah data.</li>
                                        <li>Gunakan <strong>Sum</strong> atau <strong>Average</strong> untuk variabel numerik seperti usia atau pendapatan.</li>
                                        <li>Gunakan filter tambahan untuk mempersempit analisis (misalnya hanya melihat data dari desa tertentu).</li>
                                        <li>Persentase baris menunjukkan distribusi data dalam satu baris (total 100% per baris).</li>
                                        <li>Persentase kolom menunjukkan distribusi data dalam satu kolom (total 100% per kolom).</li>
                                        <li>Highlight cell membantu melihat pola data dengan lebih mudah melalui intensitas warna.</li>
                                    </ul>
                                </div>
                                
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-800 flex items-center">
                                        <i class="fas fa-chart-bar text-primary-500 mr-2"></i>
                                        Interpretasi Hasil
                                    </h4>
                                    <ul class="mt-2 text-gray-600 list-disc list-inside space-y-1">
                                        <li>Lihat pola distribusi data antar variabel.</li>
                                        <li>Perhatikan nilai yang menonjol (lebih tinggi atau lebih rendah dari yang diharapkan).</li>
                                        <li>Chi-Square yang signifikan (p < 0.05) menunjukkan ada hubungan antara kedua variabel.</li>
                                        <li>Cramer's V mengukur kekuatan hubungan (semakin mendekati 1, semakin kuat hubungannya).</li>
                                        <li>Gunakan visualisasi untuk melihat pola data secara lebih jelas.</li>
                                    </ul>
                                </div>
                                
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-800 flex items-center">
                                        <i class="fas fa-file-export text-primary-500 mr-2"></i>
                                        Ekspor Hasil
                                    </h4>
                                    <p class="mt-2 text-gray-600">
                                        Hasil analisis dapat diekspor ke Excel untuk analisis lebih lanjut atau pelaporan.
                                        Gunakan tombol <strong>Export Excel</strong> untuk menyimpan hasil dalam format spreadsheet.
                                        Gunakan tombol <strong>Cetak</strong> untuk mencetak hasil analisis.
                                    </p>
                                </div>
                            </div>
                            
                            <div class="mt-6 text-center">
                                <button id="dismissHelpBtn" class="bg-primary-600 hover:bg-primary-700 text-white px-5 py-2 rounded-md shadow-sm">
                                    Mengerti
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(helpModalHTML);
            
            // Add event handlers
            $('#closeHelpBtn, #dismissHelpBtn').click(function() {
                $('#helpModal').addClass('modal-exit');
                setTimeout(function() {
                    $('#helpModal').remove();
                }, 300);
            });
        }
        
        // Toast notification system
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
        // Handle file import UI
        // Event handler for import button
        $('#importFileBtn').click(function() {
            // Reset form
            $('#selectedFile').addClass('hidden');
            $('#importProgress').addClass('hidden');
            $('#confirmImportBtn').prop('disabled', true)
                .addClass('cursor-not-allowed bg-gray-400')
                .removeClass('bg-primary-600 hover:bg-primary-700');
            
            // Show modal
            $('#importFileModal').removeClass('hidden');
            setTimeout(function() {
                $('#importModalContent').removeClass('opacity-0 translate-y-4').addClass('modal-enter');
            }, 50);
        });
        
        // Initialize event for drop zone and file input
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
        }
        
        // Event handler untuk tombol remove file
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
            const i = Math.floor(Math.log(bytes) /const i = Math.floor(Math.log(bytes) / Math.log(k));
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
                                        dataSource = filteredData;
                                        usingImportedData = true;
                                        
                                        // Update progress
                                        $('#importProgressBar').css('width', '100%');
                                        $('#importPercentage').text('100%');
                                        $('#importStatusText').text('Selesai!');
                                        
                                        // Perbarui selectors
                                        populateFromImportedData();
                                        
                                        // Sembunyikan modal setelah selesai
                                        setTimeout(function() {
                                            $('#importModalContent').removeClass('modal-enter').addClass('modal-exit');
                                            setTimeout(function() {
                                                $('#importFileModal').addClass('hidden');
                                                $('#importModalContent').removeClass('modal-exit');
                                                
                                                // Tampilkan notifikasi sukses
                                                showToast(`File ${file.name} berhasil diimpor! ${filteredData.length} baris data siap dianalisis.`, 'success');
                                                
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
                            
                            // Convert to JSON with more options
                            importedData = XLSX.utils.sheet_to_json(worksheet);
                            
                            setTimeout(function() {
                                if (importedData && importedData.length > 0) {
                                    // Simpan data yang diimpor
                                    dataSource = importedData;
                                    usingImportedData = true;
                                    
                                    // Update progress
                                    $('#importProgressBar').css('width', '100%');
                                    $('#importPercentage').text('100%');
                                    $('#importStatusText').text('Selesai!');
                                    
                                    // Perbarui selectors
                                    populateFromImportedData();
                                    
                                    // Sembunyikan modal setelah selesai
                                    setTimeout(function() {
                                        $('#importModalContent').removeClass('modal-enter').addClass('modal-exit');
                                        setTimeout(function() {
                                            $('#importFileModal').addClass('hidden');
                                            $('#importModalContent').removeClass('modal-exit');
                                            
                                            // Tampilkan notifikasi sukses
                                            showToast(`File ${file.name} berhasil diimpor! ${importedData.length} baris data siap dianalisis.`, 'success');
                                            
                                            // Perbarui tampilan untuk menunjukkan kita menggunakan data impor
                                            updateUIForImportedData(file.name, importedData.length);
                                        }, 300);
                                    }, 500);
                                } else {
                                    showToast('Tidak ada data yang ditemukan dalam file Excel.', 'error');
                                }
                            }, 500);
                        } catch (excelError) {
                            console.error("Excel parsing error:", excelError);
                            showToast("Error saat membaca file Excel: " + excelError.message, 'error');
                        }
                    }
                } catch (error) {
                    console.error('Error parsing file:', error);
                    showToast('Error saat memproses file: ' + error.message, 'error');
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
        }
        
        // Fungsi untuk memperbarui UI setelah import
        function updateUIForImportedData(fileName, rowCount) {
            // Perbarui badge info
            $('#importInfoBadge').removeClass('hidden');
            $('#importInfoText').text(`Data: ${fileName} (${rowCount} baris)`);
        }
        
        // Handle help button click
        $('#helpBtn').click(function() {
            createHelpModal();
        });
    });
</script>



</body>
</html>