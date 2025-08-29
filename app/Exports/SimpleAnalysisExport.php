<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class SimpleAnalysisExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    protected $rows;
    protected $headers;
    protected $nikColumnIndex = -1;

    public function __construct(array $data)
    {
        $this->headers = $data['headers'];
        $this->rows = $data['rows'];

        // Mencari indeks kolom NIK
        foreach ($this->headers as $index => $header) {
            if ($header === 'NIK') {
                $this->nikColumnIndex = $index;
                break;
            }
        }
    }

    /**
     * @return array
     */
    public function array(): array
    {
        // Cari indeks kolom Usia
        $ageColumnIndex = -1;
        foreach ($this->headers as $index => $header) {
            if ($header === 'Usia') {
                $ageColumnIndex = $index;
                break;
            }
        }

        // Jika kolom NIK ditemukan, pastikan nilai-nilainya adalah string
        if ($this->nikColumnIndex >= 0 || $ageColumnIndex >= 0) {
            foreach ($this->rows as &$row) {
                // Pastikan NIK berupa string
                if (isset($row[$this->nikColumnIndex])) {
                    $row[$this->nikColumnIndex] = (string) $row[$this->nikColumnIndex];
                }

                // Usia sudah diformat sebelumnya oleh AnalysisService, jadi tidak perlu diformat lagi
                // Formatnya sudah termasuk satuan (tahun/bulan)
            }
        }

        return $this->rows;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return $this->headers;
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
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Jika kolom NIK ditemukan
                if ($this->nikColumnIndex >= 0) {
                    // Konversi index kolom ke huruf kolom Excel (0 = A, 1 = B, dst)
                    $nikColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($this->nikColumnIndex + 1);

                    // Format kolom NIK sebagai teks
                    $totalRows = count($this->rows) + 1; // +1 untuk header
                    $nikRange = $nikColumn . '2:' . $nikColumn . $totalRows;

                    // Set format sebagai teks & tandai semua sel
                    $sheet->getStyle($nikRange)->getNumberFormat()->setFormatCode('@');

                    // Format setiap sel individual sebagai teks
                    for ($row = 2; $row <= $totalRows; $row++) {
                        $cellCoordinate = $nikColumn . $row;
                        $cellValue = $sheet->getCell($cellCoordinate)->getValue();

                        // Reset sel dengan nilai yang sama tapi bertipe teks
                        $sheet->getCell($cellCoordinate)->setValueExplicit(
                            $cellValue,
                            DataType::TYPE_STRING
                        );
                    }
                }
            }
        ];
    }
}
