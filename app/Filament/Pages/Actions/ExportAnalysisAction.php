<?php
// app/Filament/Pages/Actions/ExportAnalysisAction.php

namespace App\Filament\Pages\Actions;

use Filament\Actions\Action;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Storage;

class ExportAnalysisAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Export Data')
            ->icon('heroicon-o-document-arrow-down')
            ->action(function () {
                $livewire = $this->getLivewire();

                if (empty($livewire->results)) {
                    return;
                }

                $analyticsService = app(\App\Services\AnalyticsService::class);
                $filename = $analyticsService->exportToCsv(
                    collect($livewire->results['data']),
                    $livewire->results['columns']
                );

                // Redirect ke URL download
                return redirect()->to(Storage::url($filename));
            })
            ->visible(fn($livewire) => !empty($livewire->results));
    }
}
