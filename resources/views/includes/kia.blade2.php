<!-- Statistik Ibu Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
    <!-- Header dengan toggle button untuk statistik ibu -->
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-medium text-gray-900">Statistik Kesehatan Ibu</h2>

        <!-- Toggle button untuk statistik ibu -->
        <button
            x-data="{ show: true }"
            @click="show = !show; $dispatch('toggle-maternal', { show })"
            class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <span x-text="show ? 'Sembunyikan Data' : 'Tampilkan Data'">Sembunyikan Data</span>
        </button>
    </div>

    <!-- Cards container dengan toggle functionality -->
    <div
        x-data="{ visible: true }"
        @toggle-maternal.window="visible = $event.detail.show"
        x-show="visible"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95">
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Ibu dengan KB -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-pink-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Menggunakan KB
                                </dt>
                                <dd class="tooltip">
                                    <div class="text-lg font-medium text-gray-900">
                                        {{ number_format($maternalStats['kb_count']) }}
                                        <span class="text-xs text-blue-600">Lihat detail</span>
                                    </div>
                                    <div class="tooltip-content">
                                        <div class="tooltip-title">Detail Ibu menggunakan KB ({{ count($kbCases) }} orang)</div>
                                        @foreach($kbCases as $case)
                                        <div class="tooltip-item">
                                            <div><strong>Nama:</strong> {{ $case->name }}</div>
                                            <div><strong>Desa:</strong> {{ $case->family->building->village->name }}</div>
                                            <div><strong>No. Bangunan:</strong> {{ $case->family->building->building_number }}</div>
                                            <div><strong>Umur:</strong> {{ $case->age }} tahun</div>
                                        </div>
                                        @endforeach
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tidak Menggunakan KB -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-red-400 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Tidak Menggunakan KB
                                </dt>
                                <dd class="tooltip">
                                    <div class="text-lg font-medium text-gray-900">
                                        {{ number_format($maternalStats['no_kb_count']) }}
                                        <span class="text-xs text-blue-600">Lihat detail</span>
                                    </div>
                                    <div class="tooltip-content">
                                        <div class="tooltip-title">Detail Ibu tidak menggunakan KB ({{ count($noKbCases) }} orang)</div>
                                        @foreach($noKbCases as $case)
                                        <div class="tooltip-item">
                                            <div><strong>Nama:</strong> {{ $case->name }}</div>
                                            <div><strong>Desa:</strong> {{ $case->family->building->village->name }}</div>
                                            <div><strong>No. Bangunan:</strong> {{ $case->family->building->building_number }}</div>
                                            <div><strong>Umur:</strong> {{ $case->age }} tahun</div>
                                        </div>
                                        @endforeach
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ibu Hamil -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Ibu Hamil
                                </dt>
                                <dd class="tooltip">
                                    <div class="text-lg font-medium text-gray-900">
                                        {{ number_format($maternalStats['pregnant_count']) }}
                                        <span class="text-xs text-blue-600">Lihat detail</span>
                                    </div>
                                    <div class="tooltip-content">
                                        <div class="tooltip-title">Detail Ibu Hamil ({{ count($pregnantCases) }} orang)</div>
                                        @foreach($pregnantCases as $case)
                                        <div class="tooltip-item">
                                            <div><strong>Nama:</strong> {{ $case->name }}</div>
                                            <div><strong>Desa:</strong> {{ $case->family->building->village->name }}</div>
                                            <div><strong>No. Bangunan:</strong> {{ $case->family->building->building_number }}</div>
                                            <div><strong>Umur:</strong> {{ $case->age }} tahun</div>
                                        </div>
                                        @endforeach
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bersalin di Fasilitas Kesehatan -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Bersalin di Faskes
                                </dt>
                                <dd class="tooltip">
                                    <div class="text-lg font-medium text-gray-900">
                                        {{ number_format($maternalStats['health_facility_birth_count']) }}
                                        <span class="text-xs text-blue-600">Lihat detail</span>
                                    </div>
                                    <div class="tooltip-content">
                                        <div class="tooltip-title">Detail Bersalin di Faskes ({{ count($healthFacilityBirthCases) }} orang)</div>
                                        @foreach($healthFacilityBirthCases as $case)
                                        <div class="tooltip-item">
                                            <div><strong>Nama:</strong> {{ $case->name }}</div>
                                            <div><strong>Desa:</strong> {{ $case->family->building->village->name }}</div>
                                            <div><strong>No. Bangunan:</strong> {{ $case->family->building->building_number }}</div>
                                            <div><strong>Umur:</strong> {{ $case->age }} tahun</div>
                                        </div>
                                        @endforeach
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Empty state saat data disembunyikan -->
    <div
        x-data="{ visible: false }"
        @toggle-maternal.window="visible = !$event.detail.show"
        x-show="visible"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="py-10 flex flex-col items-center justify-center text-gray-500 bg-gray-50 rounded-lg shadow">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-4 text-pink-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <p class="text-center">Data Statistik Kesehatan Ibu disembunyikan.</p>
        <p class="text-center text-sm">Klik tombol "Tampilkan Data" untuk melihat informasi.</p>
    </div>
</div>

