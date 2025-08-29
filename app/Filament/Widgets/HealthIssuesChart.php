<?php

namespace App\Filament\Widgets;

use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\Village;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\View\View;

class LivewireHealthIssuesChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Masalah Kesehatan';

    // Gunakan columnSpan untuk mengatur lebar widget
    protected int|string|array $columnSpan = 'full';

    // Properti Livewire untuk filter
    public ?string $selectedVillage = null;

    protected function getData(): array
    {
        // Query dasar untuk anggota keluarga dengan filter desa jika dipilih
        $familyMemberQuery = FamilyMember::query()
            ->when($this->selectedVillage, function ($query) {
                $query->whereHas('family.building', function ($q) {
                    $q->where('village_id', $this->selectedVillage);
                });
            });

        // Query dasar untuk keluarga dengan filter desa jika dipilih
        $familyQuery = Family::query()
            ->when($this->selectedVillage, function ($query) {
                $query->whereHas('building', function ($q) {
                    $q->where('village_id', $this->selectedVillage);
                });
            });

        // Hitung jumlah kasus untuk masing-masing masalah kesehatan
        $tbcCount = (clone $familyMemberQuery)->where('has_tuberculosis', true)->count();
        $darahTinggiCount = (clone $familyMemberQuery)->where('has_hypertension', true)->count();
        $batukCount = (clone $familyMemberQuery)->where('has_chronic_cough', true)->count();
        $gangguanJiwaCount = (clone $familyQuery)->where('has_mental_illness', true)->count();
        $pasungCount = (clone $familyQuery)->where('has_restrained_member', true)->count();

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

    // Tambahkan filter custom di bagian atas widget
    protected function getHeaderWidgets(): array
    {
        return [];
    }

    // Override view untuk menambahkan filter custom
    public function render(): View
    {
        $villages = Village::pluck('name', 'id')->toArray();

        return view('filament.widgets.livewire-health-issues-chart', [
            'villages' => $villages,
            'chart' => $this->getChart(),
        ]);
    }
}
