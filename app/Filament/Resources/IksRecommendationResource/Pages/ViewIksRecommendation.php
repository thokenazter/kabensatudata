<?php

namespace App\Filament\Resources\IksRecommendationResource\Pages;

use App\Filament\Resources\IksRecommendationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewIksRecommendation extends ViewRecord
{
    protected static string $resource = IksRecommendationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            Actions\Action::make('mark_in_progress')
                ->label('Tandai Dalam Proses')
                ->icon('heroicon-o-play')
                ->color('warning')
                ->action(fn() => $this->record->markAsInProgress())
                ->visible(fn() => $this->record->status === 'pending'),

            Actions\Action::make('mark_completed')
                ->label('Tandai Selesai')
                ->icon('heroicon-o-check')
                ->color('success')
                ->action(fn(array $data) => $this->record->markAsCompleted($data['notes'] ?? null))
                ->form([
                    \Filament\Forms\Components\Textarea::make('notes')
                        ->label('Catatan Penyelesaian')
                        ->placeholder('Tambahkan catatan tentang penyelesaian rekomendasi ini')
                        ->maxLength(500),
                ])
                ->visible(fn() => in_array($this->record->status, ['pending', 'in_progress'])),

            Actions\Action::make('mark_rejected')
                ->label('Tolak Rekomendasi')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->action(fn(array $data) => $this->record->markAsRejected($data['notes'] ?? null))
                ->form([
                    \Filament\Forms\Components\Textarea::make('notes')
                        ->label('Alasan Penolakan')
                        ->required()
                        ->placeholder('Tambahkan alasan penolakan rekomendasi ini')
                        ->maxLength(500),
                ])
                ->visible(fn() => in_array($this->record->status, ['pending', 'in_progress'])),
        ];
    }
}
