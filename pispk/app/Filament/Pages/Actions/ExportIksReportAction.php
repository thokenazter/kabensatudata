<?php

namespace App\Filament\Pages\Actions;

use App\Services\IksReportService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ExportIksReportAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Ekspor Laporan PDF');
        $this->icon('heroicon-o-document-arrow-down');
        $this->color('gray');
        $this->action(function () {
            $reportService = app(IksReportService::class);
            $overallData = $reportService->generateOverallReport();
            $villageData = $reportService->generateVillageReport();

            $data = [
                'overallData' => $overallData,
                'villageData' => $villageData,
                'exportDate' => now()->format('d F Y'),
            ];

            $pdf = Pdf::loadView('reports.iks-pdf', $data);

            $filename = 'laporan-iks-' . now()->format('Y-m-d') . '.pdf';
            $path = 'public/reports/' . $filename;

            Storage::put($path, $pdf->output());

            Notification::make()
                ->title('Laporan IKS berhasil diekspor')
                ->success()
                ->send();

            return Storage::download($path, $filename, [
                'Content-Type' => 'application/pdf',
            ]);
        });
    }

    public static function getDefaultName(): ?string
    {
        return 'exportIksReport';
    }
}
