<?php

namespace App\Filament\Widgets;

use App\Models\Medicine;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MedicineStockWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalMedicines = Medicine::active()->count();
        $lowStockCount = Medicine::active()->lowStock()->where('stock_quantity', '>', 0)->count();
        $outOfStockCount = Medicine::active()->where('stock_quantity', '<=', 0)->count();
        $totalStockValue = Medicine::active()->sum('stock_quantity');

        return [
            Stat::make('Total Obat Aktif', $totalMedicines)
                ->description('Obat yang tersedia dalam sistem')
                ->descriptionIcon('heroicon-m-beaker')
                ->color('primary'),

            Stat::make('Stok Menipis', $lowStockCount)
                ->description('Obat dengan stok di bawah minimum')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockCount > 0 ? 'warning' : 'success'),

            Stat::make('Habis Stok', $outOfStockCount)
                ->description('Obat yang sudah habis')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($outOfStockCount > 0 ? 'danger' : 'success'),

            Stat::make('Total Unit Stok', number_format($totalStockValue))
                ->description('Total unit obat tersedia')
                ->descriptionIcon('heroicon-m-cube')
                ->color('info'),
        ];
    }
}