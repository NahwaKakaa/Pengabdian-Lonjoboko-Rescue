<?php

namespace App\Models;

use App\Enums\FacilityCategory;
use App\Models\Concerns\HasLocation;
use App\Models\Concerns\HasVerification;
use Illuminate\Database\Eloquent\Model;

/** Fasilitas umum dan kesehatan yang ditampilkan di peta. */
class Facility extends Model
{
    use HasLocation, HasVerification;

    protected $fillable = [
        'name',
        'category',
        'address',
        'phone',
        'opening_hours',
        'location',
        'status',
        'review_note',
    ];

    protected function casts(): array
    {
        return [
            'category' => FacilityCategory::class,
        ];
    }
}
