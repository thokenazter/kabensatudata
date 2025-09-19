<?php

namespace App\Exports;

use App\Models\MedicalRecord;
use App\Models\Medicine;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection as SupportCollection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Files\LocalTemporaryFile;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;

class MedicalRecordExport implements FromCollection, WithMapping, WithEvents, WithColumnFormatting
{
    protected $filters;
    protected ?SupportCollection $records = null;
    protected ?SupportCollection $medicineReport = null;
    protected ?Carbon $medicineReportPeriod = null;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
        if (!empty($this->filters['medicine_report_month'])) {
            $this->medicineReportPeriod = Carbon::parse($this->filters['medicine_report_month'])->startOfMonth();
        }
    }

    protected function buildQuery(): Builder
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

        return $query->orderBy('visit_date', 'desc');
    }

    public function collection()
    {
        if ($this->records === null) {
            $this->records = $this->buildQuery()->get();
        }

        return $this->records;
    }

    protected function medicineReport(): SupportCollection
    {
        if ($this->medicineReport === null) {
            $period = $this->getMedicineReportPeriod();
            $periodString = $period->toDateString();

            $this->medicineReport = Medicine::query()
                ->with(['monthlyStocks' => function ($query) use ($periodString) {
                    $query->where('period_start', $periodString);
                }])
                ->orderBy('name')
                ->get();
        }

        return $this->medicineReport;
    }

    protected function getMedicineReportPeriod(): Carbon
    {
        if ($this->medicineReportPeriod) {
            return $this->medicineReportPeriod;
        }

        return $this->medicineReportPeriod = now()->startOfMonth();
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
            BeforeExport::class => function (BeforeExport $event) {
                $templatePath = storage_path('app/templates/medical-records-template.xlsx');

                if (!file_exists($templatePath)) {
                    throw new \RuntimeException('Template file not found at: ' . $templatePath);
                }

                $template = new LocalTemporaryFile($templatePath);
                $event->writer->reopen($template, ExcelFormat::XLSX);
                $event->writer->getSheetByIndex(0);
            },
            AfterSheet::class => function (AfterSheet $event) {
                $defaultSheet = $event->sheet->getDelegate();
                $spreadsheet = $defaultSheet->getParent();

                $records = $this->collection();
                $recordSheet = $spreadsheet->getSheetByName('Data Rekam Medis') ?? $defaultSheet;
                $recordRows = $records->map(fn ($record) => $this->map($record))->toArray();
                if ($recordRows) {
                    $recordSheet->fromArray($recordRows, null, 'A5');
                }
                $recordSheet->setCellValue('A2', 'Tanggal Export: ' . Carbon::now()->format('d F Y H:i:s'));
                $recordSheet->setCellValue('A3', 'Total Data: ' . $records->count() . ' rekam medis');
                $recordSheet->getStyle('B:B')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
                $recordSheet->getStyle('D:D')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

                $medicineSheet = $spreadsheet->getSheetByName('Master Laporan Obat');
                if ($medicineSheet) {
                    $period = $this->getMedicineReportPeriod();
                    $medicineSheet->setCellValue('B2', 'Periode: ' . $period->translatedFormat('F Y'));

                    $medicineRows = $this->medicineReport()->map(function (Medicine $medicine) {
                        $monthly = $medicine->monthlyStocks->first();

                        if ($monthly) {
                            $initial = $monthly->opening_stock;
                            $closing = $monthly->closing_stock;
                            $usage = $monthly->usage_quantity;
                        } else {
                            $initial = $medicine->stock_initial ?? $medicine->stock_quantity;
                            $closing = $medicine->stock_quantity;
                            $usage = max(0, $initial - $closing);
                        }

                        return [
                            $medicine->full_name,
                            $initial,
                            $closing,
                            $usage,
                        ];
                    })->toArray();

                    if ($medicineRows) {
                        $medicineSheet->fromArray($medicineRows, null, 'B4');
                    }

                    $medicineSheet->getStyle('C:C')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);
                    $medicineSheet->getStyle('D:D')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);
                    $medicineSheet->getStyle('E:E')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);
                }

                if ($recordSheet !== $defaultSheet) {
                    $spreadsheet->removeSheetByIndex($spreadsheet->getIndex($defaultSheet));
                    $spreadsheet->setActiveSheetIndex($spreadsheet->getIndex($recordSheet));
                }
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
