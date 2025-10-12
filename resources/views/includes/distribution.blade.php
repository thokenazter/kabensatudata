<!-- Sanitasi Charts Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
    <!-- Header dengan toggle button untuk charts sanitasi -->
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-medium text-gray-900">Grafik Sanitasi</h3>
        
        <!-- Toggle button -->
        <button 
            x-data="{ show: true }" 
            @click="show = !show; $dispatch('toggle-sanitation-charts', { show })" 
            class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
            <span x-text="show ? 'Sembunyikan Grafik' : 'Tampilkan Grafik'">Sembunyikan Grafik</span>
        </button>
    </div>
    
    <!-- Charts container dengan toggle functionality -->
    <div 
        x-data="{ visible: true }" 
        @toggle-sanitation-charts.window="visible = $event.detail.show" 
        x-show="visible" 
        x-transition:enter="transition ease-out duration-300" 
        x-transition:enter-start="opacity-0 transform scale-95" 
        x-transition:enter-end="opacity-100 transform scale-100" 
        x-transition:leave="transition ease-in duration-200" 
        x-transition:leave-start="opacity-100 transform scale-100" 
        x-transition:leave-end="opacity-0 transform scale-95"
    >
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <!-- Sanitation Charts -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Distribusi Air Bersih</h3>
                    <div class="mt-2 h-64">
                        <canvas id="waterChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Distribusi Jamban</h3>
                    <div class="mt-2 h-64">
                        <canvas id="toiletChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Empty state saat grafik sanitasi disembunyikan -->
    <div 
        x-data="{ visible: false }" 
        @toggle-sanitation-charts.window="visible = !$event.detail.show" 
        x-show="visible" 
        x-transition:enter="transition ease-out duration-300" 
        x-transition:enter-start="opacity-0" 
        x-transition:enter-end="opacity-100" 
        x-transition:leave="transition ease-in duration-200" 
        x-transition:leave-start="opacity-100" 
        x-transition:leave-end="opacity-0" 
        class="py-10 flex flex-col items-center justify-center text-gray-500 bg-gray-50 rounded-lg shadow"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        <p class="text-center">Grafik Sanitasi disembunyikan.</p>
        <p class="text-center text-sm">Klik tombol "Tampilkan Grafik" untuk melihat visualisasi data.</p>
    </div>
</div>

<!-- Demografi Charts Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
    <!-- Header dengan toggle button untuk charts demografi -->
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-medium text-gray-900">Grafik Demografi & Kesehatan</h3>
        
        <!-- Toggle button -->
        <button 
            x-data="{ show: true }" 
            @click="show = !show; $dispatch('toggle-demography-charts', { show })" 
            class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
            <span x-text="show ? 'Sembunyikan Grafik' : 'Tampilkan Grafik'">Sembunyikan Grafik</span>
        </button>
    </div>
    
    <!-- Charts container dengan toggle functionality -->
    <div 
        x-data="{ visible: true }" 
        @toggle-demography-charts.window="visible = $event.detail.show" 
        x-show="visible" 
        x-transition:enter="transition ease-out duration-300" 
        x-transition:enter-start="opacity-0 transform scale-95" 
        x-transition:enter-end="opacity-100 transform scale-100" 
        x-transition:leave="transition ease-in duration-200" 
        x-transition:leave-start="opacity-100 transform scale-100" 
        x-transition:leave-end="opacity-0 transform scale-95"
    >
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <!-- Pie Chart - Distribusi Jenis Kelamin -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Distribusi Jenis Kelamin</h3>
                    <div class="mt-2 h-64">
                        <canvas id="genderChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Bar Chart - Distribusi Usia -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Distribusi Usia</h3>
                    <div class="mt-2 h-64">
                        <canvas id="ageChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Pie Chart - Distribusi Pendidikan -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Distribusi Pendidikan</h3>
                    <div class="mt-2 h-64">
                        <canvas id="educationChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Bar Chart - Masalah Kesehatan -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Masalah Kesehatan</h3>
                    <div class="mt-2 h-64">
                        <canvas id="healthChart"></canvas>
                    </div>
                    <div class="mt-3 text-xs text-gray-600">
                        <div class="inline-flex items-center gap-2 bg-gray-50 border border-gray-200 rounded px-2 py-1 mr-2">
                            <span class="font-semibold">Gangguan Jiwa (Keluarga):</span>
                            <span>{{ number_format($stats['mental_illness_count'] ?? 0) }}</span>
                        </div>
                        <div class="inline-flex items-center gap-2 bg-gray-50 border border-gray-200 rounded px-2 py-1">
                            <span class="font-semibold">ODGJ (Individu):</span>
                            <span>{{ number_format($stats['mental_disorder_individual_count'] ?? 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Empty state saat grafik demografi disembunyikan -->
    <div 
        x-data="{ visible: false }" 
        @toggle-demography-charts.window="visible = !$event.detail.show" 
        x-show="visible" 
        x-transition:enter="transition ease-out duration-300" 
        x-transition:enter-start="opacity-0" 
        x-transition:enter-end="opacity-100" 
        x-transition:leave="transition ease-in duration-200" 
        x-transition:leave-start="opacity-100" 
        x-transition:leave-end="opacity-0" 
        class="py-10 flex flex-col items-center justify-center text-gray-500 bg-gray-50 rounded-lg shadow"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
        <p class="text-center">Grafik Demografi & Kesehatan disembunyikan.</p>
        <p class="text-center text-sm">Klik tombol "Tampilkan Grafik" untuk melihat visualisasi data.</p>
    </div>
</div>
