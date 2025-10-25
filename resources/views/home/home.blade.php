<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Aplikasi Kesehatan Keluarga') }}</title>
    @include('includes.meta')
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Family -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes pulse-glow {
            0% { box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.4); }
            70% { box-shadow: 0 0 0 15px rgba(99, 102, 241, 0); }
            100% { box-shadow: 0 0 0 0 rgba(99, 102, 241, 0); }
        }
        
        .pulse-glow {
            animation: pulse-glow 2s infinite;
        }
    </style>
</head>
<body class="bg-gray-100">
    @include('includes.navbar')

    <main>
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold mb-8 mt-12 text-center">Selamat Datang di Website Kesehatan Keluarga</h1>
            
            <!-- IKS Dashboard Content Langsung di Sini -->
            <div class="bg-gradient-to-br from-blue-900 via-indigo-900 to-purple-900 text-white py-16 px-4 sm:px-6 lg:px-8 rounded-3xl shadow-xl mb-10">
                <div class="max-w-7xl mx-auto">
                    <div class="text-center mb-12">
                        <h2 class="text-3xl md:text-4xl font-bold mb-4">Indeks Keluarga Sehat</h2>
                        <p class="text-blue-200 max-w-3xl mx-auto">Dashboard monitoring kesehatan keluarga untuk meningkatkan kualitas hidup masyarakat</p>
                    </div>
                    
                    @php
                        $iksService = app(\App\Services\IksReportService::class);
                        $overallData = $iksService->generateOverallReport();
                        $villageData = $iksService->generateVillageReport()->take(3);
                        
                        $statusColor = $overallData['avg_iks'] > 80 ? 'bg-emerald-500' : ($overallData['avg_iks'] > 50 ? 'bg-amber-500' : 'bg-rose-500');
                        $statusTextColor = $overallData['avg_iks'] > 80 ? 'text-emerald-500' : ($overallData['avg_iks'] > 50 ? 'text-amber-500' : 'text-rose-500');
                        $statusBgColor = $overallData['avg_iks'] > 80 ? 'bg-emerald-900/30' : ($overallData['avg_iks'] > 50 ? 'bg-amber-900/30' : 'bg-rose-900/30');
                    @endphp
                    
                    <!-- Main Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Rata-rata IKS -->
                        <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-6 border border-white/10 hover:shadow-lg hover:shadow-indigo-500/20 transition-all duration-300 flex flex-col justify-between">
                            <div class="text-sm text-blue-200 font-medium">Rata-rata IKS</div>
                            <div class="mt-2 flex items-baseline">
                                <span class="text-5xl font-extrabold text-white tracking-tight">{{ number_format($overallData['avg_iks'], 1) }}%</span>
                                <span class="ml-2 text-sm font-medium px-2.5 py-0.5 rounded-full {{ $statusBgColor }} {{ $statusTextColor }}">{{ $overallData['health_status'] }}</span>
                            </div>
                            <div class="mt-4 w-full bg-black/20 rounded-full h-2">
                                <div class="h-2 rounded-full {{ $statusColor }}" style="width: {{ min($overallData['avg_iks'], 100) }}%; transition: width 1.5s ease-in-out;"></div>
                            </div>
                        </div>
                        
                        <!-- Jumlah Keluarga -->
                        <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-6 border border-white/10 hover:shadow-lg hover:shadow-indigo-500/20 transition-all duration-300">
                            <div class="text-sm text-blue-200 font-medium">Jumlah Keluarga</div>
                            <div class="mt-2 text-5xl font-extrabold text-white tracking-tight">{{ number_format($overallData['total_families']) }}</div>
                            <div class="mt-4 flex items-center text-blue-200 text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Dari seluruh wilayah
                            </div>
                        </div>
                        
                        <!-- Status Distribution -->
                        <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-6 border border-white/10 hover:shadow-lg hover:shadow-indigo-500/20 transition-all duration-300 col-span-1 md:col-span-2">
                            <div class="text-sm text-blue-200 font-medium mb-4">Distribusi Status Kesehatan</div>
                            <div class="grid grid-cols-3 gap-4">
                                <div class="text-center">
                                    <div class="inline-flex items-center justify-center rounded-full p-2 bg-emerald-900/30 mb-2">
                                        <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                                    </div>
                                    <div class="text-xl md:text-2xl font-bold text-white">{{ number_format($overallData['healthy_percentage'], 1) }}%</div>
                                    <div class="text-xs text-blue-200">{{ number_format($overallData['healthy_count']) }} Keluarga Sehat</div>
                                </div>
                                <div class="text-center">
                                    <div class="inline-flex items-center justify-center rounded-full p-2 bg-amber-900/30 mb-2">
                                        <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                                    </div>
                                    <div class="text-xl md:text-2xl font-bold text-white">{{ number_format($overallData['pre_healthy_percentage'], 1) }}%</div>
                                    <div class="text-xs text-blue-200">{{ number_format($overallData['pre_healthy_count']) }} Keluarga Pra-Sehat</div>
                                </div>
                                <div class="text-center">
                                    <div class="inline-flex items-center justify-center rounded-full p-2 bg-rose-900/30 mb-2">
                                        <div class="w-3 h-3 rounded-full bg-rose-500"></div>
                                    </div>
                                    <div class="text-xl md:text-2xl font-bold text-white">{{ number_format($overallData['unhealthy_percentage'], 1) }}%</div>
                                    <div class="text-xs text-blue-200">{{ number_format($overallData['unhealthy_count']) }} Keluarga Tidak Sehat</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Call to Action -->
                    <div class="mt-10 text-center">
                        <a href="{{ route('filament.admin.pages.iks-report') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300">
                            Lihat Laporan Lengkap
                            <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 -mr-1 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <p>&copy; {{ date('Y') }} {{ config('app.name', 'Aplikasi Kesehatan Keluarga') }}. Hak Cipta Dilindungi.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animasi progress bar saat pertama kali dimuat
            setTimeout(function() {
                const progressBars = document.querySelectorAll('[class*="progress-bar"]');
                progressBars.forEach(bar => {
                    const width = bar.style.width;
                    bar.style.width = '0';
                    setTimeout(() => {
                        bar.style.width = width;
                    }, 100);
                });
            }, 300);
        });
    </script>
</body>
</html>
