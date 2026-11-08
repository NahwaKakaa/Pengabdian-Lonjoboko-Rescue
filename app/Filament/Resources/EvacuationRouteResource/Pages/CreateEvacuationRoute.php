<?php

namespace App\Filament\Resources\EvacuationRouteResource\Pages;

use App\Filament\Resources\EvacuationRouteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEvacuationRoute extends CreateRecord
{
    protected static string $resource = EvacuationRouteResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
