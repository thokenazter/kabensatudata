<?php

namespace App\Filament\Widgets;

use App\Models\FamilyHealthIndex;
use App\Models\Village;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class FamilyHealthIndexOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        // Mendapatkan data IKS berdasarkan status
        $sehat = FamilyHealthIndex::where('health_status', 'Keluarga Sehat')->count();
        $praSehat = FamilyHealthIndex::where('health_status', 'Keluarga Pra-Sehat')->count();
        $tidakSehat = FamilyHealthIndex::where('health_status', 'Keluarga Tidak Sehat')->count();

        $total = $sehat + $praSehat + $tidakSehat;

        // Persentase untuk setiap status
        $persenSehat = $total > 0 ? round(($sehat / $total) * 100, 2) : 0;
        $persenPraSehat = $total > 0 ? round(($praSehat / $total) * 100, 2) : 0;
        $persenTidakSehat = $total > 0 ? round(($tidakSehat / $total) * 100, 2) : 0;

        // Rata-rata IKS keseluruhan
        $avgIks = FamilyHealthIndex::avg('iks_value') * 100;

        return [
            Stat::make('Rata-rata IKS', number_format($avgIks, 2) . '%')
                ->description('Rata-rata IKS Seluruh Keluarga')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->chart([
                    $avgIks - 5 > 0 ? $avgIks - 5 : 0,
                    $avgIks - 2.5 > 0 ? $avgIks - 2.5 : 0,
                    $avgIks
                ])
                ->color($avgIks > 80 ? 'success' : ($avgIks > 50 ? 'warning' : 'danger')),

            Stat::make('Keluarga Sehat', $sehat . ' keluarga')
                ->description($persenSehat . '% dari total')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Keluarga Pra-Sehat', $praSehat . ' keluarga')
                ->description($persenPraSehat . '% dari total')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning'),

            Stat::make('Keluarga Tidak Sehat', $tidakSehat . ' keluarga')
                ->description($persenTidakSehat . '% dari total')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
        ];
    }
}
