<?php

namespace App\Services;

use App\Models\FamilyHealthIndex;
use App\Models\Village;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class IksReportService
{
    /**
     * Menghasilkan laporan IKS per desa
     */
    public function generateVillageReport(): Collection
    {
        // Dapatkan desa dengan menghitung keluarga yang memiliki IKS
        $villages = Village::all();

        $report = collect();

        foreach ($villages as $village) {
            // Hitung jumlah keluarga dengan IKS di desa ini
            $familiesCount = DB::table('family_health_indices')
                ->join('families', 'family_health_indices.family_id', '=', 'families.id')
                ->join('buildings', 'families.building_id', '=', 'buildings.id')
                ->where('buildings.village_id', $village->id)
                ->count();

            // Skip desa tanpa keluarga yang dihitung IKS-nya
            if ($familiesCount === 0) {
                continue;
            }

            // Hitung rata-rata IKS di desa ini
            $avgIks = DB::table('family_health_indices')
                ->join('families', 'family_health_indices.family_id', '=', 'families.id')
                ->join('buildings', 'families.building_id', '=', 'buildings.id')
                ->where('buildings.village_id', $village->id)
                ->avg('family_health_indices.iks_value');

            // Hitung jumlah keluarga berdasarkan status kesehatan
            $healthStatusCount = DB::table('family_health_indices')
                ->join('families', 'family_health_indices.family_id', '=', 'families.id')
                ->join('buildings', 'families.building_id', '=', 'buildings.id')
                ->where('buildings.village_id', $village->id)
                ->select(
                    DB::raw('COUNT(CASE WHEN family_health_indices.health_status = "Keluarga Sehat" THEN 1 END) as healthy_count'),
                    DB::raw('COUNT(CASE WHEN family_health_indices.health_status = "Keluarga Pra-Sehat" THEN 1 END) as pre_healthy_count'),
                    DB::raw('COUNT(CASE WHEN family_health_indices.health_status = "Keluarga Tidak Sehat" THEN 1 END) as unhealthy_count')
                )
                ->first();

            // Hitung persentase terpenuhi untuk setiap indikator
            $indicatorData = $this->calculateVillageIndicatorData($village);

            $report->push([
                'village' => $village,
                'avg_iks' => $avgIks * 100,
                'health_status' => $this->determineHealthStatus($avgIks),
                'total_families' => $village->families_count,
                'healthy_count' => $healthStatusCount->healthy_count ?? 0,
                'pre_healthy_count' => $healthStatusCount->pre_healthy_count ?? 0,
                'unhealthy_count' => $healthStatusCount->unhealthy_count ?? 0,
                'indicators' => $indicatorData,
            ]);
        }

        return $report->sortByDesc('avg_iks')->values();
    }

    /**
     * Menghitung data indikator untuk suatu desa
     */
    private function calculateVillageIndicatorData(Village $village): array
    {
        $indicatorFields = [
            'kb' => 'Keluarga Berencana',
            'birth_facility' => 'Persalinan di Faskes',
            'immunization' => 'Imunisasi Dasar Lengkap',
            'exclusive_breastfeeding' => 'ASI Eksklusif',
            'growth_monitoring' => 'Pemantauan Pertumbuhan',
            'tb_treatment' => 'Pengobatan TB',
            'hypertension_treatment' => 'Pengobatan Hipertensi',
            'mental_treatment' => 'Pengobatan Gangguan Jiwa',
            'no_smoking' => 'Tidak Merokok',
            'jkn_membership' => 'Kepesertaan JKN',
            'clean_water' => 'Akses Air Bersih',
            'sanitary_toilet' => 'Jamban Sehat',
        ];

        $result = [];

        foreach ($indicatorFields as $field => $label) {
            // Hitung jumlah keluarga yang relevan dengan indikator ini
            $relevantCount = DB::table('family_health_indices')
                ->join('families', 'family_health_indices.family_id', '=', 'families.id')
                ->join('buildings', 'families.building_id', '=', 'buildings.id')
                ->where('buildings.village_id', $village->id)
                ->where($field . '_relevant', true)
                ->count();

            // Hitung jumlah keluarga yang memenuhi indikator ini
            $fulfilledCount = DB::table('family_health_indices')
                ->join('families', 'family_health_indices.family_id', '=', 'families.id')
                ->join('buildings', 'families.building_id', '=', 'buildings.id')
                ->where('buildings.village_id', $village->id)
                ->where($field . '_relevant', true)
                ->where($field . '_status', true)
                ->count();

            // Hitung persentase
            $percentage = $relevantCount > 0 ? ($fulfilledCount / $relevantCount) * 100 : 0;

            $result[$field] = [
                'label' => $label,
                'relevant_count' => $relevantCount,
                'fulfilled_count' => $fulfilledCount,
                'percentage' => $percentage,
            ];
        }

        return $result;
    }

    /**
     * Menentukan status kesehatan berdasarkan nilai IKS
     */
    private function determineHealthStatus(float $iksValue): string
    {
        if ($iksValue > 0.8) {
            return 'Keluarga Sehat';
        } elseif ($iksValue >= 0.5) {
            return 'Keluarga Pra-Sehat';
        } else {
            return 'Keluarga Tidak Sehat';
        }
    }

    /**
     * Menghasilkan laporan IKS seluruh wilayah
     */
    public function generateOverallReport(): array
    {
        // Hitung total keluarga yang sudah dihitung IKS
        $totalFamilies = FamilyHealthIndex::count();

        // Hitung rata-rata IKS keseluruhan
        $avgIks = FamilyHealthIndex::avg('iks_value');

        // Hitung jumlah keluarga per status kesehatan
        $healthStatusCount = FamilyHealthIndex::select(
            DB::raw('COUNT(CASE WHEN health_status = "Keluarga Sehat" THEN 1 END) as healthy_count'),
            DB::raw('COUNT(CASE WHEN health_status = "Keluarga Pra-Sehat" THEN 1 END) as pre_healthy_count'),
            DB::raw('COUNT(CASE WHEN health_status = "Keluarga Tidak Sehat" THEN 1 END) as unhealthy_count')
        )->first();

        // Hitung persentase untuk setiap status kesehatan
        $healthyPercentage = $totalFamilies > 0 ? ($healthStatusCount->healthy_count / $totalFamilies) * 100 : 0;
        $preHealthyPercentage = $totalFamilies > 0 ? ($healthStatusCount->pre_healthy_count / $totalFamilies) * 100 : 0;
        $unhealthyPercentage = $totalFamilies > 0 ? ($healthStatusCount->unhealthy_count / $totalFamilies) * 100 : 0;

        // Hitung persentase terpenuhi untuk setiap indikator secara keseluruhan
        $indicatorData = $this->calculateOverallIndicatorData();

        return [
            'total_families' => $totalFamilies,
            'avg_iks' => $avgIks * 100,
            'health_status' => $this->determineHealthStatus($avgIks),
            'healthy_count' => $healthStatusCount->healthy_count,
            'healthy_percentage' => $healthyPercentage,
            'pre_healthy_count' => $healthStatusCount->pre_healthy_count,
            'pre_healthy_percentage' => $preHealthyPercentage,
            'unhealthy_count' => $healthStatusCount->unhealthy_count,
            'unhealthy_percentage' => $unhealthyPercentage,
            'indicators' => $indicatorData,
        ];
    }

    /**
     * Menghitung data indikator secara keseluruhan
     */
    private function calculateOverallIndicatorData(): array
    {
        $indicatorFields = [
            'kb' => 'Keluarga Berencana',
            'birth_facility' => 'Persalinan di Faskes',
            'immunization' => 'Imunisasi Dasar Lengkap',
            'exclusive_breastfeeding' => 'ASI Eksklusif',
            'growth_monitoring' => 'Pemantauan Pertumbuhan',
            'tb_treatment' => 'Pengobatan TB',
            'hypertension_treatment' => 'Pengobatan Hipertensi',
            'mental_treatment' => 'Pengobatan Gangguan Jiwa',
            'no_smoking' => 'Tidak Merokok',
            'jkn_membership' => 'Kepesertaan JKN',
            'clean_water' => 'Akses Air Bersih',
            'sanitary_toilet' => 'Jamban Sehat',
        ];

        $result = [];

        foreach ($indicatorFields as $field => $label) {
            // Hitung jumlah keluarga yang relevan dengan indikator ini
            $relevantCount = FamilyHealthIndex::where($field . '_relevant', true)->count();

            // Hitung jumlah keluarga yang memenuhi indikator ini
            $fulfilledCount = FamilyHealthIndex::where($field . '_relevant', true)
                ->where($field . '_status', true)
                ->count();

            // Hitung persentase
            $percentage = $relevantCount > 0 ? ($fulfilledCount / $relevantCount) * 100 : 0;

            $result[$field] = [
                'label' => $label,
                'relevant_count' => $relevantCount,
                'fulfilled_count' => $fulfilledCount,
                'percentage' => $percentage,
            ];
        }

        return $result;
    }
}
