<?php

namespace App\Filament\Resources\HazardZoneResource\Pages;

use App\Filament\Resources\HazardZoneResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHazardZone extends CreateRecord
{
    protected static string $resource = HazardZoneResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
