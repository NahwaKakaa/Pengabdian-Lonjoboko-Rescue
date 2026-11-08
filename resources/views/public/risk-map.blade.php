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
        <div class="flex flex-wrap items-center gap-2" role="group" aria-label="Tampilkan lapisan peta">
            <label class="inline-flex items-center gap-2 rounded-full border border-line bg-card px-3.5 py-1.5 text-xs sm:text-sm font-semibold cursor-pointer shadow-xs transition-all duration-200 select-none hover:border-emerald-600 has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50 has-[:checked]:text-emerald-800">
                <input type="checkbox" class="accent-emerald-600 size-4 rounded" data-layer-toggle="boundary" checked> Lingkup Area Lonjoboko
            </label>
            <label class="inline-flex items-center gap-2 rounded-full border border-line bg-card px-3.5 py-1.5 text-xs sm:text-sm font-semibold cursor-pointer shadow-xs transition-all duration-200 select-none hover:border-flood/50 has-[:checked]:border-flood has-[:checked]:bg-flood-soft has-[:checked]:text-flood">
                <input type="checkbox" class="accent-flood size-4 rounded" data-layer-toggle="banjir" checked> Rawan banjir
            </label>
            <label class="inline-flex items-center gap-2 rounded-full border border-line bg-card px-3.5 py-1.5 text-xs sm:text-sm font-semibold cursor-pointer shadow-xs transition-all duration-200 select-none hover:border-slide/50 has-[:checked]:border-slide has-[:checked]:bg-slide-soft has-[:checked]:text-slide">
                <input type="checkbox" class="accent-slide size-4 rounded" data-layer-toggle="longsor" checked> Rawan longsor
            </label>
            <label class="inline-flex items-center gap-2 rounded-full border border-line bg-card px-3.5 py-1.5 text-xs sm:text-sm font-semibold cursor-pointer shadow-xs transition-all duration-200 select-none hover:border-safe/50 has-[:checked]:border-safe has-[:checked]:bg-safe-soft has-[:checked]:text-safe">
                <input type="checkbox" class="accent-safe size-4 rounded" data-layer-toggle="points" checked> Titik kumpul
            </label>
            <label class="inline-flex items-center gap-2 rounded-full border border-line bg-card px-3.5 py-1.5 text-xs sm:text-sm font-semibold cursor-pointer shadow-xs transition-all duration-200 select-none hover:border-route/50 has-[:checked]:border-route has-[:checked]:bg-pink-50 has-[:checked]:text-route">
                <input type="checkbox" class="accent-route size-4 rounded" data-layer-toggle="routes"> Jalur evakuasi
            </label>
            <label class="inline-flex items-center gap-2 rounded-full border border-line bg-card px-3.5 py-1.5 text-xs sm:text-sm font-semibold cursor-pointer shadow-xs transition-all duration-200 select-none hover:border-ink-soft has-[:checked]:border-ink-soft has-[:checked]:bg-river-50">
                <input type="checkbox" class="size-4 rounded" data-layer-toggle="facilities" checked> Fasilitas umum
            </label>
        </div>

        <div class="map-frame">
            <div data-lr-map="risk" class="h-[62vh] min-h-[380px] max-h-[640px] w-full" role="region" aria-label="Peta risiko bencana Desa {{ $site['village'] }}"></div>
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
                        <button type="button" data-focus-hazard="{{ $zone->id }}" class="text-sm font-semibold text-river-700 hover:text-river-900 underline underline-offset-4 w-fit inline-flex items-center gap-1">
                            <span>Tunjukkan di peta</span>
                            <svg viewBox="0 0 24 24" class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
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
                    </div>
                @empty
                    <p class="text-sm text-ink-soft">Belum ada data fasilitas.</p>
                @endforelse
            </div>

            <div class="elevation-card rounded-2xl bg-river-50 border border-river-100 p-5 sm:p-6 grid gap-2 text-sm">
                <p class="font-bold">Ingin melihat peta risiko resmi?</p>
                <p class="text-ink-soft">BNPB menyediakan peta potensi bencana per wilayah di InaRISK.</p>
                <a href="https://inarisk.bnpb.go.id" target="_blank" rel="noopener" class="font-semibold text-river-700 underline underline-offset-4 w-fit">Buka InaRISK BNPB</a>
            </div>
        </aside>
    </section>
@endsection
