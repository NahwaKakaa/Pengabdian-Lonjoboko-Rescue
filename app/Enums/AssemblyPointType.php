<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Jenis titik aman. */
enum AssemblyPointType: string implements HasColor, HasLabel
{
    case TitikKumpul = 'titik_kumpul';
    case Pengungsian = 'pengungsian';

    public function getLabel(): string
    {
        return match ($this) {
            self::TitikKumpul => 'Titik Kumpul',
            self::Pengungsian => 'Tempat Pengungsian',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::TitikKumpul => 'success',
            self::Pengungsian => 'primary',
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
