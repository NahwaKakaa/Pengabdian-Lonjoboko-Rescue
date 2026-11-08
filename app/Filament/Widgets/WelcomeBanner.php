<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\AnnouncementResource;
use App\Filament\Resources\AssemblyPointResource;
use App\Filament\Resources\EvacuationRouteResource;
use App\Filament\Resources\HazardZoneResource;
use App\Support\AdminStatus;
use Filament\Widgets\Widget;

/** Sapaan, ringkasan singkat, dan tombol aksi cepat di atas Dasbor. */
class WelcomeBanner extends Widget
{
    protected static string $view = 'filament.widgets.welcome-banner';

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $user = auth()->user();
        $isAdmin = (bool) $user?->isAdmin();
        $pending = AdminStatus::pendingCount();
        $alerts = AdminStatus::activeAlerts();

        $actions = [
            ['label' => 'Tandai lokasi rawan', 'url' => HazardZoneResource::getUrl('create'), 'icon' => 'pin'],
            ['label' => 'Gambar jalur evakuasi', 'url' => EvacuationRouteResource::getUrl('create'), 'icon' => 'route'],
            ['label' => 'Tambah titik kumpul', 'url' => AssemblyPointResource::getUrl('create'), 'icon' => 'flag'],
        ];

        if ($isAdmin) {
            $actions[] = ['label' => 'Buat peringatan', 'url' => AnnouncementResource::getUrl('create'), 'icon' => 'megaphone'];
            $actions[] = ['label' => 'Cetak poster siaga', 'url' => route('poster'), 'icon' => 'printer', 'external' => true];
        }

        if ($pending === 0) {
            $summary = 'Semua data peta sudah terverifikasi dan tampil di web.';
        } elseif ($isAdmin) {
            $summary = "Ada {$pending} data peta dari kader yang menunggu verifikasi Anda.";
        } else {
            $summary = "{$pending} data peta sedang menunggu verifikasi admin desa.";
        }

        return [
            'greeting' => AdminStatus::greeting(),
            'firstName' => strtok((string) $user?->name, ' ') ?: 'Pengelola',
            'roleLabel' => $user?->role?->getLabel() ?? 'Pengelola',
            'summary' => $summary,
            'pending' => $pending,
            'verified' => AdminStatus::verifiedCount(),
            'alertCount' => $alerts->count(),
            'actions' => $actions,
            'today' => now('Asia/Makassar')->locale('id')->translatedFormat('l, j F Y'),
        ];
    }
}
