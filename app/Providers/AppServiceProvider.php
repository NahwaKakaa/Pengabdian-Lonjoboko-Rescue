<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\Setting;
use Carbon\Carbon;
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
            $view->with([
                'site' => self::siteInfo(),
                'activeAlerts' => self::safely(fn () => Announcement::activeAlerts()->latest('published_at')->get(), collect()),
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
