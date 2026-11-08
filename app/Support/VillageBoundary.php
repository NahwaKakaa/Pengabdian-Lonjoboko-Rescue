<?php

namespace App\Support;

use App\Models\Setting;
use Throwable;

/**
 * Batas wilayah desa. Bisa digambar ulang atau diunggah admin di menu
 * "Pengaturan Peta & Sumber"; kalau belum pernah diubah, memakai file
 * bawaan resources/data/lonjoboko-boundary.json (perkiraan).
 *
 * Di dalam aplikasi batas disimpan sebagai satu cincin titik [[lat, lng], ...].
 */
class VillageBoundary
{
    /** GeoJSON FeatureCollection untuk peta publik dan poster. */
    public static function geojson(): ?array
    {
        $ring = self::customRing();

        if ($ring === null) {
            return self::defaultGeojson();
        }

        $coords = array_map(fn ($p) => [$p[1], $p[0]], $ring);
        $coords[] = $coords[0];

        return [
            'type' => 'FeatureCollection',
            'features' => [[
                'type' => 'Feature',
                'properties' => [
                    'name' => 'Desa '.(Setting::get('village_name') ?: config('lonjoboko.village')),
                    'source' => 'Diatur pengelola desa',
                ],
                'geometry' => ['type' => 'Polygon', 'coordinates' => [$coords]],
            ]],
        ];
    }

    /** Batas yang dipakai sekarang (untuk peta admin). @return list<array{0: float, 1: float}> */
    public static function ring(): array
    {
        return self::customRing() ?? self::defaultRing();
    }

    public static function isCustom(): bool
    {
        return self::customRing() !== null;
    }

    /** @return list<array{0: float, 1: float}> */
    public static function defaultRing(): array
    {
        return self::ringFromGeojson(self::defaultGeojson()) ?? [];
    }

    /**
     * Ambil cincin luar terbesar dari GeoJSON apa pun (FeatureCollection,
     * Feature, Polygon, MultiPolygon). Kembali null bila tidak ada poligon.
     *
     * @return list<array{0: float, 1: float}>|null
     */
    public static function ringFromGeojson(mixed $geo): ?array
    {
        if (! is_array($geo)) {
            return null;
        }

        $polygons = [];
        $walk = function (array $node) use (&$walk, &$polygons) {
            switch ($node['type'] ?? null) {
                case 'FeatureCollection':
                    foreach ($node['features'] ?? [] as $f) {
                        is_array($f) && $walk($f);
                    }
                    break;
                case 'Feature':
                    is_array($node['geometry'] ?? null) && $walk($node['geometry']);
                    break;
                case 'GeometryCollection':
                    foreach ($node['geometries'] ?? [] as $g) {
                        is_array($g) && $walk($g);
                    }
                    break;
                case 'Polygon':
                    $polygons[] = $node['coordinates'][0] ?? [];
                    break;
                case 'MultiPolygon':
                    foreach ($node['coordinates'] ?? [] as $poly) {
                        $polygons[] = $poly[0] ?? [];
                    }
                    break;
            }
        };
        $walk($geo);

        $rings = array_values(array_filter(array_map(function ($ring) {
            $pts = [];
            foreach ((array) $ring as $c) {
                if (is_array($c) && is_numeric($c[0] ?? null) && is_numeric($c[1] ?? null)) {
                    $pts[] = [round((float) $c[1], 6), round((float) $c[0], 6)]; // [lat, lng]
                }
            }
            if (count($pts) > 1 && $pts[0] === end($pts)) {
                array_pop($pts);
            }

            return count($pts) >= 3 ? $pts : null;
        }, $polygons)));

        if (! $rings) {
            return null;
        }

        usort($rings, fn ($a, $b) => count($b) <=> count($a));

        return $rings[0];
    }

    /**
     * Baca batas dari isi file yang diunggah: GeoJSON, atau KML dari
     * Google My Maps / Google Earth (cincin terpanjang yang dipakai).
     *
     * @return list<array{0: float, 1: float}>|null
     */
    public static function ringFromFile(string $contents): ?array
    {
        $json = json_decode($contents, true);

        if (is_array($json)) {
            return self::ringFromGeojson($json);
        }

        if (! preg_match_all('/<coordinates>(.*?)<\/coordinates>/is', $contents, $matches)) {
            return null;
        }

        $rings = array_filter(array_map(function (string $block) {
            $pts = [];
            foreach (preg_split('/\s+/', trim($block)) as $tuple) {
                $parts = explode(',', $tuple);
                if (count($parts) >= 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                    $pts[] = [round((float) $parts[1], 6), round((float) $parts[0], 6)];
                }
            }
            if (count($pts) > 1 && $pts[0] === end($pts)) {
                array_pop($pts);
            }

            return count($pts) >= 3 ? $pts : null;
        }, $matches[1]));

        if (! $rings) {
            return null;
        }

        usort($rings, fn ($a, $b) => count($b) <=> count($a));

        return array_values($rings)[0];
    }

    /** @return list<array{0: float, 1: float}>|null */
    public static function normalizeRing(mixed $ring): ?array
    {
        if (! is_array($ring)) {
            return null;
        }

        $pts = [];
        foreach ($ring as $p) {
            if (is_array($p) && is_numeric($p[0] ?? null) && is_numeric($p[1] ?? null)
                && abs((float) $p[0]) <= 90 && abs((float) $p[1]) <= 180) {
                $pts[] = [round((float) $p[0], 6), round((float) $p[1], 6)];
            }
        }

        return count($pts) >= 3 ? $pts : null;
    }

    /** @return list<array{0: float, 1: float}>|null */
    private static function customRing(): ?array
    {
        try {
            return self::normalizeRing(json_decode((string) Setting::get('village_boundary'), true));
        } catch (Throwable) {
            return null;
        }
    }

    private static function defaultGeojson(): ?array
    {
        $file = resource_path('data/lonjoboko-boundary.json');

        return file_exists($file) ? json_decode((string) file_get_contents($file), true) : null;
    }
}
