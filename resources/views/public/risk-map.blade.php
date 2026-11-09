@extends('layouts.public')

@section('title', 'Peta Risiko')

@push('head')
    @vite('resources/js/public-map.js')
@endpush

@section('content')
    <x-page-header
        eyebrow="Peta risiko bencana"
        title="Lokasi rawan banjir dan longsor"
        intro="Lokasi di peta ini dihimpun bersama kader dan pemerintah desa, lalu diverifikasi sebelum ditampilkan. Lingkaran menunjukkan perkiraan luas area rawan."
    />

    <section class="wrap py-8 grid gap-4">
        {{-- Tombol lapisan peta: warna mengikuti warna lapisannya di peta --}}
        <div class="flex flex-wrap items-center gap-2" role="group" aria-label="Tampilkan lapisan peta">
            <label class="layer-chip layer-chip--boundary">
                <input type="checkbox" class="sr-only" data-layer-toggle="boundary" checked>
                <span class="layer-chip__box" aria-hidden="true"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M3.5 8.5l3 3 6-7"/></svg></span>
                Lingkup Area Lonjoboko
            </label>
            <label class="layer-chip layer-chip--flood">
                <input type="checkbox" class="sr-only" data-layer-toggle="banjir" checked>
                <span class="layer-chip__box" aria-hidden="true"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M3.5 8.5l3 3 6-7"/></svg></span>
                Rawan banjir
            </label>
            <label class="layer-chip layer-chip--slide">
                <input type="checkbox" class="sr-only" data-layer-toggle="longsor" checked>
                <span class="layer-chip__box" aria-hidden="true"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M3.5 8.5l3 3 6-7"/></svg></span>
                Rawan longsor
            </label>
            <label class="layer-chip layer-chip--safe">
                <input type="checkbox" class="sr-only" data-layer-toggle="points" checked>
                <span class="layer-chip__box" aria-hidden="true"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M3.5 8.5l3 3 6-7"/></svg></span>
                Titik kumpul
            </label>
            <label class="layer-chip layer-chip--route">
                <input type="checkbox" class="sr-only" data-layer-toggle="routes">
                <span class="layer-chip__box" aria-hidden="true"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M3.5 8.5l3 3 6-7"/></svg></span>
                Jalur evakuasi
            </label>
            <label class="layer-chip layer-chip--facility">
                <input type="checkbox" class="sr-only" data-layer-toggle="facilities" checked>
                <span class="layer-chip__box" aria-hidden="true"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M3.5 8.5l3 3 6-7"/></svg></span>
                Fasilitas umum
            </label>
            <label class="layer-chip layer-chip--vulnerable">
                <input type="checkbox" class="sr-only" data-layer-toggle="hamlets" checked>
                <span class="layer-chip__box" aria-hidden="true"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M3.5 8.5l3 3 6-7"/></svg></span>
                Warga rentan
            </label>
        </div>

        <div class="map-frame">
            <div data-lr-map="risk" class="h-[52vh] sm:h-[62vh] min-h-[340px] max-h-[640px] w-full" role="region" aria-label="Peta risiko bencana Desa {{ $site['village'] }}"></div>

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

    <section class="wrap grid gap-8 lg:grid-cols-[1.5fr_1fr]">
        <div class="grid gap-4 content-start">
            <h2 class="text-2xl font-extrabold tracking-tight">Daftar lokasi rawan</h2>
            @forelse ($hazards as $zone)
                <article class="elevation-card rounded-2xl p-5 sm:p-6 grid gap-3 sm:grid-cols-[1fr_auto]">
                    <div class="grid gap-2 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <x-hazard-badge :type="$zone->hazard_type" />
                            <x-hazard-badge :type="$zone->risk_level">Kerawanan {{ $zone->risk_level->getLabel() }}</x-hazard-badge>
                        </div>
                        <h3 class="font-bold text-lg leading-snug">{{ $zone->name }}</h3>
                        @if ($zone->description)
                            <p class="text-sm text-ink-soft leading-relaxed">{{ $zone->description }}</p>
                        @endif
                        <button type="button" data-focus-hazard="{{ $zone->id }}" class="action-link">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5A7 7 0 0112 2.5a7 7 0 017 7C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
                            <span>Tunjukkan di peta</span>
                        </button>
                    </div>
                    @if ($zone->photo)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($zone->photo) }}" alt="Foto {{ $zone->name }}" loading="lazy" class="w-full sm:w-36 aspect-[4/3] rounded-xl object-cover shadow-xs">
                    @endif
                </article>
            @empty
                <p class="rounded-2xl border border-dashed border-line bg-card p-6 text-ink-soft">Belum ada lokasi rawan yang diverifikasi.</p>
            @endforelse
        </div>

        <aside class="grid gap-4 content-start">
            <div class="elevation-card rounded-2xl p-5 sm:p-6 grid gap-3">
                <h2 class="font-extrabold text-lg">Arti warna di peta</h2>
                <ul class="grid gap-2 text-sm">
                    <li class="flex items-center gap-3"><span class="size-4 rounded-full bg-flood/40 ring-2 ring-flood" aria-hidden="true"></span>Area rawan banjir</li>
                    <li class="flex items-center gap-3"><span class="size-4 rounded-full bg-slide/40 ring-2 ring-slide" aria-hidden="true"></span>Area rawan tanah longsor</li>
                    <li class="flex items-center gap-3"><span class="size-4 rounded-full bg-safe ring-2 ring-white shadow" aria-hidden="true"></span>Titik kumpul / pengungsian</li>
                    <li class="flex items-center gap-3"><span class="h-1 w-6 rounded bg-route" aria-hidden="true"></span>Jalur evakuasi</li>
                    <li class="flex items-center gap-3"><span class="size-4 rounded bg-ink-soft ring-2 ring-white shadow" aria-hidden="true"></span>Fasilitas umum dan kesehatan</li>
                    <li class="flex items-center gap-3"><span class="size-4 rounded-full bg-vulnerable/30 ring-2 ring-vulnerable" aria-hidden="true"></span>Warga rentan per dusun (makin besar, makin banyak)</li>
                </ul>
                <p class="text-xs text-ink-soft">Warna lingkaran makin pekat berarti tingkat kerawanan makin tinggi.</p>
            </div>

            <div class="elevation-card rounded-2xl p-5 sm:p-6 grid gap-3">
                <h2 class="font-extrabold text-lg">Fasilitas umum & kesehatan</h2>
                @forelse ($facilities as $facility)
                    <div class="border-t border-line pt-3 first:border-0 first:pt-0">
                        <p class="font-semibold">{{ $facility->name }}</p>
                        <p class="text-sm text-ink-soft">{{ $facility->category->getLabel() }}@if ($facility->address) · {{ $facility->address }}@endif</p>
                        @if ($facility->phone)
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $facility->phone) }}" class="text-sm font-semibold text-river-700 num">{{ $facility->phone }}</a>
                        @endif
                        @if ($facility->opening_hours)
                            <p class="text-xs text-ink-soft">Jam layanan: {{ $facility->opening_hours }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-ink-soft">Belum ada data fasilitas.</p>
                @endforelse
            </div>

            <div class="elevation-card rounded-2xl p-5 sm:p-6 grid gap-3">
                <h2 class="font-extrabold text-lg">Bencana yang pernah terjadi</h2>
                @forelse ($events as $event)
                    <a href="{{ route('history') }}#kejadian-{{ $event->id }}" class="block border-t border-line pt-3 first:border-0 first:pt-0 hover:text-river-700">
                        <p class="text-xs font-bold uppercase tracking-wider text-ink-soft">{{ $event->occurred_on->translatedFormat('j F Y') }} · {{ $event->hazard_type->getLabel() }}</p>
                        <p class="font-semibold">{{ $event->title }}</p>
                    </a>
                @empty
                    <p class="text-sm text-ink-soft">Belum ada riwayat kejadian yang dicatat.</p>
                @endforelse
                <a href="{{ route('history') }}" class="action-link mt-1">
                    <span>Lihat semua riwayat</span>
                    <svg class="action-link__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
            </div>

            <div class="elevation-card rounded-2xl bg-river-50 border border-river-100 p-5 sm:p-6 grid gap-2 text-sm">
                <p class="font-bold">Ingin melihat peta risiko resmi?</p>
                <p class="text-ink-soft">BNPB menyediakan peta potensi bencana per wilayah di InaRISK.</p>
                <a href="https://inarisk.bnpb.go.id" target="_blank" rel="noopener" class="action-link mt-1">
                    <span>Buka InaRISK BNPB</span>
                    <svg class="action-link__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17L17 7M9 7h8v8"/></svg>
                </a>
            </div>
        </aside>
    </section>

    {{-- Penduduk & warga rentan per dusun --}}
    <section class="wrap py-10 grid gap-4" id="penduduk">
        <div class="grid gap-1">
            <p class="eyebrow">Penduduk &amp; warga rentan</p>
            <h2 class="text-2xl font-extrabold tracking-tight">Siapa yang perlu didahulukan saat evakuasi</h2>
            <p class="text-ink-soft max-w-[62ch]">Lansia, balita, ibu hamil, dan penyandang disabilitas butuh bantuan lebih dulu. Lingkaran ungu di peta menunjukkan dusun dengan warga rentan terbanyak.</p>
        </div>
        @include('public.partials.population', ['hamlets' => $hamlets, 'detailed' => true])
    </section>
@endsection
