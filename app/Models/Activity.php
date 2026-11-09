<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Kegiatan kesiapsiagaan Karang Taruna (sosialisasi, simulasi, cek jalur). */
class Activity extends Model
{
    protected $fillable = ['title', 'held_on', 'place', 'description', 'photo', 'is_published'];

    protected function casts(): array
    {
        return [
            'held_on' => 'date',
            'is_published' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $activity) {
            $activity->created_by ??= auth()->id();
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
