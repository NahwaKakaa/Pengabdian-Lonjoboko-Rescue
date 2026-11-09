@extends('layouts.public')

@section('title', $activity->title)

@php
    $photos = $activity->photoUrls();
@endphp

@section('content')
    <div class="wrap py-8 sm:py-10 max-w-4xl">
        <article class="act-detail">
            <a href="{{ route('volunteers') }}#kegiatan" class="action-link action-link--ghost">
                <svg class="action-link__back" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
                <span>Kembali ke kegiatan</span>
            </a>

            <header class="act-detail__head">
                <p class="act-detail__eyebrow">Kegiatan Karang Taruna</p>
                <h1 class="act-detail__title">{{ $activity->title }}</h1>
                <ul class="act-detail__meta">
                    <li>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4.5" width="18" height="16" rx="2"/><path d="M3 9.5h18M8 2.5v4M16 2.5v4"/></svg>
                        <time datetime="{{ $activity->held_on->toDateString() }}">{{ $activity->held_on->translatedFormat('l, j F Y') }}</time>
                    </li>
                    @if ($activity->place)
                        <li>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0114 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
                            {{ $activity->place }}
                        </li>
                    @endif
                </ul>
            </header>

            @if ($photos)
                <button type="button" class="act-detail__cover" data-photo-open="0" aria-label="Perbesar foto 1">
                    <img src="{{ $photos[0] }}" alt="Foto kegiatan {{ $activity->title }}">
                </button>
            @endif

            @if ($activity->description)
                <div class="act-detail__text">{{ $activity->description }}</div>
            @endif

            @if (count($photos) > 1)
                <section class="act-detail__gallery-wrap" aria-label="Foto kegiatan">
                    <h2>Foto kegiatan <span>{{ count($photos) }} foto · tekan untuk memperbesar</span></h2>
                    <ul class="act-detail__gallery">
                        @foreach ($photos as $i => $url)
                            <li>
                                <button type="button" data-photo-open="{{ $i }}" aria-label="Perbesar foto {{ $i + 1 }}">
                                    <img src="{{ $url }}" alt="" loading="lazy">
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </article>

        @if ($others->isNotEmpty())
            <section class="mt-10">
                <h2 class="vol-title">Kegiatan lainnya</h2>
                <ul class="act-grid">
                    @foreach ($others as $other)
                        @include('public.partials.activity-card', ['activity' => $other])
                    @endforeach
                </ul>
            </section>
        @endif
    </div>

    @if ($photos)
        <dialog class="act-viewer" id="act-viewer" aria-label="Foto kegiatan">
            <img src="" alt="" data-viewer-img>
            <p class="act-viewer__count" data-viewer-count></p>
            <button type="button" class="act-viewer__btn act-viewer__close" data-viewer-close aria-label="Tutup">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
            @if (count($photos) > 1)
                <button type="button" class="act-viewer__btn act-viewer__prev" data-viewer-step="-1" aria-label="Foto sebelumnya">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                </button>
                <button type="button" class="act-viewer__btn act-viewer__next" data-viewer-step="1" aria-label="Foto berikutnya">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                </button>
            @endif
        </dialog>

        <script>
            (() => {
                const photos = @json($photos);
                const viewer = document.getElementById('act-viewer');
                if (!viewer || typeof viewer.showModal !== 'function') return;
                const img = viewer.querySelector('[data-viewer-img]');
                const count = viewer.querySelector('[data-viewer-count]');
                let index = 0;

                const show = (i) => {
                    index = (i + photos.length) % photos.length;
                    img.src = photos[index];
                    img.alt = `Foto kegiatan ${index + 1} dari ${photos.length}`;
                    count.textContent = photos.length > 1 ? `${index + 1} / ${photos.length}` : '';
                };

                document.querySelectorAll('[data-photo-open]').forEach((btn) => {
                    btn.addEventListener('click', () => { show(Number(btn.dataset.photoOpen)); viewer.showModal(); });
                });
                viewer.querySelector('[data-viewer-close]').addEventListener('click', () => viewer.close());
                viewer.querySelectorAll('[data-viewer-step]').forEach((btn) => {
                    btn.addEventListener('click', () => show(index + Number(btn.dataset.viewerStep)));
                });
                viewer.addEventListener('click', (e) => { if (e.target === viewer) viewer.close(); });
                viewer.addEventListener('keydown', (e) => {
                    if (e.key === 'ArrowRight') show(index + 1);
                    if (e.key === 'ArrowLeft') show(index - 1);
                });

                // Geser foto di HP
                let startX = null;
                viewer.addEventListener('touchstart', (e) => { startX = e.touches[0].clientX; }, { passive: true });
                viewer.addEventListener('touchend', (e) => {
                    if (startX === null || photos.length < 2) return;
                    const dx = e.changedTouches[0].clientX - startX;
                    if (Math.abs(dx) > 50) show(index + (dx < 0 ? 1 : -1));
                    startX = null;
                });
            })();
        </script>
    @endif
@endsection
