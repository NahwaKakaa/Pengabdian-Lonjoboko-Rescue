<?php

namespace App\Filament\Resources\AssemblyPointResource\Pages;

use App\Filament\Resources\AssemblyPointResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAssemblyPoint extends CreateRecord
{
    protected static string $resource = AssemblyPointResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
