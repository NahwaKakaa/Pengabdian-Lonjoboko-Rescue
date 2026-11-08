@props(['contact'])

<li class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-line bg-card px-4 py-3">
    <div class="min-w-0">
        <p class="font-bold">{{ $contact->name }}</p>
        @if ($contact->description)
            <p class="text-sm text-ink-soft">{{ $contact->description }}</p>
        @endif
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ $contact->telHref() }}" class="inline-flex items-center gap-2 rounded-lg bg-river-800 px-3 py-2 text-white font-bold num hover:bg-river-700">
            <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg>
            {{ $contact->phone }}
        </a>
        @if ($contact->whatsappHref())
            <a href="{{ $contact->whatsappHref() }}" target="_blank" rel="noopener" class="rounded-lg border border-line px-3 py-2 text-sm font-semibold hover:bg-safe-soft">WhatsApp</a>
        @endif
    </div>
</li>
