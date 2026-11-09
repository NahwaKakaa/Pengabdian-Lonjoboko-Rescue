<?php

namespace App\Filament\Resources\HamletPopulationResource\Pages;

use App\Filament\Resources\HamletPopulationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHamletPopulation extends CreateRecord
{
    protected static string $resource = HamletPopulationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
