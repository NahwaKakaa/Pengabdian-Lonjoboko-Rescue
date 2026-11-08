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

    {{-- Langkah saat bencana --}}
    <section class="wrap py-14 grid gap-8">
        <div class="grid gap-2">
            <span class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-river-600 bg-river-50 border border-river-100 px-3 py-1 rounded-full w-fit">
                <span class="size-1.5 rounded-full bg-alert"></span>
                Kesiapsiagaan Tanggap Darurat
            </span>
            <h2 class="text-2xl sm:text-4xl font-extrabold tracking-tight">Tiga hal pertama yang harus dilakukan</h2>
        </div>
        <ol class="grid gap-5 md:grid-cols-3">
            <li class="elevation-card rounded-3xl p-6 sm:p-7 grid gap-3 content-start relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="grid place-items-center size-11 rounded-2xl bg-gradient-to-br from-alert to-amber-600 text-white font-black text-lg shadow-md shadow-alert/25 num" aria-hidden="true">1</span>
                    <span class="text-xs font-bold text-alert uppercase tracking-wider">Langkah 1</span>
                </div>
                <h3 class="font-extrabold text-xl text-ink">Selamatkan diri, bukan barang</h3>
                <p class="text-sm text-ink-soft leading-relaxed">Bawa tas siaga bencana, segera matikan listrik dari meteran utama bila aman, dan utamakan anak-anak, lansia, ibu hamil, serta penyandang disabilitas.</p>
            </li>
            <li class="elevation-card rounded-3xl p-6 sm:p-7 grid gap-3 content-start relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="grid place-items-center size-11 rounded-2xl bg-gradient-to-br from-alert to-amber-600 text-white font-black text-lg shadow-md shadow-alert/25 num" aria-hidden="true">2</span>
                    <span class="text-xs font-bold text-alert uppercase tracking-wider">Langkah 2</span>
                </div>
                <h3 class="font-extrabold text-xl text-ink">Ikuti jalur ke titik kumpul</h3>
                <p class="text-sm text-ink-soft leading-relaxed">Jangan menyeberangi arus banjir deras dan jauhi lereng perbukitan yang retak atau longsor. Buka <a href="{{ route('evacuation') }}" class="font-bold text-river-700 underline underline-offset-2">jalur evakuasi</a> terdekat dari lokasi Anda.</p>
            </li>
            <li class="elevation-card rounded-3xl p-6 sm:p-7 grid gap-3 content-start relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="grid place-items-center size-11 rounded-2xl bg-gradient-to-br from-alert to-amber-600 text-white font-black text-lg shadow-md shadow-alert/25 num" aria-hidden="true">3</span>
                    <span class="text-xs font-bold text-alert uppercase tracking-wider">Langkah 3</span>
                </div>
                <h3 class="font-extrabold text-xl text-ink">Segera lapor aparat desa</h3>
                <p class="text-sm text-ink-soft leading-relaxed">Segera laporkan status terkini, warga yang membutuhkan evakuasi, atau korban luka-luka melalui <a href="{{ route('emergency') }}" class="font-bold text-river-700 underline underline-offset-2">kontak darurat desa</a>.</p>
            </li>
        </ol>
    </section>

    {{-- Showcase Wilayah & Lanskap Pedesaan Lonjoboko --}}
    <section class="wrap pb-8">
        <div class="relative rounded-3xl overflow-hidden elevation-card border border-river-100">
            <div class="grid lg:grid-cols-[1.1fr_1fr] items-stretch">
                <div class="relative min-h-[300px] lg:min-h-full">
                    <img src="{{ asset('images/hero-mountains.jpg') }}" alt="Bentang Alam Pegunungan dan Hamparan Sawah Desa Lonjoboko" class="absolute inset-0 w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent lg:bg-gradient-to-r lg:from-transparent lg:to-black/30"></div>
                    <div class="absolute bottom-4 left-4 right-4 text-white">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-xs font-bold uppercase tracking-wider text-white border border-white/30">
                            <span class="size-1.5 rounded-full bg-emerald-400"></span>
                            Bentang Alam Lonjoboko
                        </span>
                        <p class="mt-2 text-sm font-semibold text-white/90 drop-shadow-sm">Hamparan sawah terasering dan gugusan pegunungan kaki Bawakaraeng</p>
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
                            <span class="size-6 shrink-0 rounded-full bg-flood-soft text-flood grid place-items-center font-bold text-xs mt-0.5">✓</span>
                            <p class="text-xs sm:text-sm text-ink-soft"><strong class="text-ink">Kawasan Sawah & Aliran Sungai:</strong> Pemantauan kenaikan debit air dan antisipasi genangan banjir kiriman.</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="size-6 shrink-0 rounded-full bg-slide-soft text-slide grid place-items-center font-bold text-xs mt-0.5">✓</span>
                            <p class="text-xs sm:text-sm text-ink-soft"><strong class="text-ink">Lereng Pegunungan:</strong> Identifikasi dini retakan tanah pada tebing dan perbukitan rawan longsor.</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="size-6 shrink-0 rounded-full bg-safe-soft text-safe grid place-items-center font-bold text-xs mt-0.5">✓</span>
                            <p class="text-xs sm:text-sm text-ink-soft"><strong class="text-ink">Titik Evakuasi Strategis:</strong> Penempatan titik kumpul di dataran aman dan mudah dijangkau dari pemukiman warga.</p>
                        </div>
                    </div>
                </div>
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
            <a href="{{ route('emergency') }}" class="inline-flex items-center gap-1 text-sm font-bold text-river-700 hover:text-river-900 underline underline-offset-4 w-fit pt-1">
                <span>Lihat semua nomor kontak & alur pelaporan</span>
                <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2.25"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
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
                <a href="{{ route('education') }}" class="inline-flex items-center gap-1 text-sm font-bold text-river-700 hover:text-river-900 underline underline-offset-4">
                    <span>Lihat semua modul & panduan</span>
                    <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2.25"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </div>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($education as $item)
                    <x-education-card :item="$item" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Profil desa & program --}}
    <section class="wrap pt-14 pb-8 grid gap-6 md:grid-cols-2">
        <div class="elevation-card rounded-3xl p-7 grid gap-3 content-start">
            <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-river-600 bg-river-50 border border-river-100 px-3 py-0.5 rounded-full w-fit">
                Tentang Program
            </span>
            <h2 class="text-xl font-extrabold text-ink">LONJOBOKO-RESCUE</h2>
            <p class="text-ink-soft leading-relaxed text-sm whitespace-pre-line">{{ $site['program'] ?: 'LONJOBOKO-RESCUE adalah inovasi teknologi informasi berbasis web yang menjadi media informasi, edukasi, dan komunikasi kebencanaan bagi masyarakat Desa '.$site['village'].', sebagai upaya mewujudkan desa tangguh bencana banjir dan tanah longsor.' }}</p>
        </div>
        <div class="elevation-card rounded-3xl p-7 grid gap-3 content-start">
            <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-river-600 bg-river-50 border border-river-100 px-3 py-0.5 rounded-full w-fit">
                Profil Desa
            </span>
            <h2 class="text-xl font-extrabold text-ink">Desa {{ $site['village'] }}</h2>
            @if ($site['profile'])
                <p class="text-ink-soft leading-relaxed text-sm whitespace-pre-line">{{ $site['profile'] }}</p>
            @else
                <p class="text-ink-soft leading-relaxed text-sm">Kecamatan {{ $site['district'] }}, Kabupaten {{ $site['regency'] }}, Sulawesi Selatan.</p>
            @endif
            @if ($site['population_note'])
                <p class="text-xs text-ink-soft leading-relaxed whitespace-pre-line border-t border-line pt-3 mt-1"><span class="font-bold text-ink">Kelompok rentan desa:</span> {{ $site['population_note'] }}</p>
            @endif
        </div>
    </section>
@endsection
