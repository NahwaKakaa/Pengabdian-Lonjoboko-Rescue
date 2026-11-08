<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Kategori fasilitas umum. */
enum FacilityCategory: string implements HasColor, HasLabel
{
    case Kesehatan = 'kesehatan';
    case Pendidikan = 'pendidikan';
    case Ibadah = 'ibadah';
    case Pemerintahan = 'pemerintahan';
    case Umum = 'umum';

    public function getLabel(): string
    {
        return match ($this) {
            self::Kesehatan => 'Fasilitas Kesehatan',
            self::Pendidikan => 'Sekolah',
            self::Ibadah => 'Tempat Ibadah',
            self::Pemerintahan => 'Kantor Pemerintahan',
            self::Umum => 'Fasilitas Umum',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Kesehatan => 'danger',
            self::Pendidikan => 'info',
            self::Ibadah => 'success',
            self::Pemerintahan => 'primary',
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
