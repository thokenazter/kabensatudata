<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batch QR Code - PIS-PK</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-after: always;
            }
            .qr-card {
                break-inside: avoid;
            }
        }
        
        /* Grid untuk printing */
        .print-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10mm;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <div class="no-print">
        @include('includes.navbar')
    </div>

    <!-- Main Content -->
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="text-center mb-8 no-print">
                <h1 class="text-3xl font-bold text-gray-900">Cetak QR Code Keluarga</h1>
                <p class="mt-2 text-gray-600">Total {{ count($families) }} keluarga</p>
                
                <!-- Filter Form -->
                <div class="mt-4 max-w-md mx-auto">
                    <form action="{{ route('qrcode.batch') }}" method="GET" class="bg-white p-4 rounded-lg shadow-sm">
                        <div class="mb-3">
                            <label for="village_id" class="block text-sm font-medium text-gray-700">Desa</label>
                            <select id="village_id" name="village_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                <option value="">-- Semua Desa --</option>
                                @foreach(App\Models\Village::orderBy('name')->get() as $village)
                                    <option value="{{ $village->id }}" @if(request('village_id') == $village->id) selected @endif>{{ $village->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="building_id" class="block text-sm font-medium text-gray-700">Bangunan</label>
                            <select id="building_id" name="building_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                <option value="">-- Semua Bangunan --</option>
                                <!-- Akan diisi via JavaScript -->
                            </select>
                        </div>
                        
                        <div class="mt-4 flex justify-between">
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-500 focus:outline-none focus:border-blue-700 focus:shadow-outline-blue active:bg-blue-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                </svg>
                                Filter
                            </button>
                            
                            <button type="button" onclick="window.print()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-green-600 hover:bg-green-500 focus:outline-none focus:border-green-700 focus:shadow-outline-green active:bg-green-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Cetak Semua
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- QR Code Cards -->
            <div class="print-grid">
                @foreach($families as $item)
                    <div class="qr-card bg-white shadow-md rounded-lg overflow-hidden border border-gray-200 mb-6">
                        <!-- Header/Info Keluarga -->
                        <div class="px-4 py-3 bg-blue-600 text-white">
                            <h2 class="text-base font-semibold">Keluarga {{ $item['family']->head_name }}</h2>
                            <p class="text-xs text-blue-100">
                                {{ $item['family']->building->village->name }}, 
                                Bangunan No.{{ $item['family']->building->building_number }}, 
                                Keluarga No.{{ $item['family']->family_number }}
                            </p>
                        </div>
                        
                        <!-- QR Code -->
                        <div class="flex flex-col items-center py-3 px-4">
                            <div class="border p-2 rounded-lg bg-white">
                                <img src="data:image/png;base64,{{ $item['qrCodeImage'] }}" alt="QR Code" class="w-36 h-36">
                            </div>
                            
                            <div class="mt-2 text-center">
                                <p class="text-xs text-gray-500">Scan untuk mengakses</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Empty State -->
            @if(count($families) == 0)
                <div class="text-center py-10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-lg font-medium text-gray-900">Tidak ada data keluarga</h3>
                    <p class="mt-1 text-gray-500">Coba ubah filter atau tambahkan data keluarga baru</p>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Script for dynamic building selection -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const villageSelect = document.getElementById('village_id');
            const buildingSelect = document.getElementById('building_id');
            
            // Fungsi untuk memuat bangunan berdasarkan desa
            function loadBuildings() {
                const villageId = villageSelect.value;
                
                // Reset building select
                buildingSelect.innerHTML = '<option value="">-- Semua Bangunan --</option>';
                
                if (!villageId) return;
                
                // Fetch buildings by village
                fetch(`/api/buildings?village_id=${villageId}`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(building => {
                            const option = document.createElement('option');
                            option.value = building.id;
                            option.textContent = `No. ${building.building_number}`;
                            
                            // Cek jika ini selected berdasarkan URL parameter
                            if (building.id == '{{ request('building_id') }}') {
                                option.selected = true;
                            }
                            
                            buildingSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error loading buildings:', error));
            }
            
            // Init buildings on load
            loadBuildings();
            
            // Update buildings when village changes
            villageSelect.addEventListener('change', loadBuildings);
        });
    </script>
</body>
</html>