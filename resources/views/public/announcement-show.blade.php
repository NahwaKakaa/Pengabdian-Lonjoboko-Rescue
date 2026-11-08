@extends('layouts.public')

@section('title', $announcement->title)

@section('content')
    @php
        $isDarurat = $announcement->level === \App\Enums\AnnouncementLevel::Darurat;
        $isWaspada = $announcement->level === \App\Enums\AnnouncementLevel::Waspada;
    @endphp
    <div class="wrap py-10 max-w-4xl">
        <article class="elevation-card rounded-3xl p-6 sm:p-10 bg-card grid gap-6">
            <a href="{{ route('home') }}" class="action-link action-link--ghost">
                <svg class="action-link__back" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
                <span>Kembali ke beranda</span>
            </a>

            <header class="grid gap-3">
                <div class="flex flex-wrap items-center gap-2 text-sm text-ink-soft">
                    <x-hazard-badge :type="$announcement->level" />
                    <time datetime="{{ $announcement->published_at?->toIso8601String() }}" class="text-xs font-semibold">{{ $announcement->published_at?->translatedFormat('l, d F Y · H:i') }} WITA</time>
                </div>
                <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight text-ink">{{ $announcement->title }}</h1>
            </header>

            @if ($announcement->isExpired())
                <p class="rounded-xl bg-river-50 border border-river-100 px-4 py-3 text-sm font-semibold text-ink-soft">Informasi ini sudah tidak berlaku sejak {{ $announcement->expires_at->translatedFormat('d F Y, H:i') }} WITA.</p>
            @elseif ($isDarurat || $isWaspada)
                <p @class(['rounded-xl px-4 py-3 text-sm font-semibold border-l-8', 'bg-danger-soft border-danger' => $isDarurat, 'bg-alert-soft border-alert' => $isWaspada])>
                    @if ($announcement->expires_at)
                        Berlaku sampai {{ $announcement->expires_at->translatedFormat('d F Y, H:i') }} WITA.
                    @else
                        Peringatan ini berlaku sampai ada pemberitahuan lebih lanjut.
                    @endif
                </p>
            @endif

            <div class="prose-lr text-[1.0625rem] whitespace-pre-line text-ink">{{ $announcement->body }}</div>

            @if ($announcement->source)
                <p class="text-sm text-ink-soft border-t border-line/60 pt-4">
                    Sumber: <span class="font-semibold text-ink">{{ $announcement->source }}</span>
                    @if ($announcement->source_url)
                        · <a href="{{ $announcement->source_url }}" target="_blank" rel="noopener" class="text-river-700 underline underline-offset-4">lihat sumber resmi</a>
                    @endif
                </p>
            @endif

            <div class="flex flex-wrap gap-3 pt-4 border-t border-line/60">
                <a href="{{ route('evacuation') }}" class="rounded-xl bg-river-800 px-5 py-2.5 font-bold text-white hover:bg-river-700 transition-colors shadow-xs">Lihat jalur evakuasi</a>
                <a href="{{ route('emergency') }}" class="rounded-xl border border-line bg-card px-5 py-2.5 font-bold hover:bg-river-50 transition-colors">Kontak darurat</a>
            </div>
        </article>
    </div>
@endsection
