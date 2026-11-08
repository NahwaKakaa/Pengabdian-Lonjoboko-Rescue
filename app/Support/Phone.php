<?php

namespace App\Support;

/**
 * Pembantu nomor telepon: tautan tel: dan WhatsApp.
 */
class Phone
{
    /** Tautan tel: (hanya angka dan +). */
    public static function telHref(string $phone): string
    {
        return 'tel:'.preg_replace('/[^0-9+]/', '', $phone);
    }

    /**
     * Tautan wa.me dengan format 62xxx. Nomor 08xx diubah ke 628xx.
     * Kembali null kalau nomornya kosong.
     */
    public static function whatsappHref(?string $phone, ?string $message = null): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        }

        $url = "https://wa.me/{$digits}";

        return $message ? $url.'?text='.rawurlencode($message) : $url;
    }
}
