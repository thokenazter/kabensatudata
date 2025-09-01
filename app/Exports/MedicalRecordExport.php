<?php

namespace App\Exports;

use App\Models\MedicalRecord;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;

class MedicalRecordExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithColumnWidths, WithEvents, ShouldAutoSize, WithColumnFormatting
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = MedicalRecord::with(['familyMember', 'creator']);

        if (!empty($this->filters['visit_from'])) {
            $query->whereDate('visit_date', '>=', $this->filters['visit_from']);
        }

        if (!empty($this->filters['visit_until'])) {
            $query->whereDate('visit_date', '<=', $this->filters['visit_until']);
        }

        if (!empty($this->filters['patient_gender'])) {
            $query->where('patient_gender', $this->filters['patient_gender']);
        }

        if (!empty($this->filters['diagnosis_name'])) {
            $query->where('diagnosis_name', $this->filters['diagnosis_name']);
        }

        if (!empty($this->filters['workflow_status'])) {
            $query->where('workflow_status', $this->filters['workflow_status']);
        }

        return $query->orderBy('visit_date', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal Kunjungan',
            'NIK',
            'Nama Pasien',
            'No. RM',
            'Jenis Kelamin',
            'Tanggal Lahir',
            'Alamat',
            'Keluhan Utama',
            'Anamnesis',
            'Tekanan Darah Sistolik',
            'Tekanan Darah Diastolik',
            'Kategori Tekanan Darah',
            'Berat Badan (kg)',
            'Tinggi Badan (cm)',
            'Detak Jantung (bpm)',
            'Suhu Tubuh (C)',
            'Laju Pernapasan (/menit)',
            'Kode Diagnosis (ICD)',
            'Nama Diagnosis',
            'Terapi Obat',
            'Tindakan',
            'Dibuat Oleh',
            'Status Workflow',
            'Tanggal Dibuat',
        ];
    }

    public function map($medicalRecord): array
    {
        $bloodPressureCategory = null;
        if ($medicalRecord->systolic && $medicalRecord->diastolic) {
            if ($medicalRecord->systolic < 120 && $medicalRecord->diastolic < 80) {
                $bloodPressureCategory = 'Normal';
            } elseif ($medicalRecord->systolic < 130 && $medicalRecord->diastolic < 80) {
                $bloodPressureCategory = 'Elevated';
            } elseif ($medicalRecord->systolic < 140 || $medicalRecord->diastolic < 90) {
                $bloodPressureCategory = 'Hipertensi Stage 1';
            } else {
                $bloodPressureCategory = 'Hipertensi Stage 2';
            }
        }

        // Format birth date
        $birthDate = '';
        if ($medicalRecord->patient_birth_date) {
            $birthDate = $medicalRecord->patient_birth_date->format('d/m/Y');
        } elseif ($medicalRecord->familyMember && $medicalRecord->familyMember->birth_date) {
            $birthDate = Carbon::parse($medicalRecord->familyMember->birth_date)->format('d/m/Y');
        }

        // Combine therapy and medication for "Terapi Obat" column
        $therapyMedication = '';
        if ($medicalRecord->therapy && $medicalRecord->medication) {
            $therapyMedication = $medicalRecord->therapy . ' | ' . $medicalRecord->medication;
        } elseif ($medicalRecord->therapy) {
            $therapyMedication = $medicalRecord->therapy;
        } elseif ($medicalRecord->medication) {
            $therapyMedication = $medicalRecord->medication;
        }

        return [
            $medicalRecord->visit_date ? $medicalRecord->visit_date->format('d/m/Y') : '',
            "'" . ($medicalRecord->patient_nik ?? ''), // Force NIK as string with apostrophe
            $medicalRecord->patient_name ?? '',
            "'" . ($medicalRecord->patient_rm_number ?? ''), // Force as string with apostrophe
            $medicalRecord->patient_gender ?? '',
            $birthDate,
            $medicalRecord->patient_address ?? '',
            $medicalRecord->chief_complaint ?? '',
            $medicalRecord->anamnesis ?? '',
            $medicalRecord->systolic ?? '',
            $medicalRecord->diastolic ?? '',
            $bloodPressureCategory ?? '',
            $medicalRecord->weight ?? '',
            $medicalRecord->height ?? '',
            $medicalRecord->heart_rate ?? '',
            $medicalRecord->body_temperature ?? '',
            $medicalRecord->respiratory_rate ?? '',
            $medicalRecord->diagnosis_code ?? '',
            $medicalRecord->diagnosis_name ?? '',
            $therapyMedication,
            $medicalRecord->procedure ?? '',
            $medicalRecord->creator->name ?? 'Tidak diketahui',
            match($medicalRecord->workflow_status ?? 'draft') {
                'draft' => 'Draft',
                'registered' => 'Terdaftar',
                'nurse_examined' => 'Diperiksa Perawat',
                'doctor_examined' => 'Diperiksa Dokter',
                'completed' => 'Selesai',
                default => 'Draft'
            },
            $medicalRecord->created_at ? $medicalRecord->created_at->format('d/m/Y H:i') : '',
        ];
    }


    public function styles(Worksheet $sheet)
    {
        return [
            // Header row styling (row 4 after inserting 3 rows at top)
            4 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF4472C4'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Data Rekam Medis';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,  // Tanggal Kunjungan
            'B' => 20,  // NIK
            'C' => 25,  // Nama Pasien
            'D' => 15,  // No. RM
            'E' => 15,  // Jenis Kelamin
            'F' => 15,  // Tanggal Lahir
            'G' => 40,  // Alamat
            'H' => 30,  // Keluhan Utama
            'I' => 40,  // Anamnesis
            'J' => 12,  // Tekanan Darah Sistolik
            'K' => 12,  // Tekanan Darah Diastolik
            'L' => 20,  // Kategori Tekanan Darah
            'M' => 12,  // Berat Badan (kg)
            'N' => 12,  // Tinggi Badan (cm)
            'O' => 12,  // Detak Jantung (bpm)
            'P' => 12,  // Suhu Tubuh (C)
            'Q' => 15,  // Laju Pernapasan (/menit)
            'R' => 15,  // Kode Diagnosis (ICD)
            'S' => 25,  // Nama Diagnosis
            'T' => 35,  // Terapi Obat
            'U' => 30,  // Tindakan
            'V' => 20,  // Dibuat Oleh
            'W' => 18,  // Tanggal Dibuat
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT, // NIK column (column B)
            'D' => NumberFormat::FORMAT_TEXT, // No. RM column (column D)
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->insertNewRowBefore(1, 3);
                $sheet->setCellValue('A1', 'LAPORAN DATA REKAM MEDIS');
                $sheet->setCellValue('A2', 'Tanggal Export: ' . Carbon::now()->format('d F Y H:i:s'));

                $dataRowCount = $sheet->getHighestRow() - 4;
                $sheet->setCellValue('A3', 'Total Data: ' . $dataRowCount . ' rekam medis');

                $highestColumn = $sheet->getHighestColumn();
                $highestRow = $sheet->getHighestRow();

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->getStyle('A2:A3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                ]);

                $sheet->mergeCells('A1:' . $highestColumn . '1');

                // Apply borders to all data including header
                $sheet->getStyle('A4:' . $highestColumn . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);

                // Ensure data rows have white background (excluding header row 4)
                if ($highestRow > 4) {
                    $sheet->getStyle('A5:' . $highestColumn . $highestRow)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFFFFFFF'], // White background
                        ],
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                }

                $sheet->freezePane('A5');
                $sheet->setAutoFilter('A4:' . $highestColumn . '4');
                $sheet->getRowDimension('4')->setRowHeight(25);

                // Force NIK and No. RM columns to be text format
                $sheet->getStyle('B:B')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT); // NIK
                $sheet->getStyle('D:D')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT); // No. RM
            },
        ];
    }

    public function export($filters = [])
    {
        $this->filters = $filters;

        // Generate consistent filename with timestamp
        $timestamp = now()->format('Y-m-d-H-i-s');
        $filename = "medical-records-{$timestamp}.xlsx";

        // Store the file in storage/app/public/exports directory
        $relativePath = "exports/{$filename}";

        // Ensure exports directory exists in storage/app/public
        $directory = storage_path('app/public/exports');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        // Store the file using Excel facade to public disk
        \Maatwebsite\Excel\Facades\Excel::store($this, $relativePath, 'public');

        // Return the full path to the file in storage
        $fullPath = storage_path("app/public/{$relativePath}");

        // Verify file was created
        if (!file_exists($fullPath)) {
            throw new \Exception("Failed to create export file at: {$fullPath}");
        }

        return $fullPath;
    }
}
