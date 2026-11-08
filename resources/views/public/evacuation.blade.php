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
            <span class="text-sm font-semibold text-ink-soft mr-1">Tampilkan jalur untuk:</span>
            <button type="button" data-route-filter="all" aria-pressed="true" class="rounded-full border border-line bg-card px-3 py-1.5 text-sm font-semibold aria-pressed:bg-river-800 aria-pressed:text-white aria-pressed:border-river-800">Semua</button>
            <button type="button" data-route-filter="banjir" aria-pressed="false" class="rounded-full border border-line bg-card px-3 py-1.5 text-sm font-semibold aria-pressed:bg-flood aria-pressed:text-white aria-pressed:border-flood">Banjir</button>
            <button type="button" data-route-filter="longsor" aria-pressed="false" class="rounded-full border border-line bg-card px-3 py-1.5 text-sm font-semibold aria-pressed:bg-slide aria-pressed:text-white aria-pressed:border-slide">Tanah longsor</button>
        </div>

        <div class="map-frame">
            <div data-lr-map="evacuation" class="h-[62vh] min-h-[380px] max-h-[640px] w-full" role="region" aria-label="Peta jalur evakuasi Desa {{ $site['village'] }}"></div>
            <button type="button" data-locate class="absolute bottom-4 left-4 z-[500] inline-flex items-center gap-2 rounded-xl bg-white px-3 py-2 text-sm font-bold shadow-lg ring-1 ring-black/10 hover:bg-river-50">
                <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg>
                Lokasi saya
            </button>
        </div>
        <p data-map-status class="text-sm text-ink-soft min-h-5" aria-live="polite"></p>

        <script type="application/json" id="map-data">@json($mapData)</script>
    </section>

    <div class="wrap grid gap-10 lg:grid-cols-2">
        <section class="grid gap-4 content-start">
            <h2 class="text-2xl font-extrabold tracking-tight">Jalur evakuasi</h2>
            @forelse ($routes as $route)
                @php $meters = $route->lengthInMeters(); @endphp
                <article class="rounded-2xl border border-line bg-card p-5 grid gap-2" data-route-card="{{ $route->hazard_type->value }}">
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
                    <button type="button" data-focus-route="{{ $route->id }}" class="text-sm font-semibold text-river-700 underline underline-offset-4 w-fit">Tunjukkan di peta</button>
                </article>
            @empty
                <p class="rounded-2xl border border-dashed border-line bg-card p-6 text-ink-soft">Belum ada jalur evakuasi yang diverifikasi.</p>
            @endforelse
        </section>

        <section class="grid gap-4 content-start">
            <h2 class="text-2xl font-extrabold tracking-tight">Titik kumpul & pengungsian</h2>
            @forelse ($points as $point)
                <article class="rounded-2xl border border-line bg-card p-5 grid gap-3 sm:grid-cols-[1fr_auto]">
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
                        <div class="flex flex-wrap gap-x-4 gap-y-1 pt-1">
                            <button type="button" data-focus-point="{{ $point->id }}" class="text-sm font-semibold text-river-700 underline underline-offset-4">Tunjukkan di peta</button>
                            <a href="https://www.google.com/maps/dir/?api=1&destination={{ $point->lat }},{{ $point->lng }}&travelmode=walking" target="_blank" rel="noopener" class="text-sm font-semibold text-river-700 underline underline-offset-4">Petunjuk arah</a>
                        </div>
                    </div>
                    @if ($point->photo)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($point->photo) }}" alt="Foto {{ $point->name }}" loading="lazy" class="w-full sm:w-36 aspect-[4/3] rounded-xl object-cover">
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
                    <li>Jangan berjalan atau berkendara melewati arus banjir. Air setinggi lutut yang mengalir sudah bisa menyeret orang dewasa.</li>
                    <li>Matikan listrik dari meteran sebelum air masuk rumah, bila masih aman dilakukan.</li>
                    <li>Jauhi tepi sungai, selokan besar, dan jembatan yang tergenang.</li>
                    <li>Pindahkan dokumen penting ke tempat tinggi atau bawa dalam tas siaga.</li>
                </ul>
            </div>
            <div class="rounded-2xl border-2 border-slide/30 bg-slide-soft p-5 grid gap-3 content-start">
                <h3 class="font-extrabold text-lg text-slide">Saat tanah longsor</h3>
                <ul class="grid gap-2 text-sm leading-relaxed list-disc pl-5">
                    <li>Segera menjauh dari lereng bila muncul retakan baru, pohon atau tiang miring, atau suara gemuruh.</li>
                    <li>Bergerak ke samping menjauhi arah luncuran, jangan searah aliran longsor.</li>
                    <li>Waspadai longsor susulan, terutama saat hujan masih turun. Jangan kembali mengambil barang.</li>
                    <li>Hindari jalan di kaki tebing selama dan sesudah hujan deras.</li>
                </ul>
            </div>
        </div>
    </section>
@endsection
