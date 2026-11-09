@php
    $cover = $activity->coverUrl();
    $count = count($activity->photoUrls());
@endphp
<li>
    <a href="{{ route('activity.show', $activity) }}" class="act-card">
        <span class="act-card__media">
            @if ($cover)
                <img src="{{ $cover }}" alt="" loading="lazy" class="act-card__img">
            @else
                <span class="act-card__placeholder" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 00-3-3.9M16 3.1a4 4 0 010 7.8"/></svg>
                </span>
            @endif
            @if ($count > 1)
                <span class="act-card__count">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="10" r="1.5"/><path d="M21 16l-5-5-8 8"/></svg>
                    {{ $count }} foto
                </span>
            @endif
        </span>
        <span class="act-card__body">
            <span class="act-card__date">{{ $activity->held_on->translatedFormat('j F Y') }}@if ($activity->place) · {{ $activity->place }}@endif</span>
            <span class="act-card__title">{{ $activity->title }}</span>
            @if ($activity->description)
                <span class="act-card__text">{{ \Illuminate\Support\Str::limit($activity->description, 140) }}</span>
            @endif
            <span class="act-card__more">Lihat detail
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </span>
        </span>
    </a>
</li>
