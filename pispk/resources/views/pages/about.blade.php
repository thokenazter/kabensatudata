<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PKM Kaben - Satu Data</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-50">
    @include('includes.navbar')
    
    <!-- Hero Section dengan glassmorphism style -->
    <div class="relative bg-gradient-to-br from-blue-900 via-indigo-900 to-purple-900 overflow-hidden mt-12 py-12 px-4 sm:px-6 lg:px-8 rounded-b-3xl shadow-xl">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-12 animate-fade-in" style="animation-delay: 0ms;">
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold text-white tracking-tight counter-animate" data-target="PKM Kaben - Satu Data">PKM Kaben - Satu Data</h1>
                <p class="mt-4 max-w-3xl mx-auto text-base text-blue-200 sm:text-lg md:text-xl">
                    Sistem Informasi Pendataan Wilayah Kerja Puskesmas yang Interaktif dan Terintegrasi
                </p>
            </div>
            
            <!-- Circle Decorations -->
            <div class="absolute top-16 right-12 w-40 h-40 bg-purple-500 rounded-full opacity-10 animate-blob"></div>
            <div class="absolute top-32 left-12 w-32 h-32 bg-blue-500 rounded-full opacity-10 animate-blob animation-delay-2000"></div>
            <div class="absolute bottom-16 right-1/4 w-24 h-24 bg-indigo-500 rounded-full opacity-10 animate-blob animation-delay-4000"></div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- About Section -->
        <div class="bg-white/10 backdrop-blur-lg rounded-3xl p-8 border border-indigo-100 shadow-xl mb-12 transform hover:scale-[1.01] transition-all duration-300 animate-fade-in" style="animation-delay: 300ms;">
            <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-3 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Tentang Aplikasi
            </h2>
            <div class="prose prose-lg text-gray-600 max-w-none">
                <p class="bg-gradient-to-r from-blue-900 to-indigo-900 bg-clip-text text-transparent text-lg font-medium leading-relaxed text-justify">PKM Kaben - Satu Data adalah platform digital yang di buat dan dikembangkan oleh Admin Puskesmas untuk memudahkan pendataan dan monitoring kesehatan masyarakat di wilayah kerja Puskesmas Kaben. Aplikasi ini mengintegrasikan berbagai data kesehatan masyarakat untuk memberikan gambaran yang komprehensif tentang kondisi kesehatan di wilayah tersebut.</p>
            </div>
        </div>

        <!-- Features Section -->
        <div class="mb-12 animate-fade-in" style="animation-delay: 500ms;">
            <h2 class="text-3xl font-bold text-gray-900 mb-8 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-3 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                </svg>
                Fitur Utama
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Feature Card 1 -->
                <div class="bg-gradient-to-br from-indigo-50 to-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 border border-indigo-100 transform hover:scale-[1.02] animate-fade-in" style="animation-delay: 700ms;">
                    <div class="w-16 h-16 bg-blue-500 rounded-2xl flex items-center justify-center mb-6 shadow-md">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Filter Data per Desa / Indikator</h3>
                    <p class="text-gray-600">Kemampuan untuk memfilter dan menampilkan data berdasarkan desa maupun indikator yang dipilih dan memudahkan monitoring.</p>
                    
                    <!-- Animated Progress Bar untuk Indikator Fitur -->
                    <div class="mt-6 w-full bg-gray-200 rounded-full h-1.5">
                        <div class="h-1.5 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 animate-width-expand" data-width="100" style="width: 0%;"></div>
                    </div>
                </div>

                <!-- Feature Card 2 -->
                <div class="bg-gradient-to-br from-indigo-50 to-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 border border-indigo-100 transform hover:scale-[1.02] animate-fade-in" style="animation-delay: 900ms;">
                    <div class="w-16 h-16 bg-emerald-500 rounded-2xl flex items-center justify-center mb-6 shadow-md">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Detail Anggota Keluarga</h3>
                    <p class="text-gray-600">Informasi lengkap tentang setiap anggota keluarga, termasuk data kesehatan dan kondisi sosial.</p>
                    
                    <!-- Animated Progress Bar untuk Indikator Fitur -->
                    <div class="mt-6 w-full bg-gray-200 rounded-full h-1.5">
                        <div class="h-1.5 rounded-full bg-gradient-to-r from-emerald-500 to-emerald-600 animate-width-expand" data-width="100" style="width: 0%;"></div>
                    </div>
                </div>

                <!-- Feature Card 3 -->
                <div class="bg-gradient-to-br from-indigo-50 to-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 border border-indigo-100 transform hover:scale-[1.02] animate-fade-in" style="animation-delay: 1100ms;">
                    <div class="w-16 h-16 bg-rose-500 rounded-2xl flex items-center justify-center mb-6 shadow-md">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Monitoring Penyakit</h3>
                    <p class="text-gray-600">Pemantauan kasus TBC, Darah Tinggi, dan gejala batuk berdahak yang berkelanjutan.</p>
                    
                    <!-- Animated Progress Bar untuk Indikator Fitur -->
                    <div class="mt-6 w-full bg-gray-200 rounded-full h-1.5">
                        <div class="h-1.5 rounded-full bg-gradient-to-r from-rose-500 to-rose-600 animate-width-expand" data-width="100" style="width: 0%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Future Development -->
        <div class="bg-gradient-to-br from-blue-900 via-indigo-900 to-purple-900 text-white rounded-3xl p-8 shadow-xl animate-fade-in" style="animation-delay: 1300ms;">
            <h2 class="text-3xl font-bold mb-6 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-3 text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Pengembangan Kedepan
            </h2>
            
            <div class="text-blue-200 text-lg mb-8">
                <p>Kami berencana untuk mengintegrasikan fitur pemetaan digital yang akan menampilkan:</p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-5 border border-white/10 hover:shadow-lg hover:shadow-indigo-500/20 transition-all duration-300 animate-fade-in" style="animation-delay: 1500ms;">
                    <div class="flex items-center justify-center h-12 w-12 rounded-2xl bg-blue-500/30 mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <p class="text-center text-blue-100 font-medium">Peta lokasi rumah warga</p>
                </div>
                
                <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-5 border border-white/10 hover:shadow-lg hover:shadow-indigo-500/20 transition-all duration-300 animate-fade-in" style="animation-delay: 1650ms;">
                    <div class="flex items-center justify-center h-12 w-12 rounded-2xl bg-indigo-500/30 mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                    </div>
                    <p class="text-center text-blue-100 font-medium">Denah desa interaktif</p>
                </div>
                
                <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-5 border border-white/10 hover:shadow-lg hover:shadow-indigo-500/20 transition-all duration-300 animate-fade-in" style="animation-delay: 1800ms;">
                    <div class="flex items-center justify-center h-12 w-12 rounded-2xl bg-purple-500/30 mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </div>
                    <p class="text-center text-blue-100 font-medium">Penomoran rumah digital</p>
                </div>
                
                <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-5 border border-white/10 hover:shadow-lg hover:shadow-indigo-500/20 transition-all duration-300 animate-fade-in" style="animation-delay: 1950ms;">
                    <div class="flex items-center justify-center h-12 w-12 rounded-2xl bg-emerald-500/30 mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <p class="text-center text-blue-100 font-medium">Visualisasi sebaran penyakit</p>
                </div>
                
                <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-5 border border-white/10 hover:shadow-lg hover:shadow-indigo-500/20 transition-all duration-300 animate-fade-in" style="animation-delay: 2100ms;">
                    <div class="flex items-center justify-center h-12 w-12 rounded-2xl bg-amber-500/30 mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <p class="text-center text-blue-100 font-medium">Dashboard monitoring real-time</p>
                </div>
            </div>
            
            <!-- Call to Action Button -->
            <div class="mt-10 text-center animate-fade-in" style="animation-delay: 2250ms;">
                <a href="#" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-full shadow-sm text-indigo-900 bg-blue-100 hover:bg-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Pelajari Lebih Lanjut
                </a>
            </div>
        </div>
    </div>
    
    @include('includes.footer')
    
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
        
        @keyframes blob {
            0% {
                transform: scale(1);
            }
            33% {
                transform: scale(1.1);
            }
            66% {
                transform: scale(0.9);
            }
            100% {
                transform: scale(1);
            }
        }
        
        .animate-blob {
            animation: blob 7s infinite;
        }
        
        .animation-delay-2000 {
            animation-delay: 2s;
        }
        
        .animation-delay-4000 {
            animation-delay: 4s;
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
</body>
</html>