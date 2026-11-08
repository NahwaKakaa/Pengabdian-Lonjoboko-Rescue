<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Status verifikasi data peta sebelum tampil ke publik. */
enum VerificationStatus: string implements HasColor, HasLabel
{
    case Menunggu = 'menunggu';
    case Terverifikasi = 'terverifikasi';
    case Ditolak = 'ditolak';

    public function getLabel(): string
    {
        return match ($this) {
            self::Menunggu => 'Menunggu Verifikasi',
            self::Terverifikasi => 'Terverifikasi',
            self::Ditolak => 'Ditolak',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Menunggu => 'warning',
            self::Terverifikasi => 'success',
            self::Ditolak => 'danger',
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
