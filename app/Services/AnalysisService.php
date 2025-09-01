<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AnalysisService
{
    public function performAnalysis($filters = [], $visualizationType = 'table')
    {
        try {
            // Log filter yang diterima untuk debugging
            Log::info('performAnalysis filters received:', [
                'filters' => $filters,
                'visualizationType' => $visualizationType
            ]);

            $query = DB::table('family_members')
                ->join('families', 'family_members.family_id', '=', 'families.id')
                ->join('buildings', 'families.building_id', '=', 'buildings.id')
                ->join('villages', 'buildings.village_id', '=', 'villages.id')
                ->select([
                    'family_members.*',
                    'family_members.nik',
                    'family_members.birth_date',
                    'family_members.relationship',
                    'families.family_number',
                    'families.has_clean_water',
                    'families.has_toilet',
                    'families.is_water_protected',
                    'families.is_toilet_sanitary',
                    'buildings.building_number',
                    'villages.name as village_name'
                ]);

            // Terapkan filter
            if (!empty($filters)) {
                $this->applyFilters($query, $filters);
            }

            // Urutkan data
            $query->orderBy('villages.name')
                ->orderBy('buildings.building_number')
                ->orderBy('families.family_number');

            // Log query lengkap sebelum dieksekusi
            Log::info('Final SQL query:', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);

            $results = $query->get();

            // Log jumlah hasil
            Log::info('Query results count:', ['count' => $results->count()]);

            return $this->formatData($results, $filters, $visualizationType);
        } catch (\Exception $e) {
            Log::error('Analysis Service Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function applyFilters($query, $filters)
    {
        foreach ($filters as $field => $value) {
            if ($value === '' || $value === null) continue;

            switch ($field) {
                case 'age':
                    $this->applyAgeFilter($query, $value);
                    break;

                case 'village_id':
                    $query->where('villages.id', $value);
                    break;

                // Kolom dari tabel families
                case 'is_water_protected':
                case 'is_toilet_sanitary':
                case 'has_toilet':
                    if ($value === '1') {
                        // Gunakan SQL eksplisit untuk filter boolean
                        $query->whereRaw("families." . $field . " = 1");
                    } else {
                        // Gunakan SQL eksplisit untuk filter boolean
                        $query->whereRaw("(families." . $field . " = 0 OR families." . $field . " IS NULL)");
                    }
                    break;

                // Filter khusus untuk sarana air bersih (dari tabel families)
                case 'sarana_air_bersih':
                    if ($value === '1') {
                        $query->whereRaw("families.has_clean_water = 1");
                    } else {
                        $query->whereRaw("(families.has_clean_water = 0 OR families.has_clean_water IS NULL)");
                    }
                    break;

                case 'building_number':
                    $query->where('buildings.building_number', $value);
                    break;

                case 'family_number':
                    $query->where('families.family_number', $value);
                    break;

                // Personal Info
                case 'gender':
                case 'education':
                case 'marital_status':
                case 'religion':
                case 'occupation':
                    $query->where('family_members.' . $field, $value);
                    break;

                // Kasus khusus untuk filter kepala keluarga
                case 'is_head_of_family':
                    if ($value === '1') {
                        // Hanya kepala keluarga
                        $query->whereRaw("family_members.relationship = 'Kepala Keluarga'");
                    } elseif ($value === '0') {
                        // Bukan kepala keluarga
                        $query->whereRaw("family_members.relationship != 'Kepala Keluarga'");
                    }
                    // Jika $value === 'all' atau kosong, tidak ada filter yang diterapkan
                    break;

                // Kasus khusus untuk filter terkait KIA
                case 'is_wus':
                    if ($value === '1') {
                        $query->where('family_members.gender', 'Perempuan')
                            ->whereRaw("TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 10 AND 54")
                            ->whereIn('family_members.marital_status', ['Kawin', 'Cerai Hidup', 'Cerai Mati']);
                    }
                    break;

                case 'is_eligible_asi':
                    if ($value === '1') {
                        $query->whereRaw("TIMESTAMPDIFF(MONTH, birth_date, CURDATE()) BETWEEN 7 AND 23");
                    }
                    break;

                case 'is_eligible_imunisasi':
                    if ($value === '1') {
                        $query->whereRaw("TIMESTAMPDIFF(MONTH, birth_date, CURDATE()) BETWEEN 12 AND 23");
                    }
                    break;

                case 'is_eligible_growth':
                    if ($value === '1') {
                        $query->whereRaw("TIMESTAMPDIFF(MONTH, birth_date, CURDATE()) BETWEEN 2 AND 59");
                    }
                    break;

                // Boolean fields dengan penanganan nilai NULL
                case 'uses_contraception':
                    if ($value === '1') {
                        // Untuk opsi "Ya", hanya ambil yang benar-benar bernilai true
                        $query->where('family_members.uses_contraception', '=', true);
                    } else {
                        // Untuk opsi "Tidak", ambil yang bernilai false atau NULL
                        $query->where(function ($q) {
                            $q->where('family_members.uses_contraception', '=', false)
                                ->orWhereNull('family_members.uses_contraception');
                        });
                    }
                    break;

                case 'growth_monitoring':
                    if ($value === '1') {
                        // Untuk opsi "Ya", gunakan raw SQL langsung
                        Log::info('Filter growth_monitoring: Yes', ['value' => $value]);
                        $query->whereRaw("family_members.growth_monitoring = 1")
                            ->whereRaw("TIMESTAMPDIFF(MONTH, birth_date, CURDATE()) BETWEEN 2 AND 59");
                    } else {
                        // Untuk opsi "Tidak", gunakan raw SQL langsung dengan eksplisit IS FALSE atau IS NULL
                        Log::info('Filter growth_monitoring: No', ['value' => $value]);
                        $query->whereRaw("TIMESTAMPDIFF(MONTH, birth_date, CURDATE()) BETWEEN 2 AND 59")
                            ->whereRaw("(family_members.growth_monitoring = 0 OR family_members.growth_monitoring IS NULL)");

                        // Log SQL query yang dihasilkan
                        Log::info('SQL Query for growth_monitoring=No:', [
                            'sql' => $query->toSql(),
                            'bindings' => $query->getBindings()
                        ]);
                    }
                    break;

                case 'exclusive_breastfeeding':
                    if ($value === '1') {
                        // Untuk opsi "Ya", gunakan raw SQL langsung
                        $query->whereRaw("family_members.exclusive_breastfeeding = 1")
                            ->whereRaw("TIMESTAMPDIFF(MONTH, birth_date, CURDATE()) BETWEEN 7 AND 23");
                    } else {
                        // Untuk opsi "Tidak", gunakan raw SQL langsung
                        $query->whereRaw("TIMESTAMPDIFF(MONTH, birth_date, CURDATE()) BETWEEN 7 AND 23")
                            ->whereRaw("(family_members.exclusive_breastfeeding = 0 OR family_members.exclusive_breastfeeding IS NULL)");
                    }
                    break;

                case 'complete_immunization':
                    if ($value === '1') {
                        // Untuk opsi "Ya", gunakan raw SQL langsung
                        $query->whereRaw("family_members.complete_immunization = 1")
                            ->whereRaw("TIMESTAMPDIFF(MONTH, birth_date, CURDATE()) BETWEEN 12 AND 23");
                    } else {
                        // Untuk opsi "Tidak", gunakan raw SQL langsung
                        $query->whereRaw("TIMESTAMPDIFF(MONTH, birth_date, CURDATE()) BETWEEN 12 AND 23")
                            ->whereRaw("(family_members.complete_immunization = 0 OR family_members.complete_immunization IS NULL)");
                    }
                    break;

                case 'gave_birth_in_health_facility':
                    if ($value === '1') {
                        // Untuk opsi "Ya", hanya ambil yang benar-benar bernilai true
                        $query->where('family_members.gave_birth_in_health_facility', '=', true);
                    } else {
                        // Untuk opsi "Tidak", ambil yang bernilai false atau NULL
                        $query->where(function ($q) {
                            $q->where('family_members.gave_birth_in_health_facility', '=', false)
                                ->orWhereNull('family_members.gave_birth_in_health_facility');
                        });
                    }
                    break;

                // Boolean fields lainnya
                case 'is_pregnant':
                case 'has_jkn':
                case 'is_smoker':
                case 'has_tuberculosis':
                case 'takes_tb_medication_regularly':
                case 'has_chronic_cough':
                case 'has_hypertension':
                case 'use_water':
                case 'use_toilet':
                case 'takes_hypertension_medication_regularly':
                    if ($value === '1') {
                        // Gunakan SQL eksplisit untuk filter boolean
                        $query->whereRaw("family_members." . $field . " = 1");
                    } else {
                        // Gunakan SQL eksplisit untuk filter boolean
                        $query->whereRaw("(family_members." . $field . " = 0 OR family_members." . $field . " IS NULL)");
                    }
                    break;

                // Filter khusus untuk BAB di jamban (menggunakan kolom use_toilet di family_members)
                case 'bab_di_jamban':
                    if ($value === '1') {
                        $query->whereRaw("family_members.use_toilet = 1");
                    } else {
                        $query->whereRaw("(family_members.use_toilet = 0 OR family_members.use_toilet IS NULL)");
                    }
                    break;
            }
        }
    }

    protected function applyAgeFilter($query, $ageRange)
    {
        // Cek apakah format kustom dengan pola "x-y" (rentang usia kustom)
        if (preg_match('/^(\d+)-(\d+)$/', $ageRange, $matches) && !in_array($ageRange, ['0-5', '6-12', '13-17', '18-45'])) {
            $minAge = (int)$matches[1];
            $maxAge = (int)$matches[2];

            // Gunakan TIMESTAMPDIFF untuk mendapatkan hasil yang lebih akurat
            $query->whereRaw("TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) >= ?", [$minAge])
                ->whereRaw("TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) <= ?", [$maxAge]);
            return;
        }

        // Cek apakah format kustom dengan pola "x+" (usia minimum)
        if (preg_match('/^(\d+)\+$/', $ageRange, $matches)) {
            $minAge = (int)$matches[1];
            // Gunakan TIMESTAMPDIFF untuk mendapatkan hasil yang lebih akurat
            $query->whereRaw("TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) >= ?", [$minAge]);
            return;
        }

        // Format standar yang sudah ada - gunakan TIMESTAMPDIFF untuk konsistensi
        switch ($ageRange) {
            case '0-5':
                $query->whereRaw("TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) >= 0")
                    ->whereRaw("TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) <= 5");
                break;
            case '6-12':
                $query->whereRaw("TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) >= 6")
                    ->whereRaw("TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) <= 12");
                break;
            case '13-17':
                $query->whereRaw("TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) >= 13")
                    ->whereRaw("TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) <= 17");
                break;
            case '18-45':
                $query->whereRaw("TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) >= 18")
                    ->whereRaw("TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) <= 45");
                break;
            case '46-plus':
                $query->whereRaw("TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) > 45");
                break;
        }
    }

    protected function formatData($results, $filters, $visualizationType)
    {
        // Post-filtering untuk kolom-kolom boolean tertentu
        $booleanColumns = [
            'growth_monitoring',
            'exclusive_breastfeeding',
            'complete_immunization',
            'gave_birth_in_health_facility',
            'uses_contraception',
            'is_toilet_sanitary',    // Jamban saniter (dari tabel families)
            'is_water_protected',     // Sumber air terlindungi (dari tabel families)
            'has_toilet',              // Tersedia jamban keluarga (dari tabel families)
            'takes_tb_medication_regularly',    // Minum Obat TBC Teratur
            'takes_hypertension_medication_regularly'  // Minum Obat Hipertensi Teratur
        ];

        // Lakukan post-filtering untuk setiap kolom boolean di atas jika ada filter
        foreach ($booleanColumns as $column) {
            if (isset($filters[$column])) {
                $filterValue = $filters[$column];

                // Filter ulang data berdasarkan nilai kolom boolean
                $results = $results->filter(function ($row) use ($column, $filterValue) {
                    // Jika filter adalah 1 (Ya), hanya tampilkan row dengan kolom = 1/true
                    if ($filterValue === '1') {
                        return $row->{$column} === 1 || $row->{$column} === true;
                    }
                    // Jika filter adalah 0 (Tidak), hanya tampilkan row dengan kolom = 0/false/null
                    else {
                        return $row->{$column} === 0 || $row->{$column} === false || $row->{$column} === null;
                    }
                });

                // Convert kembali ke collection
                $results = collect(array_values($results->all()));

                // Logging untuk debug
                $totalCount = $results->count();
                $yesCount = $results->filter(function ($row) use ($column) {
                    return $row->{$column} === 1 || $row->{$column} === true;
                })->count();
                $noCount = $results->filter(function ($row) use ($column) {
                    return $row->{$column} === 0 || $row->{$column} === false || $row->{$column} === null;
                })->count();
                $nullCount = $results->filter(function ($row) use ($column) {
                    return $row->{$column} === null;
                })->count();

                Log::info("Post-filtering for {$column}:", [
                    'filter_value' => $filterValue,
                    'total_results' => $totalCount,
                    'yes_count' => $yesCount,
                    'no_count' => $noCount,
                    'null_count' => $nullCount
                ]);
            }
        }

        // Tangani kasus khusus bab_di_jamban yang mengacu ke kolom use_toilet
        if (isset($filters['bab_di_jamban'])) {
            $filterValue = $filters['bab_di_jamban'];

            // Filter ulang data berdasarkan nilai kolom use_toilet
            $results = $results->filter(function ($row) use ($filterValue) {
                // Jika filter adalah 1 (Ya), hanya tampilkan row dengan use_toilet = 1/true
                if ($filterValue === '1') {
                    return $row->use_toilet === 1 || $row->use_toilet === true;
                }
                // Jika filter adalah 0 (Tidak), hanya tampilkan row dengan use_toilet = 0/false/null
                else {
                    return $row->use_toilet === 0 || $row->use_toilet === false || $row->use_toilet === null;
                }
            });

            // Convert kembali ke collection
            $results = collect(array_values($results->all()));

            // Logging untuk debug
            Log::info("Post-filtering for bab_di_jamban:", [
                'filter_value' => $filterValue,
                'total_results' => $results->count()
            ]);
        }

        // Tangani kasus khusus sarana_air_bersih
        if (isset($filters['sarana_air_bersih'])) {
            $filterValue = $filters['sarana_air_bersih'];

            // Periksa apakah has_clean_water tersedia dalam dataset
            $sample = $results->first();
            if ($sample && property_exists($sample, 'has_clean_water')) {
                // Filter ulang data berdasarkan nilai kolom has_clean_water
                $results = $results->filter(function ($row) use ($filterValue) {
                    // Jika filter adalah 1 (Ya), hanya tampilkan row dengan has_clean_water = 1/true
                    if ($filterValue === '1') {
                        return $row->has_clean_water === 1 || $row->has_clean_water === true;
                    }
                    // Jika filter adalah 0 (Tidak), hanya tampilkan row dengan has_clean_water = 0/false/null
                    else {
                        return $row->has_clean_water === 0 || $row->has_clean_water === false || $row->has_clean_water === null;
                    }
                });

                // Convert kembali ke collection
                $results = collect(array_values($results->all()));

                // Logging untuk debug
                Log::info("Post-filtering for sarana_air_bersih:", [
                    'filter_value' => $filterValue,
                    'total_results' => $results->count()
                ]);
            } else {
                // Log error jika has_clean_water tidak tersedia
                Log::error("Error: has_clean_water not available for post-filtering");
            }
        }

        // Tangani kasus khusus has_toilet
        if (isset($filters['has_toilet'])) {
            $filterValue = $filters['has_toilet'];

            // Periksa apakah kolom has_toilet tersedia dalam dataset
            $sample = $results->first();
            if (!$sample || !property_exists($sample, 'has_toilet')) {
                // Jika tidak tersedia, gunakan default empty dataset
                Log::error('Error: has_toilet not available in dataset for has_toilet analysis');
                // Tetap lanjutkan dengan data kosong, tapi jangan langsung return dari sini
                $results = collect([]);
            } else {
                // Filter ulang data berdasarkan nilai kolom has_toilet
                $results = $results->filter(function ($row) use ($filterValue) {
                    // Jika filter adalah 1 (Ya), hanya tampilkan row dengan has_toilet = 1/true
                    if ($filterValue === '1') {
                        return $row->has_toilet === 1 || $row->has_toilet === true;
                    }
                    // Jika filter adalah 0 (Tidak), hanya tampilkan row dengan has_toilet = 0/false/null
                    else {
                        return $row->has_toilet === 0 || $row->has_toilet === false || $row->has_toilet === null;
                    }
                });

                // Convert kembali ke collection
                $results = collect(array_values($results->all()));

                // Logging untuk debug
                Log::info("Post-filtering for has_toilet:", [
                    'filter_value' => $filterValue,
                    'total_results' => $results->count()
                ]);
            }
        }

        switch ($visualizationType) {
            case 'table':
                return $this->formatTableData($results);
            case 'bar':
                return $this->formatBarChartData($results, $filters);
            case 'pie':
                return $this->formatPieChartData($results, $filters);
            case 'line':
                return $this->formatLineChartData($results, $filters);
            default:
                return $this->formatTableData($results);
        }
    }

    protected function formatTableData($results)
    {
        // Log sample dari beberapa baris pertama untuk debugging
        if ($results->count() > 0) {
            $sampleRows = $results->take(5);
            foreach ($sampleRows as $index => $row) {
                // Konversi objek ke array untuk melihat semua nilai
                $rowArray = (array) $row;

                // Ambil nilai growth_monitoring mentah
                $rawGrowthValue = $rowArray['growth_monitoring'] ?? null;

                Log::info("Sample row #{$index} growth_monitoring details:", [
                    'name' => $row->name,
                    'growth_monitoring_raw' => $rawGrowthValue,
                    'growth_monitoring_type' => gettype($rawGrowthValue),
                    'growth_monitoring_is_null' => is_null($rawGrowthValue),
                    'growth_monitoring_formatted' => $this->formatBoolean($rawGrowthValue),
                    'birth_date' => $row->birth_date,
                    'age_in_months' => $row->birth_date ? Carbon::parse($row->birth_date)->diffInMonths(Carbon::now()) : null
                ]);
            }
        }

        return [
            'type' => 'table',
            'headers' => [
                'Desa',
                'No. Bangunan',
                'No. KK',
                'NIK',
                'Nama',
                'Tanggal Lahir',
                'Jenis Kelamin',
                'Usia',
                'Status dalam Keluarga',
                'Pendidikan',
                'Status Perkawinan',
                'Agama',
                'Pekerjaan',
                'Status Kehamilan',
                'JKN',
                'Perokok',
                'TBC',
                'Minum Obat TBC',
                'Batuk Kronis',
                'Hipertensi',
                'Minum Obat Hipertensi Teratur',
                'KB',
                'Melahirkan di Faskes',
                'ASI Eksklusif',
                'Imunisasi',
                'Pemantauan Pertumbuhan',
                'Sarana Air Bersih',   // Header untuk has_clean_water
                'Menggunakan Air Bersih',
                'Sumber Air Terlindungi',
                'Tersedia Jamban Keluarga',  // has_toilet dari families
                'Jamban Saniter',           // is_toilet_sanitary dari families
                'BAB di Jamban'             // use_toilet dari family_members
            ],
            'rows' => $results->map(function ($row) {
                // Format usia: jika usia 0 tahun (belum 1 tahun), tampilkan dalam bulan
                $ageDisplay = $this->formatAgeDisplay($row->age, $row->birth_date);

                // Periksa apakah property has_clean_water tersedia
                $hasCleanWater = property_exists($row, 'has_clean_water') ? $row->has_clean_water : null;

                // Periksa apakah property has_toilet tersedia
                $hasToilet = property_exists($row, 'has_toilet') ? $row->has_toilet : null;

                return [
                    $row->village_name,
                    'B-' . str_pad($row->building_number, 3, '0', STR_PAD_LEFT),
                    'KK-' . str_pad($row->family_number, 3, '0', STR_PAD_LEFT),
                    $row->nik ?? '-',
                    $row->name,
                    $row->birth_date ? date('d-m-Y', strtotime($row->birth_date)) : '-',
                    $row->gender,
                    $ageDisplay,
                    $row->relationship ? $row->relationship : '-',
                    $row->education ?? '-',
                    $row->marital_status ?? '-',
                    $row->religion ?? '-',
                    $row->occupation ?? '-',
                    $this->formatBoolean($row->is_pregnant),
                    $this->formatBoolean($row->has_jkn),
                    $this->formatBoolean($row->is_smoker),
                    $this->formatBoolean($row->has_tuberculosis),
                    $this->formatBoolean($row->takes_tb_medication_regularly),
                    $this->formatBoolean($row->has_chronic_cough),
                    $this->formatBoolean($row->has_hypertension),
                    $this->formatBoolean($row->takes_hypertension_medication_regularly),
                    $this->formatBoolean($row->uses_contraception),
                    $this->formatBoolean($row->gave_birth_in_health_facility),
                    $this->formatBoolean($row->exclusive_breastfeeding),
                    $this->formatBoolean($row->complete_immunization),
                    $this->formatBoolean($row->growth_monitoring),
                    $this->formatBoolean($hasCleanWater),  // Sarana Air Bersih (kolom has_clean_water) dengan pengecekan
                    $this->formatBoolean($row->use_water),        // Menggunakan Air Bersih
                    $this->formatBoolean($row->is_water_protected),
                    $this->formatBoolean($hasToilet),             // Tersedia jamban keluarga? (has_toilet)
                    $this->formatBoolean($row->is_toilet_sanitary), // Jamban saniter 
                    $this->formatBoolean($row->use_toilet)        // BAB di jamban (use_toilet dari family_members)
                ];
            })->toArray()
        ];
    }

    protected function formatBoolean($value)
    {
        // Jika nilai null, tampilkan '-'
        if ($value === null) {
            return '-';
        }

        // Untuk nilai integer 1 atau boolean true
        if ($value === 1 || $value === true) {
            return 'Ya';
        }

        // Semua kasus lain (termasuk 0, false, string, dll)
        return 'Tidak';
    }

    protected function formatBarChartData($results, $filters)
    {
        // Tentukan field untuk analisis
        $analysisField = $this->determineAnalysisField($filters);

        // Tangani kasus khusus bab_di_jamban
        if ($analysisField === 'bab_di_jamban') {
            // Gunakan data 'use_toilet' untuk membuat dataset
            $groupedData = $results->groupBy('use_toilet')->map(function ($items, $key) {
                // Konversi nilai ke string yang sesuai untuk label
                if ($key === 1 || $key === true) {
                    return ['label' => 'Ya', 'count' => $items->count()];
                } else {
                    return ['label' => 'Tidak', 'count' => $items->count()];
                }
            });

            // Ubah format ke array yang sesuai
            $labels = $groupedData->pluck('label')->toArray();
            $counts = $groupedData->pluck('count')->toArray();

            return [
                'type' => 'bar',
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => $this->getFieldLabel($analysisField),
                        'data' => $counts
                    ]
                ]
            ];
        }

        // Tangani kasus khusus sarana_air_bersih
        if ($analysisField === 'sarana_air_bersih') {
            // Periksa apakah kolom has_clean_water tersedia dalam dataset
            $sample = $results->first();
            if (!$sample || !property_exists($sample, 'has_clean_water')) {
                // Jika tidak tersedia, gunakan default empty dataset
                Log::error('Error: has_clean_water not available in dataset for sarana_air_bersih analysis');

                return [
                    'type' => 'bar',
                    'labels' => ['Data tidak tersedia'],
                    'datasets' => [
                        [
                            'label' => $this->getFieldLabel($analysisField),
                            'data' => [0]
                        ]
                    ]
                ];
            }

            // Gunakan data 'has_clean_water' untuk membuat dataset
            $groupedData = $results->groupBy('has_clean_water')->map(function ($items, $key) {
                // Konversi nilai ke string yang sesuai untuk label
                if ($key === 1 || $key === true) {
                    return ['label' => 'Ya', 'count' => $items->count()];
                } else {
                    return ['label' => 'Tidak', 'count' => $items->count()];
                }
            });

            // Ubah format ke array yang sesuai
            $labels = $groupedData->pluck('label')->toArray();
            $counts = $groupedData->pluck('count')->toArray();

            return [
                'type' => 'bar',
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => $this->getFieldLabel($analysisField),
                        'data' => $counts
                    ]
                ]
            ];
        }

        // Tangani kasus khusus has_toilet
        if ($analysisField === 'has_toilet') {
            // Periksa apakah kolom has_toilet tersedia dalam dataset
            $sample = $results->first();
            if (!$sample || !property_exists($sample, 'has_toilet')) {
                // Jika tidak tersedia, gunakan default empty dataset
                Log::error('Error: has_toilet not available in dataset for has_toilet analysis');

                return [
                    'type' => 'bar',
                    'labels' => ['Data tidak tersedia'],
                    'datasets' => [
                        [
                            'label' => $this->getFieldLabel($analysisField),
                            'data' => [0]
                        ]
                    ]
                ];
            }

            // Gunakan data 'has_toilet' untuk membuat dataset
            $groupedData = $results->groupBy('has_toilet')->map(function ($items, $key) {
                // Konversi nilai ke string yang sesuai untuk label
                if ($key === 1 || $key === true) {
                    return ['label' => 'Ya', 'count' => $items->count()];
                } else {
                    return ['label' => 'Tidak', 'count' => $items->count()];
                }
            });

            // Ubah format ke array yang sesuai
            $labels = $groupedData->pluck('label')->toArray();
            $counts = $groupedData->pluck('count')->toArray();

            return [
                'type' => 'bar',
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => $this->getFieldLabel($analysisField),
                        'data' => $counts
                    ]
                ]
            ];
        }

        // Tangani kasus khusus takes_tb_medication_regularly
        if ($analysisField === 'takes_tb_medication_regularly') {
            // Periksa apakah kolom takes_tb_medication_regularly tersedia dalam dataset
            $sample = $results->first();
            if (!$sample || !property_exists($sample, 'takes_tb_medication_regularly')) {
                // Jika tidak tersedia, gunakan default empty dataset
                Log::error('Error: takes_tb_medication_regularly not available in dataset');

                return [
                    'type' => 'bar',
                    'labels' => ['Data tidak tersedia'],
                    'datasets' => [
                        [
                            'label' => $this->getFieldLabel($analysisField),
                            'data' => [0]
                        ]
                    ]
                ];
            }

            // Filter dulu hanya TBC yang positif
            $tbPositiveResults = $results->filter(function ($row) {
                return $row->has_tuberculosis === 1 || $row->has_tuberculosis === true;
            });

            // Jika tidak ada data TBC positif, kembalikan pesan khusus
            if ($tbPositiveResults->isEmpty()) {
                return [
                    'type' => 'bar',
                    'labels' => ['Tidak ada data pasien TBC'],
                    'datasets' => [
                        [
                            'label' => $this->getFieldLabel($analysisField),
                            'data' => [0]
                        ]
                    ]
                ];
            }

            // Gunakan data untuk membuat dataset
            $groupedData = $tbPositiveResults->groupBy('takes_tb_medication_regularly')->map(function ($items, $key) {
                // Konversi nilai ke string yang sesuai untuk label
                if ($key === 1 || $key === true) {
                    return ['label' => 'Ya', 'count' => $items->count()];
                } else {
                    return ['label' => 'Tidak', 'count' => $items->count()];
                }
            });

            // Ubah format ke array yang sesuai
            $labels = $groupedData->pluck('label')->toArray();
            $counts = $groupedData->pluck('count')->toArray();

            return [
                'type' => 'bar',
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => $this->getFieldLabel($analysisField),
                        'data' => $counts
                    ]
                ]
            ];
        }

        // Tangani kasus khusus takes_hypertension_medication_regularly
        if ($analysisField === 'takes_hypertension_medication_regularly') {
            // Periksa apakah kolom takes_hypertension_medication_regularly tersedia dalam dataset
            $sample = $results->first();
            if (!$sample || !property_exists($sample, 'takes_hypertension_medication_regularly')) {
                // Jika tidak tersedia, gunakan default empty dataset
                Log::error('Error: takes_hypertension_medication_regularly not available in dataset');

                return [
                    'type' => 'bar',
                    'labels' => ['Data tidak tersedia'],
                    'datasets' => [
                        [
                            'label' => $this->getFieldLabel($analysisField),
                            'data' => [0]
                        ]
                    ]
                ];
            }

            // Filter dulu hanya hipertensi yang positif
            $hyperPositiveResults = $results->filter(function ($row) {
                return $row->has_hypertension === 1 || $row->has_hypertension === true;
            });

            // Jika tidak ada data hipertensi positif, kembalikan pesan khusus
            if ($hyperPositiveResults->isEmpty()) {
                return [
                    'type' => 'bar',
                    'labels' => ['Tidak ada data pasien hipertensi'],
                    'datasets' => [
                        [
                            'label' => $this->getFieldLabel($analysisField),
                            'data' => [0]
                        ]
                    ]
                ];
            }

            // Gunakan data untuk membuat dataset
            $groupedData = $hyperPositiveResults->groupBy('takes_hypertension_medication_regularly')->map(function ($items, $key) {
                // Konversi nilai ke string yang sesuai untuk label
                if ($key === 1 || $key === true) {
                    return ['label' => 'Ya', 'count' => $items->count()];
                } else {
                    return ['label' => 'Tidak', 'count' => $items->count()];
                }
            });

            // Ubah format ke array yang sesuai
            $labels = $groupedData->pluck('label')->toArray();
            $counts = $groupedData->pluck('count')->toArray();

            return [
                'type' => 'bar',
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => $this->getFieldLabel($analysisField),
                        'data' => $counts
                    ]
                ]
            ];
        }

        // Default behavior untuk field lainnya
        $groupedData = $results->groupBy($analysisField);
        $counts = $groupedData->map->count();

        return [
            'type' => 'bar',
            'labels' => $counts->keys()->toArray(),
            'datasets' => [
                [
                    'label' => $this->getFieldLabel($analysisField),
                    'data' => $counts->values()->toArray()
                ]
            ]
        ];
    }

    protected function formatPieChartData($results, $filters)
    {
        return $this->formatBarChartData($results, $filters);
    }

    protected function formatLineChartData($results, $filters)
    {
        // Khusus untuk data time series atau berkelanjutan
        $analysisField = $this->determineAnalysisField($filters);

        if ($analysisField === 'age') {
            $groupedData = $results->groupBy(function ($item) {
                return floor($item->age / 10) * 10;
            });

            $counts = $groupedData->map->count();

            return [
                'type' => 'line',
                'labels' => $counts->keys()->map(function ($key) {
                    return $key . '-' . ($key + 9) . ' tahun';
                })->toArray(),
                'datasets' => [
                    [
                        'label' => 'Distribusi Usia',
                        'data' => $counts->values()->toArray()
                    ]
                ]
            ];
        }

        return $this->formatBarChartData($results, $filters);
    }

    protected function determineAnalysisField($filters)
    {
        // Tentukan field yang akan dianalisis berdasarkan filter yang aktif
        $priorityFields = ['gender', 'education', 'age', 'has_tuberculosis', 'has_hypertension', 'bab_di_jamban', 'sarana_air_bersih', 'has_toilet', 'takes_tb_medication_regularly', 'takes_hypertension_medication_regularly'];

        foreach ($priorityFields as $field) {
            if (isset($filters[$field])) {
                return $field;
            }
        }

        return 'gender'; // default
    }

    protected function getFieldLabel($field)
    {
        $labels = [
            'gender' => 'Jenis Kelamin',
            'education' => 'Pendidikan',
            'age' => 'Usia',
            'marital_status' => 'Status Perkawinan',
            'religion' => 'Agama',
            'occupation' => 'Pekerjaan',
            'has_tuberculosis' => 'Status TBC',
            'has_hypertension' => 'Status Hipertensi',
            'is_water_protected' => 'Status Sumber Air Terlindungi',
            'is_toilet_sanitary' => 'Status Jamban Saniter',
            'bab_di_jamban' => 'Buang Air Besar di Jamban',
            'sarana_air_bersih' => 'Tersedia Sarana Air Bersih',
            'has_toilet' => 'Tersedia Jamban Keluarga',
            'takes_tb_medication_regularly' => 'Minum Obat TBC Teratur',
            'takes_hypertension_medication_regularly' => 'Minum Obat Hipertensi Teratur'
        ];

        return $labels[$field] ?? $field;
    }

    /**
     * Format tampilan usia dengan menampilkan usia < 1 tahun dalam bulan
     *
     * @param int $age Usia dalam tahun
     * @param string|null $birthDate Tanggal lahir (format Y-m-d)
     * @return string Formatted age display
     */
    protected function formatAgeDisplay($age, $birthDate = null)
    {
        if ($age == 0 && $birthDate) {
            try {
                $birthDateObj = new \Carbon\Carbon($birthDate);
                $now = \Carbon\Carbon::now();
                $ageInMonths = $birthDateObj->diffInMonths($now);
                // Bulatkan usia dalam bulan menjadi bilangan bulat
                $ageInMonths = round($ageInMonths);
                return $ageInMonths . ' bulan';
            } catch (\Exception $e) {
                // Jika ada kesalahan, kembalikan format asli
                \Illuminate\Support\Facades\Log::warning('Error formatting age: ' . $e->getMessage());
                return $age . ' tahun';
            }
        }
        return $age . ' tahun';
    }
}
