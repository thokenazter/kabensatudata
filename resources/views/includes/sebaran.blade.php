<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <!-- Header with toggle button -->
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Sebaran Sanitasi per Desa</h3>
                
                <!-- Toggle button -->
                <button 
                    x-data="{ show: true }" 
                    @click="show = !show; $dispatch('toggle-table', { show })" 
                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    <span x-text="show ? 'Sembunyikan Tabel' : 'Tampilkan Tabel'">Sembunyikan Tabel</span>
                </button>
            </div>
            
            <!-- Table container -->
            <div x-data="{ visible: true }" @toggle-table.window="visible = $event.detail.show" x-show="visible" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Desa</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Keluarga</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Air Bersih</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Air Terlindungi</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jamban</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jamban Saniter</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($sanitationByVillage as $village)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $village['name'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $village['total_families'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="text-sm text-gray-900">{{ $village['clean_water_count'] }}</div>
                                        <div class="ml-2 text-sm text-gray-500">({{ number_format($village['clean_water_percentage'], 1) }}%)</div>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $village['clean_water_percentage'] }}%"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="text-sm text-gray-900">{{ $village['protected_water_count'] }}</div>
                                        <div class="ml-2 text-sm text-gray-500">({{ number_format($village['protected_water_percentage'], 1) }}%)</div>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ $village['protected_water_percentage'] }}%"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="text-sm text-gray-900">{{ $village['toilet_count'] }}</div>
                                        <div class="ml-2 text-sm text-gray-500">({{ number_format($village['toilet_percentage'], 1) }}%)</div>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $village['toilet_percentage'] }}%"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="text-sm text-gray-900">{{ $village['sanitary_toilet_count'] }}</div>
                                        <div class="ml-2 text-sm text-gray-500">({{ number_format($village['sanitary_toilet_percentage'], 1) }}%)</div>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-purple-600 h-2 rounded-full" style="width: {{ $village['sanitary_toilet_percentage'] }}%"></div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Empty state when table is hidden -->
            <div x-data="{ visible: false }" @toggle-table.window="visible = !$event.detail.show" x-show="visible" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="py-12 flex flex-col items-center justify-center text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                </svg>
                <p class="text-center">Tabel sebaran sanitasi disembunyikan.</p>
                <p class="text-center text-sm">Klik tombol "Tampilkan Tabel" untuk melihat data.</p>
            </div>
        </div>
    </div>
</div>