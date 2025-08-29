<x-filament-panels::page>
    <div class="space-y-8">
        {{-- CSS Tambahan --}}
        <style>
            .dashboard-card {
                transition: all 0.3s ease;
            }
            .dashboard-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            }
            .progress-bar {
                transition: width 1s ease-in-out;
            }
            .animate-on-scroll {
                opacity: 0;
                transform: translateY(20px);
                transition: all 0.6s ease;
            }
            .animate-on-scroll.show {
                opacity: 1;
                transform: translateY(0);
            }
            .indicator-card {
                transition: all 0.3s ease;
            }
            .indicator-card:hover {
                transform: scale(1.03);
            }
            .village-card {
                transition: all 0.3s ease;
            }
            .village-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            }
        </style>{{-- Header dengan banner dan statistik utama --}}
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl shadow-lg overflow-hidden">
            <div class="px-6 py-12 md:px-12 text-center">
                <h1 class="text-3xl font-bold text-slate-600 mb-2">Dashboard Indeks Keluarga Sehat</h1>
                <p class="text-blue-100 mb-8">Monitoring dan evaluasi kesehatan keluarga di seluruh desa</p>
                
                @php
                    $overallData = $this->getOverallReportData();
                    $statusColor = $overallData['avg_iks'] > 80 ? 'bg-green-500' : ($overallData['avg_iks'] > 50 ? 'bg-yellow-500' : 'bg-red-500');
                    $statusTextColor = $overallData['avg_iks'] > 80 ? 'text-green-500' : ($overallData['avg_iks'] > 50 ? 'text-yellow-500' : 'text-red-500');
                @endphp
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 md:gap-8">
                    <div class="dashboard-card bg-white rounded-xl shadow-md p-6">
                        <div class="text-sm font-medium text-gray-500 mb-1">Rata-rata IKS</div>
                        <div class="flex items-end justify-between">
                            <div class="text-4xl font-bold text-gray-800">{{ number_format($overallData['avg_iks'], 1) }}%</div>
                            <div class="text-sm font-semibold {{ $statusTextColor }} px-2 py-1 rounded-full bg-opacity-10 {{ str_replace('text-', 'bg-', $statusTextColor) }}">
                                {{ $overallData['health_status'] }}
                            </div>
                        </div>
                        <div class="w-full h-2 bg-gray-200 rounded-full mt-4">
                            <div class="h-2 rounded-full progress-bar {{ $statusColor }}" style="width: {{ min($overallData['avg_iks'], 100) }}%"></div>
                        </div>
                    </div>
                    
                    <div class="dashboard-card bg-white rounded-xl shadow-md p-6">
                        <div class="text-sm font-medium text-gray-500 mb-1">Jumlah Keluarga</div>
                        <div class="text-4xl font-bold text-gray-800">{{ number_format($overallData['total_families']) }}</div>
                        <div class="flex items-center mt-4 text-gray-500 text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Dari seluruh wilayah
                        </div>
                    </div>
                    
                    <div class="dashboard-card bg-white rounded-xl shadow-md p-6 col-span-1 md:col-span-2">
                        <div class="text-sm font-medium text-gray-500 mb-1">Distribusi Status Kesehatan</div>
                        <div class="grid grid-cols-3 gap-4 mt-2">
                            <div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 rounded-full bg-green-500 mr-2"></div>
                                    <span class="text-sm font-medium text-gray-600">Keluarga Sehat</span>
                                </div>
                                <div class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($overallData['healthy_percentage'], 1) }}%</div>
                                <div class="text-xs text-gray-500">{{ number_format($overallData['healthy_count']) }} keluarga</div>
                            </div>
                            <div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 rounded-full bg-yellow-500 mr-2"></div>
                                    <span class="text-sm font-medium text-gray-600">Keluarga Pra-Sehat</span>
                                </div>
                                <div class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($overallData['pre_healthy_percentage'], 1) }}%</div>
                                <div class="text-xs text-gray-500">{{ number_format($overallData['pre_healthy_count']) }} keluarga</div>
                            </div>
                            <div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 rounded-full bg-red-500 mr-2"></div>
                                    <span class="text-sm font-medium text-gray-600">Keluarga Tidak Sehat</span>
                                </div>
                                <div class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($overallData['unhealthy_percentage'], 1) }}%</div>
                                <div class="text-xs text-gray-500">{{ number_format($overallData['unhealthy_count']) }} keluarga</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>{{-- Capaian 12 Indikator IKS --}}
        <div class="animate-on-scroll">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Capaian 12 Indikator IKS
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">@foreach ($overallData['indicators'] as $key => $indicator)
                    @php
                        $indicatorColor = $indicator['percentage'] > 80 ? 'from-green-500 to-green-600' : ($indicator['percentage'] > 50 ? 'from-yellow-500 to-yellow-600' : 'from-red-500 to-red-600');
                        $indicatorTextColor = $indicator['percentage'] > 80 ? 'text-green-600' : ($indicator['percentage'] > 50 ? 'text-yellow-600' : 'text-red-600');
                        $indicatorBgColor = $indicator['percentage'] > 80 ? 'bg-green-100' : ($indicator['percentage'] > 50 ? 'bg-yellow-100' : 'bg-red-100');
                        
                        // Tambahkan ikon untuk setiap indikator
                        $icon = match($key) {
                            'kb' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>',
                            'birth_facility' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>',
                            'immunization' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>',
                            'exclusive_breastfeeding' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>',
                            'growth_monitoring' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>',
                            'tb_treatment' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" /></svg>',
                            'hypertension_treatment' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>',
                            'mental_treatment' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>',
                            'no_smoking' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>',
                            'jkn_membership' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>',
                            'clean_water' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>',
                            'sanitary_toilet' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 3l-6 6m0 0V4m0 5h5M5 3a2 2 0 00-2 2v1c0 8.284 6.716 15 15 15h1a2 2 0 002-2v-3.28a1 1 0 00-.684-.948l-4.493-1.498a1 1 0 00-1.21.502l-1.13 2.257a11.042 11.042 0 01-5.516-5.517l2.257-1.128a1 1 0 00.502-1.21L9.228 3.683A1 1 0 008.279 3H5z" /></svg>',
                            default => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
                        };
                    @endphp
                    
                    <div class="indicator-card bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <div class="p-4">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <div class="rounded-full p-2 {{ $indicatorBgColor }} mr-3">
                                        {!! $icon !!}
                                    </div>
                                    <h3 class="font-medium text-gray-800">{{ $indicator['label'] }}</h3>
                                </div>
                                <span class="font-bold {{ $indicatorTextColor }}">{{ number_format($indicator['percentage'], 1) }}%</span>
                            </div>
                            
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="h-2.5 rounded-full progress-bar bg-gradient-to-r {{ $indicatorColor }}" style="width: {{ min($indicator['percentage'], 100) }}%"></div>
                            </div>
                            
                            <div class="flex justify-between mt-3 text-xs text-gray-500">
                                <span>{{ number_format($indicator['fulfilled_count']) }} terpenuhi</span>
                                <span>dari {{ number_format($indicator['relevant_count']) }} relevan</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>{{-- Laporan IKS per Desa --}}
    <div class="animate-on-scroll">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Laporan IKS per Desa
            </h2>
            
            @php
                $villageData = $this->getVillageReportData();
            @endphp
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">@foreach ($villageData as $index => $village)
                @php
                    $statusColor = $village['avg_iks'] > 80 ? 'bg-green-500' : ($village['avg_iks'] > 50 ? 'bg-yellow-500' : 'bg-red-500');
                    $statusBgColor = $village['avg_iks'] > 80 ? 'bg-green-100' : ($village['avg_iks'] > 50 ? 'bg-yellow-100' : 'bg-red-100');
                    $statusTextColor = $village['avg_iks'] > 80 ? 'text-green-700' : ($village['avg_iks'] > 50 ? 'text-yellow-700' : 'text-red-700');
                    $delay = ($index % 4) * 0.1;
                @endphp
                
                <div class="village-card bg-white rounded-xl border border-gray-200 overflow-hidden" style="animation-delay: {{ $delay }}s">
                    <div class="p-5">
                        <div class="flex flex-col md:flex-row justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">{{ $village['village']['name'] }}</h3>
                                <p class="text-gray-500 text-sm">{{ $village['village']['district'] }}, {{ $village['village']['regency'] }}</p>
                            </div>
                            <div class="mt-2 md:mt-0 flex flex-col items-end">
                                <div class="flex items-center">
                                    <span class="text-2xl font-bold text-gray-800 mr-2">{{ number_format($village['avg_iks'], 1) }}%</span>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusBgColor }} {{ $statusTextColor }}">
                                        {{ $village['health_status'] }}
                                    </span>
                                </div>
                                <span class="text-sm text-gray-500">{{ number_format($village['total_families']) }} keluarga</span>
                            </div>
                        </div>
                        
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-4">
                            <div class="h-2 rounded-full progress-bar {{ $statusColor }}" style="width: {{ min($village['avg_iks'], 100) }}%"></div>
                        </div>
                        
                        <div class="grid grid-cols-3 gap-3 mt-5">
                            <div class="flex items-start">
                                <div class="rounded-full h-3 w-3 bg-green-500 mt-1.5 mr-2"></div>
                                <div>
                                    <p class="text-sm font-medium text-gray-700">Sehat</p>
                                    <p class="text-xs text-gray-500">{{ $village['healthy_count'] }} ({{ $village['total_families'] > 0 ? number_format(($village['healthy_count'] / $village['total_families']) * 100, 1) : 0 }}%)</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="rounded-full h-3 w-3 bg-yellow-500 mt-1.5 mr-2"></div>
                                <div>
                                    <p class="text-sm font-medium text-gray-700">Pra-Sehat</p>
                                    <p class="text-xs text-gray-500">{{ $village['pre_healthy_count'] }} ({{ $village['total_families'] > 0 ? number_format(($village['pre_healthy_count'] / $village['total_families']) * 100, 1) : 0 }}%)</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="rounded-full h-3 w-3 bg-red-500 mt-1.5 mr-2"></div>
                                <div>
                                    <p class="text-sm font-medium text-gray-700">Tidak Sehat</p>
                                    <p class="text-xs text-gray-500">{{ $village['unhealthy_count'] }} ({{ $village['total_families'] > 0 ? number_format(($village['unhealthy_count'] / $village['total_families']) * 100, 1) : 0 }}%)</p>
                                </div>
                            </div>
                        </div><div class="mt-5">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Indikator dengan Capaian Tertinggi & Terendah:</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @php
                                    $indicators = collect($village['indicators'])->where('relevant_count', '>', 0);
                                    $highestIndicator = $indicators->sortByDesc('percentage')->first();
                                    $lowestIndicator = $indicators->sortBy('percentage')->first();
                                    
                                    $highestColor = $highestIndicator['percentage'] > 80 ? 'text-green-600 bg-green-100' : ($highestIndicator['percentage'] > 50 ? 'text-yellow-600 bg-yellow-100' : 'text-red-600 bg-red-100');
                                    $lowestColor = $lowestIndicator['percentage'] > 80 ? 'text-green-600 bg-green-100' : ($lowestIndicator['percentage'] > 50 ? 'text-yellow-600 bg-yellow-100' : 'text-red-600 bg-red-100');
                                @endphp
                                
                                @if($highestIndicator)
                                    <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50">
                                        <span class="text-xs text-gray-700">{{ $highestIndicator['label'] }}</span>
                                        <span class="px-2 py-1 text-xs rounded-full {{ $highestColor }}">{{ number_format($highestIndicator['percentage'], 1) }}%</span>
                                    </div>
                                @endif
                                
                                @if($lowestIndicator && $lowestIndicator !== $highestIndicator)
                                    <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50">
                                        <span class="text-xs text-gray-700">{{ $lowestIndicator['label'] }}</span>
                                        <span class="px-2 py-1 text-xs rounded-full {{ $lowestColor }}">{{ number_format($lowestIndicator['percentage'], 1) }}%</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>{{-- JavaScript untuk Animasi --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animasi untuk progress bar
        setTimeout(function() {
            const progressBars = document.querySelectorAll('.progress-bar');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0';
                setTimeout(() => {
                    bar.style.width = width;
                }, 100);
            });
        }, 300);
        
        // Animasi ketika scroll
        const animateOnScroll = function() {
            const elements = document.querySelectorAll('.animate-on-scroll');
            elements.forEach(element => {
                const elementPosition = element.getBoundingClientRect().top;
                const windowHeight = window.innerHeight;
                
                if (elementPosition < windowHeight - 100) {
                    element.classList.add('show');
                }
            });
        };
        
        // Jalankan animasi pada load pertama
        animateOnScroll();
        
        // Jalankan animasi ketika scroll
        window.addEventListener('scroll', animateOnScroll);
    });
</script>
</div>
</x-filament-panels::page>