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
use App\Support\AdminStatus;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;

/** Daftar data peta terbaru yang menunggu verifikasi. */
class PendingReview extends Widget
{
    protected static string $view = 'filament.widgets.pending-review';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    private const RESOURCES = [
        HazardZone::class => HazardZoneResource::class,
        AssemblyPoint::class => AssemblyPointResource::class,
        EvacuationRoute::class => EvacuationRouteResource::class,
        Facility::class => FacilityResource::class,
    ];

    protected function getViewData(): array
    {
        $items = collect(self::RESOURCES)
            ->flatMap(fn (string $resource, string $model) => $model::pending()
                ->with('creator')
                ->latest('updated_at')
                ->take(8)
                ->get()
                ->map(fn (Model $record) => [
                    'name' => $record->name,
                    'type' => AdminStatus::MAP_MODELS[$model]['label'],
                    'tone' => AdminStatus::MAP_MODELS[$model]['tone'],
                    'creator' => $record->creator?->name ?? 'Tanpa nama',
                    'when' => $record->updated_at?->locale('id')->diffForHumans(),
                    'updated' => $record->updated_at,
                    'url' => $resource::getUrl('edit', ['record' => $record]),
                ]))
            ->sortByDesc('updated')
            ->take(8)
            ->values();

        return [
            'items' => $items,
            'total' => AdminStatus::pendingCount(),
            'isAdmin' => (bool) auth()->user()?->isAdmin(),
        ];
    }
}
