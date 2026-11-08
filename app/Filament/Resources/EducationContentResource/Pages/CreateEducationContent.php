<?php

namespace App\Filament\Resources\EducationContentResource\Pages;

use App\Filament\Resources\EducationContentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEducationContent extends CreateRecord
{
    protected static string $resource = EducationContentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
