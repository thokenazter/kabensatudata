<?php

namespace App\Filament\Resources\BuildingResource\Pages;

use Filament\Actions;
use App\Filament\Resources\FamilyResource;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\BuildingResource;

class CreateBuilding extends CreateRecord
{
    protected static string $resource = BuildingResource::class;

    protected function getRedirectUrl(): string
    {
        // Redirect ke halaman edit building yang baru dibuat
        // return $this->getResource()::getUrl('index');

        // Redirect ke halaman create Family
        return FamilyResource::getUrl('create');
    }
}
