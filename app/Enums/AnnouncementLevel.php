<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Tingkat informasi kebencanaan. */
enum AnnouncementLevel: string implements HasColor, HasLabel
{
    case Informasi = 'informasi';
    case Waspada = 'waspada';
    case Darurat = 'darurat';

    public function getLabel(): string
    {
        return match ($this) {
            self::Informasi => 'Informasi',
            self::Waspada => 'Peringatan',
            self::Darurat => 'Darurat',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Informasi => 'gray',
            self::Waspada => 'warning',
            self::Darurat => 'danger',
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
