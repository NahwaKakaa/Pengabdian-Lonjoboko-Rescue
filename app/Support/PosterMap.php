<?php

namespace App\Support;

use App\Models\AssemblyPoint;
use App\Models\EvacuationRoute;
use App\Models\HazardZone;
use Illuminate\Support\Collection;

/**
 * Mengubah koordinat lat/lng menjadi koordinat gambar SVG untuk peta
 * skematis di poster cetak (tanpa ubin peta, jadi tajam saat dicetak
 * dan tetap tampil tanpa internet).
 */
class PosterMap
{
    public const WIDTH = 1000;

    /**
     * @param  array<string, mixed>|null  $boundary  GeoJSON FeatureCollection batas desa
     * @param  Collection<int, AssemblyPoint>  $points
     * @param  Collection<int, EvacuationRoute>  $routes
     * @param  Collection<int, HazardZone>  $hazards
     * @return array<string, mixed>
     */
    public static function make(?array $boundary, Collection $points, Collection $routes, Collection $hazards): array
    {
        // Semua koordinat dalam [lat, lng]
        $rings = collect($boundary['features'] ?? [])
            ->flatMap(fn ($f) => self::polygonRings($f['geometry'] ?? []))
            ->map(fn ($ring) => array_map(fn ($c) => [(float) $c[1], (float) $c[0]], $ring))
            ->values();

        $all = $rings->flatten(1)
            ->merge($points->map(fn ($p) => [$p->lat, $p->lng]))
            ->merge($routes->flatMap(fn ($r) => $r->path))
            ->merge($hazards->map(fn ($h) => [$h->lat, $h->lng]))
            ->filter(fn ($c) => is_array($c) && count($c) >= 2)
            ->values();

        if ($all->isEmpty()) {
            $center = config('lonjoboko.map.center');
            $all = collect([[$center['lat'] - 0.01, $center['lng'] - 0.01], [$center['lat'] + 0.01, $center['lng'] + 0.01]]);
        }

        $minLat = $all->min(0);
        $maxLat = $all->max(0);
        $minLng = $all->min(1);
        $maxLng = $all->max(1);

        // Proyeksi sederhana: bujur dikoreksi cos(lintang) supaya bentuk tidak gepeng.
        $k = cos(deg2rad(($minLat + $maxLat) / 2));
        $spanX = max(($maxLng - $minLng) * $k, 1e-6);
        $spanY = max($maxLat - $minLat, 1e-6);
        $pad = 0.06;

        $width = self::WIDTH;
        $height = (int) round($width * min(max($spanY / $spanX, 0.45), 1.1));
        $scale = min(($width * (1 - 2 * $pad)) / $spanX, ($height * (1 - 2 * $pad)) / $spanY);
        $offX = ($width - $spanX * $scale) / 2;
        $offY = ($height - $spanY * $scale) / 2;

        $project = fn (float $lat, float $lng) => [
            round($offX + ($lng - $minLng) * $k * $scale, 1),
            round($offY + ($maxLat - $lat) * $scale, 1),
        ];
        $toPath = fn (array $pts) => collect($pts)->map(fn ($c) => implode(',', $project((float) $c[0], (float) $c[1])))->implode(' ');

        // Meter per piksel (1 derajat lintang ≈ 111.320 m)
        $metersPerPx = 111320 / $scale;
        $scaleMeters = collect([100, 200, 250, 500, 1000, 2000])->first(fn ($m) => $m / $metersPerPx >= 90) ?? 2000;

        return [
            'width' => $width,
            'height' => $height,
            'boundary' => $rings->map(fn ($ring) => $toPath($ring))->all(),
            'routes' => $routes->map(fn (EvacuationRoute $r) => [
                'points' => $toPath($r->path),
                'hazard' => $r->hazard_type->value,
            ])->all(),
            'points' => $points->values()->map(function (AssemblyPoint $p, int $i) use ($project) {
                [$x, $y] = $project($p->lat, $p->lng);

                return ['x' => $x, 'y' => $y, 'number' => $i + 1];
            })->all(),
            'hazards' => $hazards->map(function (HazardZone $h) use ($project, $metersPerPx) {
                [$x, $y] = $project($h->lat, $h->lng);

                return [
                    'x' => $x,
                    'y' => $y,
                    'r' => round(max(($h->radius_m ?: 80) / $metersPerPx, 7), 1),
                    'type' => $h->hazard_type->value,
                ];
            })->all(),
            'scale' => [
                'meters' => $scaleMeters,
                'px' => round($scaleMeters / $metersPerPx, 1),
                'label' => $scaleMeters >= 1000 ? ($scaleMeters / 1000).' km' : $scaleMeters.' m',
            ],
        ];
    }

    /** @return list<list<array{0: float, 1: float}>> */
    private static function polygonRings(array $geometry): array
    {
        return match ($geometry['type'] ?? null) {
            'Polygon' => [$geometry['coordinates'][0] ?? []],
            'MultiPolygon' => array_map(fn ($poly) => $poly[0] ?? [], $geometry['coordinates'] ?? []),
            default => [],
        };
    }
}
