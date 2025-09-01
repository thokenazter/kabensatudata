<div class="space-y-4">
    <!-- Patient Info Header -->
    <div class="bg-gray-50 p-4 rounded-lg">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <h3 class="font-semibold text-gray-900">{{ $patient->patient_name }}</h3>
                <p class="text-sm text-gray-600">No. RM: {{ $patient->patient_rm_number }}</p>
                <p class="text-sm text-gray-600">NIK: {{ $patient->patient_nik }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Jenis Kelamin: {{ $patient->patient_gender }}</p>
                <p class="text-sm text-gray-600">Umur: {{ $patient->patient_age }} tahun</p>
                <p class="text-sm text-gray-600">Total Kunjungan: {{ $records->count() }} kali</p>
            </div>
        </div>
    </div>

    <!-- Medical Records History -->
    <div class="space-y-3">
        <h4 class="font-medium text-gray-900">Riwayat Kunjungan:</h4>
        
        @forelse($records as $record)
            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                <div class="flex justify-between items-start mb-2">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-1">
                            <span class="font-medium text-gray-900">
                                {{ $record->visit_date ? $record->visit_date->format('d M Y') : '-' }}
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ match($record->workflow_status) {
                                    'pending_registration' => 'bg-gray-100 text-gray-800',
                                    'pending_nurse' => 'bg-blue-100 text-blue-800',
                                    'pending_doctor' => 'bg-yellow-100 text-yellow-800',
                                    'pending_pharmacy' => 'bg-green-100 text-green-800',
                                    'completed' => 'bg-purple-100 text-purple-800',
                                    default => 'bg-gray-100 text-gray-800'
                                } }}">
                                {{ match($record->workflow_status) {
                                    'pending_registration' => 'Menunggu Pendaftaran',
                                    'pending_nurse' => 'Menunggu Perawat',
                                    'pending_doctor' => 'Menunggu Dokter',
                                    'pending_pharmacy' => 'Menunggu Apoteker',
                                    'completed' => 'Selesai',
                                    default => 'Draft'
                                } }}
                            </span>
                        </div>
                        
                        @if($record->chief_complaint)
                            <p class="text-sm text-gray-600 mb-1">
                                <span class="font-medium">Keluhan:</span> {{ $record->chief_complaint }}
                            </p>
                        @endif
                        
                        @if($record->diagnosis_name)
                            <p class="text-sm text-gray-600 mb-1">
                                <span class="font-medium">Diagnosis:</span> {{ $record->diagnosis_name }}
                                @if($record->diagnosis_code)
                                    ({{ $record->diagnosis_code }})
                                @endif
                            </p>
                        @endif
                        
                        @if($record->systolic && $record->diastolic)
                            <p class="text-sm text-gray-600 mb-1">
                                <span class="font-medium">Tekanan Darah:</span> {{ $record->systolic }}/{{ $record->diastolic }} mmHg
                                @if($record->blood_pressure_category)
                                    <span class="text-xs text-gray-500">({{ $record->blood_pressure_category }})</span>
                                @endif
                            </p>
                        @endif
                        
                        @if($record->weight || $record->height)
                            <p class="text-sm text-gray-600 mb-1">
                                <span class="font-medium">Fisik:</span>
                                @if($record->weight) BB: {{ $record->weight }} kg @endif
                                @if($record->height) TB: {{ $record->height }} cm @endif
                                @if($record->weight && $record->height)
                                    BMI: {{ $record->bmi }}
                                @endif
                            </p>
                        @endif
                        
                        @if($record->medication)
                            <p class="text-sm text-gray-600 mb-1">
                                <span class="font-medium">Obat:</span> {{ Str::limit($record->medication, 100) }}
                            </p>
                        @endif
                        
                        @if($record->creator)
                            <p class="text-xs text-gray-500 mt-2">
                                Dibuat oleh: {{ $record->creator->name }} • {{ $record->created_at->format('d M Y H:i') }}
                            </p>
                        @endif
                    </div>
                    
                    <div class="flex space-x-1 ml-4">
                        <a href="{{ route('filament.admin.resources.medical-records.view', $record) }}" 
                           target="_blank"
                           class="inline-flex items-center px-2 py-1 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Lihat
                        </a>
                        <a href="{{ route('filament.admin.resources.medical-records.edit', $record) }}" 
                           target="_blank"
                           class="inline-flex items-center px-2 py-1 border border-blue-300 rounded text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100">
                            Edit
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-8 text-gray-500">
                <p>Belum ada riwayat rekam medis untuk pasien ini.</p>
            </div>
        @endforelse
    </div>
    
    @if($records->count() > 0)
        <div class="pt-4 border-t border-gray-200">
            @php
                $familyMember = $patient->familyMember;
                $historyUrl = ($familyMember && $familyMember->slug) 
                    ? route('medical-records.index', $familyMember->slug)
                    : route('medical-records.index', $patient->family_member_id);
            @endphp
            <a href="{{ $historyUrl }}" 
               target="_blank"
               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                Lihat Semua di Halaman Terpisah
                <svg class="ml-2 -mr-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                </svg>
            </a>
        </div>
    @endif
</div>