<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Jenis konten edukasi. */
enum EducationCategory: string implements HasColor, HasLabel
{
    case Modul = 'modul';
    case Video = 'video';
    case Infografis = 'infografis';
    case Panduan = 'panduan';

    public function getLabel(): string
    {
        return match ($this) {
            self::Modul => 'Modul',
            self::Video => 'Video',
            self::Infografis => 'Infografis',
            self::Panduan => 'Panduan',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Modul => 'primary',
            self::Video => 'danger',
            self::Infografis => 'success',
            self::Panduan => 'warning',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->getLabel()])
            ->all();
    }
}
