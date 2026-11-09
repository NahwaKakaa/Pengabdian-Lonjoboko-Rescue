<?php

namespace App\Filament\Resources\HamletPopulationResource\Pages;

use App\Filament\Resources\HamletPopulationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHamletPopulations extends ListRecords
{
    protected static string $resource = HamletPopulationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
