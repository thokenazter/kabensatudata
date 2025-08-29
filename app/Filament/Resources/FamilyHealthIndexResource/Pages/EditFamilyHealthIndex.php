<?php

namespace App\Filament\Resources\FamilyHealthIndexResource\Pages;

use App\Filament\Resources\FamilyHealthIndexResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFamilyHealthIndex extends EditRecord
{
    protected static string $resource = FamilyHealthIndexResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
