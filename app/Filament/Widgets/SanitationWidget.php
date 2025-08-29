<?php

namespace App\Filament\Widgets;

use App\Models\Family;
use App\Models\FamilyMember;
use Filament\Widgets\ChartWidget;

class SanitationWidget extends ChartWidget
{
    protected static ?string $heading = 'Grafik Sanitasi Keluarga';

    protected function getData(): array
    {
        // Menghitung jumlah kasus untuk masing-masing sanitasi
        $airBersihCount = Family::where('has_clean_water', true)->count();
        $jambanSaniterCount = Family::where('is_toilet_sanitary', true)->count();
        $babDiJambanCount = FamilyMember::where('use_toilet', true)->count();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Keluarga',
                    'data' => [$airBersihCount, $jambanSaniterCount, $babDiJambanCount],
                    'backgroundColor' => [
                        'rgb(75, 192, 192)', // Air Bersih
                        'rgb(153, 102, 255)', // Jamban Saniter
                        'rgb(255, 159, 64)', // BAB di Jamban
                    ],
                ],
            ],
            'labels' => ['Air Bersih', 'Jamban Saniter', 'BAB di Jamban'],
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // Tipe grafik bisa diganti, misalnya 'pie', 'line', atau lainnya
    }
}
