<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Membuat slug unik dari kolom `title` saat data dibuat.
 */
trait HasUniqueSlug
{
    public static function bootHasUniqueSlug(): void
    {
        static::creating(function (self $model) {
            if (blank($model->slug)) {
                $model->slug = static::uniqueSlug($model->title);
            }
        });
    }

    public static function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'konten';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
