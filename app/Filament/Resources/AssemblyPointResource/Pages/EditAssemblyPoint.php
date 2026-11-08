<?php

namespace App\Filament\Resources\AssemblyPointResource\Pages;

use App\Filament\Resources\AssemblyPointResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAssemblyPoint extends EditRecord
{
    protected static string $resource = AssemblyPointResource::class;

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
