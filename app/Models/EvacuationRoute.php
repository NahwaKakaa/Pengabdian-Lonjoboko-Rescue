<?php

namespace App\Models;

use App\Enums\RouteHazard;
use App\Models\Concerns\HasVerification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Jalur evakuasi. Kolom `path` berisi daftar titik
 * [[lat, lng], [lat, lng], ...] dari awal ke tujuan.
 */
class EvacuationRoute extends Model
{
    use HasVerification;

    protected $fillable = [
        'name',
        'hazard_type',
        'path',
        'description',
        'assembly_point_id',
        'status',
        'review_note',
    ];

    protected function casts(): array
    {
        return [
            'hazard_type' => RouteHazard::class,
            'path' => 'array',
        ];
    }

    public function assemblyPoint(): BelongsTo
    {
        return $this->belongsTo(AssemblyPoint::class);
    }

    /** Panjang jalur dalam meter (rumus haversine). */
    public function lengthInMeters(): int
    {
        $points = $this->path ?? [];
        $total = 0.0;

        for ($i = 1; $i < count($points); $i++) {
            $total += self::haversine($points[$i - 1], $points[$i]);
        }

        return (int) round($total);
    }

    /**
     * @param  array{0: float, 1: float}  $a
     * @param  array{0: float, 1: float}  $b
     */
    private static function haversine(array $a, array $b): float
    {
        $r = 6371000;
        $lat1 = deg2rad((float) $a[0]);
        $lat2 = deg2rad((float) $b[0]);
        $dLat = $lat2 - $lat1;
        $dLng = deg2rad((float) $b[1] - (float) $a[1]);

        $h = sin($dLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dLng / 2) ** 2;

        return 2 * $r * asin(min(1, sqrt($h)));
    }
}
