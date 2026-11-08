@props(['contact'])

<li class="elevation-card flex flex-wrap items-center justify-between gap-3.5 rounded-2xl p-4 sm:px-5 sm:py-4">
    <div class="min-w-0 flex items-center gap-3">
        <span class="grid place-items-center size-9 shrink-0 rounded-xl bg-river-50 text-river-800 font-bold border border-river-100" aria-hidden="true">
            <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg>
        </span>
        <div class="min-w-0">
            <p class="font-bold text-ink leading-snug">{{ $contact->name }}</p>
            @if ($contact->description)
                <p class="text-xs text-ink-soft">{{ $contact->description }}</p>
            @endif
        </div>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ $contact->telHref() }}" class="inline-flex items-center gap-1.5 rounded-xl bg-river-800 px-3.5 py-2 text-white font-bold text-xs num shadow-xs hover:bg-river-700 transition-colors">
            <svg viewBox="0 0 24 24" class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg>
            {{ $contact->phone }}
        </a>
        @if ($contact->whatsappHref())
            <a href="{{ $contact->whatsappHref() }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 rounded-xl border border-safe/30 bg-safe/5 px-3 py-2 text-xs font-bold text-safe hover:bg-safe-soft transition-colors">
                <span>WA</span>
                <svg viewBox="0 0 24 24" class="size-3" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14L21 3"/></svg>
            </a>
        @endif
    </div>
</li>
