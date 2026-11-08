<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Kader dan relawan Karang Taruna Tangguh Bencana. */
class Volunteer extends Model
{
    protected $fillable = [
        'name',
        'position',
        'hamlet',
        'phone',
        'show_phone',
        'photo',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'show_phone' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function initials(): string
    {
        return collect(explode(' ', trim($this->name)))
            ->filter()
            ->take(2)
            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
    }
}
