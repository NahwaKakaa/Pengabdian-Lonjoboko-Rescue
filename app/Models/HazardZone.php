<?php

namespace App\Models;

use App\Enums\HazardType;
use App\Enums\RiskLevel;
use App\Models\Concerns\HasLocation;
use App\Models\Concerns\HasVerification;
use Illuminate\Database\Eloquent\Model;

/** Lokasi rawan banjir atau tanah longsor. */
class HazardZone extends Model
{
    use HasLocation, HasVerification;

    protected $fillable = [
        'name',
        'hazard_type',
        'risk_level',
        'description',
        'location',
        'radius_m',
        'photo',
        'status',
        'review_note',
    ];

    protected function casts(): array
    {
        return [
            'hazard_type' => HazardType::class,
            'risk_level' => RiskLevel::class,
            'radius_m' => 'integer',
        ];
    }
}
