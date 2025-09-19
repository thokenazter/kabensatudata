<?php

namespace App\Services;

use App\Models\Building;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\MedicalRecord;
use App\Models\Medicine;
use App\Models\Village;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ChatbotKnowledgeService
{
    public function generateKnowledge(): string
    {
        $context = $this->buildContext();

        $sections = array_filter([
            '=== SNAPSHOT INFORMASI SISTEM ===',
            'Data diringkas pada ' . now()->format('Y-m-d H:i:s') . ' WIB.',
            $this->buildOverviewSection($context),
            $this->buildHealthSection($context),
            $this->buildMaternalChildSection($context),
            $this->buildSanitationSection($context),
            $this->buildJknSection($context),
            $this->buildVillageSection($context),
            $this->buildMedicalRecordSection($context),
            $this->buildMedicineSection($context),
        ]);

        return implode("\n\n", $sections);
    }

    protected function buildContext(): array
    {
        $totals = [
            'families' => Family::count(),
            'members' => FamilyMember::count(),
            'buildings' => Building::count(),
            'villages' => Village::count(),
        ];

        $genderCounts = FamilyMember::select('gender', DB::raw('count(*) as total'))
            ->groupBy('gender')
            ->get()
            ->mapWithKeys(function ($row) {
                return [$row->gender ?? 'Tidak diketahui' => (int) $row->total];
            })
            ->toArray();

        $ageBuckets = $this->calculateAgeBuckets();
        $educationStats = $this->collectEducationStats();
        $recentMembers = $this->collectRecentMembers();
        $healthCases = $this->collectHealthCases();
        $maternalStats = $this->collectMaternalStats();
        $childStats = $this->collectChildStats();
        $sanitationStats = $this->collectSanitationStats($totals['families']);
        $jknStats = $this->collectJknStats($totals['members']);
        $villageMetrics = $this->collectVillageMetrics();
        $medicalRecords = $this->collectMedicalRecords();
        $medicineStats = $this->collectMedicineStats();

        return [
            'totals' => $totals,
            'genderCounts' => $genderCounts,
            'ageBuckets' => $ageBuckets,
            'educationStats' => $educationStats,
            'recentMembers' => $recentMembers,
            'healthCases' => $healthCases,
            'maternalStats' => $maternalStats,
            'childStats' => $childStats,
            'sanitationStats' => $sanitationStats,
            'jknStats' => $jknStats,
            'villageMetrics' => $villageMetrics,
            'medicalRecords' => $medicalRecords,
            'medicineStats' => $medicineStats,
        ];
    }

    protected function buildOverviewSection(array $context): string
    {
        $totals = $context['totals'];
        $genderCounts = $context['genderCounts'];
        $ageBuckets = $context['ageBuckets'];
        $educationStats = $context['educationStats'];
        $recentMembers = $context['recentMembers'];

        $lines = [
            '=== DASHBOARD /dashboard ===',
            'Total keluarga: ' . number_format($totals['families']) . ', anggota keluarga: ' . number_format($totals['members']) . ', bangunan: ' . number_format($totals['buildings']) . ', desa: ' . number_format($totals['villages']) . '.',
        ];

        if (!empty($genderCounts)) {
            $totalMembers = max(1, array_sum($genderCounts));
            $genderLine = collect($genderCounts)
                ->map(function ($count, $label) use ($totalMembers) {
                    $percentage = $this->formatPercentage($count, $totalMembers);
                    return $label . ' ' . number_format($count) . ' (' . $percentage . ')';
                })
                ->values()
                ->implode(', ');
            $lines[] = 'Distribusi jenis kelamin: ' . $genderLine . '.';
        }

        if (!empty($ageBuckets)) {
            $totalKnown = array_sum($ageBuckets['buckets']);
            $ageLine = collect($ageBuckets['buckets'])
                ->map(function ($count, $label) use ($totalKnown) {
                    return $label . ' ' . number_format($count) . ' (' . $this->formatPercentage($count, max(1, $totalKnown)) . ')';
                })
                ->values()
                ->implode(', ');
            $unknownText = $ageBuckets['unknown'] > 0
                ? ' Usia tidak diketahui: ' . number_format($ageBuckets['unknown']) . ' anggota.'
                : '';
            $lines[] = 'Kelompok usia: ' . $ageLine . '.' . $unknownText;
        }

        if (!empty($educationStats)) {
            $educationLine = collect($educationStats)
                ->take(5)
                ->map(function ($item) {
                    return $item['education'] . ' (' . number_format($item['total']) . ')';
                })
                ->implode(', ');
            $lines[] = 'Pendidikan terbanyak: ' . $educationLine . '.';
        }

        if (!empty($recentMembers)) {
            $recentLines = collect($recentMembers)
                ->map(function ($member) {
                    $parts = [$member['name'] ?? 'Tanpa nama'];
                    if ($member['age'] !== null) {
                        $parts[] = $member['age'] . ' th';
                    }
                    if (!empty($member['gender'])) {
                        $parts[] = $member['gender'];
                    }
                    if (!empty($member['village'])) {
                        $parts[] = 'Desa ' . $member['village'];
                    }
                    if (!empty($member['created_at'])) {
                        $parts[] = 'ditambahkan ' . $member['created_at'];
                    }
                    return '- ' . implode(', ', $parts);
                })
                ->implode("\n");
            $lines[] = "Anggota keluarga terbaru:\n" . $recentLines;
        }

        return implode("\n", $lines);
    }

    protected function buildHealthSection(array $context): string
    {
        $cases = $context['healthCases'];

        $lines = ['=== STATISTIK KESEHATAN ==='];

        if (empty($cases)) {
            $lines[] = 'Belum ada data kasus kesehatan yang tercatat.';
            return implode("\n", $lines);
        }

        $lines[] = 'Rekap kasus anggota keluarga:';
        foreach ($cases as $label => $count) {
            $lines[] = '- ' . $label . ': ' . number_format($count);
        }

        return implode("\n", $lines);
    }

    protected function buildMaternalChildSection(array $context): string
    {
        $maternal = $context['maternalStats'];
        $child = $context['childStats'];

        $lines = ['=== KESEHATAN IBU & ANAK ==='];

        if (array_sum($maternal) === 0 && array_sum($child) === 0) {
            $lines[] = 'Belum ada indikator kesehatan ibu dan anak yang tercatat.';
            return implode("\n", $lines);
        }

        if (array_sum($maternal) > 0) {
            $lines[] = 'Indikator kesehatan ibu:';
            $lines[] = '- Peserta KB aktif: ' . number_format($maternal['kb_count']);
            $lines[] = '- Tidak menggunakan KB: ' . number_format($maternal['no_kb_count']);
            $lines[] = '- Ibu hamil tercatat: ' . number_format($maternal['pregnant_count']);
            $lines[] = '- Persalinan di fasilitas kesehatan: ' . number_format($maternal['health_facility_birth_count']);
        }

        if (array_sum($child) > 0) {
            $lines[] = 'Indikator kesehatan anak:';
            $lines[] = '- ASI eksklusif: ' . number_format($child['exclusive_breastfeeding_count']);
            $lines[] = '- Imunisasi lengkap: ' . number_format($child['complete_immunization_count']);
            $lines[] = '- Pemantauan tumbuh kembang: ' . number_format($child['growth_monitoring_count']);
        }

        return implode("\n", $lines);
    }

    protected function buildSanitationSection(array $context): string
    {
        $stats = $context['sanitationStats'];
        $totals = $context['totals'];
        $familyTotal = max(1, $totals['families']);

        $lines = ['=== SANITASI RUMAH TANGGA ==='];

        $lines[] = 'Keluarga dengan akses air bersih: ' . number_format($stats['clean_water_count']) . ' (' . $this->formatPercentage($stats['clean_water_count'], $familyTotal) . ').';
        $lines[] = 'Keluarga dengan sumber air terlindungi: ' . number_format($stats['protected_water_count']) . ' (' . $this->formatPercentage($stats['protected_water_count'], max(1, $stats['clean_water_count'])) . ' dari keluarga yang memiliki air bersih).';
        $lines[] = 'Keluarga dengan jamban keluarga: ' . number_format($stats['toilet_count']) . ' (' . $this->formatPercentage($stats['toilet_count'], $familyTotal) . ').';
        $lines[] = 'Jamban sehat: ' . number_format($stats['sanitary_toilet_count']) . ' (' . $this->formatPercentage($stats['sanitary_toilet_count'], max(1, $stats['toilet_count'])) . ' dari keluarga yang memiliki jamban).';

        return implode("\n", $lines);
    }

    protected function buildJknSection(array $context): string
    {
        $stats = $context['jknStats'];
        $lines = ['=== KEPESERTAAN JKN ==='];

        if ($stats['members'] === 0) {
            $lines[] = 'Belum ada anggota keluarga yang tercatat untuk perhitungan JKN.';
            return implode("\n", $lines);
        }

        $lines[] = 'Anggota dengan JKN: ' . number_format($stats['jkn_count']) . ' dari ' . number_format($stats['members']) . ' anggota (' . $this->formatPercentage($stats['jkn_count'], $stats['members']) . ').';

        $topVillages = $context['villageMetrics']->filter(fn ($item) => $item['members'] > 0)
            ->sortByDesc('jkn_percentage')
            ->take(5);

        if ($topVillages->isNotEmpty()) {
            $lines[] = 'Cakupan JKN tertinggi:';
            foreach ($topVillages as $village) {
                $lines[] = '- Desa ' . $village['name'] . ': ' . $this->formatPercentage($village['jkn_members'], $village['members']) . ' (' . number_format($village['jkn_members']) . ' dari ' . number_format($village['members']) . ' anggota).';
            }
        }

        return implode("\n", $lines);
    }

    protected function buildVillageSection(array $context): string
    {
        /** @var Collection $metrics */
        $metrics = $context['villageMetrics'];

        if ($metrics->isEmpty()) {
            return '=== RINGKASAN DESA ===\nBelum ada data desa yang tersedia.';
        }

        $lines = ['=== RINGKASAN DESA ==='];
        $topByMembers = $metrics->sortByDesc('members')->take(5);

        foreach ($topByMembers as $village) {
            $lines[] = sprintf(
                '- Desa %s: %s bangunan, %s keluarga, %s anggota. Air bersih: %s, jamban sehat: %s.',
                $village['name'],
                number_format($village['buildings']),
                number_format($village['families']),
                number_format($village['members']),
                $this->formatPercentage($village['clean_water_families'], max(1, $village['families'])),
                $this->formatPercentage($village['sanitary_toilet_families'], max(1, $village['families']))
            );
        }

        return implode("\n", $lines);
    }

    protected function buildMedicalRecordSection(array $context): string
    {
        $records = $context['medicalRecords']['recent'];
        $statusCounts = $context['medicalRecords']['statusCounts'];
        $topDiagnoses = $context['medicalRecords']['topDiagnoses'];

        $lines = ['=== REKAM MEDIS /medical-records ==='];

        if (empty($records)) {
            $lines[] = 'Belum ada rekam medis yang tercatat.';
            return implode("\n", $lines);
        }

        if (!empty($statusCounts)) {
            $statusLine = collect($statusCounts)
                ->map(function ($count, $status) {
                    return $status . ': ' . number_format($count);
                })
                ->implode(', ');
            $lines[] = 'Status antrean: ' . $statusLine . '.';
        }

        if (!empty($topDiagnoses)) {
            $diagnosisLine = collect($topDiagnoses)
                ->map(function ($item) {
                    return $item['diagnosis'] . ' (' . number_format($item['total']) . ')';
                })
                ->implode(', ');
            $lines[] = 'Diagnosis terbanyak: ' . $diagnosisLine . '.';
        }

        $lines[] = '5 rekam medis terbaru:';
        foreach ($records as $record) {
            $parts = [
                $record['visit_date'] ?? 'Tanggal tidak ada',
                $record['patient_name'] ?? 'Pasien tanpa nama',
            ];
            if (!empty($record['diagnosis_name'])) {
                $parts[] = 'Diagnosis ' . $record['diagnosis_name'];
            }
            if (!empty($record['workflow_status'])) {
                $parts[] = 'Status ' . $record['workflow_status'];
            }
            $lines[] = '- ' . implode(', ', $parts);
        }

        return implode("\n", $lines);
    }

    protected function buildMedicineSection(array $context): string
    {
        $stats = $context['medicineStats'];
        $lines = ['=== OBAT /medicines ==='];

        if ($stats['total'] === 0) {
            $lines[] = 'Belum ada data obat yang tercatat.';
            return implode("\n", $lines);
        }

        $lines[] = 'Total jenis obat aktif: ' . number_format($stats['total']) . '.';
        $lines[] = 'Obat stok menipis: ' . number_format($stats['low_stock_count']) . ', stok habis: ' . number_format($stats['out_of_stock_count']) . '.';

        if (!empty($stats['low_stock_list'])) {
            $lines[] = 'Daftar stok menipis (maksimal 5 item):';
            foreach ($stats['low_stock_list'] as $medicine) {
                $lines[] = sprintf(
                    '- %s: stok %s %s, batas minimum %s.',
                    $medicine['name'],
                    number_format($medicine['stock_quantity']),
                    $medicine['unit'] ?? 'unit',
                    number_format($medicine['minimum_stock'])
                );
            }
        }

        return implode("\n", $lines);
    }

    protected function calculateAgeBuckets(): array
    {
        $buckets = [
            '0-5' => 0,
            '6-12' => 0,
            '13-17' => 0,
            '18-30' => 0,
            '31-50' => 0,
            '>50' => 0,
        ];

        $unknown = 0;

        FamilyMember::select('id', 'birth_date')->orderBy('id')->chunkById(500, function ($members) use (&$buckets, &$unknown) {
            foreach ($members as $member) {
                if (!$member->birth_date) {
                    $unknown++;
                    continue;
                }

                $birthDate = $member->birth_date instanceof Carbon
                    ? $member->birth_date
                    : Carbon::parse($member->birth_date);

                $age = $birthDate->age;

                if ($age <= 5) {
                    $buckets['0-5']++;
                } elseif ($age <= 12) {
                    $buckets['6-12']++;
                } elseif ($age <= 17) {
                    $buckets['13-17']++;
                } elseif ($age <= 30) {
                    $buckets['18-30']++;
                } elseif ($age <= 50) {
                    $buckets['31-50']++;
                } else {
                    $buckets['>50']++;
                }
            }
        });

        return [
            'buckets' => $buckets,
            'unknown' => $unknown,
        ];
    }

    protected function collectEducationStats(): array
    {
        return FamilyMember::select('education', DB::raw('count(*) as total'))
            ->whereNotNull('education')
            ->groupBy('education')
            ->orderByDesc('total')
            ->get()
            ->map(function ($row) {
                return [
                    'education' => $row->education,
                    'total' => (int) $row->total,
                ];
            })
            ->toArray();
    }

    protected function collectRecentMembers(): array
    {
        return FamilyMember::with(['family.building.village'])
            ->latest('created_at')
            ->take(5)
            ->get()
            ->map(function ($member) {
                return [
                    'name' => $member->name,
                    'age' => $member->birth_date ? ($member->birth_date instanceof Carbon ? $member->birth_date->age : Carbon::parse($member->birth_date)->age) : null,
                    'gender' => $member->gender,
                    'village' => optional(optional($member->family)->building)->village->name ?? null,
                    'created_at' => optional($member->created_at)->format('Y-m-d H:i'),
                ];
            })
            ->toArray();
    }

    protected function collectHealthCases(): array
    {
        return [
            'Tuberkulosis' => FamilyMember::where('has_tuberculosis', true)->count(),
            'Hipertensi' => FamilyMember::where('has_hypertension', true)->count(),
            'Batuk kronis' => FamilyMember::where('has_chronic_cough', true)->count(),
            'Gangguan jiwa dalam keluarga' => Family::where('has_mental_illness', true)->count(),
            'Anggota keluarga dipasung' => Family::where('has_restrained_member', true)->count(),
        ];
    }

    protected function collectMaternalStats(): array
    {
        return [
            'kb_count' => FamilyMember::where('gender', 'Perempuan')->where('uses_contraception', true)->count(),
            'no_kb_count' => FamilyMember::where('gender', 'Perempuan')->where('uses_contraception', false)->count(),
            'pregnant_count' => FamilyMember::where('gender', 'Perempuan')->where('is_pregnant', true)->count(),
            'health_facility_birth_count' => FamilyMember::where('gave_birth_in_health_facility', true)->count(),
        ];
    }

    protected function collectChildStats(): array
    {
        return [
            'exclusive_breastfeeding_count' => FamilyMember::where('exclusive_breastfeeding', true)->count(),
            'complete_immunization_count' => FamilyMember::where('complete_immunization', true)->count(),
            'growth_monitoring_count' => FamilyMember::where('growth_monitoring', true)->count(),
        ];
    }

    protected function collectSanitationStats(int $totalFamilies): array
    {
        return [
            'clean_water_count' => Family::where('has_clean_water', true)->count(),
            'protected_water_count' => Family::where('is_water_protected', true)->count(),
            'toilet_count' => Family::where('has_toilet', true)->count(),
            'sanitary_toilet_count' => Family::where('is_toilet_sanitary', true)->count(),
            'total_families' => $totalFamilies,
        ];
    }

    protected function collectJknStats(int $totalMembers): array
    {
        $jknCount = FamilyMember::where('has_jkn', true)->count();

        return [
            'members' => $totalMembers,
            'jkn_count' => $jknCount,
        ];
    }

    protected function collectVillageMetrics(): Collection
    {
        $familyRows = DB::table('villages')
            ->leftJoin('buildings', 'buildings.village_id', '=', 'villages.id')
            ->leftJoin('families', 'families.building_id', '=', 'buildings.id')
            ->select(
                'villages.id',
                'villages.name',
                DB::raw('COUNT(DISTINCT buildings.id) as building_count'),
                DB::raw('COUNT(DISTINCT families.id) as family_count'),
                DB::raw('SUM(CASE WHEN families.has_clean_water = 1 THEN 1 ELSE 0 END) as clean_water_families'),
                DB::raw('SUM(CASE WHEN families.is_toilet_sanitary = 1 THEN 1 ELSE 0 END) as sanitary_toilet_families')
            )
            ->groupBy('villages.id', 'villages.name')
            ->get();

        $memberRows = DB::table('family_members')
            ->join('families', 'family_members.family_id', '=', 'families.id')
            ->join('buildings', 'families.building_id', '=', 'buildings.id')
            ->select(
                'buildings.village_id as village_id',
                DB::raw('COUNT(family_members.id) as member_count'),
                DB::raw('SUM(CASE WHEN family_members.has_jkn = 1 THEN 1 ELSE 0 END) as jkn_members')
            )
            ->groupBy('buildings.village_id')
            ->get()
            ->keyBy('village_id');

        return $familyRows->map(function ($row) use ($memberRows) {
            $membersRow = $memberRows->get($row->id);
            $members = $membersRow ? (int) $membersRow->member_count : 0;
            $jknMembers = $membersRow ? (int) $membersRow->jkn_members : 0;
            $families = (int) ($row->family_count ?? 0);

            return [
                'id' => (int) $row->id,
                'name' => $row->name,
                'buildings' => (int) ($row->building_count ?? 0),
                'families' => $families,
                'members' => $members,
                'jkn_members' => $jknMembers,
                'jkn_percentage' => $members > 0 ? $jknMembers / $members : 0,
                'clean_water_families' => (int) ($row->clean_water_families ?? 0),
                'sanitary_toilet_families' => (int) ($row->sanitary_toilet_families ?? 0),
            ];
        });
    }

    protected function collectMedicalRecords(): array
    {
        $recent = MedicalRecord::query()
            ->orderByDesc('visit_date')
            ->orderByDesc('created_at')
            ->take(5)
            ->get([
                'visit_date',
                'patient_name',
                'diagnosis_name',
                'workflow_status',
            ])
            ->map(function ($record) {
                return [
                    'visit_date' => optional($record->visit_date)->format('Y-m-d'),
                    'patient_name' => $record->patient_name,
                    'diagnosis_name' => $record->diagnosis_name,
                    'workflow_status' => $record->workflow_status,
                ];
            })
            ->toArray();

        $statusCounts = MedicalRecord::select('workflow_status', DB::raw('count(*) as total'))
            ->groupBy('workflow_status')
            ->get()
            ->mapWithKeys(function ($row) {
                return [$row->workflow_status ?? 'tidak diketahui' => (int) $row->total];
            })
            ->toArray();

        $topDiagnoses = MedicalRecord::select('diagnosis_name', DB::raw('count(*) as total'))
            ->whereNotNull('diagnosis_name')
            ->groupBy('diagnosis_name')
            ->orderByDesc('total')
            ->take(5)
            ->get()
            ->map(function ($row) {
                return [
                    'diagnosis' => $row->diagnosis_name,
                    'total' => (int) $row->total,
                ];
            })
            ->toArray();

        return [
            'recent' => $recent,
            'statusCounts' => $statusCounts,
            'topDiagnoses' => $topDiagnoses,
        ];
    }

    protected function collectMedicineStats(): array
    {
        $total = Medicine::count();
        $lowStock = Medicine::whereColumn('stock_quantity', '<=', 'minimum_stock')
            ->where('minimum_stock', '>', 0)
            ->get(['name', 'stock_quantity', 'minimum_stock', 'unit'])
            ->map(function ($medicine) {
                return [
                    'name' => $medicine->name,
                    'stock_quantity' => (int) $medicine->stock_quantity,
                    'minimum_stock' => (int) $medicine->minimum_stock,
                    'unit' => $medicine->unit,
                ];
            });

        $outOfStockCount = Medicine::where('stock_quantity', '<=', 0)->count();

        return [
            'total' => $total,
            'low_stock_count' => $lowStock->count(),
            'out_of_stock_count' => $outOfStockCount,
            'low_stock_list' => $lowStock->take(5)->toArray(),
        ];
    }

    protected function formatPercentage(int $part, int $total): string
    {
        if ($total <= 0) {
            return '0%';
        }

        return rtrim(rtrim(number_format(($part / $total) * 100, 1), '0'), '.') . '%';
    }
}
