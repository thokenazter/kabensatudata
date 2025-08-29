<?php

namespace App\Filament\Widgets;

use App\Models\Building;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\Village;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $villageNames = Village::pluck('name')->join('|');

        return [
            Stat::make('Total Desa', Village::count())
                ->description('Jumlah desa yang terdaftar')
                ->descriptionIcon('heroicon-m-map')
                ->color('success'),

            Stat::make('Total Bangunan', Building::count())
                ->description('Jumlah bangunan yang didata')
                ->descriptionIcon('heroicon-m-home')
                ->color('success'),

            Stat::make('Total Keluarga', Family::count())
                ->description('Jumlah keluarga yang didata')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make('Total Individu', FamilyMember::count())
                ->description('Jumlah individu yang didata')
                ->descriptionIcon('heroicon-m-user')
                ->color('success'),
            // Stat::make('Nama Desa', $villageNames)
            //     ->description('Daftar desa yang terdaftar')
            //     ->descriptionIcon('heroicon-m-home')
            //     ->color('primary'),
        ];
    }
}
