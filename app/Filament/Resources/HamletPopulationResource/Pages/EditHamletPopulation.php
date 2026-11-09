<?php

namespace App\Filament\Resources\HamletPopulationResource\Pages;

use App\Filament\Resources\HamletPopulationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHamletPopulation extends EditRecord
{
    protected static string $resource = HamletPopulationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
