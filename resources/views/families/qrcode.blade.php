<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Keluarga {{ $family->head_name }}</title>
    
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
    <div class="no-print">
        @include('includes.navbar')
    </div>

    <div class="max-w-2xl mx-auto py-8 px-4">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-blue-600 text-white p-4">
                <h1 class="text-xl font-bold">QR Code Keluarga</h1>
                <p class="text-sm text-blue-100">
                    Keluarga: {{ $family->head_name }} | 
                    Desa: {{ $family->building->village->name }} | 
                    No. Bangunan: {{ $family->building->building_number }}
                </p>
            </div>
            
            <!-- QR Code -->
            <div class="flex flex-col items-center py-8">
                <div class="border-4 border-blue-200 p-2 rounded-lg mb-3">
                    <img src="data:image/png;base64,{{ $qrCodeImage }}" alt="QR Code" class="w-64 h-64">
                </div>
                
                <p class="text-gray-600 text-sm">Scan untuk melihat data keluarga</p>
                <p class="text-gray-400 text-xs mt-1">{{ $url }}</p>
            </div>
            
            <!-- Informasi Keluarga -->
            <div class="border-t border-gray-200 p-4">
                <h2 class="text-lg font-semibold text-gray-800 mb-2">Anggota Keluarga:</h2>
                <ul class="space-y-1 text-sm text-gray-600">
                    @foreach($family->members as $member)
                    <li class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                        </svg>
                        {{ $member->name }} - {{ $member->relationship }} ({{ $member->age }} tahun)
                    </li>
                    @endforeach
                </ul>
            </div>
            
            <!-- Tombol Aksi -->
            <div class="bg-gray-50 p-4 border-t border-gray-200 flex justify-between no-print">
                <a href="{{ route('families.card', $family) }}" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">
                    Lihat Detail Keluarga
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