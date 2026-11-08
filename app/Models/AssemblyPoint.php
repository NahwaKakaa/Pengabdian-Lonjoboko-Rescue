<?php

namespace App\Models;

use App\Enums\AssemblyPointType;
use App\Models\Concerns\HasLocation;
use App\Models\Concerns\HasVerification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Titik kumpul atau tempat pengungsian. */
class AssemblyPoint extends Model
{
    use HasLocation, HasVerification;

    protected $fillable = [
        'name',
        'type',
        'capacity',
        'address',
        'facilities',
        'location',
        'photo',
        'status',
        'review_note',
    ];

    protected function casts(): array
    {
        return [
            'type' => AssemblyPointType::class,
            'capacity' => 'integer',
        ];
    }

    public function routes(): HasMany
    {
        return $this->hasMany(EvacuationRoute::class);
    }
}
