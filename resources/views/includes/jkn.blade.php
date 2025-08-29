<!-- JKN Per Desa Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
    <!-- Header dengan toggle button untuk data JKN -->
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg md:text-xl font-semibold text-gray-900">Sebaran JKN per Desa</h3>
        
        <!-- Toggle button dengan desain yang lebih modern -->
        <button 
            x-data="{ show: true }" 
            @click="show = !show; $dispatch('toggle-jkn', { show })" 
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path x-show="show" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                <path x-show="!show" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span x-text="show ? 'Sembunyikan Data' : 'Tampilkan Data'">Sembunyikan Data</span>
        </button>
    </div>
    
    <!-- Table container dengan toggle functionality -->
    <div 
        x-data="{ visible: true }" 
        @toggle-jkn.window="visible = $event.detail.show" 
        x-show="visible" 
        x-transition:enter="transition ease-out duration-300" 
        x-transition:enter-start="opacity-0 transform scale-95" 
        x-transition:enter-end="opacity-100 transform scale-100" 
        x-transition:leave="transition ease-in duration-200" 
        x-transition:leave-start="opacity-100 transform scale-100" 
        x-transition:leave-end="opacity-0 transform scale-95"
    >
        <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100">
            <div class="p-5 sm:p-6">
                <!-- JKN Summary Card dengan desain yang lebih modern -->
                <div class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <!-- Card Total Penduduk -->
                    <div class="bg-white overflow-hidden shadow-md rounded-xl border border-blue-100 transition-all duration-300 hover:shadow-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-3 shadow-md">
                                    <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div class="ml-5">
                                    <dt class="text-sm font-medium text-gray-500 uppercase tracking-wide">
                                        Total Penduduk
                                    </dt>
                                    <dd class="mt-1 text-2xl font-bold text-gray-900">
                                        {{ number_format($jknStats['members']) }}
                                    </dd>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Card Memiliki JKN -->
                    <div class="bg-white overflow-hidden shadow-md rounded-xl border border-green-100 transition-all duration-300 hover:shadow-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-gradient-to-br from-green-500 to-green-600 rounded-lg p-3 shadow-md">
                                    <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5">
                                    <dt class="text-sm font-medium text-gray-500 uppercase tracking-wide">
                                        Memiliki JKN
                                    </dt>
                                    <dd class="mt-1 text-2xl font-bold text-gray-900">
                                        {{ number_format($jknStats['jkn_count']) }}
                                    </dd>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Card Persentase JKN -->
                    <div class="bg-white overflow-hidden shadow-md rounded-xl border border-blue-100 transition-all duration-300 hover:shadow-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg p-3 shadow-md">
                                    <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                                    </svg>
                                </div>
                                <div class="ml-5">
                                    <dt class="text-sm font-medium text-gray-500 uppercase tracking-wide">
                                        Persentase JKN
                                    </dt>
                                    <dd class="mt-1 text-2xl font-bold text-gray-900">
                                        {{ number_format($jknStats['jkn_percentage'], 1) }}%
                                    </dd>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- JKN by Village Table dengan design yang lebih modern -->
                <div class="mb-3 flex items-center">
                    <h4 class="text-md font-semibold text-gray-800">Detail JKN per Desa</h4>
                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ count($jknByVillage) }} Desa
                    </span>
                </div>
                
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Desa</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Penduduk</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Memiliki JKN</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Persentase</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($jknByVillage as $village)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $village['name'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($village['members']) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="text-sm text-gray-900 font-medium">{{ number_format($village['jkn_count']) }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="text-sm text-gray-900 font-medium">{{ number_format($village['jkn_percentage'], 1) }}%</div>
                                            <div class="ml-3 w-24 bg-gray-200 rounded-full h-2.5">
                                                <div class="bg-green-600 h-2.5 rounded-full" style="width: {{ $village['jkn_percentage'] }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Empty state saat data disembunyikan -->
    <div 
        x-data="{ visible: false }" 
        @toggle-jkn.window="visible = !$event.detail.show" 
        x-show="visible" 
        x-transition:enter="transition ease-out duration-300" 
        x-transition:enter-start="opacity-0" 
        x-transition:enter-end="opacity-100" 
        x-transition:leave="transition ease-in duration-200" 
        x-transition:leave-start="opacity-100" 
        x-transition:leave-end="opacity-0" 
        class="py-12 flex flex-col items-center justify-center text-gray-500 bg-gray-50 rounded-xl shadow-md"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-4 text-blue-500 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <p class="text-center font-medium text-gray-600">Data Sebaran JKN per Desa disembunyikan</p>
        <p class="text-center text-sm mt-2">Klik tombol "Tampilkan Data" untuk melihat informasi</p>
        <button 
            @click="$dispatch('toggle-jkn', { show: true })"
            class="mt-6 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            Tampilkan Data
        </button>
    </div>
</div>

<!-- Visualisasi JKN dengan tampilan yang lebih modern -->
{{-- <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 mb-10">
    <!-- Header dengan toggle button untuk chart JKN -->
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg md:text-xl font-semibold text-gray-900">Visualisasi JKN</h3>
        
        <!-- Toggle button dengan desain yang lebih modern -->
        <button 
            x-data="{ show: true }" 
            @click="show = !show; $dispatch('toggle-jkn-chart', { show })" 
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path x-show="show" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                <path x-show="!show" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span x-text="show ? 'Sembunyikan Grafik' : 'Tampilkan Grafik'">Sembunyikan Grafik</span>
        </button>
    </div>
    
    <!-- Chart container dengan toggle functionality -->
    <div 
        x-data="{ visible: true }" 
        @toggle-jkn-chart.window="visible = $event.detail.show" 
        x-show="visible" 
        x-transition:enter="transition ease-out duration-300" 
        x-transition:enter-start="opacity-0 transform scale-95" 
        x-transition:enter-end="opacity-100 transform scale-100" 
        x-transition:leave="transition ease-in duration-200" 
        x-transition:leave-start="opacity-100 transform scale-100" 
        x-transition:leave-end="opacity-0 transform scale-95"
    >
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Bar Chart - JKN by Village dengan tampilan yang lebih modern -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100 transition-all duration-300 hover:shadow-xl">
                <div class="p-5 sm:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Jumlah JKN per Desa</h3>
                        <div class="flex items-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Bar Chart
                            </span>
                        </div>
                    </div>
                    <div class="mt-3 h-64 sm:h-72">
                        <canvas id="jknByVillageChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Pie Chart - JKN Overview dengan tampilan yang lebih modern -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100 transition-all duration-300 hover:shadow-xl">
                <div class="p-5 sm:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Persentase Kepemilikan JKN</h3>
                        <div class="flex items-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Pie Chart
                            </span>
                        </div>
                    </div>
                    <div class="mt-3 h-64 sm:h-72">
                        <canvas id="jknOverviewChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Empty state saat chart disembunyikan -->
    <div 
        x-data="{ visible: false }" 
        @toggle-jkn-chart.window="visible = !$event.detail.show" 
        x-show="visible" 
        x-transition:enter="transition ease-out duration-300" 
        x-transition:enter-start="opacity-0" 
        x-transition:enter-end="opacity-100" 
        x-transition:leave="transition ease-in duration-200" 
        x-transition:leave-start="opacity-100" 
        x-transition:leave-end="opacity-0" 
        class="py-12 flex flex-col items-center justify-center text-gray-500 bg-gray-50 rounded-xl shadow-md"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-4 text-green-500 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
        </svg>
        <p class="text-center font-medium text-gray-600">Grafik Visualisasi JKN disembunyikan</p>
        <p class="text-center text-sm mt-2">Klik tombol "Tampilkan Grafik" untuk melihat visualisasi data</p>
        <button 
            @click="$dispatch('toggle-jkn-chart', { show: true })"
            class="mt-6 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Tampilkan Grafik
        </button>
    </div>
</div> --}}