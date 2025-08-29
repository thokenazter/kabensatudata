<?php

namespace App\Filament\Widgets;

use App\Models\Family;
use App\Models\FamilyMember;
use Filament\Widgets\ChartWidget;

class HealthStatsOverview extends ChartWidget
{
    protected static ?string $heading = 'Grafik Masalah Kesehatan';

    // Gunakan columnSpan untuk mengatur lebar widget
    // protected int|string|array $columnSpan = '2';

    protected function getData(): array
    {
        // Hitung jumlah kasus untuk masing-masing masalah kesehatan
        $tbcCount = FamilyMember::where('has_tuberculosis', true)->count();
        $darahTinggiCount = FamilyMember::where('has_hypertension', true)->count();
        $batukCount = FamilyMember::where('has_chronic_cough', true)->count();
        $gangguanJiwaCount = Family::where('has_mental_illness', true)->count();
        $pasungCount = Family::where('has_restrained_member', true)->count();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Kasus',
                    'data' => [$tbcCount, $darahTinggiCount, $batukCount, $gangguanJiwaCount, $pasungCount],
                    'backgroundColor' => [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(153, 102, 255)',
                    ],
                ],
            ],
            'labels' => ['Pernah TBC', 'Darah Tinggi', 'Batuk Berdahak', 'Gangguan Jiwa', 'Kasus Pasung'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
