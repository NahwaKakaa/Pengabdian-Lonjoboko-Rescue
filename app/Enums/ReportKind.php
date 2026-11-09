<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Jenis kejadian yang bisa dilaporkan warga. */
enum ReportKind: string implements HasColor, HasLabel
{
    case Banjir = 'banjir';
    case Longsor = 'longsor';
    case TandaBahaya = 'tanda_bahaya';
    case Lainnya = 'lainnya';

    public function getLabel(): string
    {
        return match ($this) {
            self::Banjir => 'Banjir / air sungai naik',
            self::Longsor => 'Tanah longsor',
            self::TandaBahaya => 'Tanda bahaya (retakan tanah, tebing tergerus)',
            self::Lainnya => 'Kejadian lain',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Banjir => 'Banjir',
            self::Longsor => 'Longsor',
            self::TandaBahaya => 'Tanda bahaya',
            self::Lainnya => 'Lainnya',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Banjir => 'info',
            self::Longsor => 'warning',
            self::TandaBahaya => 'danger',
            self::Lainnya => 'gray',
        };
    }

    /** Jenis bencana untuk riwayat kejadian bila laporan disahkan. */
    public function hazardType(): HazardType
    {
        return $this === self::Banjir ? HazardType::Banjir : HazardType::Longsor;
    }
}
