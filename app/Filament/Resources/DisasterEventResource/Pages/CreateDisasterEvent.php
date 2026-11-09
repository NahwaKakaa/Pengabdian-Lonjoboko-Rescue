<?php

namespace App\Filament\Resources\DisasterEventResource\Pages;

use App\Filament\Resources\DisasterEventResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDisasterEvent extends CreateRecord
{
    protected static string $resource = DisasterEventResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
