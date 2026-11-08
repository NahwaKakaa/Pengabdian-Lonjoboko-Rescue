<?php

namespace App\Models;

use App\Enums\ContactCategory;
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
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'category' => ContactCategory::class,
            'is_whatsapp' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Nomor untuk tautan tel: (hanya angka dan +). */
    public function telHref(): string
    {
        return 'tel:'.preg_replace('/[^0-9+]/', '', $this->phone);
    }

    /** Tautan WhatsApp dengan format 62xxx. */
    public function whatsappHref(): ?string
    {
        if (! $this->is_whatsapp) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $this->phone);

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        }

        return "https://wa.me/{$digits}";
    }
}
