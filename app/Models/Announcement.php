<?php

namespace App\Models;

use App\Enums\AnnouncementLevel;
use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Informasi terkini dan peringatan kebencanaan desa. */
class Announcement extends Model
{
    use HasUniqueSlug;

    protected $fillable = [
        'title',
        'slug',
        'level',
        'body',
        'source',
        'source_url',
        'is_published',
        'published_at',
        'expires_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->created_by ??= auth()->id();
            $model->published_at ??= now();
        });
    }

    protected function casts(): array
    {
        return [
            'level' => AnnouncementLevel::class,
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    /** Peringatan/darurat yang masih berlaku. */
    public function scopeActiveAlerts(Builder $query): Builder
    {
        return $query->published()
            ->whereIn('level', [AnnouncementLevel::Waspada->value, AnnouncementLevel::Darurat->value])
            ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
