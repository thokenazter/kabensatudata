{{-- Komponen untuk menampilkan Indeks Keluarga Sehat (IKS) --}}
@props(['family'])

<div class="bg-white shadow-lg rounded-lg overflow-hidden">
    <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4">
        <h3 class="text-xl font-semibold text-white">Indeks Keluarga Sehat (IKS)</h3>
        <p class="text-green-100 text-sm">Status kesehatan keluarga berdasarkan 12 indikator</p>
    </div>
    
    <div class="p-6">
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
                            {{ $family->healthIndex->health_status }}
                        </div>
                        <div class="text-2xl font-bold mt-1">
                            {{ number_format($family->healthIndex->iks_value * 100, 1) }}%
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
                            style="width: {{ $family->healthIndex->iks_value * 100 }}%">
                        </div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                        <span>Tidak Sehat</span>
                        <span>Pra-Sehat</span>
                        <span>Sehat</span>
                    </div>
                </div>
                
                <!-- Indicator Summary -->
                <div class="mt-6 flex flex-wrap gap-2">
                    <div class="bg-gray-100 rounded-lg px-4 py-2 text-center">
                        <span class="text-sm text-gray-500">Indikator Relevan</span>
                        <div class="text-lg font-medium text-gray-900">{{ $family->healthIndex->relevant_indicators }}</div>
                    </div>
                    
                    <div class="bg-gray-100 rounded-lg px-4 py-2 text-center">
                        <span class="text-sm text-gray-500">Indikator Terpenuhi</span>
                        <div class="text-lg font-medium text-gray-900">{{ $family->healthIndex->fulfilled_indicators }}</div>
                    </div>
                </div>
            </div>
            
            <!-- Indicator Details -->
            <div>
                <h4 class="text-lg font-medium text-gray-900 mb-4">Detail Indikator</h4>
                
                <div class="space-y-3">
                    {{-- KB --}}
                    @if($family->healthIndex->kb_relevant)
                    <div class="flex items-center justify-between p-3 rounded-lg {{ $family->healthIndex->kb_status ? 'bg-green-50' : 'bg-red-50' }}">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full {{ $family->healthIndex->kb_status ? 'bg-green-500' : 'bg-red-500' }} mr-3"></div>
                            <span class="font-medium">1. Keluarga Berencana</span>
                        </div>
                        <span class="text-sm {{ $family->healthIndex->kb_status ? 'text-green-600' : 'text-red-600' }}">
                            {{ $family->healthIndex->kb_status ? 'Terpenuhi' : 'Tidak Terpenuhi' }}
                        </span>
                    </div>
                    @endif
                    
                    {{-- Persalinan di Fasilitas Kesehatan --}}
                    @if($family->healthIndex->birth_facility_relevant)
                    <div class="flex items-center justify-between p-3 rounded-lg {{ $family->healthIndex->birth_facility_status ? 'bg-green-50' : 'bg-red-50' }}">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full {{ $family->healthIndex->birth_facility_status ? 'bg-green-500' : 'bg-red-500' }} mr-3"></div>
                            <span class="font-medium">2. Persalinan di Fasilitas Kesehatan</span>
                        </div>
                        <span class="text-sm {{ $family->healthIndex->birth_facility_status ? 'text-green-600' : 'text-red-600' }}">
                            {{ $family->healthIndex->birth_facility_status ? 'Terpenuhi' : 'Tidak Terpenuhi' }}
                        </span>
                    </div>
                    @endif
                    
                    {{-- Imunisasi --}}
                    @if($family->healthIndex->immunization_relevant)
                    <div class="flex items-center justify-between p-3 rounded-lg {{ $family->healthIndex->immunization_status ? 'bg-green-50' : 'bg-red-50' }}">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full {{ $family->healthIndex->immunization_status ? 'bg-green-500' : 'bg-red-500' }} mr-3"></div>
                            <span class="font-medium">3. Imunisasi Lengkap</span>
                        </div>
                        <span class="text-sm {{ $family->healthIndex->immunization_status ? 'text-green-600' : 'text-red-600' }}">
                            {{ $family->healthIndex->immunization_status ? 'Terpenuhi' : 'Tidak Terpenuhi' }}
                        </span>
                    </div>
                    @endif
                    
                    {{-- ASI Eksklusif --}}
                    @if($family->healthIndex->exclusive_breastfeeding_relevant)
                    <div class="flex items-center justify-between p-3 rounded-lg {{ $family->healthIndex->exclusive_breastfeeding_status ? 'bg-green-50' : 'bg-red-50' }}">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full {{ $family->healthIndex->exclusive_breastfeeding_status ? 'bg-green-500' : 'bg-red-500' }} mr-3"></div>
                            <span class="font-medium">4. ASI Eksklusif</span>
                        </div>
                        <span class="text-sm {{ $family->healthIndex->exclusive_breastfeeding_status ? 'text-green-600' : 'text-red-600' }}">
                            {{ $family->healthIndex->exclusive_breastfeeding_status ? 'Terpenuhi' : 'Tidak Terpenuhi' }}
                        </span>
                    </div>
                    @endif
                    
                    {{-- Pemantauan Pertumbuhan --}}
                    @if($family->healthIndex->growth_monitoring_relevant)
                    <div class="flex items-center justify-between p-3 rounded-lg {{ $family->healthIndex->growth_monitoring_status ? 'bg-green-50' : 'bg-red-50' }}">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full {{ $family->healthIndex->growth_monitoring_status ? 'bg-green-500' : 'bg-red-500' }} mr-3"></div>
                            <span class="font-medium">5. Pemantauan Pertumbuhan</span>
                        </div>
                        <span class="text-sm {{ $family->healthIndex->growth_monitoring_status ? 'text-green-600' : 'text-red-600' }}">
                            {{ $family->healthIndex->growth_monitoring_status ? 'Terpenuhi' : 'Tidak Terpenuhi' }}
                        </span>
                    </div>
                    @endif
                    
                    {{-- Pengobatan TB --}}
                    @if($family->healthIndex->tb_treatment_relevant)
                    <div class="flex items-center justify-between p-3 rounded-lg {{ $family->healthIndex->tb_treatment_status ? 'bg-green-50' : 'bg-red-50' }}">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full {{ $family->healthIndex->tb_treatment_status ? 'bg-green-500' : 'bg-red-500' }} mr-3"></div>
                            <span class="font-medium">6. Pengobatan TB</span>
                        </div>
                        <span class="text-sm {{ $family->healthIndex->tb_treatment_status ? 'text-green-600' : 'text-red-600' }}">
                            {{ $family->healthIndex->tb_treatment_status ? 'Terpenuhi' : 'Tidak Terpenuhi' }}
                        </span>
                    </div>
                    @endif
                    
                    {{-- Pengobatan Hipertensi --}}
                    @if($family->healthIndex->hypertension_treatment_relevant)
                    <div class="flex items-center justify-between p-3 rounded-lg {{ $family->healthIndex->hypertension_treatment_status ? 'bg-green-50' : 'bg-red-50' }}">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full {{ $family->healthIndex->hypertension_treatment_status ? 'bg-green-500' : 'bg-red-500' }} mr-3"></div>
                            <span class="font-medium">7. Pengobatan Hipertensi</span>
                        </div>
                        <span class="text-sm {{ $family->healthIndex->hypertension_treatment_status ? 'text-green-600' : 'text-red-600' }}">
                            {{ $family->healthIndex->hypertension_treatment_status ? 'Terpenuhi' : 'Tidak Terpenuhi' }}
                        </span>
                    </div>
                    @endif
                    
                    {{-- Pengobatan Gangguan Jiwa --}}
                    @if($family->healthIndex->mental_treatment_relevant)
                    <div class="flex items-center justify-between p-3 rounded-lg {{ $family->healthIndex->mental_treatment_status ? 'bg-green-50' : 'bg-red-50' }}">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full {{ $family->healthIndex->mental_treatment_status ? 'bg-green-500' : 'bg-red-500' }} mr-3"></div>
                            <span class="font-medium">8. Pengobatan Gangguan Jiwa</span>
                        </div>
                        <span class="text-sm {{ $family->healthIndex->mental_treatment_status ? 'text-green-600' : 'text-red-600' }}">
                            {{ $family->healthIndex->mental_treatment_status ? 'Terpenuhi' : 'Tidak Terpenuhi' }}
                        </span>
                    </div>
                    @endif
                    
                    {{-- Tidak Merokok --}}
                    @if($family->healthIndex->no_smoking_relevant)
                    <div class="flex items-center justify-between p-3 rounded-lg {{ $family->healthIndex->no_smoking_status ? 'bg-green-50' : 'bg-red-50' }}">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full {{ $family->healthIndex->no_smoking_status ? 'bg-green-500' : 'bg-red-500' }} mr-3"></div>
                            <span class="font-medium">9. Tidak Merokok</span>
                        </div>
                        <span class="text-sm {{ $family->healthIndex->no_smoking_status ? 'text-green-600' : 'text-red-600' }}">
                            {{ $family->healthIndex->no_smoking_status ? 'Terpenuhi' : 'Tidak Terpenuhi' }}
                        </span>
                    </div>
                    @endif
                    
                    {{-- JKN --}}
                    @if($family->healthIndex->jkn_membership_relevant)
                    <div class="flex items-center justify-between p-3 rounded-lg {{ $family->healthIndex->jkn_membership_status ? 'bg-green-50' : 'bg-red-50' }}">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full {{ $family->healthIndex->jkn_membership_status ? 'bg-green-500' : 'bg-red-500' }} mr-3"></div>
                            <span class="font-medium">10. Keanggotaan JKN</span>
                        </div>
                        <span class="text-sm {{ $family->healthIndex->jkn_membership_status ? 'text-green-600' : 'text-red-600' }}">
                            {{ $family->healthIndex->jkn_membership_status ? 'Terpenuhi' : 'Tidak Terpenuhi' }}
                        </span>
                    </div>
                    @endif
                    
                    {{-- Air Bersih --}}
                    @if($family->healthIndex->clean_water_relevant)
                    <div class="flex items-center justify-between p-3 rounded-lg {{ $family->healthIndex->clean_water_status ? 'bg-green-50' : 'bg-red-50' }}">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full {{ $family->healthIndex->clean_water_status ? 'bg-green-500' : 'bg-red-500' }} mr-3"></div>
                            <span class="font-medium">11. Air Bersih</span>
                        </div>
                        <span class="text-sm {{ $family->healthIndex->clean_water_status ? 'text-green-600' : 'text-red-600' }}">
                            {{ $family->healthIndex->clean_water_status ? 'Terpenuhi' : 'Tidak Terpenuhi' }}
                        </span>
                    </div>
                    @endif
                    
                    {{-- Jamban Sehat --}}
                    @if($family->healthIndex->sanitary_toilet_relevant)
                    <div class="flex items-center justify-between p-3 rounded-lg {{ $family->healthIndex->sanitary_toilet_status ? 'bg-green-50' : 'bg-red-50' }}">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full {{ $family->healthIndex->sanitary_toilet_status ? 'bg-green-500' : 'bg-red-500' }} mr-3"></div>
                            <span class="font-medium">12. Jamban Sehat</span>
                        </div>
                        <span class="text-sm {{ $family->healthIndex->sanitary_toilet_status ? 'text-green-600' : 'text-red-600' }}">
                            {{ $family->healthIndex->sanitary_toilet_status ? 'Terpenuhi' : 'Tidak Terpenuhi' }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        @else
            <div class="text-center py-8">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h4 class="text-lg font-medium text-gray-900 mb-1">Data IKS belum tersedia</h4>
                <p class="text-gray-500 mb-4">Indeks Keluarga Sehat belum dihitung untuk keluarga ini</p>
                
                @if(auth()->check() && auth()->user()->can('calculate-iks'))
                    <a href="{{ route('families.calculate-iks', $family->id) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        Hitung IKS Sekarang
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>