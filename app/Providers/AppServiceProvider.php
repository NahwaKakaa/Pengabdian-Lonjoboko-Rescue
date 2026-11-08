<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\EmergencyContact;
use App\Models\Setting;
use App\Support\Phone;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale(config('app.locale'));

        // Data identitas desa dan peringatan aktif untuk semua halaman publik.
        View::composer('layouts.public', function ($view) {
            $site = self::siteInfo();
            $contacts = self::safely(fn () => EmergencyContact::active()->orderBy('sort_order')->get(), collect());

            $view->with([
                'site' => $site,
                'activeAlerts' => self::safely(fn () => Announcement::activeAlerts()->latest('published_at')->get(), collect()),
                'footer' => self::footerInfo($site, $contacts),
            ]);
        });

        View::composer('public.*', function ($view) {
            $view->with('site', self::siteInfo());
        });
    }

    /** @return array<string, string|null> */
    public static function siteInfo(): array
    {
        $settings = self::safely(fn () => Setting::allValues(), []);

        return [
            'village' => $settings['village_name'] ?? config('lonjoboko.village'),
            'district' => $settings['district'] ?? config('lonjoboko.district'),
            'regency' => $settings['regency'] ?? config('lonjoboko.regency'),
            'profile' => $settings['village_profile'] ?? null,
            'program' => $settings['program_description'] ?? null,
            'population_note' => $settings['population_note'] ?? null,
            'report_instructions' => $settings['report_instructions'] ?? null,
            'office_address' => $settings['office_address'] ?? null,
            'office_phone' => $settings['office_phone'] ?? null,
            'whatsapp' => $settings['whatsapp_number'] ?? null,
            'whatsapp_label' => $settings['whatsapp_label'] ?? null,
            'maps_url' => $settings['maps_url'] ?? null,
        ];
    }

    /**
     * Isi footer: WhatsApp desa, kontak darurat, dan peta lokasi.
     *
     * @param  array<string, string|null>  $site
     * @param  Collection<int, EmergencyContact>  $contacts
     * @return array<string, mixed>
     */
    public static function footerInfo(array $site, Collection $contacts): array
    {
        $message = "Halo, saya warga Desa {$site['village']}. Saya ingin melapor/bertanya: ";

        // Nomor WA dari Profil Desa. Kalau belum diisi, pakai kontak desa pertama yang punya WhatsApp.
        $whatsapp = null;

        if (filled($site['whatsapp'])) {
            $whatsapp = [
                'label' => $site['whatsapp_label'] ?: 'Posko Desa '.$site['village'],
                'number' => $site['whatsapp'],
                'href' => Phone::whatsappHref($site['whatsapp'], $message),
            ];
        } elseif ($contact = $contacts->first(fn (EmergencyContact $c) => $c->is_whatsapp)) {
            $whatsapp = [
                'label' => $contact->name,
                'number' => $contact->phone,
                'href' => $contact->whatsappHref($message),
            ];
        }

        $place = collect([
            'Desa '.$site['village'],
            $site['district'] ? 'Kec. '.$site['district'] : null,
            $site['regency'] ? 'Kab. '.$site['regency'] : null,
            config('lonjoboko.province'),
        ])->filter()->implode(', ');

        // Pencarian nama desa di Google Maps menampilkan garis batas desanya.
        $query = $site['office_address'] ? $site['office_address'].', '.$place : $place;
        $mapsQuery = rawurlencode(str_replace(['Kec. ', 'Kab. '], '', $place));

        return [
            'whatsapp' => $whatsapp,
            'contacts' => $contacts->take(5),
            'contact_count' => $contacts->count(),
            'place' => $place,
            'map_embed' => "https://maps.google.com/maps?q={$mapsQuery}&z=13&hl=id&output=embed",
            'map_open' => $site['maps_url'] ?: "https://www.google.com/maps/search/?api=1&query={$mapsQuery}",
            'map_directions' => 'https://www.google.com/maps/dir/?api=1&destination='.rawurlencode($query),
        ];
    }

    /**
     * Halaman publik tetap tampil walau database belum siap
     * (misalnya sebelum migrasi dijalankan).
     */
    private static function safely(callable $callback, mixed $fallback): mixed
    {
        try {
            return $callback();
        } catch (Throwable) {
            return $fallback;
        }
    }
}
