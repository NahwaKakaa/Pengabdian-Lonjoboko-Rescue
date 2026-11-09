<?php

namespace App\Filament\Resources\DisasterEventResource\Pages;

use App\Filament\Resources\DisasterEventResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDisasterEvents extends ListRecords
{
    protected static string $resource = DisasterEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
