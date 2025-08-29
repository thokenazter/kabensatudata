{{-- family-card.blade.php - Full Page Version --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Keluarga - {{ $family->family_number ?? 'Nomor Belum Terdaftar' }}</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            @page {
                size: A4;
                margin: 0;
            }
            
            .no-print {
                display: none;
            }
        }
        
        .page {
            min-height: 100vh;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Floating Print Button -->
    <div class="fixed top-4 right-4 z-50 no-print">
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Cetak
        </button>
    </div>
</body>
</html>

    <!-- Back Button -->
    <div class="fixed top-4 left-4 z-50 no-print">
        <a href="javascript:history.back()" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded shadow flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </div>

    <div class="page bg-white shadow-lg max-w-6xl mx-auto my-4 print:my-0 print:shadow-none">
        <!-- Header Kartu Keluarga -->
        <div class="bg-gradient-to-r from-blue-700 to-blue-900 text-white p-6 print:p-4">
            <div class="flex items-center mb-4">
                <div class="mr-4">
                    <img src="{{ asset('images/garuda.png') }}" alt="Garuda Pancasila" class="h-20 w-auto" onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/9/90/National_emblem_of_Indonesia_Garuda_Pancasila.svg/220px-National_emblem_of_Indonesia_Garuda_Pancasila.svg.png'; this.onerror=null;">
                </div>
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-center">KARTU KELUARGA</h1>
                    <p class="text-xl text-center">No. {{ $family->family_number ?? 'Belum terdaftar' }}</p>
                </div>
            </div>
            
            <div class="flex flex-col md:flex-row justify-between">
                <div>
                    <table class="text-sm">
                        <tr>
                            <td class="pr-2">Nama Kepala Keluarga</td>
                            <td>: <span class="font-semibold">{{ $family->head_name ?? ($family->members->where('relationship', 'Kepala Keluarga')->first()->name ?? '-') }}</span></td>
                        </tr>
                        <tr>
                            <td class="pr-2">Alamat</td>
                            <td>: <span class="font-semibold">{{ $family->building->description ?? 'Alamat tidak tersedia' }}</span></td>
                        </tr>
                        <tr>
                            <td class="pr-2">RT/RW</td>
                            <td>: <span class="font-semibold">-/-</span></td>
                        </tr>
                        <tr>
                            <td class="pr-2">Kode Pos</td>
                            <td>: <span class="font-semibold">-</span></td>
                        </tr>
                    </table>
                </div>
                
                <div class="mt-4 md:mt-0">
                    <table class="text-sm">
                        <tr>
                            <td class="pr-2">Desa/Kelurahan</td>
                            <td>: <span class="font-semibold">{{ $family->building->village->name ?? $family->village->name ?? '-' }}</span></td>
                        </tr>
                        <tr>
                            <td class="pr-2">Kecamatan</td>
                            <td>: <span class="font-semibold">{{ $family->building->village->district ?? '-' }}</span></td>
                        </tr>
                        <tr>
                            <td class="pr-2">Kabupaten/Kota</td>
                            <td>: <span class="font-semibold">{{ $family->building->village->regency ?? '-' }}</span></td>
                        </tr>
                        <tr>
                            <td class="pr-2">Provinsi</td>
                            <td>: <span class="font-semibold">{{ $family->building->village->province ?? '-' }}</span></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tabel Anggota Keluarga -->
        <div class="overflow-x-auto px-6 py-4 print:px-4 print:py-2">
            <table class="min-w-full border border-gray-300">
                <thead>
                    <tr class="bg-gray-200 text-gray-700 text-xs">
                        <th class="py-2 px-3 text-center border border-gray-300 w-10">No</th>
                        <th class="py-2 px-3 text-center border border-gray-300">Nama Lengkap</th>
                        <th class="py-2 px-3 text-center border border-gray-300">NIK</th>
                        <th class="py-2 px-3 text-center border border-gray-300">Jenis Kelamin</th>
                        <th class="py-2 px-3 text-center border border-gray-300">Tempat Lahir</th>
                        <th class="py-2 px-3 text-center border border-gray-300">Tanggal Lahir</th>
                        <th class="py-2 px-3 text-center border border-gray-300">Agama</th>
                        <th class="py-2 px-3 text-center border border-gray-300">Pendidikan</th>
                        <th class="py-2 px-3 text-center border border-gray-300">Pekerjaan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-300">
                    @forelse($family->members as $index => $member)
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-3 text-center border border-gray-300 text-sm">{{ $index + 1 }}</td>
                        <td class="py-2 px-3 border border-gray-300 text-sm font-medium">
                            <a href="{{ route('family-members.show', $member) }}" class="text-blue-600 hover:text-blue-800 hover:underline no-print">
                                {{ $member->name }}
                            </a>
                            <span class="print:block hidden">{{ $member->name }}</span>
                        </td>
                        <td class="py-2 px-3 border border-gray-300 text-sm">{{ $member->nik ?? '-' }}</td>
                        <td class="py-2 px-3 border border-gray-300 text-sm">{{ $member->gender }}</td>
                        <td class="py-2 px-3 border border-gray-300 text-sm">{{ $member->birth_place ?? '-' }}</td>
                        <td class="py-2 px-3 border border-gray-300 text-sm">{{ $member->birth_date ? $member->birth_date->format('d-m-Y') : '-' }}</td>
                        <td class="py-2 px-3 border border-gray-300 text-sm">{{ $member->religion ?? '-' }}</td>
                        <td class="py-2 px-3 border border-gray-300 text-sm">{{ $member->education ?? '-' }}</td>
                        <td class="py-2 px-3 border border-gray-300 text-sm">{{ $member->occupation ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-4 px-3 text-center border border-gray-300 text-gray-500">Tidak ada data anggota keluarga</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Tabel Kedua: Informasi Perkawinan, Hubungan, dll -->
        <div class="overflow-x-auto px-6 py-2 print:px-4">
            <table class="min-w-full border border-gray-300">
                <thead>
                    <tr class="bg-gray-200 text-gray-700 text-xs">
                        <th class="py-2 px-3 text-center border border-gray-300 w-10">No</th>
                        <th class="py-2 px-3 text-center border border-gray-300">Status Perkawinan</th>
                        <th class="py-2 px-3 text-center border border-gray-300">Hubungan dalam Keluarga</th>
                        <th class="py-2 px-3 text-center border border-gray-300">Kewarganegaraan</th>
                        <th class="py-2 px-3 text-center border border-gray-300">No. Dokumen Imigrasi</th>
                        <th class="py-2 px-3 text-center border border-gray-300">Nama Ayah</th>
                        <th class="py-2 px-3 text-center border border-gray-300">Nama Ibu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-300">
                    @forelse($family->members as $index => $member)
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-3 text-center border border-gray-300 text-sm">{{ $index + 1 }}</td>
                        <td class="py-2 px-3 border border-gray-300 text-sm">{{ $member->marital_status ?? '-' }}</td>
                        <td class="py-2 px-3 border border-gray-300 text-sm">{{ $member->relationship ?? '-' }}</td>
                        <td class="py-2 px-3 border border-gray-300 text-sm">Indonesia</td>
                        <td class="py-2 px-3 border border-gray-300 text-sm">-</td>
                        <td class="py-2 px-3 border border-gray-300 text-sm">-</td>
                        <td class="py-2 px-3 border border-gray-300 text-sm">-</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-4 px-3 text-center border border-gray-300 text-gray-500">Tidak ada data anggota keluarga</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Informasi PISPK Keluarga -->
        <div class="px-6 py-4 print:px-4 print:py-2">
            <h3 class="text-lg font-semibold mb-3">Informasi Kesehatan Keluarga (PISPK)</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <!-- Status Kesehatan -->
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Status Kesehatan Keluarga</h4>
                    @if(isset($family->healthIndex) && $family->healthIndex)
                        @php
                            $statusClass = match($family->healthIndex->health_status) {
                                'Sehat' => 'bg-green-100 text-green-800 border-green-200',
                                'Pra-Sehat' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                'Tidak Sehat' => 'bg-red-100 text-red-800 border-red-200',
                                default => 'bg-gray-100 text-gray-800 border-gray-200'
                            };
                        @endphp
                        <div class="flex items-center">
                            <div class="mr-3 inline-block px-3 py-1 rounded-full {{ $statusClass }} border font-medium">
                                {{ $family->healthIndex->health_status ?? 'Belum dinilai' }}
                            </div>
                            <div class="text-sm">
                                IKS: <span class="font-semibold">{{ $family->healthIndex->iks_value ?? '-' }}</span>
                            </div>
                        </div>
                    @else
                        <div class="inline-block px-3 py-1 rounded-full bg-gray-100 text-gray-800 border border-gray-200 font-medium">
                            Belum dinilai
                        </div>
                    @endif
                </div>

                <!-- Sanitasi -->
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Sanitasi</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-6 h-6 flex items-center justify-center rounded-full {{ $family->has_clean_water ? 'bg-green-100 text-green-500' : 'bg-red-100 text-red-500' }} mr-2">
                                @if($family->has_clean_water)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                @endif
                            </div>
                            <div class="text-sm">
                                <span class="font-medium">Air Bersih:</span> {{ $family->has_clean_water ? 'Tersedia' : 'Tidak Tersedia' }}
                                @if($family->has_clean_water)
                                    <span class="text-xs text-gray-500">({{ $family->is_water_protected ? 'Terlindungi' : 'Tidak Terlindungi' }})</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-6 h-6 flex items-center justify-center rounded-full {{ $family->has_toilet ? 'bg-green-100 text-green-500' : 'bg-red-100 text-red-500' }} mr-2">
                                @if($family->has_toilet)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                @endif
                            </div>
                            <div class="text-sm">
                                <span class="font-medium">Jamban:</span> {{ $family->has_toilet ? 'Tersedia' : 'Tidak Tersedia' }}
                                @if($family->has_toilet)
                                    <span class="text-xs text-gray-500">({{ $family->is_toilet_sanitary ? 'Sehat' : 'Tidak Sehat' }})</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Informasi Kesehatan Lainnya -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <!-- Gangguan Jiwa -->
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="bg-gray-100 py-2 px-4 font-medium">Kesehatan Jiwa</div>
                    <div class="p-3">
                        <div class="flex items-center mb-1">
                            <div class="w-6 h-6 flex items-center justify-center rounded-full {{ $family->has_mental_illness ? 'bg-red-100 text-red-500' : 'bg-green-100 text-green-500' }} mr-2">
                                @if(!$family->has_mental_illness)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                @endif
                            </div>
                            <div class="text-sm">
                                Ada anggota dengan gangguan jiwa: <span class="font-medium">{{ $family->has_mental_illness ? 'Ya' : 'Tidak' }}</span>
                            </div>
                        </div>
                        @if($family->has_mental_illness)
                        <div class="flex items-center">
                            <div class="w-6 h-6 flex items-center justify-center rounded-full {{ $family->takes_medication_regularly ? 'bg-green-100 text-green-500' : 'bg-red-100 text-red-500' }} mr-2">
                                @if($family->takes_medication_regularly)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                @endif
                            </div>
                            <div class="text-sm">
                                Minum obat secara teratur: <span class="font-medium">{{ $family->takes_medication_regularly ? 'Ya' : 'Tidak' }}</span>
                            </div>
                        </div>
                        <div class="flex items-center mt-1">
                            <div class="w-6 h-6 flex items-center justify-center rounded-full {{ $family->has_restrained_member ? 'bg-red-100 text-red-500' : 'bg-green-100 text-green-500' }} mr-2">
                                @if(!$family->has_restrained_member)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                @endif
                            </div>
                            <div class="text-sm">
                                Ada anggota dipasung: <span class="font-medium">{{ $family->has_restrained_member ? 'Ya' : 'Tidak' }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                
                <!-- Gaya Hidup -->
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="bg-gray-100 py-2 px-4 font-medium">Gaya Hidup</div>
                    <div class="p-3">
                        <div class="flex items-center mb-1">
                            <div class="text-sm">
                                <span class="font-medium">Anggota yang merokok:</span> 
                                {{ $family->members->where('is_smoker', true)->count() }} orang
                            </div>
                        </div>
                        <div class="flex items-center mb-1">
                            <div class="text-sm">
                                <span class="font-medium">JKN:</span> 
                                {{ $family->members->where('has_jkn', true)->count() }} dari {{ $family->members->count() }} orang
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Terakhir diperbarui -->
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="bg-gray-100 py-2 px-4 font-medium">Informasi PISPK</div>
                    <div class="p-3">
                        @if(isset($family->healthIndex) && $family->healthIndex)
                        <div class="text-sm">
                            <span class="font-medium">Terakhir dihitung:</span> 
                            {{ $family->healthIndex->calculated_at ? $family->healthIndex->calculated_at->format('d-m-Y H:i') : '-' }}
                        </div>
                        <div class="text-sm mt-1">
                            <span class="font-medium">Indikator Relevan:</span> 
                            {{ $family->healthIndex->relevant_indicators ?? '0' }} indikator
                        </div>
                        <div class="text-sm mt-1">
                            <span class="font-medium">Indikator Terpenuhi:</span> 
                            {{ $family->healthIndex->fulfilled_indicators ?? '0' }} indikator
                        </div>
                        @else
                        <div class="text-sm text-gray-500 italic">
                            Indeks Kesehatan Keluarga belum dihitung
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-4 px-6 py-4 border-t border-gray-200 text-sm text-gray-600 print:mt-0">
            <div class="flex justify-between items-center">
                <div>
                    Dicetak pada: {{ now()->format('d-m-Y H:i:s') }}
                </div>
                <div>
                    Program Indonesia Sehat dengan Pendekatan Keluarga (PISPK)
                </div>
            </div>
        </div>
    </div>