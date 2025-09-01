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
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AnalysisDataExport;

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

    // Properties for Export Excel
    public $showExportModal = false;
    public $exportFilename = '';
    public $exportColumns = [];
    public $selectAllColumnsForExport = true;

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

            // Inisialisasi select dasar dengan penambahan family_id
            $query->select([
                'family_members.id',
                'family_members.family_id', // Tambahkan family_id untuk keperluan kepala keluarga
                'family_members.name',
                'family_members.gender',
                'family_members.age',
                'family_members.birth_date',
                'family_members.has_tuberculosis',
                'family_members.has_hypertension',
                'families.family_number',
                'villages.name as village_name'
            ]);

            // Apply filters
            if (!empty($this->queries)) {
                foreach ($this->queries as $queryItem) {
                    if (isset($queryItem['variable']) && isset($queryItem['operator']) && isset($queryItem['value'])) {
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

            // Gunakan kolom dasar dengan family_id
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

            $this->results = [
                'type' => $this->visualizationType,
                'data' => $results,
                'columns' => $columns,
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
    // Method untuk membuka modal export
    public function openExportModal()
    {
        if (!$this->results || empty($this->results['data'])) {
            Notification::make()
                ->title('Tidak ada data untuk di-export')
                ->warning()
                ->send();
            return;
        }

        // Reset export options
        $this->exportFilename = 'analisis-data-' . now()->format('Y-m-d-His');

        // Set default selected columns (all)
        $this->exportColumns = collect($this->results['columns'])
            ->pluck('field')
            ->toArray();

        $this->selectAllColumnsForExport = true;

        // Show modal
        $this->showExportModal = true;

        // Debug
        \Log::info('Modal export dibuka', [
            'showExportModal' => $this->showExportModal,
            'filename' => $this->exportFilename,
            'columns_count' => count($this->exportColumns)
        ]);
    }

    // Method untuk toggle select all columns
    public function toggleAllColumnsForExport()
    {
        $this->selectAllColumnsForExport = !$this->selectAllColumnsForExport;

        if ($this->selectAllColumnsForExport) {
            $this->exportColumns = collect($this->results['columns'])
                ->pluck('field')
                ->toArray();
        } else {
            $this->exportColumns = [];
        }
    }

    /**
     * Method langsung export Excel dengan data lengkap, informasi filter, dan nama kepala keluarga
     */
    public function directExportToExcel()
    {
        try {
            if (!$this->results || empty($this->results['data'])) {
                Notification::make()
                    ->title('Tidak ada data untuk di-export')
                    ->warning()
                    ->send();
                return;
            }

            $data = $this->results['data'];

            // Dapatkan family_id dari data yang akan diexport
            $familyIds = $data->pluck('family_id')->unique()->filter()->values()->toArray();

            // Ambil data kepala keluarga
            $headOfFamilies = [];
            if (!empty($familyIds)) {
                $headOfFamilies = DB::table('family_members')
                    ->join('families', 'family_members.family_id', '=', 'families.id')
                    ->where('family_members.relationship', 'Kepala Keluarga')
                    ->orWhere('family_members.relationship', 'KEPALA KELUARGA')
                    ->orWhere('family_members.relationship', 'KK')
                    ->whereIn('family_members.family_id', $familyIds)
                    ->select('families.id as family_id', 'family_members.name as head_name', 'families.family_number')
                    ->get()
                    ->keyBy('family_id');
            }

            // Tambahkan informasi kepala keluarga ke setiap baris data
            $enrichedData = $data->map(function ($item) use ($headOfFamilies) {
                $familyId = $item->family_id ?? null;
                if ($familyId && isset($headOfFamilies[$familyId])) {
                    $item->head_of_family = $headOfFamilies[$familyId]->head_name;
                } else {
                    $item->head_of_family = '-';
                }
                return $item;
            });

            // Dapatkan semua kolom yang tersedia di database untuk export lengkap
            $completeColumns = [
                ['field' => 'head_of_family', 'label' => 'Kepala Keluarga'],
                ['field' => 'name', 'label' => 'Nama'],
                ['field' => 'nik', 'label' => 'NIK'],
                ['field' => 'relationship', 'label' => 'Hubungan'],
                ['field' => 'birth_place', 'label' => 'Tempat Lahir'],
                ['field' => 'birth_date', 'label' => 'Tanggal Lahir'],
                ['field' => 'gender', 'label' => 'Jenis Kelamin'],
                ['field' => 'age', 'label' => 'Usia'],
                ['field' => 'religion', 'label' => 'Agama'],
                ['field' => 'education', 'label' => 'Pendidikan'],
                ['field' => 'marital_status', 'label' => 'Status Perkawinan'],
                ['field' => 'occupation', 'label' => 'Pekerjaan'],
                ['field' => 'has_jkn', 'label' => 'Memiliki JKN'],
                ['field' => 'is_smoker', 'label' => 'Perokok'],
                ['field' => 'is_pregnant', 'label' => 'Hamil'],
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
                ['field' => 'village_name', 'label' => 'Desa']
            ];

            // Tambahkan kolom metrik hasil analisis jika ada
            if (!empty($this->selectedMetrics)) {
                foreach ($this->selectedMetrics as $metric) {
                    switch ($metric) {
                        case 'count':
                            $completeColumns[] = ['field' => 'total_count', 'label' => 'Total'];
                            break;
                        case 'gender_dist':
                            $completeColumns[] = ['field' => 'gender_count', 'label' => 'Jumlah per Gender'];
                            break;
                        case 'health_stats':
                            $completeColumns[] = ['field' => 'tb_count', 'label' => 'Total TB'];
                            $completeColumns[] = ['field' => 'hypertension_count', 'label' => 'Total Hipertensi'];
                            break;
                        case 'age_avg':
                            $completeColumns[] = ['field' => 'average_age', 'label' => 'Rata-rata Usia'];
                            break;
                    }
                }
            }

            // Dapatkan informasi filter
            $filterInfo = [];

            if (!empty($this->queries)) {
                foreach ($this->queries as $query) {
                    if (isset($query['variable']) && isset($query['operator']) && isset($query['value'])) {
                        $fieldLabel = $this->getFieldLabel($query['variable']);
                        $operator = $this->getOperators()[$query['operator']] ?? $query['operator'];
                        $value = $query['value'];

                        $filterInfo[$fieldLabel] = "{$operator} {$value}";
                    }
                }
            }

            // Tambahkan informasi metrik yang digunakan
            if (!empty($this->selectedMetrics)) {
                $metricLabels = [
                    'count' => 'Jumlah Total',
                    'gender_dist' => 'Distribusi Gender',
                    'health_stats' => 'Statistik Kesehatan',
                    'age_avg' => 'Rata-rata Usia',
                ];

                $selectedMetricLabels = [];
                foreach ($this->selectedMetrics as $metric) {
                    $selectedMetricLabels[] = $metricLabels[$metric] ?? $metric;
                }

                $filterInfo['Metrik Analisis'] = implode(', ', $selectedMetricLabels);
            }

            // Tambahkan informasi tipe visualisasi
            if (!empty($this->visualizationType)) {
                $visualizationLabels = [
                    'table' => 'Tabel',
                    'bar' => 'Grafik Batang',
                    'pie' => 'Grafik Pie',
                ];

                $filterInfo['Tipe Visualisasi'] = $visualizationLabels[$this->visualizationType] ?? $this->visualizationType;
            }

            // Filter kolom yang tersedia di data
            $availableColumns = collect($completeColumns)
                ->filter(function ($column) use ($enrichedData) {
                    // Periksa apakah field ada di setidaknya satu baris data
                    return $enrichedData->some(function ($row) use ($column) {
                        return property_exists($row, $column['field']);
                    });
                })
                ->values()
                ->toArray();

            $filename = 'analisis-data-lengkap-' . now()->format('Y-m-d-His') . '.xlsx';

            Notification::make()
                ->title('Memulai export data ke Excel')
                ->success()
                ->send();

            return Excel::download(new AnalysisDataExport($enrichedData, $availableColumns, $filterInfo), $filename);
        } catch (\Exception $e) {
            \Log::error('Export Error:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            Notification::make()
                ->title('Terjadi kesalahan saat export')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    // Method untuk export data ke Excel
    public function exportToExcel()
    {
        try {
            \Log::info('Method exportToExcel dipanggil');

            if (!$this->results || empty($this->results['data'])) {
                Notification::make()
                    ->title('Tidak ada data untuk di-export')
                    ->warning()
                    ->send();
                return null;
            }

            $data = $this->results['data'];

            // Filter kolom berdasarkan pilihan
            $columns = collect($this->results['columns'])
                ->filter(function ($column) {
                    return in_array($column['field'], $this->exportColumns);
                })
                ->values()
                ->toArray();

            if (empty($columns)) {
                Notification::make()
                    ->title('Pilih minimal satu kolom untuk di-export')
                    ->warning()
                    ->send();
                return null;
            }

            // Generate filename
            $filename = $this->exportFilename;
            if (!str_ends_with(strtolower($filename), '.xlsx')) {
                $filename .= '.xlsx';
            }

            // Tutup modal
            $this->showExportModal = false;

            \Log::info('Mengeksport data', [
                'filename' => $filename,
                'columns_count' => count($columns),
                'data_count' => count($data)
            ]);

            // Return Excel file untuk didownload
            return Excel::download(new AnalysisDataExport($data, $columns), $filename);
        } catch (\Exception $e) {
            \Log::error('Export Error:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            Notification::make()
                ->title('Terjadi kesalahan saat export')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return null;
        }
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
