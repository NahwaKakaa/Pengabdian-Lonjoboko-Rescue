@extends('layouts.public')

@section('title', $announcement->title)

@section('content')
    @php
        $isDarurat = $announcement->level === \App\Enums\AnnouncementLevel::Darurat;
        $isWaspada = $announcement->level === \App\Enums\AnnouncementLevel::Waspada;
    @endphp
    <article class="wrap py-10 grid gap-6 max-w-3xl">
        <a href="{{ route('home') }}" class="text-sm text-ink-soft underline underline-offset-4 w-fit">Kembali ke beranda</a>

        <header class="grid gap-3">
            <div class="flex flex-wrap items-center gap-2 text-sm text-ink-soft">
                <x-hazard-badge :type="$announcement->level" />
                <time datetime="{{ $announcement->published_at?->toIso8601String() }}">{{ $announcement->published_at?->translatedFormat('l, d F Y · H:i') }} WITA</time>
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">{{ $announcement->title }}</h1>
        </header>

        @if ($announcement->isExpired())
            <p class="rounded-xl bg-river-50 border border-river-100 px-4 py-3 text-sm font-semibold">Informasi ini sudah tidak berlaku sejak {{ $announcement->expires_at->translatedFormat('d F Y, H:i') }} WITA.</p>
        @elseif ($isDarurat || $isWaspada)
            <p @class(['rounded-xl px-4 py-3 text-sm font-semibold border-l-8', 'bg-danger-soft border-danger' => $isDarurat, 'bg-alert-soft border-alert' => $isWaspada])>
                @if ($announcement->expires_at)
                    Berlaku sampai {{ $announcement->expires_at->translatedFormat('d F Y, H:i') }} WITA.
                @else
                    Peringatan ini berlaku sampai ada pemberitahuan lebih lanjut.
                @endif
            </p>
        @endif

        <div class="prose-lr text-[1.0625rem] whitespace-pre-line">{{ $announcement->body }}</div>

        @if ($announcement->source)
            <p class="text-sm text-ink-soft border-t border-line pt-4">
                Sumber: <span class="font-semibold text-ink">{{ $announcement->source }}</span>
                @if ($announcement->source_url)
                    · <a href="{{ $announcement->source_url }}" target="_blank" rel="noopener" class="text-river-700 underline underline-offset-4">lihat sumber</a>
                @endif
            </p>
        @endif

        <div class="flex flex-wrap gap-3 pt-2">
            <a href="{{ route('evacuation') }}" class="rounded-xl bg-river-800 px-4 py-2.5 font-bold text-white hover:bg-river-700">Lihat jalur evakuasi</a>
            <a href="{{ route('emergency') }}" class="rounded-xl border border-line bg-card px-4 py-2.5 font-bold hover:bg-river-50">Kontak darurat</a>
        </div>
    </article>
@endsection
