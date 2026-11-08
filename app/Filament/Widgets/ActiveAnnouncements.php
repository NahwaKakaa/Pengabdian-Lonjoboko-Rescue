<?php

namespace App\Filament\Widgets;

use App\Enums\AnnouncementLevel;
use App\Filament\Resources\AnnouncementResource;
use App\Models\Announcement;
use Filament\Widgets\Widget;

/** Peringatan yang sedang berlaku dan informasi terbaru untuk warga. */
class ActiveAnnouncements extends Widget
{
    protected static string $view = 'filament.widgets.active-announcements';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected function getViewData(): array
    {
        $isAdmin = (bool) auth()->user()?->isAdmin();

        $items = Announcement::published()
            ->latest('published_at')
            ->take(5)
            ->get()
            ->map(fn (Announcement $a) => [
                'title' => $a->title,
                'level' => $a->level,
                'tone' => match ($a->level) {
                    AnnouncementLevel::Darurat => 'danger',
                    AnnouncementLevel::Waspada => 'warn',
                    default => 'info',
                },
                'active' => in_array($a->level, [AnnouncementLevel::Waspada, AnnouncementLevel::Darurat], true) && ! $a->isExpired(),
                'expired' => $a->isExpired(),
                'when' => $a->published_at?->locale('id')->diffForHumans(),
                'url' => $isAdmin ? AnnouncementResource::getUrl('edit', ['record' => $a]) : route('announcement.show', $a),
            ]);

        return [
            'items' => $items,
            'isAdmin' => $isAdmin,
            'createUrl' => $isAdmin ? AnnouncementResource::getUrl('create') : null,
            'indexUrl' => $isAdmin ? AnnouncementResource::getUrl('index') : null,
        ];
    }
}
