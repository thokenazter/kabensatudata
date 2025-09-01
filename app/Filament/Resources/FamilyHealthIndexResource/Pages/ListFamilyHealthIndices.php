<?php

namespace App\Filament\Resources\FamilyHealthIndexResource\Pages;

use App\Filament\Resources\FamilyHealthIndexResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFamilyHealthIndices extends ListRecords
{
    protected static string $resource = FamilyHealthIndexResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
