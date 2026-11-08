<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\ActiveAnnouncements;
use App\Filament\Widgets\PendingReview;
use App\Filament\Widgets\VerificationOverview;
use App\Filament\Widgets\WelcomeBanner;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('LONJOBOKO RESCUE')
            ->brandLogo(fn () => view('filament.brand'))
            ->brandLogoHeight('2.5rem')
            // Mode terang & gelap. Bisa diganti dari tombol di topbar atau menu profil.
            ->darkMode(true)
            ->colors([
                // Sama dengan warna aksen web publik (hijau sungai).
                'primary' => Color::hex('#0e6b66'),
                'success' => Color::Emerald,
                'danger' => Color::Rose,
                'warning' => Color::Amber,
                'info' => Color::Teal,
                // Abu-abu bernuansa hijau supaya mode gelap tetap senada dengan tema.
                'gray' => [
                    50 => '245, 248, 247',
                    100 => '236, 241, 240',
                    200 => '221, 230, 228',
                    300 => '196, 210, 207',
                    400 => '144, 164, 160',
                    500 => '102, 122, 118',
                    600 => '76, 94, 91',
                    700 => '56, 72, 70',
                    800 => '30, 43, 41',
                    900 => '17, 28, 27',
                    950 => '9, 17, 16',
                ],
            ])
            ->font('Plus Jakarta Sans')
            ->sidebarCollapsibleOnDesktop()
            // Pindah halaman tanpa memuat ulang, lebih cepat di sinyal desa.
            ->spa()
            // Peringatan kalau meninggalkan formulir yang belum disimpan.
            ->unsavedChangesAlerts()
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->navigationGroups([
                NavigationGroup::make('Peta & Evakuasi'),
                NavigationGroup::make('Informasi Publik'),
                NavigationGroup::make('Pengaturan'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->widgets([
                WelcomeBanner::class,
                VerificationOverview::class,
                PendingReview::class,
                ActiveAnnouncements::class,
            ])
            // Tema admin (resources/css/admin.css) + peta admin (Leaflet & Geoman).
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => Blade::render("@vite(['resources/css/admin.css', 'resources/js/admin-map.js'])"),
            )
            // Jam, status koneksi, status peringatan, dan tombol tema di samping profil.
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn (): string => view('filament.topbar-status')->render(),
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_FOOTER,
                fn (): string => view('filament.sidebar-footer')->render(),
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
