<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Foto Beranda. Admin bisa mengunggah foto asli desa di menu Profil Desa;
 * kalau belum, memakai gambar ilustrasi bawaan.
 */
class SitePhotos
{
    /** @return array{url: ?string, custom: bool} */
    public static function hero(): array
    {
        $path = self::setting('photo_hero');

        return ['url' => $path ? Storage::disk('public')->url($path) : null, 'custom' => (bool) $path];
    }

    /** @return array{url: string, custom: bool, caption: string, alt: string} */
    public static function profile(): array
    {
        $path = self::setting('photo_profile');

        return [
            'url' => $path ? Storage::disk('public')->url($path) : asset('images/sawah-desa.jpg'),
            'custom' => (bool) $path,
            'caption' => self::setting('photo_profile_caption') ?: ($path ? 'Desa '.self::village() : 'Persawahan di lereng perbukitan, ciri wilayah seperti Kec. Parangloe'),
            'alt' => $path ? (self::setting('photo_profile_caption') ?: 'Foto Desa '.self::village()) : 'Ilustrasi petak sawah berair di lereng bukit',
        ];
    }

    /** @return array{url: string, custom: bool, caption: string, alt: string} */
    public static function landscape(): array
    {
        $path = self::setting('photo_landscape');

        return [
            'url' => $path ? Storage::disk('public')->url($path) : asset('images/hero-mountains.jpg'),
            'custom' => (bool) $path,
            'caption' => self::setting('photo_landscape_caption') ?: ($path ? 'Bentang alam Desa '.self::village() : 'Sawah terasering di lereng perbukitan'),
            'alt' => $path ? (self::setting('photo_landscape_caption') ?: 'Bentang alam Desa '.self::village()) : 'Ilustrasi sawah terasering di lereng perbukitan',
        ];
    }

    private static function village(): string
    {
        return self::setting('village_name') ?: config('lonjoboko.village');
    }

    private static function setting(string $key): ?string
    {
        try {
            return Setting::get($key);
        } catch (Throwable) {
            return null;
        }
    }
}
