<?php

namespace App\Filament\Resources\DisasterEventResource\Pages;

use App\Filament\Resources\DisasterEventResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDisasterEvent extends EditRecord
{
    protected static string $resource = DisasterEventResource::class;

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
