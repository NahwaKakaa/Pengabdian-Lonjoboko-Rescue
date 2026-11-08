<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Kelompok kontak darurat. */
enum ContactCategory: string implements HasColor, HasLabel
{
    case Desa = 'desa';
    case Bpbd = 'bpbd';
    case Kesehatan = 'kesehatan';
    case Keamanan = 'keamanan';
    case Lainnya = 'lainnya';

    public function getLabel(): string
    {
        return match ($this) {
            self::Desa => 'Pemerintah Desa',
            self::Bpbd => 'BPBD & Instansi',
            self::Kesehatan => 'Kesehatan',
            self::Keamanan => 'Keamanan & SAR',
            self::Lainnya => 'Lainnya',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Desa => 'primary',
            self::Bpbd => 'warning',
            self::Kesehatan => 'danger',
            self::Keamanan => 'info',
            self::Lainnya => 'gray',
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
