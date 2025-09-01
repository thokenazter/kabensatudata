<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
    <!-- Header dengan toggle button -->
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-medium text-gray-900">Statistik Air Bersih & Sanitasi</h2>
        
        <!-- Toggle button -->
        <button 
            x-data="{ show: true }" 
            @click="show = !show; $dispatch('toggle-sanitation', { show })" 
            class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
            <span x-text="show ? 'Sembunyikan Data' : 'Tampilkan Data'">Sembunyikan Data</span>
        </button>
    </div>
    
    <!-- Cards container dengan toggle functionality -->
    <div 
        x-data="{ visible: true }" 
        @toggle-sanitation.window="visible = $event.detail.show" 
        x-show="visible" 
        x-transition:enter="transition ease-out duration-300" 
        x-transition:enter-start="opacity-0 transform scale-95" 
        x-transition:enter-end="opacity-100 transform scale-100" 
        x-transition:leave="transition ease-in duration-200" 
        x-transition:leave-start="opacity-100 transform scale-100" 
        x-transition:leave-end="opacity-0 transform scale-95"
    >
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Air Bersih Card -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Keluarga dengan Air Bersih
                                </dt>
                                <dd>
                                    <div class="text-lg font-medium text-gray-900">
                                        {{ number_format($sanitationStats['clean_water_count']) }}
                                    </div>
                                    <span class="ml-2 text-sm text-gray-500">
                                        {{ number_format($sanitationStats['clean_water_percentage'], 1) }}% dari total keluarga
                                    </span>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Air Terlindungi Card -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Sumber Air Terlindungi
                                </dt>
                                <dd>
                                    <div class="text-lg font-medium text-gray-900">
                                        {{ number_format($sanitationStats['protected_water_count']) }}
                                    </div>
                                    <span class="ml-2 text-sm text-gray-500">
                                        {{ number_format($sanitationStats['protected_water_percentage'], 1) }}% dari air bersih
                                    </span>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Jamban Card -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Keluarga dengan Jamban
                                </dt>
                                <dd>
                                    <div class="text-lg font-medium text-gray-900">
                                        {{ number_format($sanitationStats['toilet_count']) }}
                                    </div>
                                    <span class="ml-2 text-sm text-gray-500">
                                        {{ number_format($sanitationStats['toilet_percentage'], 1) }}% dari total keluarga
                                    </span>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Jamban Saniter Card -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Jamban Saniter
                                </dt>
                                <dd>
                                    <div class="text-lg font-medium text-gray-900">
                                        {{ number_format($sanitationStats['sanitary_toilet_count']) }}
                                    </div>
                                    <span class="ml-2 text-sm text-gray-500">
                                        {{ number_format($sanitationStats['sanitary_toilet_percentage'], 1) }}% dari total jamban
                                    </span>
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
        @toggle-sanitation.window="visible = !$event.detail.show" 
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
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
        </svg>
        <p class="text-center">Data Statistik Air Bersih & Sanitasi disembunyikan.</p>
        <p class="text-center text-sm">Klik tombol "Tampilkan Data" untuk melihat informasi.</p>
    </div>
</div>