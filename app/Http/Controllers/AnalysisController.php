<?php

namespace App\Http\Controllers;

use App\Models\Village;
use App\Models\Family;
use App\Exports\SimpleAnalysisExport;
use Illuminate\Http\Request;
use App\Services\AnalysisService;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class AnalysisController extends Controller
{

    protected $analysisService;

    // Tambahkan metode di AnalysisController.php
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls'
        ]);

        $file = $request->file('file');

        // Proses file menggunakan library seperti Maatwebsite/Laravel-Excel

        // Contoh variable untuk respons
        $processedData = []; // Inisialisasi variable

        // Contoh respons
        return response()->json([
            'success' => true,
            'data' => $processedData
        ]);
    }

    public function __construct(AnalysisService $analysisService)
    {

        $this->analysisService = $analysisService;
    }

    public function index()
    {
        $villages = Village::all();
        $variableGroups = $this->getVariableGroups();
        $visualizationTypes = $this->getVisualizationTypes();

        return view('analysis.index', compact('villages', 'variableGroups', 'visualizationTypes'));
    }

    protected function getVariableGroups()
    {
        return [
            'location' => [
                'title' => 'Informasi Lokasi',
                'variables' => [
                    'village_id' => [  // Ubah dari 'village_name' menjadi 'village_id'
                        'label' => 'Desa',
                        'type' => 'select',
                        'options' => Village::pluck('name', 'id')->toArray()
                    ],
                    'building_number' => [
                        'label' => 'Nomor Bangunan',
                        'type' => 'number'
                    ],
                    'family_number' => [
                        'label' => 'Nomor KK',
                        'type' => 'number'
                    ]
                ]
            ],
            'personal' => [
                'title' => 'Informasi Personal',
                'variables' => [
                    'gender' => [
                        'label' => 'Jenis Kelamin',
                        'type' => 'select',
                        'options' => [
                            'Laki-laki' => 'Laki-laki',
                            'Perempuan' => 'Perempuan'
                        ]
                    ],
                    'is_head_of_family' => [
                        'label' => 'Status dalam Keluarga',
                        'type' => 'select',
                        'options' => [
                            '1' => 'Hanya Kepala Keluarga',
                            '0' => 'Bukan Kepala Keluarga',
                            'all' => 'Semua Anggota Keluarga'
                        ]
                    ],
                    'is_wus' => [
                        'label' => 'Wanita Usia Subur (10-54 th)',
                        'type' => 'boolean',
                        'options' => [
                            '1' => 'Ya'
                        ]
                    ],
                    'age' => [
                        'label' => 'Usia',
                        'type' => 'age_filter',
                        'options' => [
                            'custom' => 'Kustom',
                            '0-5' => 'Balita (0-5 tahun)',
                            '6-12' => 'Anak (6-12 tahun)',
                            '13-17' => 'Remaja (13-17 tahun)',
                            '18-45' => 'Dewasa (18-45 tahun)',
                            '46-plus' => 'Lansia (>45 tahun)'
                        ]
                    ],
                    'education' => [
                        'label' => 'Pendidikan',
                        'type' => 'select',
                        'options' => [
                            'Tidak Pernah Sekolah' => 'Tidak Pernah Sekolah',
                            'Tidak Tamat SD/MI' => 'Tidak Tamat SD/MI',
                            'Tamat SD/MI' => 'Tamat SD/MI',
                            'Tamat SMP/MTs' => 'Tamat SMP/MTs',
                            'Tamat SMA/MA/SMK' => 'Tamat SMA/MA/SMK',
                            'Tamat D1/D2/D3' => 'Tamat D1/D2/D3',
                            'Tamat D4/S1' => 'Tamat D4/S1',
                            'Tamat S2/S3' => 'Tamat S2/S3'
                        ]
                    ],
                    'marital_status' => [
                        'label' => 'Status Perkawinan',
                        'type' => 'select',
                        'options' => [
                            'Belum Kawin' => 'Belum Kawin',
                            'Kawin' => 'Kawin',
                            'Cerai Hidup' => 'Cerai Hidup',
                            'Cerai Mati' => 'Cerai Mati'
                        ]
                    ],
                    'religion' => [
                        'label' => 'Agama',
                        'type' => 'select',
                        'options' => [
                            'Islam' => 'Islam',
                            'Kristen' => 'Kristen',
                            'Katolik' => 'Katolik',
                            'Hindu' => 'Hindu',
                            'Buddha' => 'Buddha',
                            'Konghucu' => 'Konghucu'
                        ]
                    ],
                    'occupation' => [
                        'label' => 'Pekerjaan',
                        'type' => 'select',
                        'options' => [
                            'Tidak Kerja' => 'Tidak Kerja',
                            'Sekolah' => 'Sekolah',
                            'ASN' => 'ASN',
                            'TNI/Polri' => 'TNI/Polri',
                            'Honorer' => 'Honorer',
                            'Pegawai Swasta' => 'Pegawai Swasta',
                            'Nelayan' => 'Nelayan',
                            'Petani' => 'Petani',
                            'IRT' => 'IRT',
                            'Lainnya' => 'Lainnya'
                        ]
                    ]
                ]
            ],
            'health' => [
                'title' => 'Kesehatan',
                'variables' => [
                    'is_pregnant' => [
                        'label' => 'Status Kehamilan',
                        'type' => 'boolean',
                        'options' => [
                            '1' => 'Ya',
                            '0' => 'Tidak'
                        ]
                    ],
                    'has_jkn' => [
                        'label' => 'Memiliki JKN',
                        'type' => 'boolean',
                        'options' => [
                            '1' => 'Ya',
                            '0' => 'Tidak'
                        ]
                    ],
                    'is_smoker' => [
                        'label' => 'Perokok',
                        'type' => 'boolean',
                        'options' => [
                            '1' => 'Ya',
                            '0' => 'Tidak'
                        ]
                    ],
                    'has_tuberculosis' => [
                        'label' => 'Menderita TBC',
                        'type' => 'boolean',
                        'options' => [
                            '1' => 'Ya',
                            '0' => 'Tidak'
                        ]
                    ],
                    'takes_tb_medication_regularly' => [
                        'label' => 'Minum Obat TBC Teratur',
                        'type' => 'boolean',
                        'options' => [
                            '1' => 'Ya',
                            '0' => 'Tidak'
                        ]
                    ],
                    'has_chronic_cough' => [
                        'label' => 'Batuk Kronis',
                        'type' => 'boolean',
                        'options' => [
                            '1' => 'Ya',
                            '0' => 'Tidak'
                        ]
                    ],
                    'has_hypertension' => [
                        'label' => 'Hipertensi',
                        'type' => 'boolean',
                        'options' => [
                            '1' => 'Ya',
                            '0' => 'Tidak'
                        ]
                    ],
                    'takes_hypertension_medication_regularly' => [
                        'label' => 'Minum Obat Hipertensi Teratur',
                        'type' => 'boolean',
                        'options' => [
                            '1' => 'Ya',
                            '0' => 'Tidak'
                        ]
                    ]
                ]
            ],
            'maternal_child' => [
                'title' => 'Kesehatan Ibu & Anak',
                'variables' => [
                    'uses_contraception' => [
                        'label' => 'Menggunakan KB',
                        'type' => 'boolean',
                        'options' => [
                            '1' => 'Ya',
                            '0' => 'Tidak'
                        ]
                    ],
                    'gave_birth_in_health_facility' => [
                        'label' => 'Melahirkan di Faskes',
                        'type' => 'boolean',
                        'options' => [
                            '1' => 'Ya',
                            '0' => 'Tidak'
                        ]
                    ],
                    'is_eligible_asi' => [
                        'label' => 'Anak usia 7-23 bulan',
                        'type' => 'boolean',
                        'options' => [
                            '1' => 'Ya'
                        ]
                    ],
                    'exclusive_breastfeeding' => [
                        'label' => 'ASI Eksklusif',
                        'type' => 'boolean',
                        'options' => [
                            '1' => 'Ya',
                            '0' => 'Tidak'
                        ]
                    ],
                    'is_eligible_imunisasi' => [
                        'label' => 'Anak usia 12-23 bulan',
                        'type' => 'boolean',
                        'options' => [
                            '1' => 'Ya'
                        ]
                    ],
                    'complete_immunization' => [
                        'label' => 'Imunisasi Lengkap',
                        'type' => 'boolean',
                        'options' => [
                            '1' => 'Ya',
                            '0' => 'Tidak'
                        ]
                    ],
                    'is_eligible_growth' => [
                        'label' => 'Anak usia 2-59 bulan',
                        'type' => 'boolean',
                        'options' => [
                            '1' => 'Ya'
                        ]
                    ],
                    'growth_monitoring' => [
                        'label' => 'Pemantauan Pertumbuhan',
                        'type' => 'boolean',
                        'options' => [
                            '1' => 'Ya',
                            '0' => 'Tidak'
                        ]
                    ]
                ]
            ],
            'sanitation' => [
                'title' => 'Sanitasi',
                'variables' => [
                    'sarana_air_bersih' => [  // Dari tabel families (has_clean_water)
                        'label' => 'Tersedia sarana air bersih di lingkungan rumah?',
                        'type' => 'boolean',
                        'options' => [
                            '1' => 'Ya',
                            '0' => 'Tidak'
                        ]
                    ],
                    'is_water_protected' => [  // Dari tabel families
                        'label' => 'Jenis sumber air terlindungi?',
                        'type' => 'boolean',
                        'options' => [
                            '1' => 'Ya',
                            '0' => 'Tidak'
                        ]
                    ],
                    'use_water' => [
                        'label' => 'Menggunakan Air Bersih?',
                        'type' => 'boolean',
                        'options' => [
                            '1' => 'Ya',
                            '0' => 'Tidak'
                        ]
                    ],
                    'has_toilet' => [  // Dari tabel families
                        'label' => 'Tersedia jamban keluarga?',
                        'type' => 'boolean',
                        'options' => [
                            '1' => 'Ya',
                            '0' => 'Tidak'
                        ]
                    ],
                    'is_toilet_sanitary' => [  // Dari tabel families
                        'label' => 'Jenis jamban saniter?',
                        'type' => 'boolean',
                        'options' => [
                            '1' => 'Ya',
                            '0' => 'Tidak'
                        ]
                    ],
                    'bab_di_jamban' => [  // Dari tabel family_members (use_toilet)
                        'label' => 'Buang Air Besar di Jamban?',
                        'type' => 'boolean',
                        'options' => [
                            '1' => 'Ya',
                            '0' => 'Tidak'
                        ]
                    ]
                ]
            ]
        ];
    }

    protected function getVisualizationTypes()
    {
        return [
            'table' => 'Tabel',
            'bar' => 'Grafik Batang',
            'pie' => 'Grafik Lingkaran',
            'line' => 'Grafik Garis'
        ];
    }

    public function analyze(Request $request)
    {
        // par middleware
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 401);
        }
        // par middleware

        try {
            $filters = $request->filters ?? [];

            // Detail log untuk debugging filters
            Log::info('Analysis Request - Request data:', [
                'all' => $request->all(),
                'filters' => $filters
            ]);

            if (isset($filters['growth_monitoring'])) {
                Log::info('Analysis Request - Growth monitoring filter:', [
                    'growth_monitoring' => $filters['growth_monitoring'],
                    'growth_monitoring_type' => gettype($filters['growth_monitoring'])
                ]);
            }

            $result = $this->analysisService->performAnalysis(
                $filters,
                $request->visualization_type ?? 'table'
            );

            Log::info('Analysis Result:', ['result_type' => $result['type']]);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Analysis Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'detail' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    public function exportForGoogleMyMaps()
    {
        // Dapatkan semua data bangunan dengan relasinya
        $buildings = \App\Models\Building::with(['village', 'families.members'])->get();

        // Siapkan array untuk data CSV
        $csvData = [];

        foreach ($buildings as $building) {
            // Kumpulkan informasi kesehatan dari keluarga di bangunan ini
            $hasTb = false;
            $hasHypertension = false;
            $hasCleanWaterIssue = false;
            $hasToiletIssue = false;
            $hasMentalIllness = false;

            foreach ($building->families as $family) {
                // Cek masalah air dan toilet
                if (!$family->has_clean_water || !$family->is_water_protected) {
                    $hasCleanWaterIssue = true;
                }

                if (!$family->has_toilet || !$family->is_toilet_sanitary) {
                    $hasToiletIssue = true;
                }

                if ($family->has_mental_illness) {
                    $hasMentalIllness = true;
                }

                // Cek masalah TB dan hipertensi dari anggota keluarga
                foreach ($family->members as $member) {
                    if ($member->has_tuberculosis) {
                        $hasTb = true;
                    }

                    if ($member->has_hypertension) {
                        $hasHypertension = true;
                    }
                }
            }

            // Buat baris data untuk bangunan ini
            $row = [
                'Name' => 'Rumah ' . $building->building_number,
                'Description' => $this->generateBuildingDescription($building),
                'Latitude' => $building->latitude,
                'Longitude' => $building->longitude,
                'Desa' => $building->village ? $building->village->name : 'Tidak diketahui',
                'Jumlah_Keluarga' => $building->families->count(),
                'TB' => $hasTb ? 'Ya' : 'Tidak',
                'Hipertensi' => $hasHypertension ? 'Ya' : 'Tidak',
                'Masalah_Air' => $hasCleanWaterIssue ? 'Ya' : 'Tidak',
                'Masalah_Toilet' => $hasToiletIssue ? 'Ya' : 'Tidak',
                'Gangguan_Jiwa' => $hasMentalIllness ? 'Ya' : 'Tidak',
            ];

            $csvData[] = $row;
        }

        // Buat file CSV
        $headers = array_keys($csvData[0]);
        $fileName = 'pkm_kaben_untuk_mymaps.csv';

        $output = fopen('php://temp', 'r+');

        // Tulis header kolom
        fputcsv($output, $headers);

        // Tulis data
        foreach ($csvData as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        // Download file CSV
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$fileName}");
    }

    // Fungsi untuk membuat deskripsi bangunan untuk popup di Google MyMaps
    private function generateBuildingDescription($building)
    {
        $description = "Nomor Rumah: {$building->building_number}\n";
        $description .= "Desa: " . ($building->village ? $building->village->name : 'Tidak diketahui') . "\n";
        $description .= "Jumlah Keluarga: {$building->families->count()}\n\n";

        foreach ($building->families as $index => $family) {
            $description .= "Keluarga " . ($index + 1) . ": {$family->head_name}\n";
            $description .= "- Jumlah Anggota: {$family->members->count()}\n";

            // Tambahkan informasi fasilitas
            $description .= "- Air Bersih: " . ($family->has_clean_water ? 'Ya' : 'Tidak') . "\n";
            $description .= "- Jamban: " . ($family->has_toilet ? 'Ya' : 'Tidak') . "\n";

            // Tambahkan informasi kesehatan jika ada masalah
            $healthIssues = [];
            foreach ($family->members as $member) {
                if ($member->has_tuberculosis) {
                    $healthIssues[] = "TB";
                }
                if ($member->has_hypertension) {
                    $healthIssues[] = "Hipertensi";
                }
            }

            if (!empty($healthIssues)) {
                $description .= "- Masalah kesehatan: " . implode(", ", $healthIssues) . "\n";
            }

            $description .= "\n";
        }

        return $description;
    }

    /**
     * Export data hasil analisis ke Excel
     */
    public function export(Request $request)
    {
        // Periksa otentikasi
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 401);
        }

        try {
            Log::info('Export Request:', $request->all());

            // Ambil data analisis dari request
            $data = $request->data;

            if (!$data || !isset($data['headers']) || !isset($data['rows'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak valid'
                ], 400);
            }

            // Coba menggunakan Laravel Excel
            return $this->exportUsingLaravelExcel($data);
        } catch (\Exception $e) {
            Log::error('Export Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Jika Laravel Excel gagal, gunakan PhpSpreadsheet langsung
            try {
                if (isset($data)) {
                    return $this->exportUsingPhpSpreadsheet($data);
                } else {
                    throw new \Exception('Data tidak tersedia untuk export');
                }
            } catch (\Exception $e2) {
                Log::error('Fallback Export Error:', [
                    'message' => $e2->getMessage(),
                    'trace' => $e2->getTraceAsString()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat mengekspor data: ' . $e2->getMessage(),
                    'detail' => config('app.debug') ? $e2->getTraceAsString() : null
                ], 500);
            }
        }
    }

    /**
     * Export menggunakan Laravel Excel
     */
    private function exportUsingLaravelExcel($data)
    {
        // Nama file
        $date = new \DateTime();
        $timestamp = $date->format('Ymd_His');
        $filename = 'analisis_data_' . $timestamp . '.xlsx';

        // Pakai package Excel dengan export class yang sudah dibuat
        $export = new SimpleAnalysisExport($data);

        // Membuat file Excel
        return Excel::download($export, $filename);
    }

    /**
     * Export menggunakan PhpSpreadsheet secara langsung
     */
    private function exportUsingPhpSpreadsheet($data)
    {
        // Buat spreadsheet dengan PhpSpreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set judul worksheet
        $sheet->setTitle('Hasil Analisis');

        // Temukan indeks kolom NIK
        $nikColumnIndex = -1;
        foreach ($data['headers'] as $index => $header) {
            if ($header === 'NIK') {
                $nikColumnIndex = $index;
                break;
            }
        }

        // Tulis header
        foreach ($data['headers'] as $colIndex => $header) {
            $col = Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($col . '1', $header);

            // Format header
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getStyle($col . '1')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E2E8F0');
        }

        // Tulis data
        foreach ($data['rows'] as $rowIndex => $row) {
            $excelRowIndex = $rowIndex + 2; // +2 karena baris 1 adalah header

            foreach ($row as $colIndex => $cell) {
                $col = Coordinate::stringFromColumnIndex($colIndex + 1);

                // Khusus untuk kolom NIK, gunakan format teks
                if ($colIndex === $nikColumnIndex) {
                    // Pastikan nilai adalah string
                    $cell = (string) $cell;
                    // Set nilai eksplisit sebagai teks
                    $sheet->getCellByColumnAndRow($colIndex + 1, $excelRowIndex)
                        ->setValueExplicit($cell, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                } else {
                    // Data usia sudah diformat sebelumnya oleh AnalysisService
                    $sheet->setCellValue($col . $excelRowIndex, $cell);
                }
            }
        }

        // Jika kolom NIK ditemukan, set format kolom sebagai teks
        if ($nikColumnIndex >= 0) {
            $nikCol = Coordinate::stringFromColumnIndex($nikColumnIndex + 1);
            $nikRange = $nikCol . '2:' . $nikCol . ($sheet->getHighestRow());
            $sheet->getStyle($nikRange)
                ->getNumberFormat()
                ->setFormatCode('@');
        }

        // Auto-size kolom
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Tambahkan filter
        $highestColumn = $sheet->getHighestColumn();
        $sheet->setAutoFilter('A1:' . $highestColumn . '1');

        // Beautify
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ];

        $sheet->getStyle('A1:' . $highestColumn . ($sheet->getHighestRow()))->applyFromArray($styleArray);

        // Freezing the top row
        $sheet->freezePane('A2');

        // Nama file
        $date = new \DateTime();
        $timestamp = $date->format('Ymd_His');
        $filename = 'analisis_data_' . $timestamp . '.xlsx';

        // Output file Excel menggunakan file temporary
        $tmpFilename = tempnam(sys_get_temp_dir(), 'excel');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tmpFilename);

        $excelOutput = file_get_contents($tmpFilename);
        @unlink($tmpFilename);

        return response($excelOutput, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => strlen($excelOutput),
            'Cache-Control' => 'max-age=0',
            'Pragma' => 'public',
        ]);
    }
}
