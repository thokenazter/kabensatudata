<?php

namespace App\Filament\Resources\FamilyResource\Pages;

use App\Filament\Resources\FamilyResource;
use App\Filament\Resources\FamilyHealthIndexResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;
use Filament\Notifications\Notification;

class ViewFamily extends ViewRecord
{
    protected static string $resource = FamilyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('calculateIks')
                ->label('Hitung IKS')
                ->icon('heroicon-o-heart')
                ->color('success')
                ->action(function () {
                    $family = $this->getRecord();
                    $iksData = $family->calculateIks();
                    $family->saveIksResult($iksData);

                    Notification::make()
                        ->title('IKS berhasil dihitung')
                        ->body("Nilai IKS: " . number_format($iksData['iks_value'] * 100, 2) . "% (" . $iksData['health_status'] . ")")
                        ->success()
                        ->send();

                    // Redirect ke halaman view IKS
                    if ($healthIndex = $family->healthIndex) {
                        $this->redirect(FamilyHealthIndexResource::getUrl('view', ['record' => $healthIndex]));
                    }
                }),

            Actions\Action::make('viewIks')
                ->label('Lihat IKS')
                ->icon('heroicon-o-eye')
                ->color('primary')
                ->action(function () {
                    $family = $this->getRecord();
                    if ($healthIndex = $family->healthIndex) {
                        $this->redirect(FamilyHealthIndexResource::getUrl('view', ['record' => $healthIndex]));
                    } else {
                        Notification::make()
                            ->title('IKS belum dihitung')
                            ->body("Silahkan hitung IKS terlebih dahulu.")
                            ->warning()
                            ->send();
                    }
                })
                ->visible(fn() => $this->getRecord()->healthIndex !== null),

            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
