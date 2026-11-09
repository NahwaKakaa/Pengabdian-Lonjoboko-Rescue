@extends('layouts.public')

@section('content')
    {{-- Pembuka / Hero dengan Background Foto Pegunungan & Sawah --}}
    <section class="hero-scenic-bg text-white relative overflow-hidden border-b border-river-800">
        <div class="relative wrap grid gap-12 py-14 sm:py-20 lg:grid-cols-[1.2fr_1fr] lg:items-center z-10">
            <div class="grid gap-6">
                <div class="inline-flex items-center gap-2.5 rounded-full bg-river-950/90 backdrop-blur-md px-4 py-1.5 text-xs font-extrabold uppercase tracking-wider text-emerald-300 border border-emerald-500/40 shadow-md w-fit">
                    <span class="relative flex size-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full size-2 bg-emerald-400"></span>
                    </span>
                    <span>Desa {{ $site['village'] }} · Kec. {{ $site['district'] }} · Kab. {{ $site['regency'] }}</span>
                </div>
                
                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-[1.12] text-white drop-shadow-sm">
                    Saat banjir atau longsor datang, <span class="text-amber-300 drop-shadow-sm">tahu ke mana harus pergi</span>.
                </h1>
                
                <p class="text-river-100 text-base sm:text-lg leading-relaxed max-w-[55ch] font-medium drop-shadow-xs">
                    Peta lokasi rawan, jalur evakuasi, titik kumpul aman, dan nomor darurat Desa {{ $site['village'] }} dalam satu genggaman. Cepat diakses dari HP kapan saja.
                </p>
                
                <div class="flex flex-wrap items-center gap-3.5 pt-2">
                    <a href="{{ route('evacuation') }}" class="inline-flex items-center gap-2.5 rounded-2xl bg-emerald-500 hover:bg-emerald-400 px-6 py-3.5 font-black text-river-950 shadow-xl shadow-black/30 hover:-translate-y-0.5 transition-all duration-200">
                        <span>Lihat jalur evakuasi</span>
                        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                    <a href="{{ route('emergency') }}" class="inline-flex items-center gap-2 rounded-2xl bg-river-950/90 hover:bg-river-900 border border-white/30 px-5 py-3.5 font-bold text-white shadow-lg hover:-translate-y-0.5 transition-all duration-200">
                        <svg viewBox="0 0 24 24" class="size-4 text-alert" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg>
                        <span>Nomor darurat</span>
                    </a>
                </div>
            </div>

            {{-- Papan ringkasan Glassmorphic Gelap Kontras Tinggi --}}
            <div class="glass-card-dark rounded-3xl p-6 sm:p-7 grid gap-5 border border-white/20 shadow-2xl relative">
                <div class="flex items-center justify-between gap-3 border-b border-white/15 pb-3">
                    <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-emerald-300 flex items-center gap-2">
                        <svg viewBox="0 0 24 24" class="size-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.25"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Data Terverifikasi Desa
                    </p>
                    <span class="text-[0.7rem] px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-200 border border-emerald-400/40 font-bold">Terkini</span>
                </div>
                
                <dl class="grid grid-cols-2 gap-3.5">
                    <div class="rounded-2xl bg-river-950/85 p-4 border border-white/15 hover:border-flood/60 transition-colors">
                        <dt class="text-xs font-semibold text-river-100 flex items-center gap-2">
                            <span class="size-2.5 rounded-full bg-flood ring-4 ring-flood/30" aria-hidden="true"></span>
                            Rawan banjir
                        </dt>
                        <dd class="mt-2 text-3xl font-black text-white num">{{ $stats['banjir'] }}<span class="text-xs font-bold text-river-200 ml-1">lokasi</span></dd>
                    </div>
                    <div class="rounded-2xl bg-river-950/85 p-4 border border-white/15 hover:border-slide/60 transition-colors">
                        <dt class="text-xs font-semibold text-river-100 flex items-center gap-2">
                            <span class="size-2.5 rounded-full bg-slide ring-4 ring-slide/30" aria-hidden="true"></span>
                            Rawan longsor
                        </dt>
                        <dd class="mt-2 text-3xl font-black text-white num">{{ $stats['longsor'] }}<span class="text-xs font-bold text-river-200 ml-1">lokasi</span></dd>
                    </div>
                    <div class="rounded-2xl bg-river-950/85 p-4 border border-white/15 hover:border-safe/60 transition-colors">
                        <dt class="text-xs font-semibold text-river-100 flex items-center gap-2">
                            <span class="size-2.5 rounded-full bg-safe ring-4 ring-safe/30" aria-hidden="true"></span>
                            Titik kumpul
                        </dt>
                        <dd class="mt-2 text-3xl font-black text-white num">{{ $stats['points'] }}<span class="text-xs font-bold text-river-200 ml-1">tempat</span></dd>
                    </div>
                    <div class="rounded-2xl bg-river-950/85 p-4 border border-white/15 hover:border-route/60 transition-colors">
                        <dt class="text-xs font-semibold text-river-100 flex items-center gap-2">
                            <span class="size-2.5 rounded-full bg-route ring-4 ring-route/30" aria-hidden="true"></span>
                            Jalur evakuasi
                        </dt>
                        <dd class="mt-2 text-3xl font-black text-white num">{{ $stats['routes'] }}<span class="text-xs font-bold text-river-200 ml-1">jalur</span></dd>
                    </div>
                </dl>

                <a href="{{ route('risk-map') }}" class="inline-flex items-center justify-between w-full rounded-xl bg-river-900/90 hover:bg-river-850 border border-white/20 px-4 py-2.5 text-xs font-bold text-white transition-colors group shadow-xs">
                    <span>Eksplorasi Peta Risiko Interaktif Desa</span>
                    <svg viewBox="0 0 24 24" class="size-4 text-emerald-300 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </section>

    {{-- Profil Singkat Desa Lonjoboko di Bagian Atas --}}
    <section class="wrap pt-10 sm:pt-14 pb-2 grid gap-6">
        <div class="elevation-card rounded-3xl p-6 sm:p-8 lg:p-10 border border-river-200/70 bg-gradient-to-br from-white via-white to-river-50/60 shadow-lg relative overflow-hidden">
            {{-- Elemen ornamen latar lembut --}}
            <div class="absolute -right-20 -top-20 size-72 rounded-full bg-emerald-100/40 blur-3xl pointer-events-none" aria-hidden="true"></div>

            <div class="relative grid gap-8 lg:grid-cols-[1.35fr_1fr] lg:items-center">
                <div class="grid gap-4">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-emerald-800 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full shadow-2xs">
                            <svg viewBox="0 0 24 24" class="size-3.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.25"><path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"/><circle cx="12" cy="10" r="3"/></svg>
                            Profil Singkat Desa
                        </span>
                        <span class="text-xs font-semibold text-ink-soft">Kecamatan {{ $site['district'] }} · Kabupaten {{ $site['regency'] }}</span>
                    </div>

                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-ink">
                        Mengenal Desa {{ $site['village'] }}
                    </h2>

                    <div class="text-ink-soft text-sm sm:text-base leading-relaxed grid gap-2.5">
                        @if ($site['profile'])
                            <p class="whitespace-pre-line">{{ $site['profile'] }}</p>
                        @else
                            <p>
                                <strong>Desa {{ $site['village'] }}</strong> terletak di Kecamatan {{ $site['district'] }}, Kabupaten {{ $site['regency'] }}, Sulawesi Selatan. Wilayah desa ini berada pada bentang alam lereng pegunungan dan perbukitan yang subur, berhiaskan hamparan persawahan terasering yang asri serta dialiri oleh aliran sungai alami.
                            </p>
                            <p class="text-xs sm:text-sm text-ink-soft/90">
                                Topografi dataran tinggi berlereng ini selain menjadi pusat kehidupan pertanian warga, juga membutuhkan kesiapsiagaan bersama dalam mitigasi potensi bencana alam hidrometeorologi, khususnya tanah longsor pada tebing curam dan genangan banjir luapan sungai saat curah hujan tinggi.
                            </p>
                        @endif
                    </div>

                    @if ($site['population_note'])
                        <div class="mt-1 flex items-start gap-2.5 rounded-2xl bg-amber-50/80 border border-amber-200/80 p-3.5 text-xs text-amber-950">
                            <svg viewBox="0 0 24 24" class="size-4 shrink-0 text-amber-600 mt-0.5" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <div>
                                <span class="font-bold">Perhatian Kependudukan & Kelompok Rentan:</span>
                                <span class="block mt-0.5 whitespace-pre-line text-amber-900/90">{{ $site['population_note'] }}</span>
                            </div>
                        </div>
                    @endif

                    @include('public.partials.population', ['hamlets' => $hamlets, 'detailed' => false])

                    <div class="flex flex-wrap gap-2 pt-1 text-xs">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-river-50 border border-river-100 font-semibold text-river-800">
                            <svg viewBox="0 0 24 24" class="size-3.5 text-river-600" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 18l7-10 4 5 3-4 4 9H3z"/></svg>
                            Kawasan Lereng Pegunungan
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-river-50 border border-river-100 font-semibold text-river-800">
                            <svg viewBox="0 0 24 24" class="size-3.5 text-river-600" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
                            Persawahan Terasering & Sungai
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-100 font-semibold text-emerald-800">
                            <svg viewBox="0 0 24 24" class="size-3.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Kader Tangguh Bencana
                        </span>
                    </div>
                </div>

                {{-- Foto kartu profil: diunggah admin di Profil Desa > Foto desa (bawaan: ilustrasi) --}}
                @php $photo = \App\Support\SitePhotos::profile(); @endphp
                <div class="relative group rounded-2xl overflow-hidden border border-river-200/80 shadow-md bg-river-900 aspect-video lg:aspect-[4/3]">
                    <img src="{{ $photo['url'] }}" alt="{{ $photo['alt'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/25 to-transparent"></div>
                    <div class="absolute bottom-0 inset-x-0 p-4 sm:p-5 text-white">
                        <p class="text-[0.7rem] font-bold text-amber-300 uppercase tracking-wider">{{ $photo['custom'] ? 'Foto desa' : 'Ilustrasi bentang alam' }}</p>
                        <p class="text-xs sm:text-sm font-semibold leading-snug drop-shadow-sm text-river-50">{{ $photo['caption'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Langkah saat bencana --}}
    <section class="wrap py-14 grid gap-8">
        <div class="grid gap-2">
            <span class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-river-600 bg-river-50 border border-river-100 px-3 py-1 rounded-full w-fit">
                <span class="size-1.5 rounded-full bg-alert"></span>
                Kesiapsiagaan Tanggap Darurat
            </span>
            <h2 class="text-2xl sm:text-4xl font-extrabold tracking-tight">Tiga hal pertama yang harus dilakukan</h2>
        </div>
        {{-- Isi langkah diatur admin di menu Profil Desa (bagian Panduan keselamatan) --}}
        <ol class="grid gap-5 md:grid-cols-3">
            @foreach (\App\Support\SafetyGuide::firstSteps() as $step)
                <li class="elevation-card rounded-3xl p-6 sm:p-7 grid gap-3 content-start relative overflow-hidden">
                    <div class="flex items-center justify-between">
                        <span class="grid place-items-center size-11 rounded-2xl bg-gradient-to-br from-alert to-amber-600 text-white font-black text-lg shadow-md shadow-alert/25 num" aria-hidden="true">{{ $loop->iteration }}</span>
                        <span class="text-xs font-bold text-alert uppercase tracking-wider">Langkah {{ $loop->iteration }}</span>
                    </div>
                    <h3 class="font-extrabold text-xl text-ink">{{ $step['title'] }}</h3>
                    @if ($step['body'])
                        <p class="text-sm text-ink-soft leading-relaxed">{{ $step['body'] }}</p>
                    @endif
                </li>
            @endforeach
        </ol>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('evacuation') }}" class="action-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="6" cy="19" r="2"/><circle cx="18" cy="5" r="2"/><path d="M8 19h7a3.5 3.5 0 000-7H9a3.5 3.5 0 010-7h7"/></svg>
                <span>Lihat jalur evakuasi</span>
            </a>
            <a href="{{ route('emergency') }}" class="action-link action-link--ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg>
                <span>Kontak darurat desa</span>
            </a>
        </div>
    </section>

    {{-- Showcase Wilayah & Lanskap Pedesaan Lonjoboko --}}
    <section class="wrap pb-8">
        <div class="relative rounded-3xl overflow-hidden elevation-card border border-river-100">
            <div class="grid lg:grid-cols-[1.1fr_1fr] items-stretch">
                @php $landscape = \App\Support\SitePhotos::landscape(); @endphp
                <div class="relative min-h-[300px] lg:min-h-full">
                    <img src="{{ $landscape['url'] }}" alt="{{ $landscape['alt'] }}" loading="lazy" class="absolute inset-0 w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent lg:bg-gradient-to-r lg:from-transparent lg:to-black/30"></div>
                    <div class="absolute bottom-4 left-4 right-4 text-white">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-xs font-bold uppercase tracking-wider text-white border border-white/30">
                            <span class="size-1.5 rounded-full bg-emerald-400"></span>
                            {{ $landscape['custom'] ? 'Foto desa' : 'Ilustrasi bentang alam' }}
                        </span>
                        <p class="mt-2 text-sm font-semibold text-white/90 drop-shadow-sm">{{ $landscape['caption'] }}</p>
                    </div>
                </div>
                <div class="p-8 sm:p-10 flex flex-col justify-center gap-5 bg-card">
                    <span class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-river-700 bg-river-50 border border-river-100 px-3 py-1 rounded-full w-fit">
                        Karakteristik Wilayah & Mitigasi
                    </span>
                    <h3 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-ink">
                        Menjaga Keindahan, Mewaspadai Kerawanan Bencana
                    </h3>
                    <p class="text-ink-soft leading-relaxed text-sm sm:text-base">
                        Desa Lonjoboko memiliki topografi perbukitan dan hamparan sawah yang subur di dataran tinggi Gowa. Namun kontur lereng terjal dan aliran sungai memerlukan kesiapsiagaan terpadu saat musim penghujan dengan intensitas tinggi tiba.
                    </p>
                    <div class="grid gap-3 pt-2">
                        <div class="flex items-start gap-3">
                            <span class="size-6 shrink-0 rounded-full bg-flood-soft text-flood grid place-items-center mt-0.5">
                                <svg viewBox="0 0 24 24" class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <p class="text-xs sm:text-sm text-ink-soft"><strong class="text-ink">Kawasan Sawah & Aliran Sungai:</strong> Pemantauan kenaikan debit air dan antisipasi genangan banjir kiriman.</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="size-6 shrink-0 rounded-full bg-slide-soft text-slide grid place-items-center mt-0.5">
                                <svg viewBox="0 0 24 24" class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <p class="text-xs sm:text-sm text-ink-soft"><strong class="text-ink">Lereng Pegunungan:</strong> Identifikasi dini retakan tanah pada tebing dan perbukitan rawan longsor.</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="size-6 shrink-0 rounded-full bg-safe-soft text-safe grid place-items-center mt-0.5">
                                <svg viewBox="0 0 24 24" class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <p class="text-xs sm:text-sm text-ink-soft"><strong class="text-ink">Titik Evakuasi Strategis:</strong> Penempatan titik kumpul di dataran aman dan mudah dijangkau dari pemukiman warga.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Warga ikut menjadi sumber informasi: lapor kejadian & riwayat bencana --}}
    <section class="wrap pb-6">
        <div class="report-band">
            <div class="report-band__text">
                <p class="report-band__eyebrow">Warga ikut menjaga desa</p>
                <h2 class="report-band__title">Melihat banjir, longsor, atau tanda bahaya? Laporkan.</h2>
                <p class="report-band__lead">Cukup isi jenis kejadian, lokasi, dan foto bila ada. Kader Karang Taruna akan mengecek ke lokasi, lalu pemerintah desa menindaklanjuti.</p>
            </div>
            <div class="report-band__actions">
                <a href="{{ route('report') }}" class="report-band__btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 11v2a1 1 0 001 1h2l5 4V6L6 10H4a1 1 0 00-1 1z"/><path d="M15.5 8.5a5 5 0 010 7M18.5 5.5a9 9 0 010 13"/></svg>
                    Lapor kejadian
                </a>
                <a href="{{ route('history') }}" class="report-band__link">
                    Lihat riwayat bencana desa
                    @if ($latestEvent)
                        <small>Terakhir: {{ $latestEvent->occurred_on->translatedFormat('F Y') }}</small>
                    @endif
                </a>
            </div>
        </div>
    </section>

    {{-- Informasi Terkini & Kontak Cepat --}}
    <div class="wrap py-8 grid gap-10 lg:grid-cols-[1.4fr_1fr]">
        {{-- Informasi terkini --}}
        <section class="grid gap-4 content-start">
            <div class="flex items-end justify-between gap-4">
                <div class="grid gap-1">
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-river-600 bg-river-50 border border-river-100 px-3 py-0.5 rounded-full w-fit">
                        Kabar Desa
                    </span>
                    <h2 class="text-2xl font-extrabold tracking-tight">Informasi & Peringatan Terkini</h2>
                </div>
            </div>
            @forelse ($announcements as $item)
                <a href="{{ route('announcement.show', $item) }}" class="elevation-card block rounded-2xl p-5 hover:border-river-600">
                    <div class="flex flex-wrap items-center gap-2 text-sm text-ink-soft">
                        <x-hazard-badge :type="$item->level" />
                        <time datetime="{{ $item->published_at?->toIso8601String() }}" class="text-xs font-medium text-ink-soft/80">{{ $item->published_at?->translatedFormat('l, d F Y · H:i') }} WITA</time>
                    </div>
                    <h3 class="mt-2.5 font-bold text-lg leading-snug text-ink">{{ $item->title }}</h3>
                    <p class="mt-1 text-sm text-ink-soft line-clamp-2 leading-relaxed">{{ $item->body }}</p>
                </a>
            @empty
                <div class="rounded-2xl border border-dashed border-line bg-card p-6 text-ink-soft text-center py-8">
                    <p class="font-medium">Belum ada peringatan darurat saat ini.</p>
                    <p class="text-xs text-ink-soft/70 mt-1">Informasi dan peringatan resmi pemerintah desa akan otomatis muncul di sini.</p>
                </div>
            @endforelse
        </section>

        {{-- Kontak cepat --}}
        <aside class="grid gap-4 content-start">
            <div class="grid gap-1">
                <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-alert bg-alert-soft border border-alert/20 px-3 py-0.5 rounded-full w-fit">
                    Butuh Bantuan
                </span>
                <h2 class="text-2xl font-extrabold tracking-tight">Kontak Darurat Desa</h2>
            </div>
            @if ($contacts->isNotEmpty())
                <ul class="grid gap-3">
                    @foreach ($contacts as $contact)
                        <x-contact-row :contact="$contact" />
                    @endforeach
                </ul>
            @else
                <p class="rounded-2xl border border-dashed border-line bg-card p-6 text-ink-soft">Kontak darurat belum diisi.</p>
            @endif
            <a href="{{ route('emergency') }}" class="action-link mt-1">
                <span>Lihat semua nomor kontak & alur pelaporan</span>
                <svg class="action-link__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        </aside>
    </div>

    {{-- Edukasi --}}
    @if ($education->isNotEmpty())
        <section class="wrap pt-10 grid gap-6">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div class="grid gap-1">
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-river-600 bg-river-50 border border-river-100 px-3 py-0.5 rounded-full w-fit">
                        Belajar Bersiap
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Edukasi Mitigasi Bencana</h2>
                </div>
                <a href="{{ route('education') }}" class="action-link">
                    <span>Lihat semua modul & panduan</span>
                    <svg class="action-link__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
            </div>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($education as $item)
                    <x-education-card :item="$item" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Program & Kolaborasi Kesiapsiagaan Desa --}}
    <section class="wrap pt-14 pb-8 grid gap-6 md:grid-cols-2">
        <div class="elevation-card rounded-3xl p-7 grid gap-3.5 content-start">
            <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-river-600 bg-river-50 border border-river-100 px-3 py-0.5 rounded-full w-fit">
                Tentang Program
            </span>
            <h2 class="text-xl font-extrabold text-ink">LONJOBOKO RESCUE</h2>
            <p class="text-ink-soft leading-relaxed text-sm whitespace-pre-line">{{ $site['program'] ?: 'LONJOBOKO RESCUE adalah inovasi teknologi informasi berbasis web yang menjadi media informasi, edukasi, dan komunikasi kebencanaan bagi masyarakat Desa '.$site['village'].', sebagai upaya mewujudkan desa tangguh bencana banjir dan tanah longsor.' }}</p>
            <div class="pt-1 flex items-center gap-2 text-xs text-river-700 font-semibold">
                <svg viewBox="0 0 24 24" class="size-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Program Pengabdian Kepada Masyarakat & Pemerintah Desa</span>
            </div>
        </div>
        <div class="elevation-card rounded-3xl p-7 grid gap-3.5 content-start">
            <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-0.5 rounded-full w-fit">
                Komitmen Bersama
            </span>
            <h2 class="text-xl font-extrabold text-ink">Mewujudkan Desa Tangguh Bencana</h2>
            <p class="text-ink-soft leading-relaxed text-sm">
                Partisipasi aktif warga dan kader desa merupakan kunci keselamatan utama. Akses informasi jalur evakuasi, kenali titik kumpul aman, dan mari bergabung menjadi relawan siaga bencana.
            </p>
            <div class="flex flex-wrap items-center gap-3 pt-2">
                <a href="{{ route('volunteers') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-xs transition-colors">
                    <svg viewBox="0 0 24 24" class="size-3.5" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span>Kader Relawan Desa</span>
                </a>
                <a href="{{ route('risk-map') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-river-50 hover:bg-river-100 text-river-900 border border-river-200 font-bold text-xs transition-colors">
                    <span>Lihat Peta Risiko</span>
                    <svg viewBox="0 0 24 24" class="size-3.5 text-river-700" fill="none" stroke="currentColor" stroke-width="2.25"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </section>
@endsection
