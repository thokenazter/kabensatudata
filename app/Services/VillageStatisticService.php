<?php

namespace App\Services;

use App\Models\Village;
use App\Models\VillageStatistic;
use Illuminate\Support\Facades\DB;

class VillageStatisticService
{
    public function calculateAllVillageStatistics(): void
    {
        $villages = Village::all();

        foreach ($villages as $village) {
            $this->calculateVillageStatistics($village->id);
        }
    }

    public function calculateVillageStatistics(int $villageId): VillageStatistic
    {
        // Ambil atau buat objek statistik desa
        $statistic = VillageStatistic::firstOrNew(['village_id' => $villageId]);

        // Hitung jumlah bangunan dan keluarga
        $buildingStats = DB::table('buildings')
            ->where('village_id', $villageId)
            ->selectRaw('COUNT(*) as total_buildings')
            ->first();

        $familyStats = DB::table('families')
            ->join('buildings', 'families.building_id', '=', 'buildings.id')
            ->where('buildings.village_id', $villageId)
            ->selectRaw('COUNT(*) as total_families')
            ->first();

        // Hitung jumlah anggota keluarga
        $memberStats = DB::table('family_members')
            ->join('families', 'family_members.family_id', '=', 'families.id')
            ->join('buildings', 'families.building_id', '=', 'buildings.id')
            ->where('buildings.village_id', $villageId)
            ->selectRaw('COUNT(*) as total_members')
            ->first();

        // Hitung jumlah keluarga yang sudah dikunjungi
        $visitedFamilies = DB::table('families')
            ->join('buildings', 'families.building_id', '=', 'buildings.id')
            ->where('buildings.village_id', $villageId)
            ->whereNotNull('families.last_visit_date')
            ->count();

        // Hitung kasus TB
        $tbCases = DB::table('family_members')
            ->join('families', 'family_members.family_id', '=', 'families.id')
            ->join('buildings', 'families.building_id', '=', 'buildings.id')
            ->where('buildings.village_id', $villageId)
            ->where('family_members.has_tuberculosis', true)
            ->count();

        // Hitung kasus Hipertensi
        $hyperCases = DB::table('family_members')
            ->join('families', 'family_members.family_id', '=', 'families.id')
            ->join('buildings', 'families.building_id', '=', 'buildings.id')
            ->where('buildings.village_id', $villageId)
            ->where('family_members.has_hypertension', true)
            ->count();

        // Hitung kasus Gangguan Jiwa
        $mentalCases = DB::table('families')
            ->join('buildings', 'families.building_id', '=', 'buildings.id')
            ->where('buildings.village_id', $villageId)
            ->where('families.has_mental_illness', true)
            ->count();

        // Hitung masalah air bersih
        $noCleanWater = DB::table('families')
            ->join('buildings', 'families.building_id', '=', 'buildings.id')
            ->where('buildings.village_id', $villageId)
            ->where(function ($query) {
                $query->where('families.has_clean_water', false)
                    ->orWhere('families.is_water_protected', false);
            })
            ->count();

        // Hitung masalah toilet
        $noToilet = DB::table('families')
            ->join('buildings', 'families.building_id', '=', 'buildings.id')
            ->where('buildings.village_id', $villageId)
            ->where(function ($query) {
                $query->where('families.has_toilet', false)
                    ->orWhere('families.is_toilet_sanitary', false);
            })
            ->count();

        // Hitung persentase
        $totalFamilies = $familyStats->total_families ?? 0;
        $totalMembers = $memberStats->total_members ?? 0;

        $cleanWaterPercentage = $totalFamilies > 0 ?
            (($totalFamilies - $noCleanWater) / $totalFamilies) * 100 : 0;

        $toiletPercentage = $totalFamilies > 0 ?
            (($totalFamilies - $noToilet) / $totalFamilies) * 100 : 0;

        $tbPercentage = $totalMembers > 0 ?
            ($tbCases / $totalMembers) * 100 : 0;

        $hyperPercentage = $totalMembers > 0 ?
            ($hyperCases / $totalMembers) * 100 : 0;

        $mentalPercentage = $totalFamilies > 0 ?
            ($mentalCases / $totalFamilies) * 100 : 0;

        // Hitung skor IKS dari data agregat (metode sederhana)
        $iksScore = (
            $cleanWaterPercentage * 0.2 +
            $toiletPercentage * 0.2 +
            (100 - $tbPercentage) * 0.2 +
            (100 - $hyperPercentage) * 0.2 +
            (100 - $mentalPercentage) * 0.2
        ) / 100;

        // Update statistik
        $statistic->total_buildings = $buildingStats->total_buildings ?? 0;
        $statistic->total_families = $totalFamilies;
        $statistic->total_members = $totalMembers;
        $statistic->visited_families = $visitedFamilies;
        $statistic->tb_cases = $tbCases;
        $statistic->hypertension_cases = $hyperCases;
        $statistic->mental_cases = $mentalCases;
        $statistic->no_clean_water = $noCleanWater;
        $statistic->no_toilet = $noToilet;
        $statistic->clean_water_percentage = $cleanWaterPercentage;
        $statistic->toilet_percentage = $toiletPercentage;
        $statistic->tb_percentage = $tbPercentage;
        $statistic->hypertension_percentage = $hyperPercentage;
        $statistic->mental_percentage = $mentalPercentage;
        $statistic->iks_score = $iksScore;
        $statistic->last_calculated_at = now();
        $statistic->save();

        return $statistic;
    }
}
