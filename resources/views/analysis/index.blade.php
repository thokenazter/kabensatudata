<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PKM Kaben - Satu Data</title>
    <link rel="icon" type="image/png" href="{{ asset('images/iconsatudata.PNG') }}">
    <link rel="shortcut icon" href="{{ asset('images/iconsatudata.PNG') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('images/iconsatudata.PNG') }}">
    @include('includes.meta')
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    @include('includes.navbar')

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto pt-24 pb-6 px-4 sm:px-6 lg:px-8">
        {{-- <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
            <div class="flex items-center gap-3">
                <a href="/dashboard" class="inline-flex items-center px-3 py-2 rounded-lg bg-gradient-to-r from-primary-600 to-secondary-600 text-white text-sm font-semibold shadow-md hover:from-primary-700 hover:to-secondary-700 transition">
                    <i class="fas fa-chart-line mr-2"></i>
                    Dashboard
                </a>
                <h1 class="text-xl font-semibold text-gray-700">PKM Kaben - Satu Data</h1>
            </div>
            <div class="flex items-center gap-3">
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
        </div> --}}
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

                                    @elseif($variable['type'] === 'age_filter')
                                        <div class="space-y-3">
                                            <select id="age_filter_select_{{ $key }}" 
                                                    class="form-select w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 text-sm">
                                                <option value="">Semua</option>
                                                @foreach($variable['options'] as $optionKey => $optionLabel)
                                                    <option value="{{ $optionKey }}">{{ $optionLabel }}</option>
                                                @endforeach
                                            </select>
                                            
                                            <div id="custom_age_range_{{ $key }}" class="hidden mt-2">
                                                <div class="grid grid-cols-2 gap-2">
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">Usia minimum</label>
                                                        <input type="number" id="age_min_{{ $key }}" min="0" max="120" 
                                                               class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 text-sm"
                                                               placeholder="Min">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">Usia maksimum</label>
                                                        <input type="number" id="age_max_{{ $key }}" min="0" max="120"
                                                               class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 text-sm"
                                                               placeholder="Max">
                                                    </div>
                                                </div>
                                                <div class="mt-2">
                                                    <button type="button" id="apply_custom_age_{{ $key }}" 
                                                            class="w-full px-2 py-1 text-xs bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1">
                                                        Terapkan Rentang Usia
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <input type="hidden" name="filters[{{ $key }}]" id="age_filter_value_{{ $key }}">
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

            <!-- Visualization Type Select -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Visualisasi</label>
                <select id="visualizationType" class="form-select w-full md:w-1/3 rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 text-sm">
                    @foreach($visualizationTypes as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
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

        <div id="chartArea" class="min-h-[5px]"></div>
        <div id="tableArea" class="overflow-x-auto"></div>
        
            <!-- Loading Indicator -->
        <div id="loadingIndicator" class="hidden bg-white rounded-xl shadow-card p-6 mt-6">
                <div class="flex flex-col items-center justify-center p-8">
                <div class="w-12 h-12 border-4 border-primary-200 border-t-primary-600 rounded-full animate-spin"></div>
                    <span class="mt-4 text-gray-600">Menganalisis data...</span>
                </div>
            </div>
            
            <!-- Welcome Message (initially shown) -->
        <div id="welcomeMessage" class="bg-white rounded-xl shadow-card p-6 mt-6 text-center py-12">
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
        
        <!-- Toast Notifications -->
        <div id="toastContainer" class="fixed bottom-4 right-4 z-50 flex flex-col gap-2"></div>
    </main>
        <script>
            $(document).ready(function() {
            // FILTER USIA KUSTOM
            // Toggle tampilan input kustom
            $('select[id^="age_filter_select_"]').change(function() {
                const id = $(this).attr('id').replace('age_filter_select_', '');
                if ($(this).val() === 'custom') {
                    $(`#custom_age_range_${id}`).removeClass('hidden');
                } else {
                    $(`#custom_age_range_${id}`).addClass('hidden');
                    // Set nilai filter berdasarkan pilihan dropdown
                    $(`#age_filter_value_${id}`).val($(this).val());
                }
            });
            
            // Terapkan rentang usia kustom
            $('button[id^="apply_custom_age_"]').click(function() {
                const id = $(this).attr('id').replace('apply_custom_age_', '');
                const minAge = $(`#age_min_${id}`).val();
                const maxAge = $(`#age_max_${id}`).val();
                
                if (minAge && maxAge) {
                    // Format rentang usia: min-max
                    $(`#age_filter_value_${id}`).val(minAge + '-' + maxAge);
                    showToast('Rentang usia ' + minAge + '-' + maxAge + ' tahun diterapkan', 'success');
                } else if (minAge) {
                    // Format usia minimum: min+
                    $(`#age_filter_value_${id}`).val(minAge + '+');
                    showToast('Usia minimum ' + minAge + '+ tahun diterapkan', 'success');
                } else if (maxAge) {
                    // Format usia maksimum: 0-max
                    $(`#age_filter_value_${id}`).val('0-' + maxAge);
                    showToast('Usia maksimum 0-' + maxAge + ' tahun diterapkan', 'success');
                } else {
                    showToast('Harap masukkan rentang usia', 'error');
                }
            });

            // Fungsi untuk menampilkan toast
                function showToast(message, type = 'info', duration = 3000) {
                const toastId = 'toast-' + Date.now();
                const toastHTML = `
                    <div id="${toastId}" class="flex items-center p-4 mb-3 rounded-lg shadow-lg ${
                        type === 'success' ? 'bg-green-100 text-green-700 border-l-4 border-green-500' :
                        type === 'error' ? 'bg-red-100 text-red-700 border-l-4 border-red-500' :
                        'bg-blue-100 text-blue-700 border-l-4 border-blue-500'
                    }" role="alert">
                        <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 ${
                            type === 'success' ? 'text-green-500 bg-green-200' :
                            type === 'error' ? 'text-red-500 bg-red-200' :
                            'text-blue-500 bg-blue-200'
                        } rounded-lg">
                            <i class="fas ${
                                type === 'success' ? 'fa-check' :
                                type === 'error' ? 'fa-times' :
                                'fa-info'
                            }"></i>
                        </div>
                                    <div class="ml-1">${message}</div>
                        </div>
                    `;
                    
                $('#toastContainer').append(toastHTML);
                    
                    setTimeout(() => {
                    $(`#${toastId}`).animate({
                        opacity: 0,
                        marginBottom: '-3rem'
                    }, 300, function() {
                        $(this).remove();
                    });
                }, duration);
            }

            // Tombol Analisis Data
            $('#analyzeBtn').click(function() {
                // Tampilkan loading indicator
                $('#loadingIndicator').removeClass('hidden');
                $('#welcomeMessage').addClass('hidden');
                $('#chartArea, #tableArea').empty();
                
                // Kumpulkan nilai filter
                const filters = {};
                $('[name^="filters["]').each(function() {
                    const $el = $(this);
                    const inputType = ($el.attr('type') || '').toLowerCase();

                    if ((inputType === 'checkbox' || inputType === 'radio') && !$el.is(':checked')) {
                        return;
                    }

                    const name = $el.attr('name');
                    const match = name.match(/filters\[(.*?)\]/);
                    if (!match) return;
                    const key = match[1];
                    const rawValue = $el.val();
                    const value = typeof rawValue === 'string' ? rawValue.trim() : rawValue;

                    if (value !== '' && value !== null && typeof value !== 'undefined') {
                        filters[key] = value;
                    }
                });
                
                // Ambil jenis visualisasi
                const visualizationType = $('#visualizationType').val() || 'table';
                
                // Simpan filter terakhir untuk digunakan saat export
                window.lastFilters = filters;
                window.lastVisualizationType = visualizationType;
                
                // Kirim request ke server
                fetch('{{ route('analysis.analyze') }}', {
                        method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        filters: filters,
                        visualization_type: visualizationType
                    })
                })
                .then(response => response.json())
                .then(result => {
                    // Sembunyikan loading
                            $('#loadingIndicator').addClass('hidden');
                    
                    if (result.success) {
                        // Simpan data terakhir untuk export
                        window.lastAnalysisData = result.data;
                        
                        // Render hasil visualisasi
                        renderVisualization(result.data);
                        
                        // Tampilkan tombol export jika ada data
                        if (result.data.rows && result.data.rows.length > 0) {
                                $('#exportExcelBtn').removeClass('hidden');
                            $('#resetImportBtn').removeClass('hidden');
                        }
                            } else {
                        // Tampilkan pesan error
                        showToast(result.message || 'Terjadi kesalahan saat menganalisis data', 'error');
                            }
                })
                .catch(error => {
                    console.error('Error:', error);
                            $('#loadingIndicator').addClass('hidden');
                    showToast('Terjadi kesalahan saat menganalisis data', 'error');
                });
            });
            
            // Export Excel
            $('#exportExcelBtn').click(function() {
                if (!window.lastAnalysisData || !window.lastAnalysisData.headers || !window.lastAnalysisData.rows) {
                    showToast('Tidak ada data untuk diekspor', 'error');
                    return;
                }
                
                // Temukan indeks kolom NIK jika ada
                const nikColumnIndex = window.lastAnalysisData.headers.findIndex(header => header === 'NIK');
                
                // Jika ada kolom NIK, pastikan semua nilai adalah string
                if (nikColumnIndex !== -1) {
                    // Buat copy dari data untuk menghindari mutasi data original
                    const modifiedData = {
                        ...window.lastAnalysisData,
                        rows: window.lastAnalysisData.rows.map(row => {
                            // Clone baris
                            const newRow = [...row];
                            // Jika nilai di kolom NIK ada, konversi ke string
                            if (newRow[nikColumnIndex] !== undefined && newRow[nikColumnIndex] !== null) {
                                newRow[nikColumnIndex] = String(newRow[nikColumnIndex]);
                            }
                            return newRow;
                        })
                    };
                    
                    // Ganti lastAnalysisData dengan data yang telah dimodifikasi
                    window.lastAnalysisData = modifiedData;
                }
                
                // Tampilkan loading
                showToast('Mempersiapkan file Excel...', 'info');
                
                // Ekspor ke Excel menggunakan API server
                fetch('{{ route('analysis.export') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        filters: window.lastFilters || {},
                        visualization_type: window.lastVisualizationType || 'table',
                        data: window.lastAnalysisData
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.blob();
                })
                .then(blob => {
                    // Validasi tipe MIME 
                    if (blob.type !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
                        console.warn('Unexpected MIME type received:', blob.type);
                        // Tetap mencoba dengan tipe yang benar
                        blob = new Blob([blob], {
                            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                        });
                    }
                    
                    // Membuat URL objek untuk blob
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    
                    // Nama file dengan timestamp
                    const date = new Date();
                    const timestamp = date.getFullYear() + 
                        ('0' + (date.getMonth() + 1)).slice(-2) + 
                        ('0' + date.getDate()).slice(-2) + '_' +
                        ('0' + date.getHours()).slice(-2) + 
                        ('0' + date.getMinutes()).slice(-2);
                    
                    a.download = `analisis_data_${timestamp}.xlsx`;
                    document.body.appendChild(a);
                    a.click();
                    
                    // Membersihkan
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                    
                    showToast('Data berhasil diekspor ke Excel', 'success');
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Gagal mengekspor data: ' + error.message, 'error');
                    
                    // Fallback: Ekspor manual jika API gagal
                    exportToCSVManually();
                        });
                    });
            
            // Ekspor manual ke CSV jika API gagal
            function exportToCSVManually() {
                if (!window.lastAnalysisData || !window.lastAnalysisData.headers || !window.lastAnalysisData.rows) {
                    return;
                }
                
                try {
                    const headers = window.lastAnalysisData.headers;
                    const rows = window.lastAnalysisData.rows;
                    
                    // Buat array data dengan header sebagai baris pertama
                    const data = [headers, ...rows];
                    
                    // Konversi setiap baris menjadi CSV
                    const csvContent = data.map(row => {
                        return row.map(cell => {
                            // Tangani kasus string dengan koma
                            if (typeof cell === 'string' && (cell.includes(',') || cell.includes('"') || cell.includes('\n'))) {
                                return `"${cell.replace(/"/g, '""')}"`;
                            }
                            return cell;
                        }).join(',');
                    }).join('\n');
                    
                    // Buat blob dan unduh
                    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    
                    // Nama file dengan timestamp
                    const date = new Date();
                    const timestamp = date.getFullYear() + 
                        ('0' + (date.getMonth() + 1)).slice(-2) + 
                        ('0' + date.getDate()).slice(-2) + '_' +
                        ('0' + date.getHours()).slice(-2) + 
                        ('0' + date.getMinutes()).slice(-2);
                    
                    a.download = `analisis_data_${timestamp}.csv`;
                    document.body.appendChild(a);
                    a.click();
                    
                    // Membersihkan
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                    
                    showToast('Data berhasil diekspor ke CSV (mode offline)', 'success');
                } catch (error) {
                    console.error('Manual export error:', error);
                    showToast('Gagal mengekspor data secara manual', 'error');
                }
            }
            
            // Reset Import
            $('#resetImportBtn').click(function() {
                // Reset data dan tampilan
                window.lastAnalysisData = null;
                window.lastFilters = null;
                window.lastVisualizationType = null;
                
                // Reset form
                $('select[name^="filters["]').val('');
                $('input[name^="filters["]').prop('checked', false);
                $('input[type="radio"][value=""]').prop('checked', true);
                $('input[type="hidden"][name^="filters["]').val('');
                
                // Reset tampilan
                $('#chartArea, #tableArea').empty();
                $('#welcomeMessage').removeClass('hidden');
                $('#exportExcelBtn, #resetImportBtn').addClass('hidden');
                
                showToast('Data dan filter berhasil direset', 'success');
            });

            // Fungsi untuk render visualisasi berdasarkan tipe
            function renderVisualization(data) {
                if (!data) return;
                
                switch (data.type) {
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
                    default:
                        renderTable(data);
                }
            }
            
            // Fungsi untuk render tabel
            function renderTable(data) {
                if (!data.headers || !data.rows || data.rows.length === 0) {
                    $('#tableArea').html('<div class="text-center py-8 text-gray-500">Tidak ada data yang sesuai dengan filter</div>');
                    return;
                }
                
                let tableHTML = `
                    <div class="bg-white rounded-xl shadow-card p-6 mt-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Hasil Analisis</h3>
                            <span class="text-sm text-gray-500">${data.rows.length} baris data</span>
                            </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full data-table divide-y divide-gray-200">
                                <thead>
                                    <tr>
                `;
                
                // Tambahkan header
                data.headers.forEach((header, index) => {
                    tableHTML += `<th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">${header}</th>`;
                });
                
                tableHTML += `
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                `;
                
                // Tambahkan baris data
                data.rows.forEach((row, rowIndex) => {
                    tableHTML += `<tr class="${rowIndex % 2 === 0 ? 'bg-white' : 'bg-gray-50'}">`;
                    
                    row.forEach((cell, cellIndex) => {
                        tableHTML += `<td class="px-3 py-2 whitespace-nowrap text-sm text-gray-800">${cell}</td>`;
                    });
                    
                    tableHTML += `</tr>`;
                });
                
                tableHTML += `
                            </tbody>
                        </table>
                        </div>
                    </div>
                `;
                
                $('#tableArea').html(tableHTML);
            }
            
            // Fungsi untuk render chart batang
            function renderBarChart(data) {
                if (!data.labels || !data.datasets) {
                    $('#chartArea').html('<div class="text-center py-8 text-gray-500">Tidak ada data yang sesuai untuk visualisasi</div>');
                    return;
                }
                
                // Buat container untuk chart
                let chartHTML = `
                    <div class="bg-white rounded-xl shadow-card p-6 mt-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Visualisasi Data</h3>
                            </div>
                        <div class="h-80">
                            <canvas id="barChart"></canvas>
                        </div>
                    </div>
                `;
                
                $('#chartArea').html(chartHTML);
                
                // Buat chart dengan Chart.js
                const ctx = document.getElementById('barChart').getContext('2d');
                
                // Pilihan warna
                const colors = [
                    'rgba(99, 102, 241, 0.7)', // primary
                    'rgba(168, 85, 247, 0.7)',  // purple
                    'rgba(236, 72, 153, 0.7)',  // pink
                    'rgba(239, 68, 68, 0.7)',   // red
                    'rgba(245, 158, 11, 0.7)',  // amber
                    'rgba(16, 185, 129, 0.7)',  // emerald
                    'rgba(6, 182, 212, 0.7)'    // cyan
                ];
                
                const borderColors = [
                    'rgb(99, 102, 241)',
                    'rgb(168, 85, 247)',
                    'rgb(236, 72, 153)',
                    'rgb(239, 68, 68)',
                    'rgb(245, 158, 11)',
                    'rgb(16, 185, 129)',
                    'rgb(6, 182, 212)'
                ];
                
                // Siapkan dataset dengan warna
                const datasets = data.datasets.map((dataset, index) => {
                    return {
                        ...dataset,
                        backgroundColor: colors[index % colors.length],
                        borderColor: borderColors[index % borderColors.length],
                        borderWidth: 1
                    };
                });
                
                // Buat chart
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                    labels: data.labels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                    legend: {
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    font: {
                                        family: 'Inter'
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleFont: {
                                    family: 'Inter',
                                    size: 14
                                },
                                bodyFont: {
                                    family: 'Inter',
                                    size: 13
                                },
                                padding: 10,
                                cornerRadius: 4
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        family: 'Inter'
                                    }
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(200, 200, 200, 0.2)'
                                },
                                ticks: {
                                    font: {
                                        family: 'Inter'
                                    }
                                }
                            }
                                    }
                                }
                            });
            }
            
            // Fungsi untuk render chart pie
            function renderPieChart(data) {
                if (!data.labels || !data.datasets) {
                    $('#chartArea').html('<div class="text-center py-8 text-gray-500">Tidak ada data yang sesuai untuk visualisasi</div>');
                    return;
                }
                
                // Buat container untuk chart
                let chartHTML = `
                    <div class="bg-white rounded-xl shadow-card p-6 mt-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Visualisasi Data</h3>
                            </div>
                        <div class="h-80 flex justify-center">
                            <canvas id="pieChart"></canvas>
                        </div>
                    </div>
                `;
                
                $('#chartArea').html(chartHTML);
                
                // Buat chart dengan Chart.js
                const ctx = document.getElementById('pieChart').getContext('2d');
                
                // Pilihan warna
                const colors = [
                    'rgba(99, 102, 241, 0.8)',  // primary
                    'rgba(168, 85, 247, 0.8)',  // purple
                    'rgba(236, 72, 153, 0.8)',  // pink
                    'rgba(239, 68, 68, 0.8)',   // red
                    'rgba(245, 158, 11, 0.8)',  // amber
                    'rgba(16, 185, 129, 0.8)',  // emerald
                    'rgba(6, 182, 212, 0.8)',   // cyan
                    'rgba(234, 88, 12, 0.8)',   // orange
                    'rgba(22, 163, 74, 0.8)',   // green
                    'rgba(79, 70, 229, 0.8)'    // indigo
                ];
                
                // Siapkan dataset dengan warna
                const datasets = data.datasets.map(dataset => {
                    return {
                        ...dataset,
                        backgroundColor: colors.slice(0, dataset.data.length),
                        borderWidth: 1
                    };
                });
                
                // Buat chart
                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: data.labels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    usePointStyle: true,
                                    font: {
                                        family: 'Inter'
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleFont: {
                                    family: 'Inter',
                                    size: 14
                                },
                                bodyFont: {
                                    family: 'Inter',
                                    size: 13
                                },
                                padding: 10,
                                cornerRadius: 4
                            }
                        }
                    }
                });
            }
            
            // Fungsi untuk render chart line
            function renderLineChart(data) {
                if (!data.labels || !data.datasets) {
                    $('#chartArea').html('<div class="text-center py-8 text-gray-500">Tidak ada data yang sesuai untuk visualisasi</div>');
                    return;
                }
                
                // Buat container untuk chart
                let chartHTML = `
                    <div class="bg-white rounded-xl shadow-card p-6 mt-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Visualisasi Data</h3>
                                                                </div>
                        <div class="h-80">
                            <canvas id="lineChart"></canvas>
                                                            </div>
                                                        </div>
                                                    `;
                
                $('#chartArea').html(chartHTML);
                
                // Buat chart dengan Chart.js
                const ctx = document.getElementById('lineChart').getContext('2d');
                
                // Pilihan warna
                const colors = [
                    'rgba(99, 102, 241, 1)',   // primary
                    'rgba(168, 85, 247, 1)',   // purple
                    'rgba(236, 72, 153, 1)',   // pink
                    'rgba(239, 68, 68, 1)',    // red
                    'rgba(245, 158, 11, 1)',   // amber
                    'rgba(16, 185, 129, 1)',   // emerald
                    'rgba(6, 182, 212, 1)'     // cyan
                ];
                
                // Siapkan dataset dengan warna
                const datasets = data.datasets.map((dataset, index) => {
                    return {
                        ...dataset,
                        borderColor: colors[index % colors.length],
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        pointBackgroundColor: colors[index % colors.length],
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.1
                    };
                });
                
                // Buat chart
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                        labels: {
                                    usePointStyle: true,
                                    font: {
                                        family: 'Inter'
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleFont: {
                                    family: 'Inter',
                                    size: 14
                                },
                                bodyFont: {
                                    family: 'Inter',
                                    size: 13
                                },
                                padding: 10,
                                cornerRadius: 4
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        family: 'Inter'
                                    }
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(200, 200, 200, 0.2)'
                                },
                                ticks: {
                                    font: {
                                        family: 'Inter'
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Guna memastikan semua fungsi tereksekusi
            $(document).ready(function() {
                // Tambahkan event listener untuk tombol "Mulai Analisis"
                $('#startAnalysisBtn').click(function() {
                    $('html, body').animate({
                        scrollTop: $('#analyzeBtn').offset().top - 100
                    }, 500);
                    
                    setTimeout(() => {
                        $('#analyzeBtn').focus();
                        $('#analyzeBtn').addClass('animate-pulse');
                        
                        setTimeout(() => {
                            $('#analyzeBtn').removeClass('animate-pulse');
                        }, 1500);
                    }, 500);
                });
            });
        });
    </script>
</body>
</html>
