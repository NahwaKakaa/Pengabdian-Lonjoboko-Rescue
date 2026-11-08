<?php

namespace App\Http\Controllers;

use App\Enums\ContactCategory;
use App\Enums\EducationCategory;
use App\Enums\HazardType;
use App\Models\Announcement;
use App\Models\AssemblyPoint;
use App\Models\EducationContent;
use App\Models\EmergencyContact;
use App\Models\EvacuationRoute;
use App\Models\Facility;
use App\Models\HazardZone;
use App\Models\Volunteer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function home(): View
    {
        return view('public.home', [
            'stats' => [
                'banjir' => HazardZone::verified()->where('hazard_type', HazardType::Banjir->value)->count(),
                'longsor' => HazardZone::verified()->where('hazard_type', HazardType::Longsor->value)->count(),
                'points' => AssemblyPoint::verified()->count(),
                'routes' => EvacuationRoute::verified()->count(),
            ],
            'announcements' => Announcement::published()->latest('published_at')->take(4)->get(),
            'education' => EducationContent::published()->orderBy('sort_order')->latest()->take(3)->get(),
            'contacts' => EmergencyContact::active()->orderBy('sort_order')->take(4)->get(),
        ]);
    }

    public function riskMap(): View
    {
        $hazards = HazardZone::verified()->orderByDesc('risk_level')->get()->filter->hasValidLocation();

        return view('public.risk-map', [
            'hazards' => $hazards,
            'facilities' => Facility::verified()->orderBy('name')->get()->filter->hasValidLocation(),
            'mapData' => $this->mapData(withHazards: true, withFacilities: true),
        ]);
    }

    public function evacuation(): View
    {
        return view('public.evacuation', [
            'routes' => EvacuationRoute::verified()->with('assemblyPoint')->orderBy('name')->get(),
            'points' => AssemblyPoint::verified()->orderBy('name')->get()->filter->hasValidLocation(),
            'mapData' => $this->mapData(withHazards: true, withFacilities: false),
        ]);
    }

    public function education(Request $request): View
    {
        $category = EducationCategory::tryFrom((string) $request->query('kategori'));

        return view('public.education', [
            'items' => EducationContent::published()
                ->when($category, fn ($q) => $q->where('category', $category->value))
                ->orderBy('sort_order')
                ->latest()
                ->paginate(12)
                ->withQueryString(),
            'activeCategory' => $category,
        ]);
    }

    public function educationShow(EducationContent $content): View
    {
        abort_unless($content->is_published, 404);

        return view('public.education-show', [
            'content' => $content,
            'related' => EducationContent::published()
                ->whereKeyNot($content->getKey())
                ->where('topic', $content->topic->value)
                ->orderBy('sort_order')
                ->take(3)
                ->get(),
        ]);
    }

    public function emergency(): View
    {
        $contacts = EmergencyContact::active()->orderBy('sort_order')->get()
            ->groupBy(fn (EmergencyContact $c) => $c->category->value);

        return view('public.emergency', [
            'contactGroups' => collect(ContactCategory::cases())
                ->filter(fn (ContactCategory $cat) => $contacts->has($cat->value))
                ->mapWithKeys(fn (ContactCategory $cat) => [$cat->getLabel() => $contacts[$cat->value]]),
            'alerts' => Announcement::activeAlerts()->latest('published_at')->get(),
            'sources' => config('lonjoboko.official_sources'),
        ]);
    }

    public function announcementShow(Announcement $announcement): View
    {
        abort_unless($announcement->is_published, 404);

        return view('public.announcement-show', [
            'announcement' => $announcement,
        ]);
    }

    public function volunteers(): View
    {
        return view('public.volunteers', [
            'volunteers' => Volunteer::active()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    /**
     * Data peta (hanya yang terverifikasi) untuk dibaca oleh resources/js/public-map.js.
     *
     * @return array<string, mixed>
     */
    private function mapData(bool $withHazards, bool $withFacilities): array
    {
        $photo = fn (?string $path) => $path ? Storage::disk('public')->url($path) : null;
        $boundaryFile = resource_path('data/lonjoboko-boundary.json');
        $boundary = file_exists($boundaryFile) ? json_decode((string) file_get_contents($boundaryFile), true) : null;

        return [
            'center' => config('lonjoboko.map.center'),
            'zoom' => config('lonjoboko.map.zoom'),
            'tileUrl' => config('lonjoboko.map.tile_url'),
            'attribution' => config('lonjoboko.map.attribution'),
            'boundary' => $boundary,
            'hazards' => $withHazards ? HazardZone::verified()->get()->filter->hasValidLocation()->map(fn (HazardZone $z) => [
                'id' => $z->id,
                'name' => $z->name,
                'type' => $z->hazard_type->value,
                'typeLabel' => $z->hazard_type->getLabel(),
                'level' => $z->risk_level->value,
                'levelLabel' => $z->risk_level->getLabel(),
                'radius' => $z->radius_m,
                'description' => $z->description,
                'photo' => $photo($z->photo),
                'lat' => $z->lat,
                'lng' => $z->lng,
            ])->values() : [],
            'facilities' => $withFacilities ? Facility::verified()->get()->filter->hasValidLocation()->map(fn (Facility $f) => [
                'id' => $f->id,
                'name' => $f->name,
                'category' => $f->category->value,
                'categoryLabel' => $f->category->getLabel(),
                'address' => $f->address,
                'phone' => $f->phone,
                'lat' => $f->lat,
                'lng' => $f->lng,
            ])->values() : [],
            'points' => AssemblyPoint::verified()->get()->filter->hasValidLocation()->map(fn (AssemblyPoint $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'type' => $p->type->value,
                'typeLabel' => $p->type->getLabel(),
                'capacity' => $p->capacity,
                'address' => $p->address,
                'facilities' => $p->facilities,
                'photo' => $photo($p->photo),
                'lat' => $p->lat,
                'lng' => $p->lng,
            ])->values(),
            'routes' => EvacuationRoute::verified()->with('assemblyPoint')->get()
                ->filter(fn (EvacuationRoute $r) => count($r->path ?? []) >= 2)
                ->map(fn (EvacuationRoute $r) => [
                    'id' => $r->id,
                    'name' => $r->name,
                    'hazard' => $r->hazard_type->value,
                    'hazardLabel' => $r->hazard_type->getLabel(),
                    'destination' => $r->assemblyPoint?->name,
                    'description' => $r->description,
                    'path' => $r->path,
                ])->values(),
        ];
    }
}
