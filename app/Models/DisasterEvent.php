<?php

namespace App\Models;

use App\Enums\HazardType;
use App\Models\Concerns\HasLocation;
use App\Models\Concerns\HasVerification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Riwayat kejadian bencana di desa beserta jumlah korban (tanpa nama). */
class DisasterEvent extends Model
{
    use HasLocation, HasVerification;

    protected $fillable = [
        'title', 'hazard_type', 'occurred_on', 'hamlet', 'location',
        'description', 'impact',
        'deaths', 'injured', 'missing', 'displaced', 'houses_damaged',
        'source', 'source_url', 'photo', 'status', 'review_note',
    ];

    public const VICTIMS = [
        'deaths' => 'Meninggal',
        'injured' => 'Luka',
        'missing' => 'Hilang',
        'displaced' => 'Mengungsi',
        'houses_damaged' => 'Rumah rusak',
    ];

    protected function casts(): array
    {
        return [
            'hazard_type' => HazardType::class,
            'occurred_on' => 'date',
            ...collect(array_keys(self::VICTIMS))->mapWithKeys(fn ($c) => [$c => 'integer'])->all(),
        ];
    }

    public function reports(): HasMany
    {
        return $this->hasMany(IncidentReport::class);
    }

    public function hasVictims(): bool
    {
        return collect(array_keys(self::VICTIMS))->contains(fn ($c) => $this->{$c} > 0);
    }
}
