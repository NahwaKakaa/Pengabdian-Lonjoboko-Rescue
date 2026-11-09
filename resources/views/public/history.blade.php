@extends('layouts.public')

@section('title', 'Riwayat Kejadian')

@php
    $fmt = fn ($n) => number_format((int) $n, 0, ',', '.');
@endphp

@section('content')
    <x-page-header
        eyebrow="Riwayat kejadian"
        title="Bencana yang pernah terjadi di Desa {{ $site['village'] }}"
        intro="Catatan kejadian banjir dan tanah longsor beserta jumlah korbannya, supaya warga dan pemerintah desa belajar dari kejadian sebelumnya."
    >
        <ul class="vol-stats">
            <li><strong class="num">{{ $events->count() }}</strong> <span>kejadian tercatat</span></li>
            <li><strong class="num">{{ $fmt($totals['deaths']) }}</strong> <span>meninggal</span></li>
            <li><strong class="num">{{ $fmt($totals['displaced']) }}</strong> <span>mengungsi</span></li>
            <li><strong class="num">{{ $fmt($totals['houses_damaged']) }}</strong> <span>rumah rusak</span></li>
        </ul>
    </x-page-header>

    <section class="wrap py-10 sm:py-12">
        @if ($events->isEmpty())
            <div class="vol-empty">
                <p class="font-bold text-ink">Belum ada kejadian yang dicatat.</p>
                <p>Kejadian yang dilaporkan warga dan sudah dicek akan tampil di sini.</p>
            </div>
        @else
            <ol class="hist">
                @foreach ($events as $event)
                    <li class="hist__item hist__item--{{ $event->hazard_type->value }}" id="kejadian-{{ $event->id }}">
                        <div class="hist__date">
                            <span class="hist__day num">{{ $event->occurred_on->format('j') }}</span>
                            <span class="hist__month">{{ $event->occurred_on->translatedFormat('M Y') }}</span>
                        </div>
                        <article class="hist__card">
                            <div class="hist__meta">
                                <span class="hist__badge">{{ $event->hazard_type->getLabel() }}</span>
                                @if ($event->hamlet)
                                    <span>{{ $event->hamlet }}</span>
                                @endif
                            </div>
                            <h2 class="hist__title">{{ $event->title }}</h2>

                            @if ($event->description)
                                <p class="hist__text">{{ $event->description }}</p>
                            @endif

                            <dl class="hist__victims" aria-label="Jumlah korban">
                                @foreach (\App\Models\DisasterEvent::VICTIMS as $col => $label)
                                    <div @class(['is-zero' => ! $event->{$col}])>
                                        <dt>{{ $label }}</dt>
                                        <dd class="num">{{ $fmt($event->{$col}) }}</dd>
                                    </div>
                                @endforeach
                            </dl>

                            @if ($event->impact)
                                <p class="hist__impact"><strong>Dampak lain:</strong> {{ $event->impact }}</p>
                            @endif

                            @if ($event->photo)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($event->photo) }}" alt="Foto kejadian" loading="lazy" class="hist__photo">
                            @endif

                            @if ($event->source || $event->source_url)
                                <p class="hist__source">
                                    Sumber:
                                    @if ($event->source_url)
                                        <a href="{{ $event->source_url }}" target="_blank" rel="noopener">{{ $event->source ?: parse_url($event->source_url, PHP_URL_HOST) }}</a>
                                    @else
                                        {{ $event->source }}
                                    @endif
                                </p>
                            @endif
                        </article>
                    </li>
                @endforeach
            </ol>
        @endif

        <p class="hist__note">
            Nama korban tidak dicantumkan untuk menjaga privasi keluarga. Data dihimpun dari laporan warga yang dicek Karang Taruna, pemerintah desa, BPBD, dan berita.
            Melihat kejadian baru? <a href="{{ route('report') }}">Laporkan di sini</a>.
        </p>
    </section>
@endsection
