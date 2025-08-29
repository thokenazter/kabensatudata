{{-- Komponen untuk menampilkan ringkasan kesehatan dalam satu card --}}
@props(['familyMember'])

<div class="bg-white shadow-lg rounded-lg overflow-hidden">
    <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
        <h3 class="text-xl font-semibold text-white">Ringkasan Kesehatan</h3>
        <p class="text-blue-100 text-sm">Data kesehatan anggota keluarga</p>
    </div>
    
    <div class="p-6 space-y-6">
        {{-- Status dasar kesehatan --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="bg-gray-50 p-4 rounded-lg">
                <span class="text-sm text-gray-500 block mb-1">Status JKN</span>
                <div class="flex items-center">
                    @if($familyMember->has_jkn)
                        <div class="w-3 h-3 rounded-full bg-green-500 mr-2"></div>
                        <span class="font-medium text-gray-900">Terdaftar</span>
                    @else
                        <div class="w-3 h-3 rounded-full bg-red-500 mr-2"></div>
                        <span class="font-medium text-gray-900">Tidak Terdaftar</span>
                    @endif
                </div>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg">
                <span class="text-sm text-gray-500 block mb-1">Status Merokok</span>
                <div class="flex items-center">
                    @if($familyMember->is_smoker)
                        <div class="w-3 h-3 rounded-full bg-yellow-500 mr-2"></div>
                        <span class="font-medium text-gray-900">Perokok Aktif</span>
                    @else
                        <div class="w-3 h-3 rounded-full bg-green-500 mr-2"></div>
                        <span class="font-medium text-gray-900">Bukan Perokok</span>
                    @endif
                </div>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg">
                <span class="text-sm text-gray-500 block mb-1">Sanitasi</span>
                <div class="flex items-center">
                    @if($familyMember->use_toilet)
                        <div class="w-3 h-3 rounded-full bg-green-500 mr-2"></div>
                        <span class="font-medium text-gray-900">Menggunakan Toilet</span>
                    @else
                        <div class="w-3 h-3 rounded-full bg-red-500 mr-2"></div>
                        <span class="font-medium text-gray-900">Tidak Menggunakan Toilet</span>
                    @endif
                </div>
            </div>
        </div>
        
        {{-- Riwayat penyakit --}}
        <div class="border-t border-gray-200 pt-6">
            <h4 class="font-medium text-gray-900 mb-4">Riwayat Penyakit</h4>
            <div class="space-y-3">
                {{-- TB Status --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 {{ $familyMember->has_tuberculosis ? 'bg-red-50' : 'bg-green-50' }} rounded-lg">
                    <div class="flex items-center mb-2 sm:mb-0">
                        <div class="w-3 h-3 rounded-full {{ $familyMember->has_tuberculosis ? 'bg-red-500' : 'bg-green-500' }} mr-2"></div>
                        <span class="font-medium text-gray-900">Tuberkulosis (TB)</span>
                    </div>
                    <div class="pl-5 sm:pl-0">
                        @if($familyMember->has_tuberculosis)
                            <span class="text-red-600 font-medium">Pernah Positif</span>
                            <span class="block text-sm text-gray-500">
                                {{ $familyMember->takes_tb_medication_regularly ? 'Minum Obat Secara Teratur' : 'Mangkir Obat' }}
                            </span>
                        @else
                            <span class="text-green-600 font-medium">Tidak Pernah</span>
                        @endif
                    </div>
                </div>
                
                {{-- Batuk Berdahak --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 {{ $familyMember->has_chronic_cough ? 'bg-red-50' : 'bg-green-50' }} rounded-lg">
                    <div class="flex items-center mb-2 sm:mb-0">
                        <div class="w-3 h-3 rounded-full {{ $familyMember->has_chronic_cough ? 'bg-red-500' : 'bg-green-500' }} mr-2"></div>
                        <span class="font-medium text-gray-900">Batuk Berdahak > 2 Minggu</span>
                    </div>
                    <div class="pl-5 sm:pl-0">
                        <span class="{{ $familyMember->has_chronic_cough ? 'text-red-600' : 'text-green-600' }} font-medium">
                            {{ $familyMember->has_chronic_cough ? 'Ya' : 'Tidak' }}
                        </span>
                    </div>
                </div>
                
                {{-- Hipertensi --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 {{ $familyMember->has_hypertension ? 'bg-red-50' : 'bg-green-50' }} rounded-lg">
                    <div class="flex items-center mb-2 sm:mb-0">
                        <div class="w-3 h-3 rounded-full {{ $familyMember->has_hypertension ? 'bg-red-500' : 'bg-green-500' }} mr-2"></div>
                        <span class="font-medium text-gray-900">Hipertensi (Darah Tinggi)</span>
                    </div>
                    <div class="pl-5 sm:pl-0">
                        @if($familyMember->has_hypertension)
                            <span class="text-red-600 font-medium">Ya</span>
                            <span class="block text-sm text-gray-500">
                                {{ $familyMember->takes_hypertension_medication_regularly ? 'Minum Obat Secara Teratur' : 'Tidak Mengonsumsi Obat' }}
                            </span>
                        @else
                            <span class="text-green-600 font-medium">Tidak</span>
                        @endif
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
                            <span class="font-medium text-gray-900">{{ $familyMember->is_pregnant ? 'Sedang Hamil' : 'Tidak Hamil' }}</span>
                        </div>
                    @endif
                    
                    {{-- Kontrasepsi --}}
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <span class="text-sm text-gray-500 block mb-1">Penggunaan Kontrasepsi</span>
                        <span class="font-medium text-gray-900">{{ $familyMember->uses_contraception ? 'Menggunakan' : 'Tidak Menggunakan' }}</span>
                    </div>
                    
                    {{-- Persalinan di Fasilitas Kesehatan --}}
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <span class="text-sm text-gray-500 block mb-1">Persalinan di Fasilitas Kesehatan</span>
                        <span class="font-medium text-gray-900">{{ $familyMember->gave_birth_in_health_facility ? 'Ya' : 'Tidak' }}</span>
                    </div>
                    
                    {{-- ASI Eksklusif --}}
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <span class="text-sm text-gray-500 block mb-1">ASI Eksklusif</span>
                        <span class="font-medium text-gray-900">{{ $familyMember->exclusive_breastfeeding ? 'Ya' : 'Tidak' }}</span>
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
                        <span class="font-medium text-gray-900">{{ $familyMember->complete_immunization ? 'Ya' : 'Tidak' }}</span>
                    </div>
                    
                    {{-- Pemantauan Pertumbuhan --}}
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <span class="text-sm text-gray-500 block mb-1">Pemantauan Pertumbuhan</span>
                        <span class="font-medium text-gray-900">{{ $familyMember->growth_monitoring ? 'Rutin' : 'Tidak Rutin' }}</span>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>