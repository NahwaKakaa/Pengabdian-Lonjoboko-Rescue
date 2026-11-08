@php
    $icons = [
        'pin' => '<path d="M12 21s-7-6.2-7-11.5A7 7 0 0112 2.5a7 7 0 017 7C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
        'route' => '<circle cx="6" cy="19" r="2"/><circle cx="18" cy="5" r="2"/><path d="M8 19h7a3.5 3.5 0 000-7H9a3.5 3.5 0 010-7h7"/>',
        'flag' => '<path d="M5 21V4M5 4h11l-2 4 2 4H5"/>',
        'megaphone' => '<path d="M3 11v2a1 1 0 001 1h2l5 4V6L6 10H4a1 1 0 00-1 1zM15.5 8.5a5 5 0 010 7M18.5 5.5a9 9 0 010 13"/>',
    ];
@endphp

<x-filament-widgets::widget class="lr-welcome-widget">
    <section class="lr-welcome">
        {{-- Garis kontur seperti peta topografi: lereng dan lembah desa --}}
        <svg class="lr-welcome__contour" viewBox="0 0 600 220" preserveAspectRatio="xMaxYMid slice" aria-hidden="true">
            <g fill="none" stroke="currentColor" stroke-width="1.2">
                <path d="M300 220c40-60 120-80 170-70s90 50 130 40"/>
                <path d="M260 220c50-80 150-110 210-98s100 58 130 48"/>
                <path d="M220 220c60-100 180-140 250-126s110 66 130 56"/>
                <path d="M180 220c70-120 210-170 290-154s120 74 130 64"/>
                <path d="M140 220c80-140 240-200 330-182s130 82 130 72"/>
                <path d="M100 220c90-160 270-230 370-210s140 90 130 80"/>
            </g>
        </svg>

        <div class="lr-welcome__main">
            <p class="lr-welcome__eyebrow">
                <span>{{ $today }}</span>
                <span class="lr-welcome__role">{{ $roleLabel }}</span>
            </p>
            <h2 class="lr-welcome__title">{{ $greeting }}, {{ $firstName }}.</h2>
            <p class="lr-welcome__summary">{{ $summary }}</p>

            <div class="lr-welcome__actions">
                @foreach ($actions as $action)
                    <a href="{{ $action['url'] }}" class="lr-action" wire:navigate>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons[$action['icon']] !!}</svg>
                        <span>{{ $action['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <dl class="lr-welcome__facts">
            <div>
                <dt>Tampil di web</dt>
                <dd>{{ $verified }}</dd>
            </div>
            <div @class(['is-warn' => $pending > 0])>
                <dt>Menunggu verifikasi</dt>
                <dd>{{ $pending }}</dd>
            </div>
            <div @class(['is-danger' => $alertCount > 0])>
                <dt>Peringatan berlaku</dt>
                <dd>{{ $alertCount }}</dd>
            </div>
        </dl>
    </section>
</x-filament-widgets::widget>
