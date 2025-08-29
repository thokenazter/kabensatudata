<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Rekam Medis - {{ $familyMember->name }}</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">

    <!-- Main Content -->
    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 p-6">
                    <h1 class="text-2xl font-bold text-white">Detail Rekam Medis</h1>
                    <p class="text-blue-100 mt-1">
                        {{ $familyMember->name }} - Kunjungan tanggal {{ $medicalRecord->visit_date->format('d-m-Y') }}
                    </p>
                    
                    <div class="mt-4 flex items-center space-x-2">
                        <a href="{{ route('medical-records.index', $familyMember) }}" class="inline-flex items-center px-3 py-1.5 bg-white bg-opacity-10 rounded-md text-sm text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                            Kembali ke Daftar
                        </a>
                        
                        <a href="{{ route('medical-records.edit', [$familyMember, $medicalRecord]) }}" class="inline-flex items-center px-3 py-1.5 bg-green-500 rounded-md text-sm text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                            </svg>
                            Edit Rekam Medis
                        </a>
                    </div>
                </div>
                
                <div class="p-4 border-b border-gray-200 bg-gray-50">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">Tanggal Lahir</p>
                            <p class="font-medium">{{ $familyMember->formatted_birth_date ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Jenis Kelamin</p>
                            <p class="font-medium">{{ $familyMember->gender }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Umur</p>
                            <p class="font-medium">{{ $familyMember->age }} tahun</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Alamat</p>
                            <p class="font-medium">Desa {{ $familyMember->family->building->village->name }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Success Message -->
            @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Medical Record Details -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Basic & Vital Info -->
                <div class="md:col-span-2 space-y-6">
                    <!-- Basic Info -->
                    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Informasi Dasar</h2>
                        </div>
                        
                        <div class="p-6 space-y-4">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Tanggal Kunjungan</h3>
                                <p class="mt-1">{{ $medicalRecord->visit_date->format('d F Y') }}</p>
                            </div>
                            
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Keluhan Utama</h3>
                                <p class="mt-1">{{ $medicalRecord->chief_complaint ?? '-' }}</p>
                            </div>
                            
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Anamnesa</h3>
                                <p class="mt-1">{{ $medicalRecord->anamnesis ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Vital Signs -->
                    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Tanda Vital</h2>
                        </div>
                        
                        <div class="p-6">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h3 class="text-sm font-medium text-gray-500">Tekanan Darah</h3>
                                    @if($medicalRecord->systolic && $medicalRecord->diastolic)
                                        <p class="mt-1 text-lg font-semibold">
                                            {{ $medicalRecord->systolic }}/{{ $medicalRecord->diastolic }} mmHg
                                        </p>
                                        <p class="text-sm {{ $medicalRecord->blood_pressure_category === 'Normal' ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $medicalRecord->blood_pressure_category }}
                                        </p>
                                    @else
                                        <p class="mt-1 text-gray-400">Tidak ada data</p>
                                    @endif
                                </div>
                                
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h3 class="text-sm font-medium text-gray-500">BB/TB</h3>
                                    @if($medicalRecord->weight && $medicalRecord->height)
                                        <p class="mt-1 text-lg font-semibold">
                                            {{ $medicalRecord->weight }} kg / {{ $medicalRecord->height }} cm
                                        </p>
                                        <p class="text-sm">BMI: {{ $medicalRecord->bmi }} ({{ $medicalRecord->bmi_category }})</p>
                                    @else
                                        <p class="mt-1 text-gray-400">Tidak ada data</p>
                                    @endif
                                </div>
                                
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h3 class="text-sm font-medium text-gray-500">Heart Rate</h3>
                                    @if($medicalRecord->heart_rate)
                                        <p class="mt-1 text-lg font-semibold">{{ $medicalRecord->heart_rate }} bpm</p>
                                    @else
                                        <p class="mt-1 text-gray-400">Tidak ada data</p>
                                    @endif
                                </div>
                                
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h3 class="text-sm font-medium text-gray-500">Suhu Tubuh</h3>
                                    @if($medicalRecord->body_temperature)
                                        <p class="mt-1 text-lg font-semibold">{{ $medicalRecord->body_temperature }}°C</p>
                                    @else
                                        <p class="mt-1 text-gray-400">Tidak ada data</p>
                                    @endif
                                </div>
                                
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h3 class="text-sm font-medium text-gray-500">Respiratory Rate</h3>
                                    @if($medicalRecord->respiratory_rate)
                                        <p class="mt-1 text-lg font-semibold">{{ $medicalRecord->respiratory_rate }} rpm</p>
                                    @else
                                        <p class="mt-1 text-gray-400">Tidak ada data</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Diagnosis & Treatment -->
                <div class="space-y-6">
                    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Diagnosis</h2>
                        </div>
                        
                        <div class="p-6">
                            @if($medicalRecord->diagnosis_name)
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h3 class="text-sm font-medium text-gray-500">
                                        {{ $medicalRecord->diagnosis_name }}
                                    </h3>
                                    @if($medicalRecord->diagnosis_code)
                                        <p class="mt-1 text-xs text-gray-500">Kode: {{ $medicalRecord->diagnosis_code }}</p>
                                    @endif
                                </div>
                            @else
                                <p class="text-gray-400">Tidak ada diagnosis</p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Terapi & Pengobatan</h2>
                        </div>
                        
                        <div class="p-6 space-y-4">
                            
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Terapi Obat</h3>
                                <p class="mt-1">{{ $medicalRecord->medication ?? '-' }}</p>
                            </div>
                            
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Tindakan</h3>
                                <p class="mt-1">{{ $medicalRecord->procedure ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 p-4 text-xs text-gray-500 rounded-lg">
                        <p>Dicatat oleh: {{ $medicalRecord->creator->name ?? 'System' }}</p>
                        <p>Tanggal input: {{ $medicalRecord->created_at->format('d-m-Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>