<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AnalysisDataExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithColumnWidths, WithCustomStartCell, WithEvents, ShouldAutoSize
{
    protected $data;
    protected $columns;
    protected $filters;

    public function __construct($data, $columns, $filters = [])
    {
        $this->data = $data;
        $this->columns = $columns;
        $this->filters = $filters;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Data Hasil Analisis';
    }

    /**
     * @return string
     */
    public function startCell(): string
    {
        return 'A7'; // Starts at A7 to leave room for title and filter information
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return new Collection($this->data);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return collect($this->columns)->pluck('label')->toArray();
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        $widths = [];

        // Set default width for all columns
        foreach (range('A', 'Z') as $column) {
            $widths[$column] = 15;
        }

        // Add wider columns for specific fields
        $widths['A'] = 20; // No KK
        $widths['B'] = 25; // Nama

        return $widths;
    }

    /**
     * @param mixed $row
     *
     * @return array
     */
    public function map($row): array
    {
        $mappedRow = [];

        foreach ($this->columns as $column) {
            $field = $column['field'];
            $value = property_exists($row, $field) ? $row->{$field} : null;

            // Format nilai berdasarkan tipe data
            if (is_bool($value) || in_array($value, [0, 1, '0', '1'])) {
                $value = $value == 1 ? 'Ya' : 'Tidak';
            } elseif ($field === 'birth_date' && $value) {
                try {
                    $value = Carbon::parse($value)->format('d/m/Y');
                } catch (\Exception $e) {
                    // Tetap gunakan nilai asli jika parsing gagal
                }
            } elseif ($value === null || $value === '') {
                $value = '-';
            }

            $mappedRow[] = $value;
        }

        return $mappedRow;
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Add title
                $sheet->setCellValue('A1', 'LAPORAN HASIL ANALISIS DATA KESEHATAN');
                $sheet->mergeCells('A1:F1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // Add export date
                $sheet->setCellValue('A2', 'Tanggal Export: ' . Carbon::now()->format('d F Y - H:i'));
                $sheet->mergeCells('A2:F2');

                // Add total data
                $sheet->setCellValue('A3', 'Jumlah Data: ' . count($this->data));
                $sheet->mergeCells('A3:F3');

                // Add filter information if available
                if (!empty($this->filters)) {
                    $filterText = 'Filter: ';
                    foreach ($this->filters as $key => $value) {
                        $filterText .= "$key: $value, ";
                    }
                    $filterText = rtrim($filterText, ', ');

                    $sheet->setCellValue('A4', $filterText);
                    $sheet->mergeCells('A4:F4');
                }

                // Style for header row (column names)
                $headerRowIndex = 7; // Based on startCell()
                $headerRange = 'A' . $headerRowIndex . ':' . $this->getLastColumn($sheet) . $headerRowIndex;

                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4472C4'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Style for data rows
                $dataRange = 'A' . ($headerRowIndex + 1) . ':' . $this->getLastColumn($sheet) . ($headerRowIndex + count($this->data));

                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Add zebra striping to data rows
                for ($i = $headerRowIndex + 1; $i <= $headerRowIndex + count($this->data); $i++) {
                    if ($i % 2 == 0) {
                        $sheet->getStyle('A' . $i . ':' . $this->getLastColumn($sheet) . $i)->applyFromArray([
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F2F2F2'],
                            ],
                        ]);
                    }
                }

                // Auto filter for header
                $sheet->setAutoFilter($headerRange);

                // Freeze panes at header row
                $sheet->freezePane('A' . ($headerRowIndex + 1));
            },
        ];
    }

    /**
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     *
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Style headers
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'E2E8F0',
                ],
            ],
        ]);

        // Borders
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . $sheet->getHighestRow())->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ]);

        // Add filter
        $sheet->setAutoFilter('A1:' . $sheet->getHighestColumn() . '1');

        // Freeze panes
        $sheet->freezePane('A2');

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * Get the last column letter for the sheet
     * 
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @return string
     */
    private function getLastColumn($sheet)
    {
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($this->columns));
    }
}
