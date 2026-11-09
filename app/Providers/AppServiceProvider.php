<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\EmergencyContact;
use App\Models\Setting;
use App\Support\Phone;
use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
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

        // Lapor kejadian: paling banyak 6 laporan per jam dari satu jaringan.
        RateLimiter::for('lapor', fn (Request $request) => Limit::perHour(6)->by($request->ip())
            ->response(fn () => back()->withInput()->withErrors([
                'description' => 'Laporan dari jaringan ini sudah banyak dalam satu jam terakhir. Bila darurat, hubungi nomor darurat atau WhatsApp posko desa.',
            ])));

        if (! $this->app->runningInConsole()) {
            self::useRequestScheme(request());
        }

        // Pengaturan peta & sumber resmi dari admin (menu Pengaturan Peta & Sumber)
        // menimpa nilai bawaan di config/lonjoboko.php.
        self::applyMapSettings();

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

    /**
     * Semua alamat (termasuk foto yang diunggah) mengikuti protokol halaman.
     * Tanpa ini, foto di disk "public" memakai APP_URL; kalau APP_URL masih
     * http://, halaman https memuat gambar lewat http dan browser menandai
     * web "Not Secure".
     */
    public static function useRequestScheme(Request $request): void
    {
        $https = $request->isSecure() || str_starts_with((string) config('app.url'), 'https://');

        if ($https) {
            URL::forceScheme('https');
        }

        $root = ($https ? 'https://' : $request->getScheme().'://').$request->getHttpHost();
        config(['filesystems.disks.public.url' => $root.'/storage']);
        Storage::forgetDisk('public');
    }

    public static function applyMapSettings(): void
    {
        $settings = self::safely(fn () => Setting::allValues(), []);

        if (is_numeric($settings['map_center_lat'] ?? null) && is_numeric($settings['map_center_lng'] ?? null)) {
            config(['lonjoboko.map.center' => [
                'lat' => (float) $settings['map_center_lat'],
                'lng' => (float) $settings['map_center_lng'],
            ]]);
        }

        if (is_numeric($settings['map_zoom'] ?? null)) {
            config(['lonjoboko.map.zoom' => max(10, min(18, (int) $settings['map_zoom']))]);
        }

        $sources = json_decode((string) ($settings['official_sources'] ?? ''), true);

        if (is_array($sources)) {
            $sources = array_values(array_filter($sources, fn ($s) => is_array($s) && filled($s['name'] ?? null) && filled($s['url'] ?? null)));
            config(['lonjoboko.official_sources' => $sources]);
        }
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
            'landscape_note' => ($settings['landscape_note'] ?? null)
                ?: 'Lereng perbukitan & persawahan '.($settings['district'] ?? config('lonjoboko.district')),
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
            'contacts' => $contacts->filter(fn (EmergencyContact $c) => $c->show_in_footer)->take(EmergencyContact::FOOTER_LIMIT)->values(),
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
