<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Alur laporan warga:
 * baru -> dicek (kader Karang Taruna sudah ke lokasi) -> sah / tidak sesuai (admin desa).
 */
enum ReportStatus: string implements HasColor, HasLabel
{
    case Baru = 'baru';
    case Dicek = 'dicek';
    case Sah = 'sah';
    case TidakSesuai = 'tidak_sesuai';

    public function getLabel(): string
    {
        return match ($this) {
            self::Baru => 'Baru masuk',
            self::Dicek => 'Sudah dicek kader',
            self::Sah => 'Disahkan desa',
            self::TidakSesuai => 'Tidak sesuai',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Baru => 'danger',
            self::Dicek => 'warning',
            self::Sah => 'success',
            self::TidakSesuai => 'gray',
        };
    }
}
