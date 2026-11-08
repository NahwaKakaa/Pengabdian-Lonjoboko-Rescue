<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Jenis bencana yang menjadi fokus program. */
enum HazardType: string implements HasColor, HasLabel
{
    case Banjir = 'banjir';
    case Longsor = 'longsor';

    public function getLabel(): string
    {
        return match ($this) {
            self::Banjir => 'Banjir',
            self::Longsor => 'Tanah Longsor',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Banjir => 'info',
            self::Longsor => 'warning',
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
