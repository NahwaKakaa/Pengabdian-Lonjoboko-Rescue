@extends('layouts.public')

@section('title', 'Informasi Darurat')

@php
    $steps = collect(preg_split('/\r\n|\r|\n/', (string) $site['report_instructions']))
        ->map(fn ($line) => trim(preg_replace('/^\s*(\d+[.)]|[-*•])\s*/u', '', $line)))
        ->filter()
        ->values();

    if ($steps->isEmpty()) {
        $steps = collect([
            'Pastikan diri Anda sudah berada di tempat aman.',
            'Hubungi kepala dusun, aparat desa, atau kader terdekat lewat nomor di halaman ini.',
            'Sebutkan: lokasi kejadian (dusun dan patokan), jenis kejadian, jumlah warga yang terdampak, dan apakah ada yang terluka atau terjebak.',
            'Bila ada korban luka berat, hubungi juga layanan gawat darurat.',
            'Jangan menyebarkan kabar yang belum jelas sumbernya.',
        ]);
    }
@endphp

@section('content')
    <x-page-header
        eyebrow="Informasi darurat"
        title="Peringatan dan nomor penting"
        intro="Simpan nomor-nomor ini di HP Anda sebelum keadaan darurat terjadi."
    />

    <div class="wrap py-8 grid gap-10 lg:grid-cols-[1.4fr_1fr]">
        <div class="grid gap-10 content-start">
            {{-- Peringatan aktif --}}
            <section class="grid gap-4" aria-labelledby="peringatan">
                <h2 id="peringatan" class="text-2xl font-extrabold tracking-tight">Peringatan yang berlaku</h2>
                @forelse ($alerts as $alert)
                    @php $isDarurat = $alert->level === \App\Enums\AnnouncementLevel::Darurat; @endphp
                    <a href="{{ route('announcement.show', $alert) }}"
                       @class(['block rounded-2xl border-l-8 p-5', 'border-danger bg-danger-soft' => $isDarurat, 'border-alert bg-alert-soft' => ! $isDarurat])>
                        <div class="flex flex-wrap items-center gap-2 text-sm">
                            <x-hazard-badge :type="$alert->level" />
                            @if ($alert->source)
                                <span class="text-ink-soft">Sumber: {{ $alert->source }}</span>
                            @endif
                        </div>
                        <h3 class="mt-2 font-bold text-lg">{{ $alert->title }}</h3>
                        <p class="mt-1 text-sm text-ink-soft line-clamp-3">{{ $alert->body }}</p>
                        @if ($alert->expires_at)
                            <p class="mt-2 text-xs font-semibold text-ink-soft">Berlaku sampai {{ $alert->expires_at->translatedFormat('d F Y, H:i') }} WITA</p>
                        @endif
                    </a>
                @empty
                    <div class="elevation-card rounded-2xl p-6 flex items-start gap-3.5">
                        <span class="mt-1 size-3 shrink-0 rounded-full bg-safe ring-4 ring-safe/20" aria-hidden="true"></span>
                        <p class="text-ink-soft leading-relaxed text-sm">Tidak ada peringatan darurat aktif dari pemerintah desa saat ini. Tetap pantau informasi cuaca resmi dari BMKG, terutama saat intensitas hujan lebat terjadi terus-menerus.</p>
                    </div>
                @endforelse
            </section>

            {{-- Kontak --}}
            <section class="grid gap-6" aria-labelledby="kontak">
                <h2 id="kontak" class="text-2xl font-extrabold tracking-tight">Kontak darurat</h2>
                @forelse ($contactGroups as $label => $contacts)
                    <div class="grid gap-3">
                        <h3 class="eyebrow">{{ $label }}</h3>
                        <ul class="grid gap-3">
                            @foreach ($contacts as $contact)
                                <x-contact-row :contact="$contact" />
                            @endforeach
                        </ul>
                    </div>
                @empty
                    <p class="rounded-2xl border border-dashed border-line bg-card p-6 text-ink-soft">Kontak darurat belum diisi oleh pengelola.</p>
                @endforelse
            </section>
        </div>

        <aside class="grid gap-6 content-start">
            <section class="elevation-card rounded-3xl bg-gradient-to-br from-river-900 via-river-800 to-river-900 text-white p-6 sm:p-7 grid gap-4 border border-river-700/60 shadow-xl" aria-labelledby="lapor">
                <h2 id="lapor" class="text-xl font-extrabold flex items-center gap-2">
                    <svg viewBox="0 0 24 24" class="size-5 text-amber-300" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Cara Melaporkan Kejadian
                </h2>
                <ol class="grid gap-3.5">
                    @foreach ($steps as $i => $step)
                        <li class="grid grid-cols-[auto_1fr] gap-3 items-start">
                            <span class="grid place-items-center size-7 rounded-xl bg-white text-river-900 text-xs font-black shadow-xs num shrink-0 mt-0.5" aria-hidden="true">{{ $i + 1 }}</span>
                            <span class="text-river-100/90 leading-relaxed text-sm">{{ $step }}</span>
                        </li>
                    @endforeach
                </ol>
                @if ($site['office_phone'])
                    <p class="text-sm text-river-100/90 border-t border-white/15 pt-3 flex items-center gap-2">
                        <span>Kantor Desa:</span>
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $site['office_phone']) }}" class="font-bold text-white num underline underline-offset-4">{{ $site['office_phone'] }}</a>
                    </p>
                @endif
            </section>

            {{-- Poster siaga untuk dicetak dan dipasang di kantor desa / titik kumpul --}}
            <section class="elevation-card rounded-3xl p-6 sm:p-7 grid gap-3" aria-labelledby="poster">
                <div class="flex items-center gap-3">
                    <span class="grid place-items-center size-10 shrink-0 rounded-xl bg-river-50 text-river-700 border border-river-100" aria-hidden="true">
                        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V3h12v6M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><path d="M6 14h12v7H6z"/></svg>
                    </span>
                    <h2 id="poster" class="text-lg font-extrabold text-ink">Poster siaga untuk dicetak</h2>
                </div>
                <p class="text-sm text-ink-soft leading-relaxed">Peta evakuasi, titik kumpul, dan nomor darurat dalam satu lembar A4, lengkap dengan kode QR ke web ini. Pasang di kantor desa, masjid, dan titik kumpul supaya tetap terbaca saat sinyal atau listrik padam.</p>
                <a href="{{ route('poster') }}" class="action-link">
                    <span>Buka poster</span>
                    <svg class="action-link__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
            </section>

            <section class="elevation-card rounded-3xl p-6 sm:p-7 grid gap-4" aria-labelledby="sumber">
                <h2 id="sumber" class="text-lg font-extrabold text-ink">Sumber Peringatan Dini Resmi</h2>
                <p class="text-xs text-ink-soft leading-relaxed">Web desa ini meneruskan informasi dan panduan lapangan bagi warga. Untuk data meteorologi dan hidrometeorologi resmi, rujuk instansi berikut:</p>
                <ul class="grid gap-3">
                    @foreach ($sources as $source)
                        <li class="border-t border-line/60 pt-3 first:border-0 first:pt-0">
                            <a href="{{ $source['url'] }}" target="_blank" rel="noopener" class="action-link">
                                <span>{{ $source['name'] }}</span>
                                <svg class="action-link__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17L17 7M9 7h8v8"/></svg>
                            </a>
                            <p class="text-xs text-ink-soft mt-1.5 leading-relaxed">{{ $source['description'] }}</p>
                        </li>
                    @endforeach
                </ul>
            </section>
        </aside>
    </div>
@endsection
