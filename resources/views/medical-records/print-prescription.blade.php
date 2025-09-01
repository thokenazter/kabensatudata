<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resep Obat - {{ $familyMember->name }}</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
                font-size: 11pt;
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .print-container {
                max-width: none !important;
                width: 100% !important;
                padding: 10mm !important;
                margin: 0 !important;
                min-height: 100vh;
                box-sizing: border-box;
            }
            @page {
                margin: 0;
                size: A4 portrait;
            }
            .shadow-lg, .shadow-md, .shadow-sm {
                box-shadow: none !important;
            }
            .rounded-lg, .rounded-xl {
                border-radius: 4px !important;
            }
            .header-section {
                margin-bottom: 8mm !important;
            }
            .patient-info {
                margin-bottom: 6mm !important;
            }
            .prescription-section {
                margin-bottom: 6mm !important;
            }
            .footer-section {
                margin-top: auto !important;
            }
        }
        
        body {
            font-family: 'Inter', 'Arial', sans-serif;
            line-height: 1.4;
        }
        
        .prescription-header {
            border-bottom: 2px solid #1e3a8a;
            position: relative;
        }
        
        .prescription-header::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 50px;
            height: 2px;
            background: #dc2626;
        }
        
        .rx-symbol {
            font-family: 'Times New Roman', serif;
            font-size: 2rem;
            font-weight: bold;
            color: #1e3a8a;
        }
        
        .medical-table {
            border-collapse: collapse;
            width: 100%;
        }
        
        .medical-table td {
            padding: 2px 6px;
            font-size: 0.85rem;
            vertical-align: top;
        }
        
        .prescription-box {
            background: #fafafa;
            border: 1.5px solid #d1d5db;
            border-radius: 6px;
            position: relative;
            overflow: hidden;
        }
        
        .prescription-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #1e3a8a, #3b82f6);
        }
        
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 2.5rem;
            color: rgba(59, 130, 246, 0.04);
            font-weight: bold;
            pointer-events: none;
            z-index: 0;
        }
        
        .content-overlay {
            position: relative;
            z-index: 1;
        }
        
        .info-card {
            background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
            border-left: 3px solid #3b82f6;
        }
        
        .compact-spacing {
            margin-bottom: 0.75rem;
        }
        
        .medication-content {
            white-space: pre-line;
            line-height: 1.5;
            font-size: 0.9rem;
        }
        
        /* Dynamic height based on content */
        .prescription-box-dynamic {
            min-height: 100px;
            max-height: none;
        }
        
        .signature-area {
            min-height: 50px;
            border-bottom: 1px solid #374151;
            margin-bottom: 6px;
        }
    </style>