<!-- Statistik Anak Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
    <!-- Header dengan toggle button untuk statistik anak -->
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-medium text-gray-900">Statistik Kesehatan Anak</h2>

        <!-- Toggle button untuk statistik anak -->
        <button
            x-data="{ show: true }"
            @click="show = !show; $dispatch('toggle-child', { show })"
            class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <span x-text="show ? 'Sembunyikan Data' : 'Tampilkan Data'">Sembunyikan Data</span>
        </button>
    </div>

    <!-- Cards container dengan toggle functionality -->
    <div
        x-data="{ visible: true }"
        @toggle-child.window="visible = $event.detail.show"
        x-show="visible"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95">
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            <!-- ASI Eksklusif -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    ASI Eksklusif 0-6 bulan (umur 7-23 bulan)
                                </dt>
                                <dd class="tooltip">
                                    <div class="text-lg font-medium text-gray-900">
                                        {{ number_format($childStats['exclusive_breastfeeding_count']) }}
                                        <span class="text-xs text-blue-600">Lihat detail</span>
                                    </div>
                                    <div class="tooltip-content">
                                        <div class="tooltip-title">Detail ASI Eksklusif ({{ count($exclusiveBreastfeedingCases) }} anak)</div>
                                        @foreach($exclusiveBreastfeedingCases as $case)
                                        <div class="tooltip-item">
                                            <div><strong>Nama:</strong> {{ $case->name }}</div>
                                            <div><strong>Desa:</strong> {{ $case->family->building->village->name }}</div>
                                            <div><strong>No. Bangunan:</strong> {{ $case->family->building->building_number }}</div>
                                            <div><strong>Umur:</strong> {{ $case->age }} tahun</div>
                                        </div>
                                        @endforeach
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Imunisasi Lengkap -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round"
                                    stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Imunisasi Lengkap (umur 12-23 bulan)
                                </dt>
                                <dd class="tooltip">
                                    <div class="text-lg font-medium text-gray-900">
                                        {{ number_format($childStats['complete_immunization_count']) }}
                                        <span class="text-xs text-blue-600">Lihat detail</span>
                                    </div>
                                    <div class="tooltip-content">
                                        <div class="tooltip-title">Detail Imunisasi Lengkap ({{ count($completeImmunizationCases) }} anak)</div>
                                        @foreach($completeImmunizationCases as $case)
                                        <div class="tooltip-item">
                                            <div><strong>Nama:</strong> {{ $case->name }}</div>
                                            <div><strong>Desa:</strong> {{ $case->family->building->village->name }}</div>
                                            <div><strong>No. Bangunan:</strong> {{ $case->family->building->building_number }}</div>
                                            <div><strong>Umur:</strong> {{ $case->age }} tahun</div>
                                        </div>
                                        @endforeach
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pemantauan Pertumbuhan -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Pemantauan 1 Bulan Terakhir (umur 2-59 bulan)
                                </dt>
                                <dd class="tooltip">
                                    <div class="text-lg font-medium text-gray-900">
                                        {{ number_format($childStats['growth_monitoring_count']) }}
                                        <span class="text-xs text-blue-600">Lihat detail</span>
                                    </div>
                                    <div class="tooltip-content">
                                        <div class="tooltip-title">Detail Pemantauan Pertumbuhan ({{ count($growthMonitoringCases) }} anak)</div>
                                        @foreach($growthMonitoringCases as $case)
                                        <div class="tooltip-item">
                                            <div><strong>Nama:</strong> {{ $case->name }}</div>
                                            <div><strong>Desa:</strong> {{ $case->family->building->village->name }}</div>
                                            <div><strong>No. Bangunan:</strong> {{ $case->family->building->building_number }}</div>
                                            <div><strong>Umur:</strong> {{ $case->age }} tahun</div>
                                        </div>
                                        @endforeach
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Empty state saat data disembunyikan -->
    <div
        x-data="{ visible: false }"
        @toggle-child.window="visible = !$event.detail.show"
        x-show="visible"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="py-10 flex flex-col items-center justify-center text-gray-500 bg-gray-50 rounded-lg shadow">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        <p class="text-center">Data Statistik Kesehatan Anak disembunyikan.</p>
        <p class="text-center text-sm">Klik tombol "Tampilkan Data" untuk melihat informasi.</p>
    </div>
</div>

<!-- Grafik dengan Toggle untuk Kesehatan Ibu dan Anak -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
    <!-- Header dengan toggle button untuk grafik -->
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-medium text-gray-900">Grafik Perbandingan</h2>

        <!-- Toggle button untuk grafik -->
        <button
            x-data="{ show: true }"
            @click="show = !show; $dispatch('toggle-charts', { show })"
            class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <span x-text="show ? 'Sembunyikan Grafik' : 'Tampilkan Grafik'">Sembunyikan Grafik</span>
        </button>
    </div>

    <!-- Charts container dengan toggle functionality -->
    <div
        x-data="{ visible: true }"
        @toggle-charts.window="visible = $event.detail.show"
        x-show="visible"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95">
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <!-- Maternal Health Chart -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Statistik Kesehatan Ibu</h3>
                    <div class="mt-2 h-64">
                        <canvas id="maternalChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Child Health Chart -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Statistik Kesehatan Anak</h3>
                    <div class="mt-2 h-64">
                        <canvas id="childChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Empty state saat grafik disembunyikan -->
    <div
        x-data="{ visible: false }"
        @toggle-charts.window="visible = !$event.detail.show"
        x-show="visible"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="py-10 flex flex-col items-center justify-center text-gray-500 bg-gray-50 rounded-lg shadow">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        <p class="text-center">Grafik perbandingan disembunyikan.</p>
        <p class="text-center text-sm">Klik tombol "Tampilkan Grafik" untuk melihat visualisasi data.</p>
    </div>
</div>