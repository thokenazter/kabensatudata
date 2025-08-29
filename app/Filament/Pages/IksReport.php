<?php

namespace App\Filament\Pages;

use App\Services\IksReportService;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\Facades\FilamentView;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\View\View;
use App\Filament\Pages\Actions;

class IksReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationLabel = 'Laporan IKS';

    protected static ?string $title = 'Laporan Indeks Keluarga Sehat';

    protected static ?string $navigationGroup = 'Kesehatan Keluarga';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.iks-report';

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public function getVillageReportData(): array
    {
        $reportService = app(IksReportService::class);
        return $reportService->generateVillageReport()->toArray();
    }

    public function getOverallReportData(): array
    {
        $reportService = app(IksReportService::class);
        return $reportService->generateOverallReport();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportIksReportAction::make(),
        ];
    }
}
