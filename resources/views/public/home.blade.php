@extends('layouts.public')

@section('content')
    {{-- Pembuka --}}
    <section class="bg-river-800 text-white overflow-hidden">
        <div class="wrap grid gap-10 py-10 sm:py-14 lg:grid-cols-[1.15fr_1fr] lg:items-center">
            <div class="grid gap-5">
                <p class="text-xs font-bold uppercase tracking-[0.12em] text-river-100">
                    Desa {{ $site['village'] }} · Kec. {{ $site['district'] }} · Kab. {{ $site['regency'] }}
                </p>
                <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight leading-[1.1]">
                    Saat banjir atau longsor datang, tahu ke mana harus pergi.
                </h1>
                <p class="text-river-100 text-base sm:text-lg leading-relaxed max-w-[55ch]">
                    Peta lokasi rawan, jalur evakuasi, titik kumpul, dan nomor darurat Desa {{ $site['village'] }} dalam satu tempat. Bisa dibuka dari HP kapan saja.
                </p>
                <div class="flex flex-wrap gap-3 pt-1">
                    <a href="{{ route('evacuation') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 font-bold text-river-900 hover:bg-river-50">
                        Lihat jalur evakuasi
                        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                    <a href="{{ route('emergency') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/30 px-5 py-3 font-bold hover:bg-white/10">
                        Nomor darurat
                    </a>
                </div>
            </div>

            {{-- Papan ringkasan --}}
            <div class="rounded-2xl bg-river-900/60 ring-1 ring-white/10 p-5 sm:p-6 grid gap-4">
                <p class="text-xs font-bold uppercase tracking-[0.12em] text-river-100">Data peta desa yang sudah diverifikasi</p>
                <dl class="grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-white/5 p-4">
                        <dt class="text-sm text-river-100 flex items-center gap-2"><span class="size-2.5 rounded-full bg-flood" aria-hidden="true"></span>Rawan banjir</dt>
                        <dd class="mt-1 text-3xl font-extrabold num">{{ $stats['banjir'] }}<span class="text-base font-semibold text-river-100"> lokasi</span></dd>
                    </div>
                    <div class="rounded-xl bg-white/5 p-4">
                        <dt class="text-sm text-river-100 flex items-center gap-2"><span class="size-2.5 rounded-full bg-slide" aria-hidden="true"></span>Rawan longsor</dt>
                        <dd class="mt-1 text-3xl font-extrabold num">{{ $stats['longsor'] }}<span class="text-base font-semibold text-river-100"> lokasi</span></dd>
                    </div>
                    <div class="rounded-xl bg-white/5 p-4">
                        <dt class="text-sm text-river-100 flex items-center gap-2"><span class="size-2.5 rounded-full bg-safe" aria-hidden="true"></span>Titik kumpul</dt>
                        <dd class="mt-1 text-3xl font-extrabold num">{{ $stats['points'] }}<span class="text-base font-semibold text-river-100"> tempat</span></dd>
                    </div>
                    <div class="rounded-xl bg-white/5 p-4">
                        <dt class="text-sm text-river-100 flex items-center gap-2"><span class="size-2.5 rounded-full bg-route" aria-hidden="true"></span>Jalur evakuasi</dt>
                        <dd class="mt-1 text-3xl font-extrabold num">{{ $stats['routes'] }}<span class="text-base font-semibold text-river-100"> jalur</span></dd>
                    </div>
                </dl>
                <a href="{{ route('risk-map') }}" class="text-sm font-semibold text-white underline underline-offset-4 decoration-white/40 hover:decoration-white w-fit">Buka peta risiko desa</a>
            </div>
        </div>
    </section>

    {{-- Langkah saat bencana --}}
    <section class="wrap py-12 grid gap-6">
        <div class="grid gap-2">
            <p class="eyebrow">Saat keadaan darurat</p>
            <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Tiga hal pertama yang dilakukan</h2>
        </div>
        <ol class="grid gap-4 md:grid-cols-3">
            <li class="rounded-2xl border border-line bg-card p-5 grid gap-2 content-start">
                <span class="grid place-items-center size-9 rounded-full bg-alert text-white font-extrabold num" aria-hidden="true">1</span>
                <h3 class="font-bold text-lg">Selamatkan diri, bukan barang</h3>
                <p class="text-sm text-ink-soft leading-relaxed">Bawa tas siaga, matikan listrik dari meteran bila aman, dan dahulukan anak, lansia, ibu hamil, serta penyandang disabilitas.</p>
            </li>
            <li class="rounded-2xl border border-line bg-card p-5 grid gap-2 content-start">
                <span class="grid place-items-center size-9 rounded-full bg-alert text-white font-extrabold num" aria-hidden="true">2</span>
                <h3 class="font-bold text-lg">Ikuti jalur ke titik kumpul</h3>
                <p class="text-sm text-ink-soft leading-relaxed">Jangan menyeberangi arus banjir dan jauhi lereng yang retak. Lihat <a href="{{ route('evacuation') }}" class="font-semibold text-river-700 underline underline-offset-2">jalur evakuasi</a> terdekat dari rumah Anda.</p>
            </li>
            <li class="rounded-2xl border border-line bg-card p-5 grid gap-2 content-start">
                <span class="grid place-items-center size-9 rounded-full bg-alert text-white font-extrabold num" aria-hidden="true">3</span>
                <h3 class="font-bold text-lg">Kabari aparat desa</h3>
                <p class="text-sm text-ink-soft leading-relaxed">Laporkan kejadian, warga yang belum terevakuasi, atau yang terluka lewat <a href="{{ route('emergency') }}" class="font-semibold text-river-700 underline underline-offset-2">kontak darurat</a>.</p>
            </li>
        </ol>
    </section>

    <div class="wrap grid gap-10 lg:grid-cols-[1.4fr_1fr]">
        {{-- Informasi terkini --}}
        <section class="grid gap-4 content-start">
            <div class="flex items-end justify-between gap-4">
                <div class="grid gap-1">
                    <p class="eyebrow">Kabar desa</p>
                    <h2 class="text-2xl font-extrabold tracking-tight">Informasi terkini</h2>
                </div>
            </div>
            @forelse ($announcements as $item)
                <a href="{{ route('announcement.show', $item) }}" class="block rounded-2xl border border-line bg-card p-5 hover:border-river-600 transition-colors">
                    <div class="flex flex-wrap items-center gap-2 text-sm text-ink-soft">
                        <x-hazard-badge :type="$item->level" />
                        <time datetime="{{ $item->published_at?->toIso8601String() }}">{{ $item->published_at?->translatedFormat('l, d F Y · H:i') }} WITA</time>
                    </div>
                    <h3 class="mt-2 font-bold text-lg leading-snug">{{ $item->title }}</h3>
                    <p class="mt-1 text-sm text-ink-soft line-clamp-2">{{ $item->body }}</p>
                </a>
            @empty
                <div class="rounded-2xl border border-dashed border-line bg-card p-6 text-ink-soft">
                    Belum ada informasi dari pemerintah desa. Informasi dan peringatan baru akan muncul di sini.
                </div>
            @endforelse
        </section>

        {{-- Kontak cepat --}}
        <aside class="grid gap-4 content-start">
            <div class="grid gap-1">
                <p class="eyebrow">Butuh bantuan</p>
                <h2 class="text-2xl font-extrabold tracking-tight">Kontak darurat</h2>
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
            <a href="{{ route('emergency') }}" class="text-sm font-semibold text-river-700 underline underline-offset-4 w-fit">Semua kontak dan cara melapor</a>
        </aside>
    </div>

    {{-- Edukasi --}}
    @if ($education->isNotEmpty())
        <section class="wrap pt-14 grid gap-5">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div class="grid gap-1">
                    <p class="eyebrow">Belajar bersiap</p>
                    <h2 class="text-2xl font-extrabold tracking-tight">Edukasi mitigasi bencana</h2>
                </div>
                <a href="{{ route('education') }}" class="text-sm font-semibold text-river-700 underline underline-offset-4">Lihat semua</a>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($education as $item)
                    <x-education-card :item="$item" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Profil desa & program --}}
    <section class="wrap pt-14 grid gap-6 md:grid-cols-2">
        <div class="rounded-2xl bg-card border border-line p-6 grid gap-3 content-start">
            <p class="eyebrow">Tentang program</p>
            <h2 class="text-xl font-extrabold">LONJOBOKO-RESCUE</h2>
            <p class="text-ink-soft leading-relaxed whitespace-pre-line">{{ $site['program'] ?: 'LONJOBOKO-RESCUE adalah inovasi teknologi informasi berbasis web yang menjadi media informasi, edukasi, dan komunikasi kebencanaan bagi masyarakat Desa '.$site['village'].', sebagai upaya mewujudkan desa tangguh bencana banjir dan tanah longsor.' }}</p>
        </div>
        <div class="rounded-2xl bg-card border border-line p-6 grid gap-3 content-start">
            <p class="eyebrow">Profil desa</p>
            <h2 class="text-xl font-extrabold">Desa {{ $site['village'] }}</h2>
            @if ($site['profile'])
                <p class="text-ink-soft leading-relaxed whitespace-pre-line">{{ $site['profile'] }}</p>
            @else
                <p class="text-ink-soft leading-relaxed">Kecamatan {{ $site['district'] }}, Kabupaten {{ $site['regency'] }}, Sulawesi Selatan.</p>
            @endif
            @if ($site['population_note'])
                <p class="text-sm text-ink-soft leading-relaxed whitespace-pre-line border-t border-line pt-3"><span class="font-bold text-ink">Kelompok rentan:</span> {{ $site['population_note'] }}</p>
            @endif
        </div>
    </section>
@endsection
