<?php
// app/Filament/Pages/Analysis.php

namespace App\Filament\Pages;



use Illuminate\Support\Facades\DB;

use Filament\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Card;
use App\Services\AnalyticsService;
use Filament\Forms\Form;
// use Filament\Actions\Action;
use Filament\Forms\Components\Actions\Action;  // Tambahkan ini
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Notifications\Notification;


class Analysis extends Page
{

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Analisis Data';
    protected static ?string $title = 'Analisis Data';
    protected static ?string $slug = 'analisis';
    protected static string $view = 'filament.pages.analysis';

    public $queries = [];
    public $results = null;
    public $selectedMetrics = [];
    public $visualizationType = 'table';
    public $crossAnalysis = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Analysis')
                    ->tabs([
                        Tabs\Tab::make('Filters')
                            ->schema([
                                Repeater::make('queries')
                                    ->schema([
                                        Select::make('variable')
                                            ->label('Variabel')
                                            ->options($this->getAvailableVariables())
                                            ->required(),
                                        Select::make('operator')
                                            ->label('Operator')
                                            ->options($this->getOperators())
                                            ->required(),
                                        TextInput::make('value')
                                            ->label('Nilai')
                                            ->required(),
                                    ])
                                    ->columns(3)
                                    ->defaultItems(1)
                                    ->addActionLabel('Tambah Kondisi'),
                            ]),

                        Tabs\Tab::make('Metrics')
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        Select::make('selectedMetrics')
                                            ->label('Pilih Metrik')
                                            ->multiple()
                                            ->options([
                                                'count' => 'Jumlah Total',
                                                'gender_dist' => 'Distribusi Gender',
                                                'health_stats' => 'Statistik Kesehatan',
                                                'age_avg' => 'Rata-rata Usia',
                                            ])
                                            ->live(),

                                        Select::make('visualizationType')
                                            ->label('Tipe Visualisasi')
                                            ->options([
                                                'table' => 'Tabel',
                                                'bar' => 'Grafik Batang',
                                                'pie' => 'Grafik Pie',
                                            ])
                                            ->default('table')
                                            ->live(),
                                    ]),
                            ]),

                        Tabs\Tab::make('Cross Analysis')
                            ->schema([
                                Select::make('crossAnalysis')
                                    ->label('Analisis Silang')
                                    ->multiple()
                                    ->options($this->getAvailableVariables())
                                    ->helperText('Pilih variabel untuk analisis silang'),
                            ]),
                    ]),
            ]);
    }

    protected function getOperators(): array
    {
        return [
            '=' => 'Sama dengan',
            '>' => 'Lebih dari',
            '<' => 'Kurang dari',
            '>=' => 'Lebih dari sama dengan',
            '<=' => 'Kurang dari sama dengan',
            'LIKE' => 'Mengandung',
            'TRUE' => 'Ya',
            'FALSE' => 'Tidak',
        ];
    }


    protected function getAvailableVariables(): array
    {
        return [
            'Demografis' => [
                'family_members.age' => 'Usia',
                'family_members.gender' => 'Jenis Kelamin',
                'family_members.education' => 'Pendidikan',
                'family_members.marital_status' => 'Status Pernikahan',
                'family_members.occupation' => 'Pekerjaan',
            ],
            'Kesehatan' => [
                'family_members.is_pregnant' => 'Status Kehamilan',
                'family_members.has_tuberculosis' => 'TBC',
                'family_members.has_hypertension' => 'Hipertensi',
                'family_members.uses_contraception' => 'Penggunaan KB',
                'family_members.takes_tb_medication_regularly' => 'Minum Obat TBC',
                'family_members.takes_hypertension_medication_regularly' => 'Minum Obat Darah Tnggi',
                'family_members.has_chronic_cough' => 'Batuk Kronis',
                'family_members.is_smoker' => 'Merokok',
            ],
            'Sanitasi' => [
                'families.has_clean_water' => 'Air Bersih',
                'families.has_toilet' => 'Jamban',
                'families.is_water_protected' => 'Air Terlindungi',
                'families.is_toilet_sanitary' => 'Jamban Saniter',
            ],
        ];
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('analyze')
                ->label('Analisis')
                ->action('analyze'),
        ];
    }


    public function analyze()
    {
        try {
            $query = DB::table('family_members')
                ->join('families', 'family_members.family_id', '=', 'families.id')
                ->join('buildings', 'families.building_id', '=', 'buildings.id')
                ->join('villages', 'buildings.village_id', '=', 'villages.id');

            // Inisialisasi select dasar
            $query->select([
                'family_members.id',
                'family_members.name',
                'family_members.gender',
                'family_members.age',
                'family_members.birth_date',
                'family_members.has_tuberculosis',
                'family_members.has_hypertension',
                'families.family_number',
                'villages.name as village_name'
            ]);

            // $query->select($baseColumns);

            // Apply filters
            if (!empty($this->queries)) {
                foreach ($this->queries as $queryItem) {
                    $field = $queryItem['variable'];
                    $operator = $queryItem['operator'];
                    $value = $queryItem['value'];

                    switch ($operator) {
                        case 'TRUE':
                            $query->where($field, true);
                            break;
                        case 'FALSE':
                            $query->where($field, false);
                            break;
                        case 'LIKE':
                            $query->where($field, 'LIKE', "%{$value}%");
                            break;
                        default:
                            $query->where($field, $operator, $value);
                    }
                }
            }

            // Apply metrics
            if (!empty($this->selectedMetrics)) {
                $needsGroupBy = false;

                foreach ($this->selectedMetrics as $metric) {
                    switch ($metric) {
                        case 'count':
                            // Untuk count, kita bisa menggunakan subquery
                            $query->selectRaw('(SELECT COUNT(*) FROM family_members) as total_count');
                            break;

                        case 'gender_dist':
                            $needsGroupBy = true;
                            $query->selectRaw('COUNT(*) as gender_count')
                                ->groupBy('family_members.gender');
                            break;

                        case 'health_stats':
                            // Gunakan subqueries untuk statistik kesehatan
                            $query->selectRaw('
                            (SELECT COUNT(*) FROM family_members WHERE has_tuberculosis = 1) as tb_count,
                            (SELECT COUNT(*) FROM family_members WHERE has_hypertension = 1) as hypertension_count
                        ');
                            break;

                        case 'age_avg':
                            // Gunakan subquery untuk rata-rata usia
                            $query->selectRaw('(SELECT AVG(age) FROM family_members) as average_age');
                            break;
                    }
                }
            }


            // Execute query
            $results = $query->get();

            // Format data untuk chart jika bukan tipe tabel
            if ($this->visualizationType !== 'table') {
                $chartData = $this->prepareChartData($results);
            }

            $formattedResults = [
                'type' => 'table',
                'data' => $results,
                'columns' => [
                    ['field' => 'family_number', 'label' => 'No. KK'],
                    ['field' => 'name', 'label' => 'Nama'],
                    ['field' => 'nik', 'label' => 'NIK'],
                    ['field' => 'relationship', 'label' => 'Hubungan'],
                    ['field' => 'birth_place', 'label' => 'Tempat Lahir'],
                    ['field' => 'birth_date', 'label' => 'Tanggal Lahir'],
                    ['field' => 'gender', 'label' => 'Jenis Kelamin'],
                    ['field' => 'religion', 'label' => 'Agama'],
                    ['field' => 'education', 'label' => 'Pendidikan'],
                    ['field' => 'marital_status', 'label' => 'Status Perkawinan'],
                    ['field' => 'occupation', 'label' => 'Pekerjaan'],
                    ['field' => 'has_jkn', 'label' => 'Memiliki JKN'],
                    ['field' => 'is_smoker', 'label' => 'Perokok'],
                    ['field' => 'use_water', 'label' => 'Menggunakan Air Bersih'],
                    ['field' => 'use_toilet', 'label' => 'Menggunakan Toilet'],
                    ['field' => 'has_tuberculosis', 'label' => 'Menderita TBC'],
                    ['field' => 'takes_tb_medication_regularly', 'label' => 'Minum Obat TBC Teratur'],
                    ['field' => 'has_chronic_cough', 'label' => 'Batuk Kronis'],
                    ['field' => 'has_hypertension', 'label' => 'Hipertensi'],
                    ['field' => 'takes_hypertension_medication_regularly', 'label' => 'Minum Obat Hipertensi Teratur'],
                    ['field' => 'uses_contraception', 'label' => 'Menggunakan KB'],
                    ['field' => 'gave_birth_in_health_facility', 'label' => 'Melahirkan di Faskes'],
                    ['field' => 'exclusive_breastfeeding', 'label' => 'ASI Eksklusif'],
                    ['field' => 'complete_immunization', 'label' => 'Imunisasi Lengkap'],
                    ['field' => 'growth_monitoring', 'label' => 'Pemantauan Pertumbuhan'],
                    ['field' => 'village_name', 'label' => 'Desa'],
                ]
            ];

            $this->results = [
                'type' => $this->visualizationType,
                'data' => $results,
                'columns' => $this->getResultColumns($results),
                'selectedMetrics' => $this->selectedMetrics,
                'chartData' => $chartData ?? null
            ];

            Notification::make()
                ->title('Analisis berhasil dilakukan')
                ->success()
                ->send();
        } catch (\Exception $e) {
            \Log::error('Analysis Error:', ['error' => $e->getMessage()]);

            Notification::make()
                ->title('Terjadi kesalahan dalam analisis')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }


    protected function prepareChartData($results)
    {
        $chartData = [
            'labels' => [],
            'values' => []
        ];

        foreach ($this->selectedMetrics as $metric) {
            switch ($metric) {
                case 'gender_dist':
                    // Group by gender
                    $grouped = $results->groupBy('gender');
                    foreach ($grouped as $gender => $items) {
                        $chartData['labels'][] = $gender;
                        $chartData['values'][] = $items->count();
                    }
                    break;

                case 'health_stats':
                    $chartData['labels'] = ['TB', 'Hipertensi'];
                    $chartData['values'] = [
                        $results->where('has_tuberculosis', true)->count(),
                        $results->where('has_hypertension', true)->count()
                    ];
                    break;

                case 'age_avg':
                    // Group by age ranges
                    $ranges = [
                        '0-5' => [0, 5],
                        '6-17' => [6, 17],
                        '18-45' => [18, 45],
                        '46+' => [46, 999]
                    ];

                    foreach ($ranges as $label => $range) {
                        $chartData['labels'][] = $label;
                        $chartData['values'][] = $results->whereBetween('age', $range)->count();
                    }
                    break;
            }
        }

        return $chartData;
    }


    protected function formatResults($results)
    {
        $formattedData = [];

        // Format data berdasarkan tipe visualisasi yang dipilih
        switch ($this->visualizationType) {
            case 'table':
                $formattedData = [
                    'type' => 'table',
                    'data' => $results,
                    'columns' => $this->getSelectedColumns(),
                ];
                break;

            case 'bar':
                $formattedData = [
                    'type' => 'bar',
                    'labels' => $results->pluck($this->crossAnalysis[0] ?? 'id')->toArray(),
                    'datasets' => $this->formatDataForChart($results),
                ];
                break;

            case 'line':
                $formattedData = [
                    'type' => 'line',
                    'labels' => $results->pluck($this->crossAnalysis[0] ?? 'id')->toArray(),
                    'datasets' => $this->formatDataForChart($results),
                ];
                break;

            case 'pie':
                $formattedData = [
                    'type' => 'pie',
                    'labels' => $results->pluck($this->crossAnalysis[0] ?? 'id')->toArray(),
                    'datasets' => $this->formatDataForChart($results),
                ];
                break;
        }

        return [
            'visualizationType' => $this->visualizationType,
            'data' => $formattedData,
            'crossAnalysis' => $this->crossAnalysis,
            'metrics' => $this->selectedMetrics,
            'queries' => $this->queries,
        ];
    }


    protected function getSelectedColumns()
    {
        $columns = [];

        // Tambahkan kolom dari cross analysis
        if (!empty($this->crossAnalysis)) {
            foreach ($this->crossAnalysis as $field) {
                $columns[] = [
                    'field' => $field,
                    'label' => $this->getFieldLabel($field),
                ];
            }
        }

        // Tambahkan kolom dari metrics
        foreach ($this->selectedMetrics as $metric) {
            switch ($metric) {
                case 'count':
                    $columns[] = [
                        'field' => 'total_count',
                        'label' => 'Total',
                    ];
                    break;
                case 'average':
                    $columns[] = [
                        'field' => 'average_age',
                        'label' => 'Rata-rata Usia',
                    ];
                    break;
                case 'sum':
                    $columns[] = [
                        'field' => 'total_tb',
                        'label' => 'Total TB',
                    ];
                    break;
            }
        }

        return $columns;
    }

    protected function formatDataForChart($results)
    {
        $datasets = [];

        foreach ($this->selectedMetrics as $metric) {
            switch ($metric) {
                case 'count':
                    $datasets[] = [
                        'label' => 'Total',
                        'data' => $results->pluck('total_count')->toArray(),
                    ];
                    break;
                case 'average':
                    $datasets[] = [
                        'label' => 'Rata-rata Usia',
                        'data' => $results->pluck('average_age')->toArray(),
                    ];
                    break;
                case 'sum':
                    $datasets[] = [
                        'label' => 'Total TB',
                        'data' => $results->pluck('total_tb')->toArray(),
                    ];
                    break;
            }
        }

        return $datasets;
    }

    protected function getFieldLabel($field)
    {
        $variables = $this->getAvailableVariables();
        $flatVariables = collect($variables)->flatMap(function ($group) {
            return $group;
        });

        return $flatVariables[$field] ?? $field;
    }


    protected function getResultColumns($results)
    {
        $columns = [
            ['field' => 'name', 'label' => 'Nama'],
            ['field' => 'gender', 'label' => 'Jenis Kelamin'],
            ['field' => 'age', 'label' => 'Usia'],
            ['field' => 'village_name', 'label' => 'Desa']
        ];

        // Tambahkan kolom metrik
        if (!empty($this->selectedMetrics)) {
            foreach ($this->selectedMetrics as $metric) {
                switch ($metric) {
                    case 'count':
                        $columns[] = ['field' => 'total_count', 'label' => 'Total'];
                        break;
                    case 'gender_dist':
                        $columns[] = ['field' => 'gender_count', 'label' => 'Jumlah per Gender'];
                        break;
                    case 'health_stats':
                        $columns[] = ['field' => 'tb_count', 'label' => 'Total TB'];
                        $columns[] = ['field' => 'hypertension_count', 'label' => 'Total Hipertensi'];
                        break;
                    case 'age_avg':
                        $columns[] = ['field' => 'average_age', 'label' => 'Rata-rata Usia'];
                        break;
                }
            }
        }

        return $columns;
    }
}
