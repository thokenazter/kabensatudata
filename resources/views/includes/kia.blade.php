<div class="max-w-7xl mx-auto px-4 pt-8 sm:px-6 lg:px-8">
    <!-- Header with toggle button -->
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg md:text-xl font-semibold text-gray-900">Ringkasan Statistik KIA</h3>
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
            {{-- ini yang baru pake slug --}}
            <!-- Card KB yang dimodifikasi -->
            <div class="bg-white rounded-xl shadow-lg transition-all duration-300 hover:shadow-xl border border-gray-100">
                <!-- Penggunaan padding yang lebih baik untuk responsivitas -->
                <div class="p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center">
                        <!-- Icon dengan ukuran dan warna yang lebih modern -->
                        <div class="flex-shrink-0 bg-gradient-to-br bg-pink-300 rounded-lg p-3 shadow-md">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M12 3a5 5 0 1 0 0 10 5 5 0 0 0 0-10zM12 15v6M9 18h6" />
                            </svg>
                        </div>
                        
                        <!-- Konten utama dengan spacing yang lebih baik -->
                        <div class="mt-4 sm:mt-0 sm:ml-5 w-full">
                            <dl>
                                <!-- Label dengan typography yang lebih modern -->
                                <dt class="text-sm font-semibold text-gray-600 tracking-wide uppercase">
                                    Menggunakan KB
                                </dt>
                                
                                <!-- Konten dengan desain yang lebih elegan -->
                                <dd class="group relative">
                                    <div class="flex items-center mt-1">
                                        <!-- Angka dengan ukuran dan font weight yang lebih baik -->
                                        <div class="text-2xl font-bold text-gray-900">
                                            @auth
                                                {{ number_format($maternalStats['kb_count']) }}
                                            @else
                                                <span class="blur-sm">{{ number_format($maternalStats['kb_count']) }}</span>
                                            @endauth
                                        </div>
                                        
                                        <!-- Call to action dengan desain yang lebih jelas -->
                                        <span class="ml-2 text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors duration-200 flex items-center cursor-pointer">
                                            Lihat detail
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </span>
                                    </div>

                                    <!-- Tooltip yang lebih modern dan responsif -->
                                    <div class="hidden group-hover:block absolute z-50 mt-2 w-full sm:w-96 tright-0 sm:right-0 transform sm:origin-top-right">
                                        <div class="bg-white rounded-lg shadow-xl border border-gray-200 p-4 max-h-96 overflow-y-auto">
                                            <!-- Header tooltip -->
                                            <div class="pb-3 mb-3 border-b border-gray-200">
                                                <h3 class="text-lg font-semibold text-gray-900">Detail Pengguna KB ({{ $kbCases ? $kbCases->count() : 0 }} orang)</h3>
                                            </div>
                                            
                                            <!-- Daftar kasus -->
                                            <div class="space-y-4">
                                                @if($kbCases)
                                                    @foreach($kbCases as $case)
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
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Tidak KB yang dimodifikasi -->
            {{-- <div class="bg-white rounded-xl shadow-lg transition-all duration-300 hover:shadow-xl border border-gray-100">
                <!-- Penggunaan padding yang lebih baik untuk responsivitas -->
                <div class="p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center">
                        <!-- Icon dengan ukuran dan warna yang lebih modern -->
                        <div class="flex-shrink-0 bg-gradient-to-br bg-pink-800 rounded-lg p-3 shadow-md">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M12 3a5 5 0 1 0 0 10 5 5 0 0 0 0-10zM12 15v6M9 18h6M4 4l16 16" />
                            </svg>
                        </div>
                        
                        <!-- Konten utama dengan spacing yang lebih baik -->
                        <div class="mt-4 sm:mt-0 sm:ml-5 w-full">
                            <dl>
                                <!-- Label dengan typography yang lebih modern -->
                                <dt class="text-sm font-semibold text-gray-600 tracking-wide uppercase">
                                    Tidak KB
                                </dt>
                                
                                <!-- Konten dengan desain yang lebih elegan -->
                                <dd class="group relative">
                                    <div class="flex items-center mt-1">
                                        <!-- Angka dengan ukuran dan font weight yang lebih baik -->
                                        <div class="text-2xl font-bold text-gray-900">
                                            @auth
                                            {{ number_format($maternalStats['no_kb_count']) }}
                                            @else
                                                <span class="blur-sm">{{ number_format($maternalStats['no_kb_count']) }}</span>
                                            @endauth
                                        </div>
                                        
                                        <!-- Call to action dengan desain yang lebih jelas -->
                                        <span class="ml-2 text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors duration-200 flex items-center cursor-pointer">
                                            Lihat detail
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </span>
                                    </div>

                                    <!-- Tooltip yang lebih modern dan responsif -->
                                    <div class="hidden group-hover:block absolute z-50 mt-2 w-full sm:w-96 tright-0 sm:right-0 transform sm:origin-top-right">
                                        <div class="bg-white rounded-lg shadow-xl border border-gray-200 p-4 max-h-96 overflow-y-auto">
                                            <!-- Header tooltip -->
                                            <div class="pb-3 mb-3 border-b border-gray-200">
                                                <h3 class="text-lg font-semibold text-gray-900">Detail Tidak KB ({{ $noKbCases ? $noKbCases->count() : 0 }} orang)</h3>
                                                <h4 class="text-red-600 text-sm font-sans">Catatan : Data Berdasarkan Form Pendataan PISPK</h4>
                                            </div>
                                            
                                            <!-- Daftar kasus -->
                                            <div class="space-y-4">
                                                @if($noKbCases)
                                                    @foreach($noKbCases as $case)
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
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div> --}}

            <!-- Card Bumil -->
            <div class="bg-white rounded-xl shadow-lg transition-all duration-300 hover:shadow-xl border border-gray-100">
                <!-- Penggunaan padding yang lebih baik untuk responsivitas -->
                <div class="p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center">
                        <!-- Icon dengan ukuran dan warna yang lebih modern -->
                        <div class="flex-shrink-0 bg-gradient-to-br bg-pink-600 rounded-lg p-3 shadow-md">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 5a2 2 0 1 0 0-4 2 2 0 0 0 0 4zM9 18c0 2 1.5 4 3 4s3-2 3-4M8 10c0-2 2-3 4-3s4 1 4 3c0 3-2 6-4 8.5C10 16 8 13 8 10z" />
                            </svg>
                        </div>
                        
                        <!-- Konten utama dengan spacing yang lebih baik -->
                        <div class="mt-4 sm:mt-0 sm:ml-5 w-full">
                            <dl>
                                <!-- Label dengan typography yang lebih modern -->
                                <dt class="text-sm font-semibold text-gray-600 tracking-wide uppercase">
                                    BUMIL
                                </dt>
                                
                                <!-- Konten dengan desain yang lebih elegan -->
                                <dd class="group relative">
                                    <div class="flex items-center mt-1">
                                        <!-- Angka dengan ukuran dan font weight yang lebih baik -->
                                        <div class="text-2xl font-bold text-gray-900">
                                            @auth
                                            {{ number_format($maternalStats['pregnant_count']) }}
                                            @else
                                                <span class="blur-sm">{{ number_format($maternalStats['pregnant_count']) }}</span>
                                            @endauth
                                        </div>
                                        
                                        <!-- Call to action dengan desain yang lebih jelas -->
                                        <span class="ml-2 text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors duration-200 flex items-center cursor-pointer">
                                            Lihat detail
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </span>
                                    </div>

                                    <!-- Tooltip yang lebih modern dan responsif -->
                                    <div class="hidden group-hover:block absolute z-50 mt-2 w-full sm:w-96 tright-0 sm:right-0 transform sm:origin-top-right">
                                        <div class="bg-white rounded-lg shadow-xl border border-gray-200 p-4 max-h-96 overflow-y-auto">
                                            <!-- Header tooltip -->
                                            <div class="pb-3 mb-3 border-b border-gray-200">
                                                <h3 class="text-lg font-semibold text-gray-900">Detail BUMIL ({{ $pregnancyCases ? $pregnancyCases->count() : 0 }} orang)</h3>
                                            </div>
                                            
                                            <!-- Daftar kasus -->
                                            <div class="space-y-4">
                                                @if($pregnancyCases)
                                                    @foreach($pregnancyCases as $case)
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
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Bersaling di Faskes -->
            <div class="bg-white rounded-xl shadow-lg transition-all duration-300 hover:shadow-xl border border-gray-100">
                <!-- Penggunaan padding yang lebih baik untuk responsivitas -->
                <div class="p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center">
                        <!-- Icon dengan ukuran dan warna yang lebih modern -->
                        <div class="flex-shrink-0 bg-gradient-to-br bg-green-600 rounded-lg p-3 shadow-md">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 9h8M12 7v4M9 15c0 1.5 1.5 3 3 3s3-1.5 3-3" />
                            </svg>
                        </div>
                        
                        <!-- Konten utama dengan spacing yang lebih baik -->
                        <div class="mt-4 sm:mt-0 sm:ml-5 w-full">
                            <dl>
                                <!-- Label dengan typography yang lebih modern -->
                                <dt class="text-sm font-semibold text-gray-600 tracking-wide uppercase">
                                    Bersalin diFaskes
                                </dt>
                                
                                <!-- Konten dengan desain yang lebih elegan -->
                                <dd class="group relative">
                                    <div class="flex items-center mt-1">
                                        <!-- Angka dengan ukuran dan font weight yang lebih baik -->
                                        <div class="text-2xl font-bold text-gray-900">
                                            @auth
                                            {{ number_format($maternalStats['health_facility_birth_count']) }}
                                            @else
                                                <span class="blur-sm">{{ number_format($maternalStats['health_facility_birth_count']) }}</span>
                                            @endauth
                                        </div>
                                        
                                        <!-- Call to action dengan desain yang lebih jelas -->
                                        <span class="ml-2 text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors duration-200 flex items-center cursor-pointer">
                                            Lihat detail
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </span>
                                    </div>

                                    <!-- Tooltip yang lebih modern dan responsif -->
                                    <div class="hidden group-hover:block absolute z-50 mt-2 w-full sm:w-96 tright-0 sm:right-0 transform sm:origin-top-right">
                                        <div class="bg-white rounded-lg shadow-xl border border-gray-200 p-4 max-h-96 overflow-y-auto">
                                            <!-- Header tooltip -->
                                            <div class="pb-3 mb-3 border-b border-gray-200">
                                                <h3 class="text-lg font-semibold text-gray-900">Detail ({{ $healthFacilityBirthCases ? $healthFacilityBirthCases->count() : 0 }} orang)</h3>
                                            </div>
                                            
                                            <!-- Daftar kasus -->
                                            <div class="space-y-4">
                                                @if($healthFacilityBirthCases)
                                                    @foreach($healthFacilityBirthCases as $case)
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
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Anak --}}
            <!-- ASI Ekslusif -->
            <div class="bg-white rounded-xl shadow-lg transition-all duration-300 hover:shadow-xl border border-gray-100">
                <!-- Penggunaan padding yang lebih baik untuk responsivitas -->
                <div class="p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center">
                        <!-- Icon dengan ukuran dan warna yang lebih modern -->
                        <div class="flex-shrink-0 bg-gradient-to-br bg-gray-400 rounded-lg p-3 shadow-md">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.5c-2.5 0-4 2-4 4 0 3 4 6 4 6s4-3 4-6c0-2-1.5-4-4-4z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8.5a1 1 0 100-2 1 1 0 000 2z"/>
                            </svg>
                        </div>
                        
                        <!-- Konten utama dengan spacing yang lebih baik -->
                        <div class="mt-4 sm:mt-0 sm:ml-5 w-full">
                            <dl>
                                <!-- Label dengan typography yang lebih modern -->
                                <dt class="text-sm font-semibold text-gray-600 tracking-wide uppercase">
                                    ASI Ekslusif (Saat Usia 0-6 Bulan)
                                </dt>
                                
                                <!-- Konten dengan desain yang lebih elegan -->
                                <dd class="group relative">
                                    <div class="flex items-center mt-1">
                                        <!-- Angka dengan ukuran dan font weight yang lebih baik -->
                                        <div class="text-2xl font-bold text-gray-900">
                                            @auth
                                            {{ number_format($childStats['exclusive_breastfeeding_count']) }}
                                            @else
                                                <span class="blur-sm">{{ number_format($childStats['exclusive_breastfeeding_count']) }}</span>
                                            @endauth
                                        </div>
                                        
                                        <!-- Call to action dengan desain yang lebih jelas -->
                                        <span class="ml-2 text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors duration-200 flex items-center cursor-pointer">
                                            Lihat detail
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </span>
                                    </div>

                                    <!-- Tooltip yang lebih modern dan responsif -->
                                    <div class="hidden group-hover:block absolute z-50 mt-2 w-full sm:w-96 tright-0 sm:right-0 transform sm:origin-top-right">
                                        <div class="bg-white rounded-lg shadow-xl border border-gray-200 p-4 max-h-96 overflow-y-auto">
                                            <!-- Header tooltip -->
                                            <div class="pb-3 mb-3 border-b border-gray-200">
                                                <h3 class="text-lg font-semibold text-gray-900">Detail ({{ $exclusiveBreastfeedingCases ? $exclusiveBreastfeedingCases->count() : 0 }} anak)</h3>
                                            </div>
                                            
                                            <!-- Daftar kasus -->
                                            <div class="space-y-4">
                                                @if($exclusiveBreastfeedingCases)
                                                    @foreach($exclusiveBreastfeedingCases as $case)
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
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Imunisasi Lengkap -->
            <div class="bg-white rounded-xl shadow-lg transition-all duration-300 hover:shadow-xl border border-gray-100">
                <!-- Penggunaan padding yang lebih baik untuk responsivitas -->
                <div class="p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center">
                        <!-- Icon dengan ukuran dan warna yang lebih modern -->
                        <div class="flex-shrink-0 bg-gradient-to-br bg-green-500 rounded-lg p-3 shadow-md">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" 
                                stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        
                        <!-- Konten utama dengan spacing yang lebih baik -->
                        <div class="mt-4 sm:mt-0 sm:ml-5 w-full">
                            <dl>
                                <!-- Label dengan typography yang lebih modern -->
                                <dt class="text-sm font-semibold text-gray-600 tracking-wide uppercase">
                                    Imunisasi Lengkap (umur 12-23 bulan)
                                </dt>
                                
                                <!-- Konten dengan desain yang lebih elegan -->
                                <dd class="group relative">
                                    <div class="flex items-center mt-1">
                                        <!-- Angka dengan ukuran dan font weight yang lebih baik -->
                                        <div class="text-2xl font-bold text-gray-900">
                                            @auth
                                            {{ number_format($childStats['complete_immunization_count']) }}
                                            @else
                                                <span class="blur-sm">{{ number_format($childStats['complete_immunization_count']) }}</span>
                                            @endauth
                                        </div>
                                        
                                        <!-- Call to action dengan desain yang lebih jelas -->
                                        <span class="ml-2 text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors duration-200 flex items-center cursor-pointer">
                                            Lihat detail
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </span>
                                    </div>

                                    <!-- Tooltip yang lebih modern dan responsif -->
                                    <div class="hidden group-hover:block absolute z-50 mt-2 w-full sm:w-96 tright-0 sm:right-0 transform sm:origin-top-right">
                                        <div class="bg-white rounded-lg shadow-xl border border-gray-200 p-4 max-h-96 overflow-y-auto">
                                            <!-- Header tooltip -->
                                            <div class="pb-3 mb-3 border-b border-gray-200">
                                                <h3 class="text-lg font-semibold text-gray-900">Detail Imunisasi Lengkap ({{ $completeImmunizationCases ? $completeImmunizationCases->count() : 0 }} anak)</h3>
                                            </div>
                                            
                                            <!-- Daftar kasus -->
                                            <div class="space-y-4">
                                                @if($completeImmunizationCases)
                                                    @foreach($completeImmunizationCases as $case)
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
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pemantauan Pertumbuhan -->
            <div class="bg-white rounded-xl shadow-lg transition-all duration-300 hover:shadow-xl border border-gray-100">
                <!-- Penggunaan padding yang lebih baik untuk responsivitas -->
                <div class="p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center">
                        <!-- Icon dengan ukuran dan warna yang lebih modern -->
                        <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        
                        <!-- Konten utama dengan spacing yang lebih baik -->
                        <div class="mt-4 sm:mt-0 sm:ml-5 w-full">
                            <dl>
                                <!-- Label dengan typography yang lebih modern -->
                                <dt class="text-sm font-semibold text-gray-600 tracking-wide uppercase">
                                    Pemantauan 1 Bulan Terakhir (umur 2-59 bulan)
                                </dt>
                                
                                <!-- Konten dengan desain yang lebih elegan -->
                                <dd class="group relative">
                                    <div class="flex items-center mt-1">
                                        <!-- Angka dengan ukuran dan font weight yang lebih baik -->
                                        <div class="text-2xl font-bold text-gray-900">
                                            @auth
                                            {{ number_format($childStats['growth_monitoring_count']) }}
                                            @else
                                                <span class="blur-sm">{{ number_format($childStats['growth_monitoring_count']) }}</span>
                                            @endauth
                                        </div>
                                        
                                        <!-- Call to action dengan desain yang lebih jelas -->
                                        <span class="ml-2 text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors duration-200 flex items-center cursor-pointer">
                                            Lihat detail
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </span>
                                    </div>

                                    <!-- Tooltip yang lebih modern dan responsif -->
                                    <div class="hidden group-hover:block absolute z-50 mt-2 w-full sm:w-96 tright-0 sm:right-0 transform sm:origin-top-right">
                                        <div class="bg-white rounded-lg shadow-xl border border-gray-200 p-4 max-h-96 overflow-y-auto">
                                            <!-- Header tooltip -->
                                            <div class="pb-3 mb-3 border-b border-gray-200">
                                                <h3 class="text-lg font-semibold text-gray-900">Detail Pemantauan Pertumbuhan ({{ $growthMonitoringCases ? $growthMonitoringCases->count() : 0 }} anak)</h3>
                                            </div>
                                            
                                            <!-- Daftar kasus -->
                                            <div class="space-y-4">
                                                @if($growthMonitoringCases)
    @foreach($growthMonitoringCases as $case)
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
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            {{-- ini yang baru pake slug --}}

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