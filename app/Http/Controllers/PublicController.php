<?php

namespace App\Http\Controllers;

use App\Enums\ContactCategory;
use App\Enums\EducationCategory;
use App\Enums\FacilityCategory;
use App\Enums\HazardType;
use App\Enums\ReportKind;
use App\Models\Activity;
use App\Models\Announcement;
use App\Models\AssemblyPoint;
use App\Models\DisasterEvent;
use App\Models\EducationContent;
use App\Models\EmergencyContact;
use App\Models\EvacuationRoute;
use App\Models\Facility;
use App\Models\HamletPopulation;
use App\Models\HazardZone;
use App\Models\IncidentReport;
use App\Models\Setting;
use App\Models\Volunteer;
use App\Support\PosterMap;
use App\Support\VillageBoundary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
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
            'hamlets' => $this->hamlets(),
            'latestEvent' => DisasterEvent::verified()->latest('occurred_on')->first(),
        ]);
    }

    public function riskMap(): View
    {
        $hazards = HazardZone::verified()->orderByDesc('risk_level')->get()->filter->hasValidLocation();

        return view('public.risk-map', [
            'hazards' => $hazards,
            'facilities' => Facility::verified()->orderBy('name')->get()->filter->hasValidLocation(),
            'hamlets' => $this->hamlets(),
            'events' => DisasterEvent::verified()->latest('occurred_on')->take(3)->get(),
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
            'healthFacilities' => Facility::verified()->where('category', FacilityCategory::Kesehatan->value)->orderBy('name')->get(),
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
            'activities' => Activity::published()->latest('held_on')->take(6)->get(),
        ]);
    }

    /** Riwayat kejadian bencana yang sudah diverifikasi, beserta jumlah korban. */
    public function history(): View
    {
        $events = DisasterEvent::verified()->latest('occurred_on')->get();

        return view('public.history', [
            'events' => $events,
            'totals' => collect(array_keys(DisasterEvent::VICTIMS))->mapWithKeys(fn ($c) => [$c => (int) $events->sum($c)])->all(),
        ]);
    }

    /** Formulir lapor kejadian untuk warga (tanpa akun). */
    public function reportForm(): View
    {
        return view('public.report', [
            'kinds' => ReportKind::cases(),
            'hamletNames' => HamletPopulation::orderBy('sort_order')->pluck('name')
                ->whenEmpty(fn () => collect(['Bontoloe', 'Galesong', 'Tombongi', 'Lantaboko'])),
        ]);
    }

    public function reportStore(Request $request): RedirectResponse
    {
        // Jebakan robot: kolom ini tersembunyi dan selalu kosong bila diisi manusia.
        if (filled($request->input('website'))) {
            return redirect()->route('report')->with('reported', 'LPR-OK');
        }

        $data = $request->validate([
            'kind' => ['required', Rule::enum(ReportKind::class)],
            'description' => ['required', 'string', 'min:10', 'max:2000'],
            'hamlet' => ['nullable', 'string', 'max:100'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'lat' => ['nullable', 'numeric', 'between:-90,90', 'required_with:lng'],
            'lng' => ['nullable', 'numeric', 'between:-180,180', 'required_with:lat'],
            'photo' => ['nullable', 'image', 'max:8192'],
            'reporter_name' => ['nullable', 'string', 'max:100'],
            'reporter_phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{8,30}$/'],
        ], [
            'kind.required' => 'Pilih jenis kejadian.',
            'description.required' => 'Ceritakan apa yang terjadi.',
            'description.min' => 'Ceritakan sedikit lebih lengkap (minimal 10 huruf).',
            'photo.image' => 'File harus berupa foto.',
            'photo.max' => 'Ukuran foto paling besar 8 MB.',
            'reporter_phone.regex' => 'Nomor HP hanya boleh berisi angka.',
        ]);

        $report = IncidentReport::create([
            'kind' => $data['kind'],
            'description' => $data['description'],
            'hamlet' => $data['hamlet'] ?? null,
            'landmark' => $data['landmark'] ?? null,
            'location' => isset($data['lat'], $data['lng']) ? ['lat' => round((float) $data['lat'], 6), 'lng' => round((float) $data['lng'], 6)] : null,
            'photo' => $request->hasFile('photo') ? $request->file('photo')->store('laporan', 'public') : null,
            'reporter_name' => $data['reporter_name'] ?? null,
            'reporter_phone' => $data['reporter_phone'] ?? null,
            'ip_hash' => hash('sha256', (string) $request->ip().config('app.key')),
        ]);

        return redirect()->route('report')->with('reported', $report->code);
    }

    /** @return Collection<int, HamletPopulation> */
    private function hamlets()
    {
        try {
            return HamletPopulation::orderBy('sort_order')->orderBy('name')->get();
        } catch (\Throwable) {
            return collect();
        }
    }

    /** Poster A4 siap cetak: peta skematis, titik kumpul, jalur, nomor darurat, QR. */
    public function poster(): View
    {
        $boundary = VillageBoundary::geojson();

        $points = AssemblyPoint::verified()->orderBy('name')->get()->filter->hasValidLocation()->values();
        $routes = EvacuationRoute::verified()->with('assemblyPoint')->orderBy('name')->get()
            ->filter(fn (EvacuationRoute $r) => count($r->path ?? []) >= 2)->values();
        $hazards = HazardZone::verified()->get()->filter->hasValidLocation()->values();

        return view('public.poster', [
            'map' => PosterMap::make($boundary, $points, $routes, $hazards),
            'points' => $points,
            'routes' => $routes,
            'contacts' => EmergencyContact::active()->orderBy('sort_order')->take(6)->get(),
            'whatsapp' => Setting::get('whatsapp_number'),
            'whatsappLabel' => Setting::get('whatsapp_label'),
            'officePhone' => Setting::get('office_phone'),
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
        $boundary = VillageBoundary::geojson();

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
                'hours' => $f->opening_hours,
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
            'hamlets' => $withFacilities ? $this->hamlets()->filter->hasValidLocation()->map(fn (HamletPopulation $h) => [
                'name' => $h->name,
                'population' => $h->population,
                'vulnerable' => $h->vulnerableTotal(),
                'parts' => collect(HamletPopulation::VULNERABLE)->map(fn ($label, $c) => [$label, $h->{$c}])->values(),
                'lat' => $h->lat,
                'lng' => $h->lng,
            ])->values() : [],
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
