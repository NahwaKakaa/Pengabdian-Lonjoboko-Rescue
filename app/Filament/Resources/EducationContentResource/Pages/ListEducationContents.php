<?php

namespace App\Filament\Resources\EducationContentResource\Pages;

use App\Filament\Resources\EducationContentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEducationContents extends ListRecords
{
    protected static string $resource = EducationContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
