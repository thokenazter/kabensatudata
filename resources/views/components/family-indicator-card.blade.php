@props(['family', 'indicators' => []])

<div class="bg-white shadow-lg rounded-lg overflow-hidden" x-data="{
    activeFilter: 'all',
    indicatorList: @js($indicators),
    
    filterIndicators(filter) {
        this.activeFilter = filter;
    },
    
    isVisible(key) {
        const indicator = this.indicatorList[key];
        
        if (this.activeFilter === 'all') return true;
        if (this.activeFilter === 'relevant' && indicator.is_relevant) return true;
        if (this.activeFilter === 'fulfilled' && indicator.is_relevant && indicator.status) return true;
        
        return false;
    },
    
    getVisibleCount() {
        return Object.keys(this.indicatorList).filter(key => {
            const indicator = this.indicatorList[key];
            
            if (this.activeFilter === 'all') return true;
            if (this.activeFilter === 'relevant' && indicator.is_relevant) return true;
            if (this.activeFilter === 'fulfilled' && indicator.is_relevant && indicator.status) return true;
            
            return false;
        }).length;
    }
}">
    <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4">
        <h3 class="text-xl font-semibold text-white">Indeks Keluarga Sehat (IKS)</h3>
        <p class="text-green-100 text-sm">Status kesehatan keluarga berdasarkan 12 indikator</p>
    </div>

    
    {{-- Login Warning Banner for Guest Users --}}
    @guest
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-yellow-700">
                    Data kesehatan disamarkan. <a href="/admin" class="font-medium underline hover:text-yellow-600">Login</a> untuk melihat informasi lengkap.
                </p>
            </div>
        </div>
    </div>
    @endguest
    
    <div class="p-6 {{ !auth()->check() ? 'relative' : '' }}">
        {{-- Blur Overlay for Guest Users --}}
        @guest
        <div class="absolute inset-0 bg-white bg-opacity-30 backdrop-blur-sm flex items-center justify-center z-10">
            <div class="text-center p-6 bg-white bg-opacity-90 rounded-lg shadow-lg max-w-md mx-auto">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-blue-500 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <h4 class="text-lg font-semibold text-gray-900 mb-2">Data Sensitif Diproteksi</h4>
                <p class="text-gray-600 mb-4">Informasi kesehatan keluarga ini diproteksi untuk menjaga privasi pasien.</p>
                <a href="/admin" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                    Login untuk Melihat
                </a>
            </div>
        </div>
        @endguest

        @if($family->healthIndex)
            <!-- IKS Summary -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-lg font-medium text-gray-900">Status Kesehatan Keluarga</h4>
                        <p class="text-sm text-gray-500">Perhitungan terakhir: {{ $family->healthIndex->calculated_at->format('d M Y, H:i') }}</p>
                    </div>
                    <div class="text-center">
                        <div class="text-xl font-bold 
                            @if($family->healthIndex->health_status == 'Sehat') text-green-600
                            @elseif($family->healthIndex->health_status == 'Pra-Sehat') text-yellow-600
                            @else text-red-600 @endif">
                            @auth
                                {{ $family->healthIndex->health_status }}
                            @else
                                ******
                            @endauth
                        </div>
                        <div class="text-2xl font-bold mt-1">
                            @auth
                                {{ number_format($family->healthIndex->iks_value * 100, 1) }}%
                            @else
                                **.**%
                            @endauth
                        </div>
                    </div>
                </div>
                
                <!-- Progress Indicators -->
                <div class="mt-4">
                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                        <span>0%</span>
                        <span>50%</span>
                        <span>100%</span>
                    </div>
                    <div class="h-3 w-full bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full 
                            @if($family->healthIndex->health_status == 'Sehat') bg-green-500
                            @elseif($family->healthIndex->health_status == 'Pra-Sehat') bg-yellow-500
                            @else bg-red-500 @endif"
                            style="width: {{ auth()->check() ? $family->healthIndex->iks_value * 100 : 0 }}%">
                        </div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                        <span>Tidak Sehat</span>
                        <span>Pra-Sehat</span>
                        <span>Sehat</span>
                    </div>
                </div>
                
                <!-- Interactive Indicator Summary -->
                <div class="mt-6 flex flex-wrap gap-2">
                    <button @click="filterIndicators('all')"
                         class="bg-gray-100 rounded-lg px-4 py-2 text-center cursor-pointer transition-all duration-200 hover:shadow-md"
                         :class="{'ring-2 ring-blue-500 bg-blue-50': activeFilter === 'all'}">
                        <span class="text-sm text-gray-500">Semua Indikator</span>
                        <div class="text-lg font-medium text-gray-900">{{ count($indicators) }}</div>
                    </button>
                    
                    <button @click="filterIndicators('relevant')"
                         class="bg-gray-100 rounded-lg px-4 py-2 text-center cursor-pointer transition-all duration-200 hover:shadow-md"
                         :class="{'ring-2 ring-blue-500 bg-blue-50': activeFilter === 'relevant'}">
                        <span class="text-sm text-gray-500">Indikator Relevan</span>
                        <div class="text-lg font-medium text-gray-900">
                            @auth
                                {{ $family->healthIndex->relevant_indicators }}
                            @else
                                **
                            @endauth
                        </div>
                    </button>
                    
                    <button @click="filterIndicators('fulfilled')"
                         class="bg-gray-100 rounded-lg px-4 py-2 text-center cursor-pointer transition-all duration-200 hover:shadow-md"
                         :class="{'ring-2 ring-blue-500 bg-blue-50': activeFilter === 'fulfilled'}">
                        <span class="text-sm text-gray-500">Indikator Terpenuhi</span>
                        <div class="text-lg font-medium text-gray-900">
                            @auth
                                {{ $family->healthIndex->fulfilled_indicators }}
                            @else
                                **
                            @endauth
                        </div>
                    </button>
                </div>
            </div>
            
            <!-- Indicator Details -->
        @else
            <div class="text-center py-8">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h4 class="text-lg font-medium text-gray-900 mb-1">Data IKS belum tersedia</h4>
                <p class="text-gray-500 mb-4">Indeks Keluarga Sehat belum dihitung untuk keluarga ini</p>
                
                @if(auth()->check() && auth()->user()->can('calculate-iks'))
                    @if(Route::has('families.calculate-iks'))
                        <a href="{{ route('families.calculate-iks', $family->id) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            Hitung IKS Sekarang
                        </a>
                    @else
                        <div class="mt-2 text-sm text-gray-500">
                            Rute perhitungan IKS belum dikonfigurasi. Hubungi administrator untuk mengaktifkan fitur ini.
                        </div>
                    @endif
                @endif
            </div>
        @endif


        <div>
            
                <!-- Toggle button -->
        <button 
        x-data="{ show: false }" 
        @click="show = !show; $dispatch('toggle-indeks', { show })" 
        class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
                <span x-text="show ? 'Sembunyikan Data' : 'Tampilkan Data'">Sembunyikan Data</span>
            </button>
        </div>

        <!-- Cards container dengan toggle functionality -->
        <div 
            x-data="{ visible: false }" 
            @toggle-indeks.window="visible = $event.detail.show" 
            x-show="visible" 
            x-transition:enter="transition ease-out duration-300" 
            x-transition:enter-start="opacity-0 transform scale-95" 
            x-transition:enter-end="opacity-100 transform scale-100" 
            x-transition:leave="transition ease-in duration-200" 
            x-transition:leave-start="opacity-100 transform scale-100" 
            x-transition:leave-end="opacity-0 transform scale-95"
        >

            <!-- Indicator List Header -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Indikator Kesehatan Keluarga</h3>
                <div class="flex items-center space-x-2">
                    <span x-show="activeFilter === 'all'" class="text-sm text-gray-500">Semua Indikator</span>
                    <span x-show="activeFilter === 'relevant'" class="text-sm text-gray-500">Hanya Indikator Relevan</span>
                    <span x-show="activeFilter === 'fulfilled'" class="text-sm text-gray-500">Hanya Indikator Terpenuhi</span>
                    <button @click="filterIndicators('all')" 
                            x-show="activeFilter !== 'all'" 
                            class="text-xs text-blue-600 hover:underline">
                        Tampilkan Semua
                    </button>
                </div>
            </div>
            
            <!-- Indicator Cards -->
            <div class="p-6">
                @if(count($indicators) > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        @foreach($indicators as $key => $indicator)
                            <div x-show="isVisible('{{ $key }}')" 
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 transform scale-95"
                                 x-transition:enter-end="opacity-100 transform scale-100"
                                 class="bg-white border rounded-lg overflow-hidden shadow-sm health-card">
                                <div class="px-4 py-3 bg-gray-50 border-b flex items-center gap-2">
                                    {{-- Custom icons based on indicator name --}}
                                    @php
                                        $indicatorName = strtolower($indicator['name'] ?? '');
                                        $iconColor = 'text-gray-500';
                                        $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21a48.309 48.309 0 01-8.135-.687c-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />';
                                        
                                        // Deteksi jenis indikator berdasarkan nama atau kata kunci
                                        if (str_contains($indicatorName, 'kb') || str_contains($indicatorName, 'keluarga berencana')) {
                                            $iconColor = 'text-blue-500';
                                            $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" />';
                                        }
                                        elseif (str_contains($indicatorName, 'persalinan') || str_contains($indicatorName, 'melahirkan')) {
                                            $iconColor = 'text-pink-500';
                                            $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />';
                                        }
                                        elseif (str_contains($indicatorName, 'imunisasi') || str_contains($indicatorName, 'vaksin')) {
                                            $iconColor = 'text-green-500';
                                            $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />';
                                        }
                                        elseif (str_contains($indicatorName, 'asi') || str_contains($indicatorName, 'air susu ibu')) {
                                            $iconColor = 'text-yellow-500';
                                            $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11.25l1.5 1.5.75-.75V8.758l2.276-.61a3 3 0 10-3.675-3.675l-.61 2.277H12l-.75.75 1.5 1.5M15 11.25l-8.47 8.47c-.34.34-.8.53-1.28.53s-.94.19-1.28.53l-.97.97-.75-.75.97-.97c.34-.34.53-.8.53-1.28s.19-.94.53-1.28L12.75 9M15 11.25L12.75 9" />';
                                        }
                                        elseif (str_contains($indicatorName, 'balita') || str_contains($indicatorName, 'pertumbuhan')) {
                                            $iconColor = 'text-purple-500';
                                            $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7.5L7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5" />';
                                        }
                                        elseif (str_contains($indicatorName, 'tb') || str_contains($indicatorName, 'tuberkulosis') || str_contains($indicatorName, 'paru')) {
                                            $iconColor = 'text-red-500';
                                            $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 01-2.25 2.25M16.5 7.5V18a2.25 2.25 0 002.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 002.25 2.25h13.5M6 7.5h3v3H6v-3z" />';
                                        }
                                        elseif (str_contains($indicatorName, 'hipertensi') || str_contains($indicatorName, 'darah tinggi')) {
                                            $iconColor = 'text-red-600';
                                            $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21a48.309 48.309 0 01-8.135-.687c-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />';
                                        }
                                        elseif (str_contains($indicatorName, 'jiwa') || str_contains($indicatorName, 'gangguan mental')) {
                                            $iconColor = 'text-indigo-500';
                                            $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />';
                                        }
                                        elseif (str_contains($indicatorName, 'rokok') || str_contains($indicatorName, 'merokok')) {
                                            $iconColor = 'text-gray-500';
                                            $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />';
                                        }
                                        elseif (str_contains($indicatorName, 'jkn') || str_contains($indicatorName, 'jaminan kesehatan')) {
                                            $iconColor = 'text-blue-600';
                                            $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />';
                                        }
                                        elseif (str_contains($indicatorName, 'air') || str_contains($indicatorName, 'air bersih')) {
                                            $iconColor = 'text-cyan-500';
                                            $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />';
                                        }
                                        elseif (str_contains($indicatorName, 'jamban') || str_contains($indicatorName, 'toilet')) {
                                            $iconColor = 'text-emerald-500';
                                            $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />';
                                        }
                                    @endphp
                                    
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 {{ $iconColor }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        {!! $iconSvg !!}
                                    </svg>
                                    
                                    <h4 class="text-md font-medium text-gray-900">{{ $indicator['name'] }}</h4>
                                </div>
                                
                                <div class="p-4">
                                    <p class="text-sm text-gray-600 mb-3">{{ $indicator['description'] }}</p>
                                    
                                    @if($indicator['is_relevant'])
                                        <div class="flex flex-col space-y-2">
                                            <!-- Status indicator -->
                                            <div class="flex items-center">
                                                <div class="mr-3">
                                                    @if(auth()->check())
                                                        @if($indicator['status'])
                                                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100">
                                                                <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                                </svg>
                                                            </span>
                                                        @else
                                                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-red-100">
                                                                <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                                </svg>
                                                            </span>
                                                        @endif
                                                    @else
                                                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-200">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                            </svg>
                                                        </span>
                                                    @endif
                                                </div>
                                                <div>
                                                    @auth
                                                        <p class="text-sm font-medium {{ $indicator['status'] ? 'text-green-600' : 'text-red-600' }}">
                                                            {{ $indicator['status'] ? 'Terpenuhi' : 'Belum Terpenuhi' }}
                                                       </p>
                                                   @else
                                                       <p class="text-sm font-medium text-gray-500">Status tersembunyi</p>
                                                   @endauth
                                               </div>
                                           </div>
                                           
                                           <!-- Details -->
                                           @if(isset($indicator['details']))
                                               <div class="mt-2">
                                                   @if(isset($indicator['details']['total_relevant']) && isset($indicator['details']['total_fulfilled']))
                                                       <div class="text-xs text-gray-500">
                                                           @auth
                                                               <span class="font-medium">{{ $indicator['details']['total_fulfilled'] }}</span> dari 
                                                               <span class="font-medium">{{ $indicator['details']['total_relevant'] }}</span> 
                                                               anggota memenuhi
                                                           @else
                                                               <span class="font-medium">*</span> dari 
                                                               <span class="font-medium">*</span> 
                                                               anggota memenuhi
                                                           @endauth
                                                       </div>
                                                       <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                                           @php
                                                               $colorClass = match($indicator['color'] ?? '') {
                                                                   'green' => 'bg-green-500',
                                                                   'red' => 'bg-red-500',
                                                                   'yellow' => 'bg-yellow-500',
                                                                   default => 'bg-blue-500'
                                                               };
                                                           @endphp
                                                           <div class="{{ $colorClass }} h-2 rounded-full" style="width: {{ auth()->check() ? $indicator['details']['percentage'] : 0 }}%"></div>
                                                       </div>
                                                       <div class="text-xs font-medium mt-0.5 text-gray-500 text-right">
                                                           @auth
                                                               {{ $indicator['details']['percentage'] }}%
                                                           @else
                                                               **%
                                                           @endauth
                                                       </div>
                                                   @elseif(isset($indicator['details']['has_mental_illness']))
                                                       <div class="space-y-1 text-xs text-gray-500">
                                                           @auth
                                                               <div>Gangguan Jiwa: {{ $indicator['details']['has_mental_illness'] ? 'Ya' : 'Tidak' }}</div>
                                                               @if($indicator['details']['has_mental_illness'])
                                                                   <div>Berobat Teratur: {{ $indicator['details']['takes_medication'] ? 'Ya' : 'Tidak' }}</div>
                                                                   <div>Tidak Dipasung: {{ $indicator['details']['not_restrained'] ? 'Ya' : 'Tidak' }}</div>
                                                               @endif
                                                           @else
                                                               <div>Gangguan Jiwa: *****</div>
                                                               <div>Detail tersembunyi</div>
                                                           @endauth
                                                       </div>
                                                   @elseif(isset($indicator['details']['has_clean_water']))
                                                       <div class="space-y-1 text-xs text-gray-500">
                                                           @auth
                                                               <div>Air Bersih: {{ $indicator['details']['has_clean_water'] ? 'Ya' : 'Tidak' }}</div>
                                                               <div>Terlindungi: {{ $indicator['details']['is_water_protected'] ? 'Ya' : 'Tidak' }}</div>
                                                           @else
                                                               <div>Air Bersih: *****</div>
                                                               <div>Terlindungi: *****</div>
                                                           @endauth
                                                       </div>
                                                   @elseif(isset($indicator['details']['has_toilet']))
                                                       <div class="space-y-1 text-xs text-gray-500">
                                                           @auth
                                                               <div>Memiliki Jamban: {{ $indicator['details']['has_toilet'] ? 'Ya' : 'Tidak' }}</div>
                                                               <div>Jamban Saniter: {{ $indicator['details']['is_toilet_sanitary'] ? 'Ya' : 'Tidak' }}</div>
                                                           @else
                                                               <div>Memiliki Jamban: *****</div>
                                                               <div>Jamban Saniter: *****</div>
                                                           @endauth
                                                       </div>
                                                   @endif
                                               </div>
                                           @endif
                                       </div>
                                   @else
                                       <div class="flex items-center">
                                           <div class="mr-3">
                                               <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100">
                                                   <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                   </svg>
                                               </span>
                                           </div>
                                           <div>
                                               <p class="text-sm font-medium text-gray-500">Tidak Relevan</p>
                                           </div>
                                       </div>
                                   @endif
                               </div>
                           </div>
                       @endforeach
                   </div>
                   
                   <!-- Empty state when no indicators match the filter -->
                   <div x-show="getVisibleCount() === 0" class="text-center py-6">
                       <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                       </svg>
                       <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak Ada Indikator yang Sesuai</h3>
                       <p class="mt-1 text-sm text-gray-500">Tidak ada indikator yang sesuai dengan filter yang dipilih.</p>
                       <button @click="filterIndicators('all')" class="mt-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                           Tampilkan Semua Indikator
                       </button>
                   </div>
               @else
                   <div class="text-center py-6">
                       <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                       </svg>
                       <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak Ada Indikator</h3>
                       <p class="mt-1 text-sm text-gray-500">Tidak ada indikator kesehatan yang relevan untuk keluarga ini.</p>
                   </div>
               @endif
           </div>
        </div>


   </div>
</div>
