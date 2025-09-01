<!-- resources/views/medical-records/edit.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Rekam Medis - {{ $familyMember->name }}</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">

    <!-- Main Content -->
    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-green-600 to-green-800 p-6">
                    <h1 class="text-2xl font-bold text-white">Edit Rekam Medis</h1>
                    <p class="text-green-100 mt-1">{{ $familyMember->name }} - Kunjungan {{ $medicalRecord->visit_date->format('d-m-Y') }}</p>
                    
                    <div class="mt-4 flex items-center space-x-2">
                        <a href="{{ route('medical-records.show', [$familyMember, $medicalRecord]) }}" class="inline-flex items-center px-3 py-1.5 bg-white bg-opacity-10 rounded-md text-sm text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                            Kembali ke Detail
                        </a>
                        
                        <a href="{{ route('medical-records.index', $familyMember) }}" class="inline-flex items-center px-3 py-1.5 bg-white bg-opacity-10 rounded-md text-sm text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                            </svg>
                            Daftar Rekam Medis
                        </a>
                    </div>
                </div>
                
                <div class="p-4 border-b border-gray-200 bg-gray-50">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Tanggal Lahir</p>
                            <p class="font-medium">{{ $familyMember->formatted_birth_date ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Jenis Kelamin</p>
                            <p class="font-medium">{{ $familyMember->gender }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Alamat</p>
                            <p class="font-medium">Desa {{ $familyMember->family->building->village->name }}, Bangunan No. {{ $familyMember->family->building->building_number }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Form -->
            <form action="{{ route('medical-records.update', [$familyMember, $medicalRecord]) }}" method="POST" class="bg-white shadow-sm rounded-lg overflow-hidden">
                @csrf
                @method('PUT')
                
                <!-- Error Messages -->
                @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Terdapat kesalahan pada form:</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                
                <div class="p-6">
                    <!-- Informasi Kunjungan -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Kunjungan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="visit_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Kunjungan *</label>
                                <input type="date" id="visit_date" name="visit_date" value="{{ old('visit_date', $medicalRecord->visit_date->format('Y-m-d')) }}" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Keluhan dan Anamnesis -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Keluhan dan Anamnesis</h3>
                        <div class="space-y-6">
                            <div>
                                <label for="chief_complaint" class="block text-sm font-medium text-gray-700 mb-2">Keluhan Utama</label>
                                <input type="text" id="chief_complaint" name="chief_complaint" value="{{ old('chief_complaint', $medicalRecord->chief_complaint) }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Masukkan keluhan utama pasien">
                            </div>
                            
                            <div>
                                <label for="anamnesis" class="block text-sm font-medium text-gray-700 mb-2">Anamnesis</label>
                                <textarea id="anamnesis" name="anamnesis" rows="4"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Masukkan riwayat penyakit dan anamnesis">{{ old('anamnesis', $medicalRecord->anamnesis) }}</textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tanda Vital -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Tanda Vital</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <label for="systolic" class="block text-sm font-medium text-gray-700 mb-2">Tekanan Darah Sistolik (mmHg)</label>
                                <input type="number" id="systolic" name="systolic" value="{{ old('systolic', $medicalRecord->systolic) }}" min="60" max="300"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="120">
                            </div>
                            
                            <div>
                                <label for="diastolic" class="block text-sm font-medium text-gray-700 mb-2">Tekanan Darah Diastolik (mmHg)</label>
                                <input type="number" id="diastolic" name="diastolic" value="{{ old('diastolic', $medicalRecord->diastolic) }}" min="40" max="200"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="80">
                            </div>
                            
                            <div>
                                <label for="heart_rate" class="block text-sm font-medium text-gray-700 mb-2">Detak Jantung (bpm)</label>
                                <input type="number" id="heart_rate" name="heart_rate" value="{{ old('heart_rate', $medicalRecord->heart_rate) }}" min="30" max="250"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="72">
                            </div>
                            
                            <div>
                                <label for="body_temperature" class="block text-sm font-medium text-gray-700 mb-2">Suhu Tubuh (°C)</label>
                                <input type="number" id="body_temperature" name="body_temperature" value="{{ old('body_temperature', $medicalRecord->body_temperature) }}" min="30" max="45" step="0.1"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="36.5">
                            </div>
                            
                            <div>
                                <label for="respiratory_rate" class="block text-sm font-medium text-gray-700 mb-2">Laju Pernapasan (/menit)</label>
                                <input type="number" id="respiratory_rate" name="respiratory_rate" value="{{ old('respiratory_rate', $medicalRecord->respiratory_rate) }}" min="5" max="60"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="20">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Antropometri -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Antropometri</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="weight" class="block text-sm font-medium text-gray-700 mb-2">Berat Badan (kg)</label>
                                <input type="number" id="weight" name="weight" value="{{ old('weight', $medicalRecord->weight) }}" min="0" max="500" step="0.1"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="70">
                            </div>
                            
                            <div>
                                <label for="height" class="block text-sm font-medium text-gray-700 mb-2">Tinggi Badan (cm)</label>
                                <input type="number" id="height" name="height" value="{{ old('height', $medicalRecord->height) }}" min="0" max="300" step="0.1"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="170">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Diagnosis -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Diagnosis</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="diagnosis_code" class="block text-sm font-medium text-gray-700 mb-2">Kode Diagnosis (ICD)</label>
                                <input type="text" id="diagnosis_code" name="diagnosis_code" value="{{ old('diagnosis_code', $medicalRecord->diagnosis_code) }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="A00.0">
                            </div>
                            
                            <div>
                                <label for="diagnosis_name" class="block text-sm font-medium text-gray-700 mb-2">Nama Diagnosis</label>
                                <input type="text" id="diagnosis_name" name="diagnosis_name" value="{{ old('diagnosis_name', $medicalRecord->diagnosis_name) }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Masukkan nama diagnosis">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Terapi dan Tindakan -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Terapi dan Tindakan</h3>
                        <div class="space-y-6">
                            <div>
                                <label for="therapy" class="block text-sm font-medium text-gray-700 mb-2">Terapi</label>
                                <textarea id="therapy" name="therapy" rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Masukkan terapi yang diberikan">{{ old('therapy', $medicalRecord->therapy) }}</textarea>
                            </div>
                            
                            <div>
                                <label for="medication" class="block text-sm font-medium text-gray-700 mb-2">Obat</label>
                                <textarea id="medication" name="medication" rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Masukkan obat yang diberikan">{{ old('medication', $medicalRecord->medication) }}</textarea>
                            </div>
                            
                            <div>
                                <label for="procedure" class="block text-sm font-medium text-gray-700 mb-2">Tindakan</label>
                                <textarea id="procedure" name="procedure" rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Masukkan tindakan yang dilakukan">{{ old('procedure', $medicalRecord->procedure) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="bg-gray-50 px-6 py-4 flex items-center justify-between">
                    <div class="text-sm text-gray-500">
                        <span class="text-red-500">*</span> Field wajib diisi
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('medical-records.show', [$familyMember, $medicalRecord]) }}" 
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Batal
                        </a>
                        
                        <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>