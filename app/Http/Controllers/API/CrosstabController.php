<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\FamilyHealthIndex;
use App\Models\Village;
use App\Models\Building;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CrosstabController extends Controller
{
    public function getVariables()
    {
        $variables = [
            'demographic' => [
                'label' => 'Demografi',
                'variables' => [
                    'members.gender' => [
                        'label' => 'Jenis Kelamin',
                        'model' => 'FamilyMember',
                        'type' => 'select',
                        'options' => ['Laki-laki', 'Perempuan']
                    ],
                    'members.age' => [
                        'label' => 'Usia (Angka)',
                        'model' => 'FamilyMember',
                        'type' => 'number'
                    ],
                    'members.age_group' => [
                        'label' => 'Kelompok Usia',
                        'model' => 'FamilyMember',
                        'type' => 'function',
                        'options' => ['0-5', '6-11', '12-18', '19-35', '36-50', '51-65', '65+']
                    ],
                    'members.education' => [
                        'label' => 'Pendidikan',
                        'model' => 'FamilyMember',
                        'type' => 'select',
                        'options' => ['Tidak Sekolah', 'SD', 'SMP', 'SMA', 'D3', 'S1', 'S2', 'S3']
                    ],
                    'members.occupation' => [
                        'label' => 'Pekerjaan',
                        'model' => 'FamilyMember',
                        'type' => 'select',
                        'options' => ['Tidak Bekerja', 'Petani', 'Nelayan', 'Buruh', 'Wiraswasta', 'PNS', 'TNI/Polri', 'Pensiunan', 'Lainnya']
                    ],
                    'members.marital_status' => [
                        'label' => 'Status Perkawinan',
                        'model' => 'FamilyMember',
                        'type' => 'select',
                        'options' => ['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati']
                    ],
                    'members.religion' => [
                        'label' => 'Agama',
                        'model' => 'FamilyMember',
                        'type' => 'select',
                        'options' => ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Lainnya']
                    ],
                    'members.relationship' => [
                        'label' => 'Hubungan dengan Kepala Keluarga',
                        'model' => 'FamilyMember',
                        'type' => 'select',
                        'options' => ['Kepala Keluarga', 'Istri', 'Suami', 'Anak', 'Menantu', 'Cucu', 'Orangtua', 'Lainnya']
                    ],
                ],
            ],
            'location' => [
                'label' => 'Lokasi',
                'variables' => [
                    'village.name' => [
                        'label' => 'Desa/Kelurahan',
                        'model' => 'Family',
                        'type' => 'select',
                        'source' => 'db'
                    ],
                    'building.building_number' => [
                        'label' => 'Nomor Bangunan',
                        'model' => 'Family',
                        'type' => 'text'
                    ],
                ],
            ],
            'health_indicators' => [
                'label' => 'Indikator Kesehatan',
                'variables' => [
                    'members.has_jkn' => [
                        'label' => 'Kepesertaan JKN',
                        'model' => 'FamilyMember',
                        'type' => 'boolean'
                    ],
                    'members.is_smoker' => [
                        'label' => 'Merokok',
                        'model' => 'FamilyMember',
                        'type' => 'boolean'
                    ],
                    'members.use_water' => [
                        'label' => 'Menggunakan Air Bersih',
                        'model' => 'FamilyMember',
                        'type' => 'boolean'
                    ],
                    'members.use_toilet' => [
                        'label' => 'Menggunakan Jamban',
                        'model' => 'FamilyMember',
                        'type' => 'boolean'
                    ],
                    'members.has_tuberculosis' => [
                        'label' => 'Tuberculosis',
                        'model' => 'FamilyMember',
                        'type' => 'boolean'
                    ],
                    'members.takes_tb_medication_regularly' => [
                        'label' => 'Minum Obat TB Teratur',
                        'model' => 'FamilyMember',
                        'type' => 'boolean'
                    ],
                    'members.has_hypertension' => [
                        'label' => 'Hipertensi',
                        'model' => 'FamilyMember',
                        'type' => 'boolean'
                    ],
                    'members.takes_hypertension_medication_regularly' => [
                        'label' => 'Minum Obat Hipertensi Teratur',
                        'model' => 'FamilyMember',
                        'type' => 'boolean'
                    ],
                    'members.uses_contraception' => [
                        'label' => 'Menggunakan KB',
                        'model' => 'FamilyMember',
                        'type' => 'boolean'
                    ],
                    'members.gave_birth_in_health_facility' => [
                        'label' => 'Melahirkan di Faskes',
                        'model' => 'FamilyMember',
                        'type' => 'boolean'
                    ],
                    'members.exclusive_breastfeeding' => [
                        'label' => 'ASI Eksklusif',
                        'model' => 'FamilyMember',
                        'type' => 'boolean'
                    ],
                    'members.complete_immunization' => [
                        'label' => 'Imunisasi Lengkap',
                        'model' => 'FamilyMember',
                        'type' => 'boolean'
                    ],
                    'members.growth_monitoring' => [
                        'label' => 'Pemantauan Pertumbuhan',
                        'model' => 'FamilyMember',
                        'type' => 'boolean'
                    ],
                ],
            ],
            'family_indicators' => [
                'label' => 'Indikator Keluarga',
                'variables' => [
                    'has_clean_water' => [
                        'label' => 'Memiliki Air Bersih',
                        'model' => 'Family',
                        'type' => 'boolean'
                    ],
                    'is_water_protected' => [
                        'label' => 'Air Terlindungi',
                        'model' => 'Family',
                        'type' => 'boolean'
                    ],
                    'has_toilet' => [
                        'label' => 'Memiliki Jamban',
                        'model' => 'Family',
                        'type' => 'boolean'
                    ],
                    'is_toilet_sanitary' => [
                        'label' => 'Jamban Sehat',
                        'model' => 'Family',
                        'type' => 'boolean'
                    ],
                    'has_mental_illness' => [
                        'label' => 'Gangguan Jiwa',
                        'model' => 'Family',
                        'type' => 'boolean'
                    ],
                    'takes_medication_regularly' => [
                        'label' => 'Minum Obat Teratur',
                        'model' => 'Family',
                        'type' => 'boolean'
                    ],
                    'has_restrained_member' => [
                        'label' => 'Dipasung',
                        'model' => 'Family',
                        'type' => 'boolean'
                    ],
                ],
            ],
            'health_index' => [
                'label' => 'Indeks Kesehatan',
                'variables' => [
                    'healthIndex.health_status' => [
                        'label' => 'Status Kesehatan',
                        'model' => 'Family',
                        'type' => 'select',
                        'options' => ['Keluarga Sehat', 'Keluarga Pra-Sehat', 'Keluarga Tidak Sehat']
                    ],
                    'healthIndex.iks_value' => [
                        'label' => 'Nilai IKS',
                        'model' => 'Family',
                        'type' => 'number'
                    ],
                    'healthIndex.kb_status' => [
                        'label' => 'Status KB',
                        'model' => 'Family',
                        'type' => 'boolean'
                    ],
                    'healthIndex.birth_facility_status' => [
                        'label' => 'Status Fasilitas Persalinan',
                        'model' => 'Family',
                        'type' => 'boolean'
                    ],
                    'healthIndex.immunization_status' => [
                        'label' => 'Status Imunisasi',
                        'model' => 'Family',
                        'type' => 'boolean'
                    ],
                    'healthIndex.exclusive_breastfeeding_status' => [
                        'label' => 'Status ASI Eksklusif',
                        'model' => 'Family',
                        'type' => 'boolean'
                    ],
                    'healthIndex.growth_monitoring_status' => [
                        'label' => 'Status Pemantauan Pertumbuhan',
                        'model' => 'Family',
                        'type' => 'boolean'
                    ],
                    'healthIndex.tb_treatment_status' => [
                        'label' => 'Status Pengobatan TB',
                        'model' => 'Family',
                        'type' => 'boolean'
                    ],
                    'healthIndex.hypertension_treatment_status' => [
                        'label' => 'Status Pengobatan Hipertensi',
                        'model' => 'Family',
                        'type' => 'boolean'
                    ],
                    'healthIndex.mental_treatment_status' => [
                        'label' => 'Status Pengobatan Gangguan Jiwa',
                        'model' => 'Family',
                        'type' => 'boolean'
                    ],
                    'healthIndex.no_smoking_status' => [
                        'label' => 'Status Tidak Merokok',
                        'model' => 'Family',
                        'type' => 'boolean'
                    ],
                    'healthIndex.jkn_membership_status' => [
                        'label' => 'Status Keanggotaan JKN',
                        'model' => 'Family',
                        'type' => 'boolean'
                    ],
                    'healthIndex.clean_water_status' => [
                        'label' => 'Status Air Bersih',
                        'model' => 'Family',
                        'type' => 'boolean'
                    ],
                    'healthIndex.sanitary_toilet_status' => [
                        'label' => 'Status Jamban Sehat',
                        'model' => 'Family',
                        'type' => 'boolean'
                    ],
                ],
            ],
        ];

        // Ambil daftar desa untuk digunakan dalam filter
        $villages = Village::select('id', 'name')->get();

        return response()->json([
            'variables' => $variables,
            'villages' => $villages
        ]);
    }

    public function getData(Request $request)
    {
        $rowVariable = $request->input('row_variable');
        $columnVariable = $request->input('column_variable');
        $aggregation = $request->input('aggregation', 'count');
        $valueField = $request->input('value_field');
        $filters = json_decode($request->input('filters', '[]'), true);

        // Parse untuk menentukan model dasar
        $rowModel = $this->getModelFromVariable($rowVariable);
        $columnModel = $this->getModelFromVariable($columnVariable);

        // Tentukan tabel base query berdasarkan variabel yang dipilih
        $baseQuery = $this->getBaseQuery($rowModel, $columnModel, $rowVariable, $columnVariable);

        // Terapkan filter jika ada
        if (!empty($filters)) {
            $baseQuery = $this->applyFilters($baseQuery, $filters);
        }

        // Dapatkan data unik untuk baris dan kolom
        $rowValues = $this->getUniqueValues($baseQuery, $rowVariable);
        $columnValues = $this->getUniqueValues($baseQuery, $columnVariable);

        // Hitung data crosstab
        $data = [];
        $rowTotals = [];
        $columnTotals = [];
        $grandTotal = 0;

        foreach ($rowValues as $rowValue) {
            if (!isset($data[$rowValue])) {
                $data[$rowValue] = [];
                $rowTotals[$rowValue] = 0;
            }

            foreach ($columnValues as $columnValue) {
                if (!isset($columnTotals[$columnValue])) {
                    $columnTotals[$columnValue] = 0;
                }

                $cellQuery = clone $baseQuery;

                // Filter untuk nilai baris dan kolom saat ini
                $cellQuery = $this->applyVariableFilter($cellQuery, $rowVariable, $rowValue);
                $cellQuery = $this->applyVariableFilter($cellQuery, $columnVariable, $columnValue);

                // Hitung nilai berdasarkan fungsi agregasi
                $cellValue = $this->calculateAggregation($cellQuery, $aggregation, $valueField);

                $data[$rowValue][$columnValue] = $cellValue;
                $rowTotals[$rowValue] += $cellValue;
                $columnTotals[$columnValue] += $cellValue;
                $grandTotal += $cellValue;
            }
        }

        // Buat label yang lebih baik untuk nilai-nilai
        $rowLabels = $this->makeDisplayValues($rowVariable, $rowValues);
        $columnLabels = $this->makeDisplayValues($columnVariable, $columnValues);

        return response()->json([
            'rows' => $rowValues,
            'columns' => $columnValues,
            'data' => $data,
            'row_totals' => $rowTotals,
            'column_totals' => $columnTotals,
            'grand_total' => $grandTotal,
            'row_labels' => $rowLabels,
            'column_labels' => $columnLabels,
            'row_variable' => $rowVariable,
            'column_variable' => $columnVariable,
            'aggregation' => $aggregation,
            'value_field' => $valueField
        ]);
    }

    protected function getModelFromVariable($variable)
    {
        if (strpos($variable, 'members.') === 0) {
            return 'FamilyMember';
        } elseif (strpos($variable, 'healthIndex.') === 0) {
            return 'Family';
        } elseif (strpos($variable, 'village.') === 0 || strpos($variable, 'building.') === 0) {
            return 'Family';
        } else {
            return 'Family';
        }
    }

    protected function getBaseQuery($rowModel, $columnModel, $rowVariable, $columnVariable)
    {
        // Jika salah satu variabel adalah dari FamilyMember, gunakan FamilyMember sebagai base query
        if ($rowModel === 'FamilyMember' || $columnModel === 'FamilyMember') {
            $query = FamilyMember::query()
                ->with(['family', 'family.village', 'family.building', 'family.healthIndex']);
        } else {
            $query = Family::query()
                ->with(['village', 'building', 'healthIndex']);
        }

        return $query;
    }

    protected function getUniqueValues($query, $variable)
    {
        // Ekstrak nilai unik untuk variabel
        $clonedQuery = clone $query;

        // Jika variabel adalah function, kita perlu menghitung nilai dengan cara khusus
        if ($variable === 'members.age_group') {
            $results = $clonedQuery->get()->map(function ($item) {
                $age = $item->age ?? 0;
                if ($age < 6) return '0-5';
                if ($age < 12) return '6-11';
                if ($age < 19) return '12-18';
                if ($age < 36) return '19-35';
                if ($age < 51) return '36-50';
                if ($age < 66) return '51-65';
                return '65+';
            })->unique()->values()->toArray();

            // Sort by age group order
            $ageGroupOrder = ['0-5', '6-11', '12-18', '19-35', '36-50', '51-65', '65+'];
            usort($results, function ($a, $b) use ($ageGroupOrder) {
                return array_search($a, $ageGroupOrder) - array_search($b, $ageGroupOrder);
            });

            return $results;
        } else {
            // Use select approach for general variables
            $parts = explode('.', $variable);

            if (count($parts) === 1) {
                // Direct field on base model
                $values = $clonedQuery->select($variable)->distinct()->pluck($variable);
            } elseif (count($parts) === 2) {
                // Relationship field
                [$relation, $field] = $parts;

                if ($relation === 'members') {
                    if ($query instanceof \Illuminate\Database\Eloquent\Builder && $query->getModel() instanceof FamilyMember) {
                        $values = $clonedQuery->select($field)->distinct()->pluck($field);
                    } else {
                        $familyIds = $clonedQuery->pluck('id')->toArray();
                        $values = FamilyMember::whereIn('family_id', $familyIds)
                            ->select($field)
                            ->distinct()
                            ->pluck($field);
                    }
                } elseif ($relation === 'healthIndex') {
                    $values = $clonedQuery->whereHas('healthIndex')->get()
                        ->pluck('healthIndex.' . $field)
                        ->unique();
                } elseif ($relation === 'village') {
                    $values = $clonedQuery->whereHas('village')->get()
                        ->pluck('village.' . $field)
                        ->unique();
                } elseif ($relation === 'building') {
                    $values = $clonedQuery->whereHas('building')->get()
                        ->pluck('building.' . $field)
                        ->unique();
                }
            }

            // Urutkan nilai untuk boolean (false kemudian true)
            if (is_array($values->toArray()) && count($values) <= 2 && $values->filter(function ($value) {
                return is_bool($value) || $value === 0 || $value === 1 || $value === '0' || $value === '1';
            })->count() === count($values)) {
                $sorted = $values->sort();
                return $sorted->values()->toArray();
            }

            return $values->filter()->sort()->values()->toArray();
        }
    }

    protected function applyVariableFilter($query, $variable, $value)
    {
        $parts = explode('.', $variable);

        // Spesial case untuk kelompok usia
        if ($variable === 'members.age_group') {
            return $query->where(function ($q) use ($value) {
                switch ($value) {
                    case '0-5':
                        $q->whereBetween('age', [0, 5]);
                        break;
                    case '6-11':
                        $q->whereBetween('age', [6, 11]);
                        break;
                    case '12-18':
                        $q->whereBetween('age', [12, 18]);
                        break;
                    case '19-35':
                        $q->whereBetween('age', [19, 35]);
                        break;
                    case '36-50':
                        $q->whereBetween('age', [36, 50]);
                        break;
                    case '51-65':
                        $q->whereBetween('age', [51, 65]);
                        break;
                    case '65+':
                        $q->where('age', '>=', 66);
                        break;
                }
            });
        }

        if (count($parts) === 1) {
            // Direct field on base model
            return $query->where($variable, $value);
        } elseif (count($parts) === 2) {
            // Relationship field
            [$relation, $field] = $parts;

            if ($relation === 'members') {
                if ($query instanceof \Illuminate\Database\Eloquent\Builder && $query->getModel() instanceof FamilyMember) {
                    return $query->where($field, $value);
                } else {
                    return $query->whereHas('members', function ($q) use ($field, $value) {
                        $q->where($field, $value);
                    });
                }
            } elseif ($relation === 'healthIndex') {
                return $query->whereHas('healthIndex', function ($q) use ($field, $value) {
                    $q->where($field, $value);
                });
            } elseif ($relation === 'village') {
                return $query->whereHas('village', function ($q) use ($field, $value) {
                    $q->where($field, $value);
                });
            } elseif ($relation === 'building') {
                return $query->whereHas('building', function ($q) use ($field, $value) {
                    $q->where($field, $value);
                });
            }
        }

        return $query;
    }

    protected function applyFilters($query, $filters)
    {
        foreach ($filters as $filter) {
            $field = $filter['field'] ?? null;
            $operator = $filter['operator'] ?? '=';
            $value = $filter['value'] ?? null;

            if (empty($field) || $value === null) {
                continue;
            }

            $dbOperator = $this->mapOperator($operator);

            // Apply filter berdasarkan jenis field
            $parts = explode('.', $field);

            if (count($parts) === 1) {
                // Direct field pada model utama
                $query->where($field, $dbOperator, $value);
            } elseif (count($parts) === 2) {
                // Relationship field
                [$relation, $relField] = $parts;

                if ($relation === 'members') {
                    if ($query instanceof \Illuminate\Database\Eloquent\Builder && $query->getModel() instanceof FamilyMember) {
                        $query->where($relField, $dbOperator, $value);
                    } else {
                        $query->whereHas('members', function ($q) use ($relField, $dbOperator, $value) {
                            $q->where($relField, $dbOperator, $value);
                        });
                    }
                } elseif ($relation === 'healthIndex') {
                    $query->whereHas('healthIndex', function ($q) use ($relField, $dbOperator, $value) {
                        $q->where($relField, $dbOperator, $value);
                    });
                } elseif ($relation === 'village') {
                    $query->whereHas('village', function ($q) use ($relField, $dbOperator, $value) {
                        $q->where($relField, $dbOperator, $value);
                    });
                } elseif ($relation === 'building') {
                    $query->whereHas('building', function ($q) use ($relField, $dbOperator, $value) {
                        $q->where($relField, $dbOperator, $value);
                    });
                }
            }
        }

        return $query;
    }

    protected function mapOperator($operator)
    {
        return match ($operator) {
            'eq' => '=',
            'neq' => '!=',
            'gt' => '>',
            'gte' => '>=',
            'lt' => '<',
            'lte' => '<=',
            'contains' => 'LIKE',
            default => $operator,
        };
    }

    protected function calculateAggregation($query, $aggregation, $valueField = null)
    {
        switch ($aggregation) {
            case 'count':
                return $query->count();

            case 'sum':
                if (!$valueField) return 0;

                $parts = explode('.', $valueField);
                if (count($parts) === 1) {
                    return $query->sum($valueField) ?? 0;
                } elseif (count($parts) === 2) {
                    [$relation, $field] = $parts;

                    if ($relation === 'members') {
                        if ($query instanceof \Illuminate\Database\Eloquent\Builder && $query->getModel() instanceof FamilyMember) {
                            return $query->sum($field) ?? 0;
                        } else {
                            $familyIds = $query->pluck('id')->toArray();
                            return FamilyMember::whereIn('family_id', $familyIds)->sum($field) ?? 0;
                        }
                    } elseif ($relation === 'healthIndex') {
                        return $query->get()->sum('healthIndex.' . $field) ?? 0;
                    }
                }
                return 0;

            case 'avg':
                if (!$valueField) return 0;

                $parts = explode('.', $valueField);
                if (count($parts) === 1) {
                    return $query->avg($valueField) ?? 0;
                } elseif (count($parts) === 2) {
                    [$relation, $field] = $parts;

                    if ($relation === 'members') {
                        if ($query instanceof \Illuminate\Database\Eloquent\Builder && $query->getModel() instanceof FamilyMember) {
                            return $query->avg($field) ?? 0;
                        } else {
                            $familyIds = $query->pluck('id')->toArray();
                            return FamilyMember::whereIn('family_id', $familyIds)->avg($field) ?? 0;
                        }
                    } elseif ($relation === 'healthIndex') {
                        return $query->get()->avg('healthIndex.' . $field) ?? 0;
                    }
                }
                return 0;

            case 'min':
                if (!$valueField) return 0;

                $parts = explode('.', $valueField);
                if (count($parts) === 1) {
                    return $query->min($valueField) ?? 0;
                } elseif (count($parts) === 2) {
                    // Similar implementation as sum and avg
                }
                return 0;

            case 'max':
                if (!$valueField) return 0;

                $parts = explode('.', $valueField);
                if (count($parts) === 1) {
                    return $query->max($valueField) ?? 0;
                } elseif (count($parts) === 2) {
                    // Similar implementation as sum and avg
                }
                return 0;

            default:
                return $query->count();
        }
    }

    protected function makeDisplayValues($variable, $values)
    {
        $labels = [];

        foreach ($values as $value) {
            if (is_bool($value) || $value === 0 || $value === 1 || $value === '0' || $value === '1') {
                $labels[$value] = $value ? 'Ya' : 'Tidak';
            } else {
                $labels[$value] = $value;
            }
        }

        return $labels;
    }
}