</head>
<body class="bg-white">
    <!-- Print Controls -->
    <div class="no-print fixed top-4 right-4 z-50">
        <div class="bg-white shadow-lg rounded-xl p-3 border border-gray-200">
            <div class="flex flex-col space-y-2">
                <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a1 1 0 001-1v-4a1 1 0 00-1-1H9a1 1 0 00-1 1v4a1 1 0 001 1zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Cetak
                </button>
                <a href="{{ route('medical-records.show', [$familyMember, $medicalRecord]) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Prescription Content -->
    <div class="print-container max-w-4xl mx-auto p-6 bg-white min-h-screen flex flex-col">
        
        <!-- Header Puskesmas -->
        <div class="header-section prescription-header pb-4 mb-4">
            <div class="flex items-start justify-between">
                <div class="flex items-center">
                    <div class="w-12 h-12 mr-3 flex items-center justify-center">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo Puskesmas" class="w-12 h-12 object-contain">
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-blue-900 leading-tight">PUSKESMAS RAWAT INAP</h1>
                        <h2 class="text-base font-semibold text-blue-700 mb-0.5">KABALSIANG BENJURING</h2>
                        <p class="text-xs text-gray-600">Desa Benjuring, Kec. Aru Utara Timur Batuley</p>
                        <p class="text-xs text-gray-600">Email: kaben032023@gmail.com</p>
                    </div>
                </div>
                <div class="text-right text-sm text-gray-600">
                    <div class="bg-blue-50 px-2 py-1.5 rounded border">
                        <p class="text-xs font-medium">No. Resep: <span class="text-blue-600">RX-{{ $medicalRecord->id ?? '000' }}-{{ date('Y') }}</span></p>
                        <p class="text-xs">Tanggal: <span class="font-medium">{{ $medicalRecord->visit_date->format('d/m/Y') }}</span></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Patient Information -->
        <div class="patient-info grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
            <div class="info-card p-3 rounded border">
                <h3 class="font-semibold text-gray-800 text-sm mb-2 flex items-center">
                    <svg class="w-4 h-4 mr-1 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                    </svg>
                    Data Pasien
                </h3>
                <table class="medical-table">
                    <tr>
                        <td class="text-gray-600 font-medium w-16">Nama</td>
                        <td class="font-medium">: {{ $medicalRecord->patient_name ?? $familyMember->name }}</td>
                    </tr>
                    <tr>
                        <td class="text-gray-600 font-medium">NIK</td>
                        <td>: {{ $medicalRecord->patient_nik ?? $familyMember->nik ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-gray-600 font-medium">No. RM</td>
                        <td>: {{ $medicalRecord->patient_rm_number ?? $familyMember->rm_number ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-gray-600 font-medium">Umur</td>
                        <td>: {{ $medicalRecord->current_patient_age ?? $familyMember->age }} tahun</td>
                    </tr>
                    <tr>
                        <td class="text-gray-600 font-medium">JK</td>
                        <td>: {{ $medicalRecord->patient_gender ?? $familyMember->gender }}</td>
                    </tr>
                </table>
            </div>
            
            <div class="info-card p-3 rounded border">
                <h3 class="font-semibold text-gray-800 text-sm mb-2 flex items-center">
                    <svg class="w-4 h-4 mr-1 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Data Kunjungan
                </h3>
                <table class="medical-table">
                    <tr>
                        <td class="text-gray-600 font-medium w-16">Tanggal</td>
                        <td class="font-medium">: {{ $medicalRecord->visit_date->format('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-gray-600 font-medium">Jam</td>
                        <td>: {{ $medicalRecord->visit_date->format('H:i') }} WIB</td>
                    </tr>
                    @if($medicalRecord->creator)
                    <tr>
                        <td class="text-gray-600 font-medium">Petugas</td>
                        <td>: {{ $medicalRecord->creator->name }}</td>
                    </tr>
                    @endif
                    @if($medicalRecord->diagnosis_name || $medicalRecord->diagnosis_code)
                    <tr>
                        <td class="text-gray-600 font-medium" style="vertical-align: top;">Diagnosis</td>
                        <td style="line-height: 1.3;">: {{ $medicalRecord->diagnosis_name }}
                            @if($medicalRecord->diagnosis_code)
                                <br><span class="text-gray-500 text-xs">({{ $medicalRecord->diagnosis_code }})</span>
                            @endif
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Prescription Section -->
        <div class="prescription-section flex-grow">
            <div class="flex items-center mb-3">
                <span class="rx-symbol mr-2">℞</span>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">RESEP OBAT</h3>
                    <p class="text-xs text-gray-600">Prescription</p>
                </div>
            </div>
            
            <div class="prescription-box prescription-box-dynamic p-4 relative">
                <div class="watermark">PKM KABEN</div>
                <div class="content-overlay">
                    @if($medicalRecord->medication)
                        <div class="medication-content text-gray-800">{{ $medicalRecord->medication }}</div>
                    @else
                        <div class="text-gray-400 italic text-center py-8 text-sm">
                            <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                            Tidak ada resep obat yang diberikan
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Additional Sections -->
        @if($medicalRecord->therapy)
        <div class="compact-spacing">
            <div class="bg-green-50 border-l-3 border-green-400 p-3 rounded-r border border-green-200">
                <h4 class="font-semibold text-green-800 text-sm mb-1 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Terapi/Anjuran:
                </h4>
                <div class="whitespace-pre-line text-green-700 text-xs">{{ $medicalRecord->therapy }}</div>
            </div>
        </div>
        @endif

        @if($medicalRecord->procedure)
        <div class="compact-spacing">
            <div class="bg-yellow-50 border-l-3 border-yellow-400 p-3 rounded-r border border-yellow-200">
                <h4 class="font-semibold text-yellow-800 text-sm mb-1 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                    </svg>
                    Tindakan/Prosedur:
                </h4>
                <div class="whitespace-pre-line text-yellow-700 text-xs">{{ $medicalRecord->procedure }}</div>
            </div>
        </div>
        @endif

        <!-- Footer with Doctor Signature -->
        <div class="footer-section grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4 pt-4 border-t border-gray-300">
            <div class="text-xs text-gray-600">
                <h4 class="font-semibold text-gray-800 text-sm mb-1">Informasi Penting:</h4>
                <div class="bg-red-50 border border-red-200 rounded p-2">
                    <ul class="space-y-0.5 text-red-700">
                        <li>• Minum obat sesuai petunjuk</li>
                        <li>• Simpan obat di tempat sejuk dan kering</li>
                        <li>• Jauhkan dari jangkauan anak-anak</li>
                        <li>• Hubungi petugas jika ada efek samping</li>
                    </ul>
                </div>
                <p class="text-xs text-gray-400 mt-2">Dicetak: {{ now()->format('d/m/Y H:i') }} WIB</p>
            </div>
            
            <div class="text-center">
                <p class="text-xs text-gray-700 mb-1">{{ $medicalRecord->visit_date->format('d F Y') }}</p>
                <p class="text-sm font-semibold text-gray-800 mb-1">Dokter/Petugas Kesehatan</p>
                
                <div class="signature-area relative">
                    <div class="absolute top-0 right-0 w-10 h-10 border border-gray-400 flex items-center justify-center text-xs text-gray-400">
                        QR
                    </div>
                </div>
                
                <div class="text-center">
                    <p class="font-semibold text-gray-900 text-sm">
                        @if($medicalRecord->creator)
                            {{ $medicalRecord->creator->name }}
                        @else
                            (Nama Dokter/Petugas)
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Compact Validation Footer -->
        <div class="mt-3 pt-2 border-t border-gray-200 bg-gray-50 -mx-6 px-6 py-2">
            <div class="flex justify-between items-center text-xs text-gray-500">
                <span>Dokumen resmi elektronik - tidak perlu tanda tangan basah</span>
                <span>Verifikasi: <span class="font-mono font-semibold">RX{{ date('y') }}-{{ str_pad($medicalRecord->id ?? 0, 4, '0', STR_PAD_LEFT) }}</span> ✅</span>
            </div>
        </div>
    </div>

    <script>
        // Dynamic prescription box height based on content length
        document.addEventListener('DOMContentLoaded', function() {
            const medicationContent = document.querySelector('.medication-content');
            const prescriptionBox = document.querySelector('.prescription-box-dynamic');
            
            if (medicationContent && prescriptionBox) {
                const contentLength = medicationContent.textContent.length;
                
                // Adjust height based on content length
                if (contentLength > 500) {
                    prescriptionBox.style.minHeight = '200px';
                } else if (contentLength > 200) {
                    prescriptionBox.style.minHeight = '150px';
                } else if (contentLength > 0) {
                    prescriptionBox.style.minHeight = '120px';
                } else {
                    prescriptionBox.style.minHeight = '100px';
                }
            }
        });
        
        // Print function
        function printPrescription() {
            window.print();
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
    </script>
</body>
</html>