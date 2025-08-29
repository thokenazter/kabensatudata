<?php

namespace App\Filament\Resources\IksRecommendationResource\Pages;

use App\Filament\Resources\IksRecommendationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIksRecommendation extends EditRecord
{
    protected static string $resource = IksRecommendationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
