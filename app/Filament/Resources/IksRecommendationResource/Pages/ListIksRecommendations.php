<?php

namespace App\Filament\Resources\IksRecommendationResource\Pages;

use App\Filament\Resources\IksRecommendationResource;
use App\Models\Family;
use App\Services\IksRecommendationService;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListIksRecommendations extends ListRecords
{
    protected static string $resource = IksRecommendationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('generate_recommendations')
                ->label('Buat Rekomendasi Otomatis')
                ->icon('heroicon-o-sparkles')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\Select::make('family_id')
                        ->label('Keluarga')
                        ->relationship('family', 'head_name', fn($query) => $query->has('healthIndex'))
                        ->searchable()
                        ->preload()
                        ->required(),
                ])
                ->action(function (array $data) {
                    $family = Family::findOrFail($data['family_id']);
                    $recommendationService = app(IksRecommendationService::class);

                    $recommendations = $recommendationService->generateRecommendations($family);

                    if (count($recommendations) > 0) {
                        Notification::make()
                            ->title('Rekomendasi berhasil dibuat')
                            ->body('Berhasil membuat ' . count($recommendations) . ' rekomendasi untuk keluarga ' . $family->head_name)
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Tidak ada rekomendasi yang perlu dibuat')
                            ->body('Semua indikator yang relevan sudah terpenuhi untuk keluarga ' . $family->head_name)
                            ->warning()
                            ->send();
                    }
                }),
        ];
    }
}
