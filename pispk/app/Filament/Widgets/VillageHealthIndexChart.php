<?php

namespace App\Filament\Widgets;

use App\Models\FamilyHealthIndex;
use App\Models\Family;
use App\Models\Village;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class VillageHealthIndexChart extends ChartWidget
{
    protected static ?string $heading = 'IKS per Desa';

    protected static ?string $pollingInterval = null;

    protected static ?string $maxHeight = '400px';

    protected function getData(): array
    {
        // Mendapatkan data rata-rata IKS per desa
        $villageData = DB::table('family_health_indices')
            ->join('families', 'family_health_indices.family_id', '=', 'families.id')
            ->join('buildings', 'families.building_id', '=', 'buildings.id')
            ->join('villages', 'buildings.village_id', '=', 'villages.id')
            ->select('villages.name as village_name', DB::raw('AVG(family_health_indices.iks_value) * 100 as avg_iks'))
            ->groupBy('villages.id', 'villages.name')
            ->orderBy('avg_iks', 'desc')
            ->get();

        // Data untuk jumlah keluarga sehat, pra-sehat, dan tidak sehat per desa
        $healthStatusData = DB::table('family_health_indices')
            ->join('families', 'family_health_indices.family_id', '=', 'families.id')
            ->join('buildings', 'families.building_id', '=', 'buildings.id')
            ->join('villages', 'buildings.village_id', '=', 'villages.id')
            ->select(
                'villages.name as village_name',
                DB::raw('COUNT(CASE WHEN family_health_indices.health_status = "Keluarga Sehat" THEN 1 END) as healthy_count'),
                DB::raw('COUNT(CASE WHEN family_health_indices.health_status = "Keluarga Pra-Sehat" THEN 1 END) as pre_healthy_count'),
                DB::raw('COUNT(CASE WHEN family_health_indices.health_status = "Keluarga Tidak Sehat" THEN 1 END) as unhealthy_count')
            )
            ->groupBy('villages.id', 'villages.name')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Rata-rata IKS (%)',
                    'data' => $villageData->pluck('avg_iks')->toArray(),
                    'backgroundColor' => $villageData->map(function ($item) {
                        $avg = floatval($item->avg_iks);
                        if ($avg > 80) {
                            return 'rgba(34, 197, 94, 0.7)'; // Success color
                        } elseif ($avg > 50) {
                            return 'rgba(234, 179, 8, 0.7)'; // Warning color
                        } else {
                            return 'rgba(239, 68, 68, 0.7)'; // Danger color
                        }
                    })->toArray(),
                    'borderColor' => $villageData->map(function ($item) {
                        $avg = floatval($item->avg_iks);
                        if ($avg > 80) {
                            return 'rgb(34, 197, 94)'; // Success color
                        } elseif ($avg > 50) {
                            return 'rgb(234, 179, 8)'; // Warning color
                        } else {
                            return 'rgb(239, 68, 68)'; // Danger color
                        }
                    })->toArray(),
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $villageData->pluck('village_name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'max' => 100,
                    'title' => [
                        'display' => true,
                        'text' => 'Rata-rata IKS (%)',
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Desa',
                    ],
                ],
            ],
        ];
    }
}
