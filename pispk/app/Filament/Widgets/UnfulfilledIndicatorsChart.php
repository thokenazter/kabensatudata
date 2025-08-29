<?php

namespace App\Filament\Widgets;

use App\Models\FamilyHealthIndex;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class UnfulfilledIndicatorsChart extends ChartWidget
{
    protected static ?string $heading = 'Indikator yang Sering Tidak Terpenuhi';

    protected static ?string $pollingInterval = null;

    protected static ?string $maxHeight = '400px';

    protected function getData(): array
    {
        $data = [
            'kb' => [
                'name' => 'KB',
                'unfulfilled' => 0,
                'relevant' => 0,
            ],
            'birth_facility' => [
                'name' => 'Persalinan di Faskes',
                'unfulfilled' => 0,
                'relevant' => 0,
            ],
            'immunization' => [
                'name' => 'Imunisasi Lengkap',
                'unfulfilled' => 0,
                'relevant' => 0,
            ],
            'exclusive_breastfeeding' => [
                'name' => 'ASI Eksklusif',
                'unfulfilled' => 0,
                'relevant' => 0,
            ],
            'growth_monitoring' => [
                'name' => 'Pemantauan Pertumbuhan',
                'unfulfilled' => 0,
                'relevant' => 0,
            ],
            'tb_treatment' => [
                'name' => 'Pengobatan TB',
                'unfulfilled' => 0,
                'relevant' => 0,
            ],
            'hypertension_treatment' => [
                'name' => 'Pengobatan Hipertensi',
                'unfulfilled' => 0,
                'relevant' => 0,
            ],
            'mental_treatment' => [
                'name' => 'Pengobatan Gangguan Jiwa',
                'unfulfilled' => 0,
                'relevant' => 0,
            ],
            'no_smoking' => [
                'name' => 'Tidak Merokok',
                'unfulfilled' => 0,
                'relevant' => 0,
            ],
            'jkn_membership' => [
                'name' => 'Kepesertaan JKN',
                'unfulfilled' => 0,
                'relevant' => 0,
            ],
            'clean_water' => [
                'name' => 'Akses Air Bersih',
                'unfulfilled' => 0,
                'relevant' => 0,
            ],
            'sanitary_toilet' => [
                'name' => 'Jamban Sehat',
                'unfulfilled' => 0,
                'relevant' => 0,
            ],
        ];

        // Query untuk mengambil jumlah indikator yang relevan dan tidak terpenuhi
        foreach ($data as $key => $value) {
            $relevant = FamilyHealthIndex::where($key . '_relevant', true)->count();
            $unfulfilled = FamilyHealthIndex::where($key . '_relevant', true)
                ->where($key . '_status', false)
                ->count();

            $data[$key]['relevant'] = $relevant;
            $data[$key]['unfulfilled'] = $unfulfilled;
        }

        // Hitung persentase indikator yang tidak terpenuhi
        foreach ($data as $key => $value) {
            if ($value['relevant'] > 0) {
                $data[$key]['percentage'] = ($value['unfulfilled'] / $value['relevant']) * 100;
            } else {
                $data[$key]['percentage'] = 0;
            }
        }

        // Urutkan berdasarkan persentase terbesar
        uasort($data, function ($a, $b) {
            return $b['percentage'] <=> $a['percentage'];
        });

        // Ambil 8 indikator teratas
        $data = array_slice($data, 0, 8);

        $labels = [];
        $percentages = [];
        $colors = [];

        foreach ($data as $key => $value) {
            $labels[] = $value['name'];
            $percentages[] = round($value['percentage'], 2);

            // Set warna berdasarkan persentase
            if ($value['percentage'] > 75) {
                $colors[] = 'rgba(239, 68, 68, 0.7)'; // Merah
            } elseif ($value['percentage'] > 50) {
                $colors[] = 'rgba(234, 179, 8, 0.7)'; // Kuning
            } elseif ($value['percentage'] > 25) {
                $colors[] = 'rgba(59, 130, 246, 0.7)'; // Biru
            } else {
                $colors[] = 'rgba(34, 197, 94, 0.7)'; // Hijau
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Persentase Tidak Terpenuhi',
                    'data' => $percentages,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'horizontalBar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'elements' => [
                'bar' => [
                    'borderWidth' => 1,
                ],
            ],
            'responsive' => true,
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                    'max' => 100,
                    'title' => [
                        'display' => true,
                        'text' => 'Persentase Tidak Terpenuhi (%)',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => '(context) => `${context.parsed.x}% tidak terpenuhi`',
                    ],
                ],
            ],
        ];
    }
}
