<?php

namespace App\Filament\Resources\FamilyResource\Pages;

use Filament\Actions;
use App\Filament\Resources\FamilyResource;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\FamilyMemberResource;

class CreateFamily extends CreateRecord
{
    protected static string $resource = FamilyResource::class;

    protected function getRedirectUrl(): string
    {
        // redirect ke family member
        return FamilyMemberResource::getUrl('create');
    }
}
