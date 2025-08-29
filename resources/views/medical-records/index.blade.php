<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Rekam Medis - {{ $familyMember->name }}</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    {{-- @include('includes.navbar') --}}

    <!-- Main Content -->
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 p-6">
                    <h1 class="text-2xl font-bold text-white">Riwayat Rekam Medis</h1>
                    <p class="text-blue-100 mt-1">{{ $familyMember->name }} - {{ $familyMember->nik ?? 'NIK tidak tersedia' }}</p>
                    
                    <div class="mt-4 flex items-center space-x-2">
                        <a href="{{ route('family-members.show', $familyMember) }}" class="inline-flex items-center px-3 py-1.5 bg-white bg-opacity-10 rounded-md text-sm text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                            Kembali ke Detail
                        </a>
                        
                        <a href="{{ route('medical-records.create', $familyMember) }}" class="inline-flex items-center px-3 py-1.5 bg-green-500 rounded-md text-sm text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Tambah Rekam Medis
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
            
            <!-- Medical Records Table -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                    <h2 class="text-lg font-medium text-gray-900">Daftar Kunjungan</h2>
                    
                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                        Total: {{ $medicalRecords->total() }}
                    </span>
                </div>
                
                @if($medicalRecords->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tgl. Kunjungan
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Keluhan Utama
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tekanan Darah
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    BB/TB
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Diagnosa
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($medicalRecords as $record)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $record->visit_date->format('d-m-Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-normal text-sm text-gray-500 max-w-xs truncate">
                                    {{ $record->chief_complaint ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($record->systolic && $record->diastolic)
                                        <span class="font-medium">{{ $record->systolic }}/{{ $record->diastolic }}</span> mmHg
                                        <span class="block text-xs mt-1 {{ $record->blood_pressure_category === 'Normal' ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $record->blood_pressure_category }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($record->weight && $record->height)
                                        {{ $record->weight }} kg / {{ $record->height }} cm
                                        <span class="block text-xs mt-1">
                                            BMI: {{ $record->bmi }} ({{ $record->bmi_category }})
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-normal text-sm text-gray-500 max-w-xs truncate">
                                    {{ $record->diagnosis_name ?? '-' }}
                                    @if($record->diagnosis_code)
                                        <span class="text-xs text-gray-400">({{ $record->diagnosis_code }})</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('medical-records.show', [$familyMember, $record]) }}" class="text-blue-600 hover:text-blue-900">Detail</a>
                                        <span class="text-gray-300">|</span>
                                        <a href="{{ route('medical-records.edit', [$familyMember, $record]) }}" class="text-green-600 hover:text-green-900">Edit</a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
                    {{ $medicalRecords->links() }}
                </div>
                @else
                <div class="px-6 py-12 text-center">
                    <svg class="h-12 w-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada data rekam medis</h3>
                    <p class="mt-1 text-sm text-gray-500">Mulai buat rekam medis baru untuk pasien ini.</p>
                    <div class="mt-6">
                        <a href="{{ route('medical-records.create', $familyMember) }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Tambah Rekam Medis
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>