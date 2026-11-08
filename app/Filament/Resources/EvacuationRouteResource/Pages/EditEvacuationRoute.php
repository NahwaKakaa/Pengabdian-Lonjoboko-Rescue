<?php

namespace App\Filament\Resources\EvacuationRouteResource\Pages;

use App\Filament\Resources\EvacuationRouteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEvacuationRoute extends EditRecord
{
    protected static string $resource = EvacuationRouteResource::class;

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
