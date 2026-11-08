@props(['eyebrow' => null, 'title', 'intro' => null])

<section class="page-header-scenic text-white relative overflow-hidden border-b border-river-800 shadow-inner">
    <div class="relative wrap py-10 sm:py-14 grid gap-3.5 z-10">
        @if ($eyebrow)
            <div>
                <span class="inline-flex items-center gap-2 rounded-full bg-river-950/90 backdrop-blur-xs px-3.5 py-1 text-xs font-bold uppercase tracking-wider text-emerald-300 border border-emerald-500/40 shadow-xs">
                    <span class="size-1.5 rounded-full bg-emerald-400"></span>
                    {{ $eyebrow }}
                </span>
            </div>
        @endif
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-white leading-[1.15] drop-shadow-sm">
            {{ $title }}
        </h1>
        @if ($intro)
            <p class="text-river-100 text-base sm:text-lg max-w-[68ch] leading-relaxed font-normal drop-shadow-xs">
                {{ $intro }}
            </p>
        @endif
        @if (! $slot->isEmpty())
            <div class="pt-2">
                {{ $slot }}
            </div>
        @endif
    </div>
</section>
