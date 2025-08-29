<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AnalysisService
{
    public function performAnalysis($filters = [], $visualizationType = 'table')
    {
        try {
            $query = DB::table('family_members')
                ->join('families', 'family_members.family_id', '=', 'families.id')
                ->join('buildings', 'families.building_id', '=', 'buildings.id')
                ->join('villages', 'buildings.village_id', '=', 'villages.id')
                ->select([
                    'family_members.*',
                    'families.family_number',
                    'families.is_water_protected', // Tambahkan kolom dari families
                    'families.is_toilet_sanitary', // Tambahkan kolom dari families
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

            $results = $query->get();

            return $this->formatData($results, $filters, $visualizationType);
        } catch (\Exception $e) {
            \Log::error('Analysis Service Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function applyFilters($query, $filters)
    {
        foreach ($filters as $field => $value) {
            if (empty($value)) continue;

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
                    $query->where('families.' . $field, $value === '1');
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

                // Boolean fields
                case 'is_pregnant':
                case 'has_jkn':
                case 'is_smoker':
                case 'has_tuberculosis':
                case 'takes_tb_medication_regularly':
                case 'has_chronic_cough':
                case 'has_hypertension':
                case 'uses_contraception':
                case 'gave_birth_in_health_facility':
                case 'exclusive_breastfeeding':
                case 'complete_immunization':
                case 'growth_monitoring':
                case 'use_water':
                case 'use_toilet':
                case 'takes_hypertension_medication_regularly';
                    $query->where('family_members.' . $field, $value === '1');
                    break;
            }
        }
    }

    protected function applyAgeFilter($query, $ageRange)
    {
        switch ($ageRange) {
            case '0-5':
                $query->whereBetween('family_members.age', [0, 5]);
                break;
            case '6-12':
                $query->whereBetween('family_members.age', [6, 12]);
                break;
            case '13-17':
                $query->whereBetween('family_members.age', [13, 17]);
                break;
            case '18-45':
                $query->whereBetween('family_members.age', [18, 45]);
                break;
            case '46-plus':
                $query->where('family_members.age', '>', 45);
                break;
        }
    }

    protected function formatData($results, $filters, $visualizationType)
    {
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
        return [
            'type' => 'table',
            'headers' => [
                'Desa',
                'No. Bangunan',
                'No. KK',
                'Nama',
                'Jenis Kelamin',
                'Usia',
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
                'Air Bersih',
                'Sumber Air Terlindungi',  // Header baru
                'Jamban',
                'Jamban Saniter'  // Header baru
            ],
            'rows' => $results->map(function ($row) {
                return [
                    $row->village_name,
                    'B-' . str_pad($row->building_number, 3, '0', STR_PAD_LEFT),
                    'KK-' . str_pad($row->family_number, 3, '0', STR_PAD_LEFT),
                    $row->name,
                    $row->gender,
                    $row->age,
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
                    $this->formatBoolean($row->use_water),
                    $this->formatBoolean($row->is_water_protected),
                    $this->formatBoolean($row->use_toilet),
                    $this->formatBoolean($row->is_toilet_sanitary)
                ];
            })->toArray()
        ];
    }

    protected function formatBoolean($value)
    {
        if ($value === null) return '-';
        return $value ? 'Ya' : 'Tidak';
    }

    protected function formatBarChartData($results, $filters)
    {
        // Tentukan field untuk analisis
        $analysisField = $this->determineAnalysisField($filters);

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
        $priorityFields = ['gender', 'education', 'age', 'has_tuberculosis', 'has_hypertension'];

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
            'is_toilet_sanitary' => 'Status Jamban Saniter'
        ];

        return $labels[$field] ?? $field;
    }
}
