@props(['alerts'])

{{--
    Popup keadaan darurat. Muncul otomatis saat web dibuka kalau ada
    peringatan tingkat Darurat yang masih berlaku. Setelah ditutup, tidak
    muncul lagi selama tab itu masih terbuka; muncul lagi saat web dibuka
    ulang atau saat admin memperbarui peringatannya.
--}}
@php
    $emergencies = $alerts
        ->filter(fn ($alert) => $alert->level === \App\Enums\AnnouncementLevel::Darurat)
        ->sortByDesc('published_at')
        ->values();
    $alert = $emergencies->first();
    $current = request()->route('announcement');
    $isOwnPage = $alert && $current instanceof \App\Models\Announcement && $current->is($alert);
@endphp

@if ($alert && ! $isOwnPage)
    <dialog
        id="lr-emergency"
        class="lr-emergency m-auto w-[min(34rem,calc(100vw-2rem))] max-h-[calc(100dvh-2rem)] overflow-y-auto rounded-3xl bg-card p-0 text-ink shadow-2xl ring-1 ring-black/10"
        role="alertdialog"
        aria-labelledby="lr-emergency-title"
        aria-describedby="lr-emergency-body"
        data-key="lr-emergency-{{ $alert->id }}-{{ $alert->updated_at?->timestamp }}"
    >
        {{-- Kepala merah --}}
        <div class="lr-emergency__head relative overflow-hidden bg-danger px-6 pb-6 pt-5 text-white sm:px-7">
            <div class="flex items-start justify-between gap-4">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/20 px-3 py-1 text-xs font-black uppercase tracking-[0.14em] ring-1 ring-white/30">
                    <span class="relative flex size-2" aria-hidden="true">
                        <span class="absolute inline-flex size-full animate-ping rounded-full bg-white opacity-80"></span>
                        <span class="relative inline-flex size-2 rounded-full bg-white"></span>
                    </span>
                    Keadaan darurat
                </span>
                <form method="dialog">
                    <button type="submit" class="-mr-2 -mt-1 grid size-9 place-items-center rounded-full text-white/80 outline-none transition-colors hover:bg-white/15 hover:text-white focus-visible:ring-2 focus-visible:ring-white" aria-label="Tutup">
                        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
                    </button>
                </form>
            </div>

            <div class="mt-4 flex items-start gap-4">
                <span class="lr-emergency__icon grid size-12 shrink-0 place-items-center rounded-2xl bg-white text-danger shadow-lg shadow-black/20" aria-hidden="true">
                    <svg viewBox="0 0 24 24" class="size-7" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01"/><path d="M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/></svg>
                </span>
                <div class="min-w-0">
                    <h2 id="lr-emergency-title" tabindex="-1" autofocus class="text-xl font-extrabold leading-snug tracking-tight outline-none sm:text-2xl" style="text-wrap: balance">{{ $alert->title }}</h2>
                    <p class="mt-1 text-sm font-semibold text-white/85">
                        Diumumkan {{ $alert->published_at?->diffForHumans() }}
                        @if ($alert->published_at)
                            · {{ $alert->published_at->timezone('Asia/Makassar')->format('H.i') }} WITA
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <div class="grid gap-5 px-6 py-6 sm:px-7">
            <p id="lr-emergency-body" class="whitespace-pre-line text-[0.97rem] leading-relaxed text-ink">{{ \Illuminate\Support\Str::limit($alert->body, 320) }}</p>

            <div class="flex items-start gap-3 rounded-2xl border border-danger/20 bg-danger-soft px-4 py-3 text-sm text-ink">
                <svg viewBox="0 0 24 24" class="mt-0.5 size-5 shrink-0 text-danger" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
                <p>{{ \App\Support\SafetyGuide::popupMessage() }}</p>
            </div>

            @if ($alert->source || $emergencies->count() > 1)
                <p class="-mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-ink-soft">
                    @if ($alert->source)
                        <span>Sumber: <strong class="text-ink">{{ $alert->source }}</strong></span>
                    @endif
                    @if ($emergencies->count() > 1)
                        <a href="{{ route('emergency') }}" class="font-bold text-danger underline underline-offset-2">+{{ $emergencies->count() - 1 }} peringatan darurat lain</a>
                    @endif
                </p>
            @endif

            <div class="grid gap-2 sm:grid-cols-2">
                <a href="{{ route('announcement.show', $alert) }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-danger px-4 py-3 text-sm font-bold text-white shadow-md shadow-danger/25 transition hover:brightness-110 sm:col-span-2">
                    Baca petunjuk lengkap
                    <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
                <a href="{{ route('evacuation') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-line bg-card px-4 py-2.5 text-sm font-bold text-river-800 transition-colors hover:bg-river-50">
                    <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="6" cy="19" r="2"/><circle cx="18" cy="5" r="2"/><path d="M8 19h7a3.5 3.5 0 000-7H9a3.5 3.5 0 010-7h7"/></svg>
                    Jalur evakuasi
                </a>
                <a href="{{ route('emergency') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-line bg-card px-4 py-2.5 text-sm font-bold text-river-800 transition-colors hover:bg-river-50">
                    <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg>
                    Kontak darurat
                </a>
                <form method="dialog" class="sm:col-span-2">
                    <button type="submit" class="mt-1 w-full rounded-xl px-4 py-2.5 text-sm font-bold text-ink-soft transition-colors hover:bg-river-50 hover:text-ink">Saya mengerti, tutup</button>
                </form>
            </div>
        </div>
    </dialog>

    <script>
        (() => {
            const dialog = document.getElementById('lr-emergency');

            if (!dialog || typeof dialog.showModal !== 'function') {
                return;
            }

            const key = dialog.dataset.key;
            let seen = false;

            try {
                seen = sessionStorage.getItem(key) === '1';
            } catch (e) {}

            if (seen) {
                return;
            }

            const markSeen = () => {
                try {
                    sessionStorage.setItem(key, '1');
                } catch (e) {}
            };

            // Ditutup atau salah satu tautannya dibuka = sudah dilihat.
            dialog.addEventListener('close', markSeen);
            dialog.querySelectorAll('a').forEach((link) => link.addEventListener('click', markSeen));

            // Klik di luar kotak menutup popup.
            dialog.addEventListener('click', (event) => {
                if (event.target === dialog) {
                    dialog.close();
                }
            });

            dialog.showModal();
        })();
    </script>
@endif
