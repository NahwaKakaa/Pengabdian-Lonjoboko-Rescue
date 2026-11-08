<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Topik konten edukasi. */
enum EducationTopic: string implements HasColor, HasLabel
{
    case Banjir = 'banjir';
    case Longsor = 'longsor';
    case Umum = 'umum';

    public function getLabel(): string
    {
        return match ($this) {
            self::Banjir => 'Banjir',
            self::Longsor => 'Tanah Longsor',
            self::Umum => 'Kesiapsiagaan Umum',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Banjir => 'info',
            self::Longsor => 'warning',
            self::Umum => 'gray',
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
