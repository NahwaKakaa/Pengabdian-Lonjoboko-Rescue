@extends('layouts.public')

@section('title', 'Jalur Evakuasi')

@push('head')
    @vite('resources/js/public-map.js')
@endpush

@section('content')
    <x-page-header
        eyebrow="Jalur evakuasi & titik kumpul"
        title="Ke mana harus pergi saat darurat"
        intro="Garis merah muda adalah jalur evakuasi menuju titik kumpul (hijau). Tekan “Lokasi saya” untuk melihat posisi Anda dan titik kumpul terdekat."
    />

    <section class="wrap py-8 grid gap-4">
        <div class="flex flex-wrap items-center gap-2" role="group" aria-label="Saring jalur menurut jenis bencana">
            <span class="text-xs sm:text-sm font-bold text-ink-soft mr-1">Tampilkan jalur untuk:</span>
            <button type="button" data-route-filter="all" aria-pressed="true" class="filter-chip filter-chip--all">Semua</button>
            <button type="button" data-route-filter="banjir" aria-pressed="false" class="filter-chip filter-chip--flood">Banjir</button>
            <button type="button" data-route-filter="longsor" aria-pressed="false" class="filter-chip filter-chip--slide">Tanah longsor</button>
            <div class="h-5 w-px bg-line mx-1 hidden sm:block" aria-hidden="true"></div>
            <label class="layer-chip layer-chip--boundary">
                <input type="checkbox" class="sr-only" data-layer-toggle="boundary" checked>
                <span class="layer-chip__box" aria-hidden="true"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M3.5 8.5l3 3 6-7"/></svg></span>
                Lingkup Area Lonjoboko
            </label>
        </div>

        <div class="map-frame">
            <div data-lr-map="evacuation" class="h-[62vh] min-h-[380px] max-h-[640px] w-full" role="region" aria-label="Peta jalur evakuasi Desa {{ $site['village'] }}"></div>

            {{-- Kartu Info Petunjuk Jalur Aktif --}}
            <div data-nav-card class="hidden absolute top-4 inset-x-4 sm:inset-x-auto sm:right-4 z-[500] max-w-sm bg-river-950/95 backdrop-blur-md text-white p-4 rounded-2xl shadow-2xl border border-white/20 transition-all">
                <div class="flex items-start justify-between gap-3">
                    <div class="grid gap-1 min-w-0">
                        <span class="inline-flex items-center gap-1.5 text-[0.7rem] uppercase font-black tracking-wider text-emerald-300">
                            <span class="size-2 rounded-full bg-emerald-400 animate-ping"></span>
                            Petunjuk Jalur Aktif
                        </span>
                        <p data-nav-title class="font-bold text-sm text-white truncate"></p>
                        <p data-nav-meta class="text-xs text-river-100"></p>
                    </div>
                    <button type="button" data-nav-close class="rounded-lg p-1 text-river-300 hover:text-white hover:bg-white/10 transition-colors" title="Tutup Rute">
                        <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="mt-2.5 pt-2.5 border-t border-white/15 flex items-center justify-between gap-2">
                    <a data-nav-gmaps href="#" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-river-950 transition-colors">
                        <span>Buka Navigasi HP</span>
                        <svg viewBox="0 0 24 24" class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14L21 3"/></svg>
                    </a>
                    <button type="button" data-nav-zoom class="text-xs font-semibold text-river-200 hover:text-white underline underline-offset-2">
                        Fokus Rute
                    </button>
                </div>
            </div>

            <div class="absolute bottom-4 left-4 z-[500] flex flex-wrap items-center gap-2">
                <button type="button" data-locate class="inline-flex items-center gap-2 rounded-xl bg-white px-3.5 py-2 text-xs sm:text-sm font-bold shadow-lg ring-1 ring-black/10 hover:bg-river-50">
                    <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg>
                    Lokasi saya
                </button>
                <button type="button" data-fit-village class="inline-flex items-center gap-1.5 rounded-xl bg-white px-3.5 py-2 text-xs sm:text-sm font-bold shadow-lg ring-1 ring-black/10 hover:bg-emerald-50 text-emerald-900">
                    <svg viewBox="0 0 24 24" class="size-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round"><path d="M3 18l7-10 4 5 3-4 4 9H3z"/></svg>
                    Lingkup Desa
                </button>
            </div>
        </div>
        <p data-map-status class="text-sm text-ink-soft min-h-5" aria-live="polite"></p>

        <script type="application/json" id="map-data">@json($mapData)</script>
    </section>

    <div class="wrap grid gap-10 lg:grid-cols-2">
        <section class="grid gap-4 content-start">
            <h2 class="text-2xl font-extrabold tracking-tight">Jalur evakuasi</h2>
            @forelse ($routes as $route)
                @php $meters = $route->lengthInMeters(); @endphp
                <article class="elevation-card rounded-2xl p-5 sm:p-6 grid gap-2" data-route-card="{{ $route->hazard_type->value }}">
                    <div class="flex flex-wrap items-center gap-2">
                        <x-hazard-badge :type="$route->hazard_type->value === 'semua' ? 'umum' : $route->hazard_type">{{ $route->hazard_type->getLabel() }}</x-hazard-badge>
                        <span class="text-sm text-ink-soft num">
                            {{ $meters >= 1000 ? number_format($meters / 1000, 1, ',', '.').' km' : $meters.' m' }}
                            · ±{{ max(1, (int) round($meters / (4000 / 60))) }} menit jalan kaki
                        </span>
                    </div>
                    <h3 class="font-bold text-lg leading-snug">{{ $route->name }}</h3>
                    @if ($route->assemblyPoint)
                        <p class="text-sm"><span class="text-ink-soft">Tujuan:</span> <span class="font-semibold text-safe">{{ $route->assemblyPoint->name }}</span></p>
                    @endif
                    @if ($route->description)
                        <p class="text-sm text-ink-soft leading-relaxed">{{ $route->description }}</p>
                    @endif
                    <button type="button" data-focus-route="{{ $route->id }}" class="action-link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5A7 7 0 0112 2.5a7 7 0 017 7C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
                        <span>Tunjukkan di peta</span>
                    </button>
                </article>
            @empty
                <p class="rounded-2xl border border-dashed border-line bg-card p-6 text-ink-soft">Belum ada jalur evakuasi yang diverifikasi.</p>
            @endforelse
        </section>

        <section class="grid gap-4 content-start">
            <h2 class="text-2xl font-extrabold tracking-tight">Titik kumpul & pengungsian</h2>
            @forelse ($points as $point)
                <article class="elevation-card rounded-2xl p-5 sm:p-6 grid gap-3 sm:grid-cols-[1fr_auto]">
                    <div class="grid gap-1.5 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <x-hazard-badge :type="$point->type" />
                            @if ($point->capacity)
                                <span class="text-sm text-ink-soft num">Daya tampung ±{{ number_format($point->capacity, 0, ',', '.') }} orang</span>
                            @endif
                        </div>
                        <h3 class="font-bold text-lg leading-snug">{{ $point->name }}</h3>
                        @if ($point->address)
                            <p class="text-sm text-ink-soft">{{ $point->address }}</p>
                        @endif
                        @if ($point->facilities)
                            <p class="text-sm"><span class="text-ink-soft">Fasilitas:</span> {{ $point->facilities }}</p>
                        @endif
                        <div class="flex flex-wrap gap-2 pt-1.5">
                            <button type="button" data-focus-point="{{ $point->id }}" class="action-link">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5A7 7 0 0112 2.5a7 7 0 017 7C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
                                <span>Tunjukkan di peta</span>
                            </button>
                            <a href="https://www.google.com/maps/dir/?api=1&destination={{ $point->lat }},{{ $point->lng }}&travelmode=walking" target="_blank" rel="noopener" class="action-link action-link--ghost">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 11l19-9-9 19-2-8-8-2z"/></svg>
                                <span>Petunjuk arah</span>
                                <svg class="action-link__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17L17 7M9 7h8v8"/></svg>
                            </a>
                        </div>
                    </div>
                    @if ($point->photo)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($point->photo) }}" alt="Foto {{ $point->name }}" loading="lazy" class="w-full sm:w-36 aspect-[4/3] rounded-xl object-cover shadow-xs">
                    @endif
                </article>
            @empty
                <p class="rounded-2xl border border-dashed border-line bg-card p-6 text-ink-soft">Belum ada titik kumpul yang diverifikasi.</p>
            @endforelse
        </section>
    </div>

    {{-- Petunjuk keselamatan selama evakuasi --}}
    <section class="wrap pt-14 grid gap-5">
        <div class="grid gap-1">
            <p class="eyebrow">Petunjuk keselamatan</p>
            <h2 class="text-2xl font-extrabold tracking-tight">Selama evakuasi</h2>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-2xl border-2 border-flood/30 bg-flood-soft p-5 grid gap-3 content-start">
                <h3 class="font-extrabold text-lg text-flood">Saat banjir</h3>
                <ul class="grid gap-2 text-sm leading-relaxed list-disc pl-5">
                    @foreach (\App\Support\SafetyGuide::floodSteps() as $line)
                        <li>{{ $line }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="rounded-2xl border-2 border-slide/30 bg-slide-soft p-5 grid gap-3 content-start">
                <h3 class="font-extrabold text-lg text-slide">Saat tanah longsor</h3>
                <ul class="grid gap-2 text-sm leading-relaxed list-disc pl-5">
                    @foreach (\App\Support\SafetyGuide::landslideSteps() as $line)
                        <li>{{ $line }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>
@endsection
