<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\AssemblyPointResource;
use App\Filament\Resources\EvacuationRouteResource;
use App\Filament\Resources\FacilityResource;
use App\Filament\Resources\HazardZoneResource;
use App\Models\AssemblyPoint;
use App\Models\EvacuationRoute;
use App\Models\Facility;
use App\Models\HazardZone;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** Ringkasan data peta: berapa yang sudah tampil dan berapa yang menunggu. */
class VerificationOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $items = [
            ['Lokasi rawan', HazardZone::class, HazardZoneResource::class, 'heroicon-m-exclamation-triangle'],
            ['Titik kumpul', AssemblyPoint::class, AssemblyPointResource::class, 'heroicon-m-flag'],
            ['Jalur evakuasi', EvacuationRoute::class, EvacuationRouteResource::class, 'heroicon-m-arrow-trending-up'],
            ['Fasilitas', Facility::class, FacilityResource::class, 'heroicon-m-building-office-2'],
        ];

        return collect($items)->map(function (array $item) {
            [$label, $model, $resource, $icon] = $item;

            $verified = $model::verified()->count();
            $pending = $model::pending()->count();

            return Stat::make($label, $verified.' tampil di web')
                ->description($pending > 0 ? $pending.' menunggu verifikasi' : 'Tidak ada yang menunggu')
                ->descriptionIcon($pending > 0 ? 'heroicon-m-clock' : 'heroicon-m-check-circle')
                ->color($pending > 0 ? 'warning' : 'success')
                ->icon($icon)
                ->url($resource::getUrl('index'));
        })->all();
    }
}
