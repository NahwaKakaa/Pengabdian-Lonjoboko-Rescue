<?php

namespace App\Filament\Resources\HazardZoneResource\Pages;

use App\Filament\Resources\HazardZoneResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHazardZones extends ListRecords
{
    protected static string $resource = HazardZoneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
