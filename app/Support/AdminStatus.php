<?php

namespace App\Support;

use App\Models\Announcement;
use App\Models\AssemblyPoint;
use App\Models\EvacuationRoute;
use App\Models\Facility;
use App\Models\HazardZone;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Angka ringkas untuk topbar dan dasbor admin.
 * Semua query dibungkus try/catch supaya panel tetap terbuka
 * walau database sedang bermasalah.
 */
class AdminStatus
{
    /** Model data peta yang melalui alur verifikasi, beserta label dan warnanya. */
    public const MAP_MODELS = [
        HazardZone::class => ['label' => 'Lokasi rawan', 'tone' => 'hazard'],
        AssemblyPoint::class => ['label' => 'Titik kumpul', 'tone' => 'safe'],
        EvacuationRoute::class => ['label' => 'Jalur evakuasi', 'tone' => 'route'],
        Facility::class => ['label' => 'Fasilitas', 'tone' => 'facility'],
    ];

    public static function pendingCount(): int
    {
        return self::safely(
            fn () => collect(array_keys(self::MAP_MODELS))->sum(fn (string $model) => $model::pending()->count()),
            0,
        );
    }

    public static function verifiedCount(): int
    {
        return self::safely(
            fn () => collect(array_keys(self::MAP_MODELS))->sum(fn (string $model) => $model::verified()->count()),
            0,
        );
    }

    /** @return Collection<int, Announcement> */
    public static function activeAlerts(): Collection
    {
        return self::safely(fn () => Announcement::activeAlerts()->latest('published_at')->get(), collect());
    }

    /** Sapaan sesuai jam WITA. */
    public static function greeting(): string
    {
        $hour = (int) now('Asia/Makassar')->format('G');

        return match (true) {
            $hour >= 4 && $hour < 10 => 'Selamat pagi',
            $hour >= 10 && $hour < 15 => 'Selamat siang',
            $hour >= 15 && $hour < 18 => 'Selamat sore',
            default => 'Selamat malam',
        };
    }

    private static function safely(callable $callback, mixed $fallback): mixed
    {
        try {
            return $callback();
        } catch (Throwable) {
            return $fallback;
        }
    }
}
