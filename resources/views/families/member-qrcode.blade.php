<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code {{ $familyMember->name }}</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100">

    <div class="max-w-2xl mx-auto py-8 px-4">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-blue-600 text-white p-4">
                <h1 class="text-xl font-bold">QR Code Anggota Keluarga</h1>
                <p class="text-sm text-blue-100">
                    Nama: {{ $familyMember->name }} | 
                    Status: {{ $familyMember->relationship }} | 
                    Keluarga: {{ $family->head_name }}
                </p>
            </div>
            
            <!-- QR Code -->
            <div class="flex flex-col items-center py-8">
                <div class="border-4 border-blue-200 p-2 rounded-lg mb-3">
                    <img src="data:image/png;base64,{{ $qrCodeImage }}" alt="QR Code" class="w-64 h-64">
                </div>
                
                <p class="text-gray-600 text-sm">Scan untuk melihat data anggota keluarga</p>
                <p class="text-gray-400 text-xs mt-1">{{ $url }}</p>
            </div>
            
            <!-- Informasi Anggota -->
            <div class="border-t border-gray-200 p-4">
                <h2 class="text-lg font-semibold text-gray-800 mb-2">Informasi Anggota:</h2>
                <ul class="space-y-1 text-sm text-gray-600">
                    <li>Nama: {{ $familyMember->name }}</li>
                    <li>NIK: {{ $familyMember->nik ?? 'Tidak ada' }}</li>
                    <li>Umur: {{ $familyMember->age }} tahun</li>
                    <li>Status: {{ $familyMember->relationship }}</li>
                    <li>Keluarga: {{ $family->head_name }}</li>
                    <li>Alamat: Desa {{ $family->building->village->name }}, No. {{ $family->building->building_number }}</li>
                </ul>
            </div>
            
            <!-- Tombol Aksi -->
            <div class="bg-gray-50 p-4 border-t border-gray-200 flex justify-between no-print">
                <a href="{{ route('family-members.show', $familyMember) }}" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">
                    Lihat Detail Anggota
                </a>
                
                <button onclick="window.print()" class="px-4 py-2 bg-gray-600 text-white rounded-md text-sm hover:bg-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd" />
                    </svg>
                    Cetak
                </button>
            </div>
        </div>
    </div>
</body>
</html>