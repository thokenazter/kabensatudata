<div class="bg-gradient-to-br from-blue-900 via-indigo-900 to-purple-900 text-white py-16 px-4 sm:px-6 lg:px-8 rounded-3xl shadow-xl mb-10">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-12 lg:my-auto animate-fade-in" style="animation-delay: 0ms;">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">Puskesmas Kaben - Satu Data</h2>
            <p class="text-blue-200 max-w-3xl pb-4 mx-auto">Dashboard Capaian Indeks Keluarga Sehat di Wilayah Kerja Puskesmas Kabalsiang Benjuring</p>
        </div>
        
        @php
            $iksService = app(\App\Services\IksReportService::class);
            $overallData = $iksService->generateOverallReport();
            $villageData = $iksService->generateVillageReport()->take(5);
            
            $statusColor = $overallData['avg_iks'] > 80 ? 'bg-emerald-500' : ($overallData['avg_iks'] > 50 ? 'bg-amber-500' : 'bg-rose-500');
            $statusTextColor = $overallData['avg_iks'] > 80 ? 'text-emerald-500' : ($overallData['avg_iks'] > 50 ? 'text-amber-500' : 'text-rose-500');
            $statusBgColor = $overallData['avg_iks'] > 80 ? 'bg-emerald-900/30' : ($overallData['avg_iks'] > 50 ? 'bg-amber-900/30' : 'bg-rose-900/30');
        @endphp
        
        <!-- Main Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Rata-rata IKS -->
            <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-6 border border-white/10 hover:shadow-lg hover:shadow-indigo-500/20 transition-all duration-300 flex flex-col justify-between animate-fade-in" style="animation-delay: 150ms;">
                <div class="text-sm text-blue-200 font-medium">Rata-rata IKS</div>
                <div class="mt-2 flex items-baseline">
                    <span class="text-5xl font-extrabold text-white tracking-tight counter-animate" data-target="{{ number_format($overallData['avg_iks'], 1) }}">0.0%</span>
                    <span class="ml-2 text-sm font-medium px-2.5 py-0.5 rounded-full {{ $statusBgColor }} {{ $statusTextColor }}">{{ $overallData['health_status'] }}</span>
                </div>
                <div class="mt-4 w-full bg-black/20 rounded-full h-2">
                    <div class="h-2 rounded-full {{ $statusColor }} animate-width-expand" data-width="{{ min($overallData['avg_iks'], 100) }}" style="width: 0%;"></div>
                </div>
            </div>
            
            <!-- Jumlah Keluarga -->
            {{-- <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-6 border border-white/10 hover:shadow-lg hover:shadow-indigo-500/20 transition-all duration-300 animate-fade-in" style="animation-delay: 300ms;">
                <div class="text-sm text-blue-200 font-medium">Jumlah Keluarga</div>
                <div class="mt-2 text-5xl font-extrabold text-white tracking-tight counter-animate" data-target="{{ number_format($overallData['total_families']) }}">0</div>
                <div class="mt-4 flex items-center text-blue-200 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Dari seluruh wilayah
                </div>
            </div> --}}
            
            <!-- Status Distribution -->
            <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-6 border border-white/10 hover:shadow-lg hover:shadow-indigo-500/20 transition-all duration-300 col-span-1 md:col-span-2 animate-fade-in" style="animation-delay: 450ms;">
                <div class="text-sm text-blue-200 font-medium mb-4">Distribusi Status Kesehatan</div>
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center rounded-full p-2 bg-emerald-900/30 mb-2">
                            <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                        </div>
                        <div class="text-xl md:text-2xl font-bold text-white counter-animate" data-target="{{ number_format($overallData['healthy_percentage'], 1) }}">0.0%</div>
                        <div class="text-xs text-blue-200"><span class="counter-animate" data-target="{{ number_format($overallData['healthy_count']) }}">0</span> Keluarga Sehat</div>
                    </div>
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center rounded-full p-2 bg-amber-900/30 mb-2">
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                        </div>
                        <div class="text-xl md:text-2xl font-bold text-white counter-animate" data-target="{{ number_format($overallData['pre_healthy_percentage'], 1) }}">0.0%</div>
                        <div class="text-xs text-blue-200"><span class="counter-animate" data-target="{{ number_format($overallData['pre_healthy_count']) }}">0</span> Keluarga Pra-Sehat</div>
                    </div>
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center rounded-full p-2 bg-rose-900/30 mb-2">
                            <div class="w-3 h-3 rounded-full bg-rose-500"></div>
                        </div>
                        <div class="text-xl md:text-2xl font-bold text-white counter-animate" data-target="{{ number_format($overallData['unhealthy_percentage'], 1) }}">0.0%</div>
                        <div class="text-xs text-blue-200"><span class="counter-animate" data-target="{{ number_format($overallData['unhealthy_count']) }}">0</span> Keluarga Tidak Sehat</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Village Cards -->
        @if($villageData->count() > 0)
            <div class="mt-10 animate-fade-in" style="animation-delay: 600ms;">
                <h3 class="text-xl font-bold text-white mb-6 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Top Desa dengan IKS Tertinggi
                </h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($villageData as $index => $village)
                        @php
                            $vStatusColor = $village['avg_iks'] > 80 ? 'bg-emerald-500' : ($village['avg_iks'] > 50 ? 'bg-amber-500' : 'bg-rose-500');
                            $vStatusTextColor = $village['avg_iks'] > 80 ? 'text-emerald-500' : ($village['avg_iks'] > 50 ? 'text-amber-500' : 'text-rose-500');
                            $vStatusBgColor = $village['avg_iks'] > 80 ? 'bg-emerald-900/30' : ($village['avg_iks'] > 50 ? 'bg-amber-900/30' : 'bg-rose-900/30');
                            $animationDelay = 750 + ($index * 150);
                        @endphp
                        <div class="bg-white/5 backdrop-blur-lg rounded-2xl p-6 border border-white/10 hover:shadow-lg hover:shadow-indigo-500/20 transition-all duration-300 animate-fade-in" style="animation-delay: {{ $animationDelay }}ms;">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-lg font-semibold text-white">{{ $village['village']['name'] }}</h4>
                                    <p class="text-xs text-blue-200">{{ $village['village']['district'] }}, {{ $village['village']['regency'] }}</p>
                                </div>
                                <div class="flex flex-col items-end">
                                    <div class="flex items-center">
                                        <span class="text-2xl font-bold text-white mr-2 counter-animate" data-target="{{ number_format($village['avg_iks'], 1) }}">0.0%</span>
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $vStatusBgColor }} {{ $vStatusTextColor }}">
                                            {{ $village['health_status'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4 w-full bg-black/20 rounded-full h-1.5">
                                <div class="h-1.5 rounded-full {{ $vStatusColor }} animate-width-expand" data-width="{{ min($village['avg_iks'], 100) }}" style="width: 0%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

<!-- JavaScript untuk Animasi -->
<style>
    .animate-fade-in {
        opacity: 0;
        transform: translateY(10px);
    }
    
    @keyframes countAnimation {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    .counter-animate {
        animation: countAnimation 0.5s ease-out;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animasi untuk progress bar
    const progressBars = document.querySelectorAll('.animate-width-expand');
    progressBars.forEach(bar => {
        const targetWidth = bar.getAttribute('data-width') + '%';
        setTimeout(() => {
            bar.style.width = targetWidth;
            bar.style.transition = 'width 1.5s ease-in-out';
        }, 300);
    });

    // Animasi untuk counter
    const counters = document.querySelectorAll('.counter-animate');
    counters.forEach(counter => {
        const targetValue = counter.getAttribute('data-target');
        const isPercentage = targetValue.includes('%') || counter.closest('.text-5xl, .text-2xl, .text-xl');
        const target = parseFloat(targetValue);
        let count = 0;
        const duration = 1500; // ms
        const frameRate = 30;
        const increment = target / (duration / (1000 / frameRate));
        
        const timer = setInterval(() => {
            count += increment;
            if (count >= target) {
                clearInterval(timer);
                // Cek apakah ini persentase atau angka biasa
                if (isPercentage) {
                    counter.textContent = target.toFixed(1) + '%';
                } else if (counter.textContent.includes('Keluarga')) {
                    counter.textContent = Math.round(target) + ' ' + counter.textContent.split(' ').slice(1).join(' ');
                } else {
                    counter.textContent = target.toFixed(0); // Angka biasa tanpa desimal
                }
            } else {
                // Cek apakah ini persentase atau angka biasa
                if (isPercentage) {
                    counter.textContent = count.toFixed(1) + '%';
                } else if (counter.textContent.includes('Keluarga')) {
                    counter.textContent = Math.round(count) + ' ' + counter.textContent.split(' ').slice(1).join(' ');
                } else {
                    counter.textContent = count.toFixed(0); // Angka biasa tanpa desimal
                }
            }
        }, 1000 / frameRate);
    });

    // Animasi fade-in untuk cards
    const cards = document.querySelectorAll('.animate-fade-in');
    cards.forEach((card, index) => {
        const delay = card.style.animationDelay ? parseInt(card.style.animationDelay) : 100 + (index * 150);
        setTimeout(() => {
            card.classList.remove('opacity-0', 'transform', 'translate-y-4');
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
            card.style.transition = 'all 0.5s ease-out';
        }, delay);
    });
});
</script>