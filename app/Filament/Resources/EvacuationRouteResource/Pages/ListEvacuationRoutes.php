<?php

namespace App\Filament\Resources\EvacuationRouteResource\Pages;

use App\Filament\Resources\EvacuationRouteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEvacuationRoutes extends ListRecords
{
    protected static string $resource = EvacuationRouteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
