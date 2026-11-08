<?php

namespace App\Filament\Resources\HazardZoneResource\Pages;

use App\Filament\Resources\HazardZoneResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHazardZone extends EditRecord
{
    protected static string $resource = HazardZoneResource::class;

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
