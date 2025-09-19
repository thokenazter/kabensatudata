@php
    $canViewSensitiveHealth = auth()->check() && auth()->user()->hasAnyRole(['nakes', 'super_admin']);
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header with toggle button -->
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg md:text-xl font-semibold text-gray-900">Ringkasan Statistik</h3>
        
        <!-- Toggle button -->
        <button 
            x-data="{ show: true }" 
            @click="show = !show; $dispatch('toggle-cards', { show })" 
            class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
            <span x-text="show ? 'Sembunyikan Cards' : 'Tampilkan Cards'">Sembunyikan Cards</span>
        </button>
    </div>

    <!-- Cards container with toggle functionality -->
    <div 
        x-data="{ visible: true }" 
        @toggle-cards.window="visible = $event.detail.show" 
        x-show="visible" 
        x-transition:enter="transition ease-out duration-300" 
        x-transition:enter-start="opacity-0 transform scale-95" 
        x-transition:enter-end="opacity-100 transform scale-100" 
        x-transition:leave="transition ease-in duration-200" 
        x-transition:leave-start="opacity-100 transform scale-100" 
        x-transition:leave-end="opacity-0 transform scale-95"
    >
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Penduduk -->
            <div class="bg-white rounded-xl shadow-lg transition-all duration-300 hover:shadow-xl border border-gray-100">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-semibold text-gray-600 tracking-wide uppercase">
                                    Total Penduduk
                                </dt>
                                <dd>
                                    <div class="text-2xl font-bold text-gray-900">
                                        {{ number_format($stats['members']) }}
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Keluarga -->
            <div class="bg-white rounded-xl shadow-lg transition-all duration-300 hover:shadow-xl border border-gray-100">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-semibold text-gray-600 tracking-wide uppercase">
                                    Total Keluarga
                                </dt>
                                <dd>
                                    <div class="text-2xl font-bold text-gray-900">
                                        {{ number_format($stats['families']) }}
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Kasus TBC yang dimodifikasi -->
            <div class="bg-white rounded-xl shadow-lg transition-all duration-300 hover:shadow-xl border border-gray-100">
                <!-- Penggunaan padding yang lebih baik untuk responsivitas -->
                <div class="p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center">
                        <!-- Icon dengan ukuran dan warna yang lebih modern -->
                        <div class="flex-shrink-0 bg-gradient-to-br from-red-500 to-red-600 rounded-lg p-3 shadow-md">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        
                        <!-- Konten utama dengan spacing yang lebih baik -->
                        <div class="mt-4 sm:mt-0 sm:ml-5 w-full">
                            <dl>
                                <!-- Label dengan typography yang lebih modern -->
                                <dt class="text-sm font-semibold text-gray-600 tracking-wide uppercase">
                                    Pernah TBC
                                </dt>
                                
                                <!-- Konten dengan desain yang lebih elegan -->
                                <dd class="group relative">
                                    <div class="flex items-center mt-1">
                                        <!-- Angka dengan ukuran dan font weight yang lebih baik -->
                                        <div class="text-2xl font-bold text-gray-900">
                                            @if($canViewSensitiveHealth)
                                                {{ number_format($stats['tbc_count']) }}
                                            @else
                                                <span class="text-sm font-medium text-gray-400 italic">Khusus tenaga kesehatan</span>
                                            @endif
                                        </div>
                                        
                                        <!-- Call to action dengan desain yang lebih jelas -->
                                        @if($canViewSensitiveHealth)
                                            <span class="ml-2 text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors duration-200 flex items-center cursor-pointer">
                                                Lihat detail
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </span>
                                        @endif
                                    </div>

                                    @if($canViewSensitiveHealth)
                                    <!-- Tooltip yang lebih modern dan responsif -->
                                    <div class="hidden group-hover:block absolute z-50 mt-2 w-full sm:w-96 tright-0 sm:right-0 transform sm:origin-top-right">
                                        <div class="bg-white rounded-lg shadow-xl border border-gray-200 p-4 max-h-96 overflow-y-auto">
                                            <!-- Header tooltip -->
                                            <div class="pb-3 mb-3 border-b border-gray-200">
                                                <h3 class="text-lg font-semibold text-gray-900">Detail Kasus TBC ({{ $tbcCases ? $tbcCases->count() : 0 }} orang)</h3>
                                            </div>
                                            
                                            <!-- Daftar kasus -->
                                            <div class="space-y-4">
                                                @if($tbcCases)
                                                    @foreach($tbcCases as $case)
                                                        <div class="p-3 rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors duration-200">
                                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                                <div class="col-span-1 sm:col-span-2">
                                                                    <strong>Nama:</strong> 
                                                                    @auth 
                                                                        <a href="{{ route('family-members.show', $case) }}" class="text-blue-600 hover:text-blue-800 hover:underline font-medium">
                                                                            {{ $case->name }}
                                                                        </a> 
                                                                    @else 
                                                                        <span class="blur-sm">[Login Sebagai Staf Puskesmas]</span> 
                                                                    @endauth
                                                                </div>
                                                                
                                                                <div class="col-span-1 sm:col-span-2">
                                                                    <strong>Status Pengobatan:</strong> 
                                                                    @auth
                                                                        @if($case->takes_tb_medication_regularly == 1)
                                                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                                <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                                                </svg>
                                                                                Minum Obat Teratur
                                                                            </span>
                                                                        @else
                                                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                                                <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                                                </svg>
                                                                                Mangkir Obat
                                                                            </span>
                                                                        @endif
                                                                    @else
                                                                        <span class="blur-sm">[********]</span>
                                                                    @endauth
                                                                </div>
                                                                
                                                                <div><strong>Desa:</strong> @auth {{ $case->family->building->village->name ?? $case->family->village->name ?? 'Tidak tersedia' }} @else <span class="blur-sm">[********]</span> @endauth</div>
                                                                <div><strong>No. Bangunan:</strong> @auth {{ $case->family->building->building_number ?? 'Tidak tersedia' }} @else <span class="blur-sm">[********]</span> @endauth</div>
                                                                <div><strong>Umur:</strong> @auth {{ $case->age }} tahun @else <span class="blur-sm">[********]</span> @endauth</div>
                                                                
                                                                <!-- Tombol Detail -->
                                                                <div class="col-span-1 sm:col-span-2 mt-2">
                                                                    @auth
                                                                        <a href="{{ route('family-members.show', $case) }}" class="inline-flex items-center px-3 py-1.5 border border-blue-600 text-xs font-medium rounded-md text-blue-600 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                                            </svg>
                                                                            Lihat Profil Lengkap
                                                                        </a>
                                                                    @endauth
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                            
                                            <!-- Tidak ada pagination -->
                                        </div>
                                    </div>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Kasus Hipertensi yang dimodifikasi -->
            <div class="bg-white rounded-xl shadow-lg transition-all duration-300 hover:shadow-xl border border-gray-100">
                <!-- Penggunaan padding yang lebih baik untuk responsivitas -->
                <div class="p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center">
                        <!-- Icon dengan ukuran dan warna yang lebih modern -->
                        <div class="flex-shrink-0 bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-lg p-3 shadow-md">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        
                        <!-- Konten utama dengan spacing yang lebih baik -->
                        <div class="mt-4 sm:mt-0 sm:ml-5 w-full">
                            <dl>
                                <!-- Label dengan typography yang lebih modern -->
                                <dt class="text-sm font-semibold text-gray-600 tracking-wide uppercase">
                                    Hipertensi
                                </dt>
                                
                                <!-- Konten dengan desain yang lebih elegan -->
                                <dd class="group relative">
                                    <div class="flex items-center mt-1">
                                        <!-- Angka dengan ukuran dan font weight yang lebih baik -->
                                        <div class="text-2xl font-bold text-gray-900">
                                            @if($canViewSensitiveHealth)
                                                {{ number_format($stats['hypertension_count']) }}
                                            @else
                                                <span class="text-sm font-medium text-gray-400 italic">Khusus tenaga kesehatan</span>
                                            @endif
                                        </div>
                                        
                                        <!-- Call to action dengan desain yang lebih jelas -->
                                        @if($canViewSensitiveHealth)
                                            <span class="ml-2 text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors duration-200 flex items-center cursor-pointer">
                                                Lihat detail
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </span>
                                        @endif
                                    </div>

                                    @if($canViewSensitiveHealth)
                                    <!-- Tooltip yang lebih modern dan responsif dengan posisi yang diperbaiki -->
                                    <div class="hidden group-hover:block absolute z-50 mt-2 w-full sm:w-96 right-0 sm:right-0 transform sm:origin-top-right">
                                        <div class="bg-white rounded-lg shadow-xl border border-gray-200 p-4 max-h-96 overflow-y-auto">
                                            <!-- Header tooltip -->
                                            <div class="pb-3 mb-3 border-b border-gray-200">
                                                <h3 class="text-lg font-semibold text-gray-900">Detail Kasus Hipertensi ({{ $hypertensionCases ? $hypertensionCases->count() : 0 }} orang)</h3>
                                            </div>
                                            
                                            <!-- Daftar kasus -->
                                            <div class="space-y-4">
                                                @if($hypertensionCases)
                                                    @foreach($hypertensionCases as $case)
                                                        <div class="p-3 rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors duration-200">
                                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                                <div class="col-span-1 sm:col-span-2">
                                                                    <strong>Nama:</strong> 
                                                                    @auth 
                                                                        <a href="{{ route('family-members.show', $case) }}" class="text-blue-600 hover:text-blue-800 hover:underline font-medium">
                                                                            {{ $case->name }}
                                                                        </a>
                                                                    @else 
                                                                        <span class="blur-sm">[Login Sebagai Staf]</span> 
                                                                    @endauth
                                                                </div>
                                                                
                                                                <div class="col-span-1 sm:col-span-2">
                                                                    <strong>Status Pengobatan Darah Tinggi:</strong> 
                                                                    @auth
                                                                        @if($case->takes_hypertension_medication_regularly == 1)
                                                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                                <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                                                </svg>
                                                                                Minum Obat Teratur
                                                                            </span>
                                                                        @else
                                                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                                                <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                                                </svg>
                                                                                Minum Obat Tidak Teratur
                                                                            </span>
                                                                        @endif
                                                                    @else
                                                                        <span class="blur-sm">[*********]</span>
                                                                    @endauth
                                                                </div>
                                                                
                                                                <div><strong>Desa:</strong> @auth {{ $case->family->building->village->name ?? $case->family->village->name ?? 'Tidak tersedia' }} @else <span class="blur-sm">[*********]</span> @endauth</div>
                                                                <div><strong>No. Bangunan:</strong> @auth {{ $case->family->building->building_number ?? 'Tidak tersedia' }} @else <span class="blur-sm">[*********]</span> @endauth</div>
                                                                <div><strong>Umur:</strong> @auth {{ $case->age }} tahun @else <span class="blur-sm">[*********]</span> @endauth</div>
                                                                
                                                                <!-- Tombol Detail -->
                                                                <div class="col-span-1 sm:col-span-2 mt-2">
                                                                    @auth
                                                                        <a href="{{ route('family-members.show', $case) }}" class="inline-flex items-center px-3 py-1.5 border border-blue-600 text-xs font-medium rounded-md text-blue-600 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                                            </svg>
                                                                            Lihat Profil Lengkap
                                                                        </a>
                                                                    @endauth
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                            
                                            <!-- Tidak ada pagination -->
                                        </div>
                                    </div>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>
    
    <!-- Empty state when cards are hidden -->
    <div 
        x-data="{ visible: false }" 
        @toggle-cards.window="visible = !$event.detail.show" 
        x-show="visible" 
        x-transition:enter="transition ease-out duration-300" 
        x-transition:enter-start="opacity-0" 
        x-transition:enter-end="opacity-100" 
        x-transition:leave="transition ease-in duration-200" 
        x-transition:leave-start="opacity-100" 
        x-transition:leave-end="opacity-0" 
        class="py-12 flex flex-col items-center justify-center text-gray-500 bg-gray-50 rounded-lg shadow"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <p class="text-center">Ringkasan statistik disembunyikan.</p>
        <p class="text-center text-sm">Klik tombol "Tampilkan Cards" untuk melihat data.</p>
    </div>
</div>
