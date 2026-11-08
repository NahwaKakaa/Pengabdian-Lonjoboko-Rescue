<?php

namespace App\Models;

use App\Enums\ContactCategory;
use App\Support\Phone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EmergencyContact extends Model
{
    protected $fillable = [
        'name',
        'category',
        'phone',
        'is_whatsapp',
        'description',
        'is_active',
        'show_in_footer',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'category' => ContactCategory::class,
            'is_whatsapp' => 'boolean',
            'is_active' => 'boolean',
            'show_in_footer' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** Jumlah maksimal kontak yang muat di footer web. */
    public const FOOTER_LIMIT = 5;

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Nomor untuk tautan tel: (hanya angka dan +). */
    public function telHref(): string
    {
        return Phone::telHref($this->phone);
    }

    /** Tautan WhatsApp dengan format 62xxx. */
    public function whatsappHref(?string $message = null): ?string
    {
        return $this->is_whatsapp ? Phone::whatsappHref($this->phone, $message) : null;
    }
}
