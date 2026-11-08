<?php

namespace App\Filament\Resources\EmergencyContactResource\Pages;

use App\Filament\Resources\EmergencyContactResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmergencyContact extends CreateRecord
{
    protected static string $resource = EmergencyContactResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
