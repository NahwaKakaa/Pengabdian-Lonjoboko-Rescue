<?php

namespace App\Models;

use App\Enums\EducationCategory;
use App\Enums\EducationTopic;
use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** Modul, video, infografis, dan panduan mitigasi bencana. */
class EducationContent extends Model
{
    use HasUniqueSlug;

    protected $fillable = [
        'title',
        'slug',
        'category',
        'topic',
        'summary',
        'body',
        'video_url',
        'image',
        'attachment',
        'is_published',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'category' => EducationCategory::class,
            'topic' => EducationTopic::class,
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /** Isi konten (Markdown) diubah menjadi HTML yang aman. */
    public function bodyHtml(): string
    {
        return Str::markdown($this->body ?? '', [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    /** ID video YouTube dari berbagai format tautan. */
    public function youtubeId(): ?string
    {
        if (blank($this->video_url)) {
            return null;
        }

        $pattern = '~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})~';

        return preg_match($pattern, $this->video_url, $m) ? $m[1] : null;
    }
}
