<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Peran pengguna panel admin. */
enum UserRole: string implements HasColor, HasLabel
{
    case Admin = 'admin';
    case Kader = 'kader';

    public function getLabel(): string
    {
        return match ($this) {
            self::Admin => 'Admin Desa',
            self::Kader => 'Kader',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Admin => 'danger',
            self::Kader => 'info',
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
