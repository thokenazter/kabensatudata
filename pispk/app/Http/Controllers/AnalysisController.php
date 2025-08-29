<?php

namespace App\Http\Controllers;

use App\Models\Village;
use App\Models\Family;
use Illuminate\Http\Request;
use App\Services\AnalysisService;
use Illuminate\Routing\Controller;

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
                    // 'village_name' => [
                    //     'label' => 'Desa',
                    //     'type' => 'select',
                    //     'options' => Village::pluck('name', 'id')->toArray()
                    // ],
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
                    'age' => [
                        'label' => 'Usia',
                        'type' => 'range',
                        'options' => [
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
                            'Tamat S2/S3' => 'Tamat S2/S3',
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
                            // '0' => 'Tidak',
                            'NULL' => 'Tidak',
                        ]
                    ],
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
                    'exclusive_breastfeeding' => [
                        'label' => 'ASI Eksklusif',
                        'type' => 'boolean',
                        'options' => [
                            '1' => 'Ya',
                            '0' => 'Tidak'
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
                    'use_water' => [
                        'label' => 'Menggunakan Air Bersih',
                        'type' => 'boolean',
                        'options' => [
                            '1' => 'Ya',
                            '0' => 'Tidak',
                        ]
                    ],
                    'is_water_protected' => [  // Dari tabel families
                        'label' => 'Sumber Air Terlindungi',
                        'type' => 'boolean',
                        'options' => [
                            '1' => 'Ya',
                            '0' => 'Tidak'
                        ]
                    ],
                    'use_toilet' => [
                        'label' => 'Menggunakan Jamban',
                        'type' => 'boolean',
                        'options' => [
                            '1' => 'Ya',
                            '0' => 'Tidak'
                        ]
                    ],
                    'is_toilet_sanitary' => [  // Dari tabel families
                        'label' => 'Jamban Saniter',
                        'type' => 'boolean',
                        'options' => [
                            '1' => 'Ya',
                            // '0' => 'Tidak',
                            'NULL' => 'Tidak',
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
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 401);
        }
        // par middleware

        try {
            \Log::info('Analysis Request:', $request->all());

            $filters = $request->filters ?? [];

            $result = $this->analysisService->performAnalysis(
                $filters,
                $request->visualization_type ?? 'table'
            );

            \Log::info('Analysis Result:', ['result' => $result]);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            \Log::error('Analysis Error:', [
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
}
