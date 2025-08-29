{{-- Komponen untuk menampilkan ringkasan kesehatan dalam satu card --}}
@props(['familyMember'])

<div class="bg-white shadow-lg rounded-lg overflow-hidden">
    <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
        <h3 class="text-xl font-semibold text-white">Ringkasan Kesehatan</h3>
        <p class="text-blue-100 text-sm">Data kesehatan anggota keluarga</p>
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
    
    <div class="p-6 space-y-6 {{ !auth()->check() ? 'relative' : '' }}">
        {{-- Blur Overlay for Guest Users --}}
        @guest
        <div class="absolute inset-0 bg-white bg-opacity-30 backdrop-blur-sm flex items-center justify-center z-10">
            <div class="text-center p-6 bg-white bg-opacity-90 rounded-lg shadow-lg max-w-md mx-auto">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-blue-500 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <h4 class="text-lg font-semibold text-gray-900 mb-2">Data Kesehatan Diproteksi</h4>
                <p class="text-gray-600 mb-4">Informasi ini diproteksi untuk menjaga privasi pasien sesuai dengan aturan kerahasiaan medis.</p>
                <a href="/admin" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                    Login untuk Melihat
                </a>
            </div>
        </div>
        @endguest

        <div>

            <!-- Toggle button -->
        <button 
        x-data="{ show: false }" 
        @click="show = !show; $dispatch('toggle-ringkasan', { show })" 
        class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
                <span x-text="show ? 'Sembunyikan Data' : 'Tampilkan Data'">Sembunyikan Data</span>
            </button>
        </div>

        <!-- Cards container dengan toggle functionality -->
        <div 
            x-data="{ visible: false }" 
            @toggle-ringkasan.window="visible = $event.detail.show" 
            x-show="visible" 
            x-transition:enter="transition ease-out duration-300" 
            x-transition:enter-start="opacity-0 transform scale-95" 
            x-transition:enter-end="opacity-100 transform scale-100" 
            x-transition:leave="transition ease-in duration-200" 
            x-transition:leave-start="opacity-100 transform scale-100" 
            x-transition:leave-end="opacity-0 transform scale-95"
        >
            
            {{-- Status dasar kesehatan --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <span class="text-sm text-gray-500 block mb-1">Status JKN</span>
                    <div class="flex items-center">
                        @auth
                            @if($familyMember->has_jkn)
                                <div class="w-3 h-3 rounded-full bg-green-500 mr-2"></div>
                                <span class="font-medium text-gray-900">Terdaftar</span>
                            @else
                                <div class="w-3 h-3 rounded-full bg-red-500 mr-2"></div>
                                <span class="font-medium text-gray-900">Tidak Terdaftar</span>
                            @endif
                        @else
                            <div class="w-3 h-3 rounded-full bg-gray-400 mr-2"></div>
                            <span class="font-medium text-gray-500">Data tersembunyi</span>
                        @endauth
                    </div>
                </div>
                
                <div class="bg-gray-50 p-4 rounded-lg">
                    <span class="text-sm text-gray-500 block mb-1">Status Merokok</span>
                    <div class="flex items-center">
                        @auth
                            @if($familyMember->is_smoker)
                                <div class="w-3 h-3 rounded-full bg-yellow-500 mr-2"></div>
                                <span class="font-medium text-gray-900">Perokok Aktif</span>
                            @else
                                <div class="w-3 h-3 rounded-full bg-green-500 mr-2"></div>
                                <span class="font-medium text-gray-900">Bukan Perokok</span>
                            @endif
                        @else
                            <div class="w-3 h-3 rounded-full bg-gray-400 mr-2"></div>
                            <span class="font-medium text-gray-500">Data tersembunyi</span>
                        @endauth
                    </div>
                </div>
                
                <div class="bg-gray-50 p-4 rounded-lg">
                    <span class="text-sm text-gray-500 block mb-1">Sanitasi</span>
                    <div class="flex items-center">
                        @auth
                            @if($familyMember->use_toilet)
                                <div class="w-3 h-3 rounded-full bg-green-500 mr-2"></div>
                                <span class="font-medium text-gray-900">Menggunakan Toilet</span>
                            @else
                                <div class="w-3 h-3 rounded-full bg-red-500 mr-2"></div>
                                <span class="font-medium text-gray-900">Tidak Menggunakan Toilet</span>
                            @endif
                        @else
                            <div class="w-3 h-3 rounded-full bg-gray-400 mr-2"></div>
                            <span class="font-medium text-gray-500">Data tersembunyi</span>
                        @endauth
                    </div>
                </div>
            </div>
            
            {{-- Riwayat penyakit --}}
            <div class="border-t border-gray-200 pt-6">
                <h4 class="font-medium text-gray-900 mb-4">Riwayat Penyakit</h4>
                <div class="space-y-3">
                    {{-- TB Status --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 
                        @auth
                            {{ $familyMember->has_tuberculosis ? 'bg-red-50' : 'bg-green-50' }}
                        @else
                            bg-gray-50
                        @endauth
                        rounded-lg">
                        <div class="flex items-center mb-2 sm:mb-0">
                            <div class="w-3 h-3 rounded-full 
                                @auth
                                    {{ $familyMember->has_tuberculosis ? 'bg-red-500' : 'bg-green-500' }}
                                @else
                                    bg-gray-400
                                @endauth
                                mr-2"></div>
                            <span class="font-medium text-gray-900">Tuberkulosis (TB)</span>
                        </div>
                        <div class="pl-5 sm:pl-0">
                            @auth
                                @if($familyMember->has_tuberculosis)
                                    <span class="text-red-600 font-medium">Pernah Positif</span>
                                    <span class="block text-sm text-gray-500">
                                        {{ $familyMember->takes_tb_medication_regularly ? 'Minum Obat Secara Teratur' : 'Mangkir Obat' }}
                                    </span>
                                @else
                                    <span class="text-green-600 font-medium">Tidak Pernah</span>
                                @endif
                            @else
                                <span class="text-gray-500 font-medium">Data tersembunyi</span>
                            @endauth
                        </div>
                    </div>
                    
                    {{-- Batuk Berdahak --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 
                        @auth
                            {{ $familyMember->has_chronic_cough ? 'bg-red-50' : 'bg-green-50' }}
                        @else
                            bg-gray-50
                        @endauth
                        rounded-lg">
                        <div class="flex items-center mb-2 sm:mb-0">
                            <div class="w-3 h-3 rounded-full 
                                @auth
                                    {{ $familyMember->has_chronic_cough ? 'bg-red-500' : 'bg-green-500' }}
                                @else
                                    bg-gray-400
                                @endauth
                                mr-2"></div>
                            <span class="font-medium text-gray-900">Batuk Berdahak > 2 Minggu</span>
                        </div>
                        <div class="pl-5 sm:pl-0">
                            @auth
                                <span class="{{ $familyMember->has_chronic_cough ? 'text-red-600' : 'text-green-600' }} font-medium">
                                    {{ $familyMember->has_chronic_cough ? 'Ya' : 'Tidak' }}
                                </span>
                            @else
                                <span class="text-gray-500 font-medium">Data tersembunyi</span>
                            @endauth
                        </div>
                    </div>
                    
                    {{-- Hipertensi --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 
                        @auth
                            {{ $familyMember->has_hypertension ? 'bg-red-50' : 'bg-green-50' }}
                        @else
                            bg-gray-50
                        @endauth
                        rounded-lg">
                        <div class="flex items-center mb-2 sm:mb-0">
                            <div class="w-3 h-3 rounded-full 
                                @auth
                                    {{ $familyMember->has_hypertension ? 'bg-red-500' : 'bg-green-500' }}
                                @else
                                    bg-gray-400
                                @endauth
                                mr-2"></div>
                            <span class="font-medium text-gray-900">Hipertensi (Darah Tinggi)</span>
                        </div>
                        <div class="pl-5 sm:pl-0">
                            @auth
                                @if($familyMember->has_hypertension)
                                    <span class="text-red-600 font-medium">Ya</span>
                                    <span class="block text-sm text-gray-500">
                                        {{ $familyMember->takes_hypertension_medication_regularly ? 'Minum Obat Secara Teratur' : 'Tidak Mengonsumsi Obat' }}
                                    </span>
                                @else
                                    <span class="text-green-600 font-medium">Tidak</span>
                                @endif
                            @else
                                <span class="text-gray-500 font-medium">Data tersembunyi</span>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Informasi khusus untuk kategori --}}
            @if($familyMember->is_women_of_reproductive_age)
                <div class="border-t border-gray-200 pt-6">
                    <h4 class="font-medium text-gray-900 mb-4">Informasi Kesehatan Reproduksi</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Kehamilan --}}
                        @if($familyMember->gender === 'Perempuan')
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <span class="text-sm text-gray-500 block mb-1">Status Kehamilan</span>
                                @auth
                                    <span class="font-medium text-gray-900">{{ $familyMember->is_pregnant ? 'Sedang Hamil' : 'Tidak Hamil' }}</span>
                                @else
                                    <span class="font-medium text-gray-500">Data tersembunyi</span>
                                @endauth
                            </div>
                        @endif
                        
                        {{-- Kontrasepsi --}}
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <span class="text-sm text-gray-500 block mb-1">Penggunaan Kontrasepsi</span>
                            @auth
                                <span class="font-medium text-gray-900">{{ $familyMember->uses_contraception ? 'Menggunakan' : 'Tidak Menggunakan' }}</span>
                            @else
                                <span class="font-medium text-gray-500">Data tersembunyi</span>
                            @endauth
                        </div>
                        
                        {{-- Persalinan di Fasilitas Kesehatan --}}
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <span class="text-sm text-gray-500 block mb-1">Persalinan di Fasilitas Kesehatan</span>
                            @auth
                                <span class="font-medium text-gray-900">{{ $familyMember->gave_birth_in_health_facility ? 'Ya' : 'Tidak' }}</span>
                            @else
                                <span class="font-medium text-gray-500">Data tersembunyi</span>
                            @endauth
                        </div>
                        
                        {{-- ASI Eksklusif --}}
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <span class="text-sm text-gray-500 block mb-1">ASI Eksklusif</span>
                            @auth
                                <span class="font-medium text-gray-900">{{ $familyMember->exclusive_breastfeeding ? 'Ya' : 'Tidak' }}</span>
                            @else
                                <span class="font-medium text-gray-500">Data tersembunyi</span>
                            @endauth
                        </div>
                    </div>
                </div>
            @endif
            
            {{-- Informasi balita --}}
            @if($familyMember->is_under_five)
                <div class="border-t border-gray-200 pt-6">
                    <h4 class="font-medium text-gray-900 mb-4">Informasi Kesehatan Balita</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Imunisasi --}}
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <span class="text-sm text-gray-500 block mb-1">Imunisasi Lengkap</span>
                            @auth
                                <span class="font-medium text-gray-900">{{ $familyMember->complete_immunization ? 'Ya' : 'Tidak' }}</span>
                            @else
                                <span class="font-medium text-gray-500">Data tersembunyi</span>
                            @endauth
                        </div>
                        
                        {{-- Pemantauan Pertumbuhan --}}
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <span class="text-sm text-gray-500 block mb-1">Pemantauan Pertumbuhan</span>
                            @auth
                                <span class="font-medium text-gray-900">{{ $familyMember->growth_monitoring ? 'Rutin' : 'Tidak Rutin' }}</span>
                            @else
                                <span class="font-medium text-gray-500">Data tersembunyi</span>
                            @endauth
                        </div>
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>