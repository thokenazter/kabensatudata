<?php

namespace App\Filament\Resources\MedicalRecordResource\Pages;

use App\Filament\Resources\MedicalRecordResource;
use Filament\Resources\Pages\Page;

class ViewMedicalRecord extends Page
{
    protected static string $resource = MedicalRecordResource::class;

    protected static string $view = 'filament.resources.medical-record-resource.pages.view-medical-record';
}
