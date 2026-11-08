<?php

namespace App\Filament\Resources\EducationContentResource\Pages;

use App\Filament\Resources\EducationContentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEducationContent extends EditRecord
{
    protected static string $resource = EducationContentResource::class;

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
