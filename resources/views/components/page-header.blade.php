@props(['eyebrow' => null, 'title', 'intro' => null])

<section class="border-b border-line bg-card">
    <div class="wrap py-8 sm:py-10 grid gap-3">
        @if ($eyebrow)
            <p class="eyebrow">{{ $eyebrow }}</p>
        @endif
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">{{ $title }}</h1>
        @if ($intro)
            <p class="text-ink-soft text-base sm:text-lg max-w-[65ch] leading-relaxed">{{ $intro }}</p>
        @endif
        {{ $slot }}
    </div>
</section>
