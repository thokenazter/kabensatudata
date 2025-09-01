<?php

namespace App\Filament\Resources\FamilyMemberResource\Pages;

use App\Filament\Resources\FamilyMemberResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFamilyMember extends CreateRecord
{
    protected static string $resource = FamilyMemberResource::class;
    protected function getRedirectUrl(): string
    {
        // Redirect ke halaman edit building yang baru dibuat
        return $this->getResource()::getUrl('index');
    }
}
