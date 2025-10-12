<!-- resources/views/medical-records/create.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Rekam Medis - {{ $familyMember->name }}</title>
    
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
                    <h1 class="text-2xl font-bold text-white">Tambah Rekam Medis</h1>
                    <p class="text-blue-100 mt-1">{{ $familyMember->name }} - {{ $familyMember->nik ?? 'NIK tidak tersedia' }}</p>
                    
                    <div class="mt-4">
                        <a href="{{ route('medical-records.index', $familyMember) }}" class="inline-flex items-center px-3 py-1.5 bg-white bg-opacity-10 rounded-md text-sm text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                            Kembali ke Daftar
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
            
            <!-- Medical Record Form -->
            <form action="{{ route('medical-records.store', $familyMember) }}" method="POST" class="bg-white shadow-sm rounded-lg overflow-hidden">
                @csrf
                
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-medium text-gray-900">Form Rekam Medis</h2>
                    <p class="mt-1 text-sm text-gray-500">Isikan data pemeriksaan medis dengan lengkap</p>
                </div>
                
                <div class="p-6 space-y-6">
                    <!-- Validation Errors -->
                    @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700">
                                    <strong>Terdapat kesalahan pada form:</strong>
                                </p>
                                <ul class="mt-1 text-xs text-red-700 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Date & Complaint -->
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label for="visit_date" class="block text-sm font-medium text-gray-700">Tanggal Kunjungan <span class="text-red-500">*</span></label>
                            <input type="date" name="visit_date" id="visit_date" required 
                                value="{{ old('visit_date', date('Y-m-d')) }}" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 bg-gray-400 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label for="chief_complaint" class="block text-sm font-medium text-gray-700">Keluhan Utama</label>
                            <input type="text" name="chief_complaint" id="chief_complaint" 
                                value="{{ old('chief_complaint') }}" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-gray-400">
                        </div>
                    </div>
                    
                    <!-- Anamnesa -->
                    <div>
                        <label for="anamnesis" class="block text-sm font-medium text-gray-700">Anamnesa</label>
                        <textarea name="anamnesis" id="anamnesis" rows="3" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 bg-gray-400 focus:ring-blue-500">{{ old('anamnesis') }}</textarea>
                    </div>
                    
                    <!-- Vital Signs Section -->
                    <div class="border-t border-gray-200 pt-4">
                        <h3 class="text-md font-medium text-gray-900">Tanda Vital</h3>
                        
                        <div class="mt-4 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2 md:grid-cols-3">
                            <!-- Blood Pressure -->
                            <div>
                                <label for="systolic" class="block text-sm font-medium text-gray-700">Tekanan Darah (mmHg)</label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <input type="number" name="systolic" id="systolic" min="60" max="300" placeholder="Sistol"
                                        value="{{ old('systolic') }}" 
                                        class="flex-1 min-w-0 block w-full rounded-none rounded-l-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                    <span class="inline-flex items-center px-3 border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                        /
                                    </span>
                                    <input type="number" name="diastolic" id="diastolic" min="40" max="200" placeholder="Diastol"
                                        value="{{ old('diastolic') }}" 
                                        class="flex-1 min-w-0 block w-full rounded-none rounded-r-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>
                            
                            <!-- Weight & Height -->
                            <div>
                                <label for="weight" class="block text-sm font-medium text-gray-700">Berat Badan (kg)</label>
                                <input type="number" name="weight" id="weight" step="0.1" min="0" max="500"
                                    value="{{ old('weight') }}" 
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-400 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label for="height" class="block text-sm font-medium text-gray-700">Tinggi Badan (cm)</label>
                                <input type="number" name="height" id="height" step="0.1" min="0" max="300"
                                    value="{{ old('height') }}" 
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-400 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            
                            <!-- Heart & Respiratory Rate -->
                            <div>
                                <label for="heart_rate" class="block text-sm font-medium text-gray-700">Heart Rate (bpm)</label>
                                <input type="number" name="heart_rate" id="heart_rate" min="30" max="250"
                                    value="{{ old('heart_rate') }}" 
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-400 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label for="body_temperature" class="block text-sm font-medium text-gray-700">Suhu Tubuh (°C)</label>
                                <input type="number" name="body_temperature" id="body_temperature" step="0.1" min="30" max="45"
                                    value="{{ old('body_temperature') }}" 
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-400 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label for="respiratory_rate" class="block text-sm font-medium text-gray-700">Respiratory Rate (rpm)</label>
                                <input type="number" name="respiratory_rate" id="respiratory_rate" min="5" max="60"
                                    value="{{ old('respiratory_rate') }}" 
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-400 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Diagnosis Section -->
                    <div class="border-t border-gray-200 pt-4">
                        <h3 class="text-md font-medium text-gray-900">Diagnosis & Terapi</h3>

                        <div class="mt-4">
                            <label for="spm_sub_indicator_ids" class="block text-sm font-medium text-gray-700">Sub‑Indikator SPM (opsional, bisa pilih lebih dari satu)</label>
                            <select name="spm_sub_indicator_ids[]" id="spm_sub_indicator_ids" multiple size="8" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @isset($subIndicators)
                                    @foreach($subIndicators as $si)
                                        <option value="{{ $si->id }}">{{ $si->code }} — {{ $si->name }} ({{ $si->indicator?->name }})</option>
                                    @endforeach
                                @endisset
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Tahan Ctrl (Windows) / Cmd (Mac) untuk pilih multiple.</p>
                        </div>
                        
                        <div class="mt-4 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                            <div>
                                <label for="diagnosis_code" class="block text-sm font-medium text-gray-700">Kode Diagnosa (ICD-10)</label>
                                <input type="text" name="diagnosis_code" id="diagnosis_code" 
                                    value="{{ old('diagnosis_code') }}" 
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-400 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label for="diagnosis_name" class="block text-sm font-medium text-gray-700">Nama Diagnosa</label>
                                <input type="text" name="diagnosis_name" id="diagnosis_name" 
                                    value="{{ old('diagnosis_name') }}" 
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-400 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                        
                        <div class="mt-4 grid grid-cols-1 gap-y-6 gap-x-4 md:grid-cols-3">
                            
                            <div class="md:col-span-1">
                                <label for="medication" class="block text-sm font-medium text-gray-700">Terapi Obat</label>
                                <textarea name="medication" id="medication" rows="3" 
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-400 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('medication') }}</textarea>
                            </div>
                            
                            <div class="md:col-span-1">
                                <label for="procedure" class="block text-sm font-medium text-gray-700">Tindakan</label>
                                <textarea name="procedure" id="procedure" rows="3" 
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-400 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('procedure') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-6 py-4 flex justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Simpan Rekam Medis
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
