<div class="bg-white shadow-sm rounded-lg overflow-hidden info-card md:col-span-2">
    <div class="px-4 py-3 bg-red-50 border-b border-red-100">
        <h3 class="text-lg font-medium text-red-800 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-red-600" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
            </svg>
            Informasi Kesehatan
        </h3>
    </div>
    <div class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Kolom 1: Status Kesehatan -->
            <div>
                <h4 class="font-medium text-gray-700 mb-2">Status Kesehatan</h4>
                <ul class="space-y-1 text-sm">
                    <li class="flex items-center">
                        <span class="w-6 h-6 flex-shrink-0 mr-2 {{ $familyMember->has_jkn ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500' }} rounded-full flex items-center justify-center text-xs font-medium">
                            {{ $familyMember->has_jkn ? '✓' : '✕' }}
                        </span>
                        <span>Memiliki JKN</span>
                    </li>
                    <li class="flex items-center">
                        <span class="w-6 h-6 flex-shrink-0 mr-2 {{ $familyMember->is_smoker ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }} rounded-full flex items-center justify-center text-xs font-medium">
                            {{ $familyMember->is_smoker ? '✕' : '✓' }}
                        </span>
                        <span>{{ $familyMember->is_smoker ? 'Perokok' : 'Tidak Merokok' }}</span>
                    </li>
                    <li class="flex items-center">
                        <span class="w-6 h-6 flex-shrink-0 mr-2 {{ $familyMember->has_tuberculosis ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }} rounded-full flex items-center justify-center text-xs font-medium">
                            {{ $familyMember->has_tuberculosis ? '!' : '✓' }}
                        </span>
                        <span>
                            @if($familyMember->has_tuberculosis)
                                TBC {{ $familyMember->takes_tb_medication_regularly ? '(Minum Obat Teratur)' : '(Tidak Teratur)' }}
                            @else
                                Tidak TBC
                            @endif
                        </span>
                    </li>
                    <li class="flex items-center">
                        <span class="w-6 h-6 flex-shrink-0 mr-2 {{ $familyMember->has_hypertension ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }} rounded-full flex items-center justify-center text-xs font-medium">
                            {{ $familyMember->has_hypertension ? '!' : '✓' }}
                        </span>
                        <span>
                            @if($familyMember->has_hypertension)
                                Hipertensi {{ $familyMember->takes_hypertension_medication_regularly ? '(Minum Obat Teratur)' : '(Tidak Teratur)' }}
                            @else
                                Tidak Hipertensi
                            @endif
                        </span>
                    </li>
                </ul>
            </div>
            
            <!-- Kolom 2: Status Kesehatan Khusus -->
            <div>
                <h4 class="font-medium text-gray-700 mb-2">Status Khusus</h4>
                <ul class="space-y-1 text-sm">
                    @if($familyMember->gender === 'Perempuan' && $familyMember->is_pregnant)
                    <li class="flex items-center">
                        <span class="w-6 h-6 flex-shrink-0 mr-2 bg-blue-100 text-blue-800 rounded-full flex items-center justify-center text-xs font-medium">!</span>
                        <span>Sedang Hamil</span>
                    </li>
                    @endif
                    
                    @if($familyMember->uses_contraception)
                    <li class="flex items-center">
                        <span class="w-6 h-6 flex-shrink-0 mr-2 bg-green-100 text-green-800 rounded-full flex items-center justify-center text-xs font-medium">✓</span>
                        <span>Menggunakan KB</span>
                    </li>
                    @endif
                    
                    @if($familyMember->gave_birth_in_health_facility)
                    <li class="flex items-center">
                        <span class="w-6 h-6 flex-shrink-0 mr-2 bg-green-100 text-green-800 rounded-full flex items-center justify-center text-xs font-medium">✓</span>
                        <span>Melahirkan di Fasilitas Kesehatan</span>
                    </li>
                    @endif
                    
                    @if($familyMember->exclusive_breastfeeding)
                    <li class="flex items-center">
                        <span class="w-6 h-6 flex-shrink-0 mr-2 bg-green-100 text-green-800 rounded-full flex items-center justify-center text-xs font-medium">✓</span>
                        <span>ASI Eksklusif</span>
                    </li>
                    @endif
                    
                    @if($familyMember->complete_immunization)
                    <li class="flex items-center">
                        <span class="w-6 h-6 flex-shrink-0 mr-2 bg-green-100 text-green-800 rounded-full flex items-center justify-center text-xs font-medium">✓</span>
                        <span>Imunisasi Lengkap</span>
                    </li>
                    @endif
                    
                    @if($familyMember->growth_monitoring)
                    <li class="flex items-center">
                        <span class="w-6 h-6 flex-shrink-0 mr-2 bg-green-100 text-green-800 rounded-full flex items-center justify-center text-xs font-medium">✓</span>
                        <span>Pemantauan Pertumbuhan</span>
                    </li>
                    @endif
                </ul>
            </div>
            
            <!-- Kolom 3: Status Sanitasi -->
            <div>
                <h4 class="font-medium text-gray-700 mb-2">Sanitasi</h4>
                <ul class="space-y-1 text-sm">
                    <li class="flex items-center">
                        <span class="w-6 h-6 flex-shrink-0 mr-2 {{ $familyMember->family->has_clean_water ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} rounded-full flex items-center justify-center text-xs font-medium">
                            {{ $familyMember->family->has_clean_water ? '✓' : '✕' }}
                        </span>
                        <span>
                            @if($familyMember->family->has_clean_water)
                                Air Bersih {{ $familyMember->family->is_water_protected ? '(Terlindungi)' : '(Tidak Terlindungi)' }}
                            @else
                                Tidak Ada Air Bersih
                            @endif
                        </span>
                    </li>
                    <li class="flex items-center">
                        <span class="w-6 h-6 flex-shrink-0 mr-2 {{ $familyMember->family->has_toilet ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} rounded-full flex items-center justify-center text-xs font-medium">
                            {{ $familyMember->family->has_toilet ? '✓' : '✕' }}
                        </span>
                        <span>
                            @if($familyMember->family->has_toilet)
                                Jamban {{ $familyMember->family->is_toilet_sanitary ? '(Saniter)' : '(Tidak Saniter)' }}
                            @else
                                Tidak Ada Jamban
                            @endif
                        </span>
                    </li>
                    <li class="flex items-center">
                        <span class="w-6 h-6 flex-shrink-0 mr-2 {{ $familyMember->family->has_mental_illness ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }} rounded-full flex items-center justify-center text-xs font-medium">
                            {{ $familyMember->family->has_mental_illness ? '!' : '✓' }}
                        </span>
                        <span>
                            @if($familyMember->family->has_mental_illness)
                                Ada Gangguan Jiwa {{ $familyMember->family->takes_medication_regularly ? '(Berobat Teratur)' : '(Tidak Teratur)' }}
                            @else
                                Tidak Ada Gangguan Jiwa
                            @endif
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>