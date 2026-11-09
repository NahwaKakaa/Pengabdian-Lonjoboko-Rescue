@php
    $menu = [
        ['route' => 'home', 'label' => 'Beranda'],
        ['route' => 'risk-map', 'label' => 'Peta Risiko'],
        ['route' => 'evacuation', 'label' => 'Jalur Evakuasi'],
        ['route' => 'education*', 'label' => 'Edukasi', 'href' => route('education')],
        ['route' => 'emergency', 'label' => 'Info Darurat'],
        ['route' => 'volunteers', 'label' => 'Relawan'],
    ];
    $topAlert = $activeAlerts->sortByDesc(fn ($a) => $a->level === \App\Enums\AnnouncementLevel::Darurat)->first();
    $isEmergency = $topAlert?->level === \App\Enums\AnnouncementLevel::Darurat;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#062b29">
    {{-- Aplikasi web (PWA): bisa dipasang di HP dan dibuka tanpa internet --}}
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="icon" href="/icons/favicon.svg" type="image/svg+xml">
    <link rel="icon" href="/icons/favicon-32.png" sizes="32x32" type="image/png">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Lonjoboko Rescue">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="description" content="@yield('description', 'Sistem informasi dan edukasi mitigasi bencana banjir dan tanah longsor Desa '.$site['village'].'.')">
    @php $pageTitle = trim($__env->yieldContent('title')); @endphp
    <title>{{ $pageTitle !== '' ? $pageTitle.' · ' : '' }}LONJOBOKO RESCUE</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">

    {{-- Tema siang/malam dipasang sebelum halaman tergambar (tanpa kilatan putih).
         Logo hanya digambar ulang di halaman pertama kunjungan. --}}
    <script>
        try {
            var theme = localStorage.getItem('lr-theme');
            if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        } catch (e) {}
        try {
            if (sessionStorage.getItem('lr-visited')) {
                document.documentElement.classList.add('lr-returning');
            }
            sessionStorage.setItem('lr-visited', '1');
        } catch (e) {}
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
@php $heroPhoto = \App\Support\SitePhotos::hero(); @endphp
<body @class(['min-h-screen flex flex-col antialiased bg-scenic-ambient text-ink', 'has-hero-photo' => $heroPhoto['custom']])
      @if ($heroPhoto['custom']) style="--hero-photo: url('{{ $heroPhoto['url'] }}')" @endif>
    <a href="#konten" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-50 focus:bg-white focus:text-river-900 focus:px-4 focus:py-2 focus:rounded-lg focus:shadow-lg focus:font-bold">Langsung ke konten</a>

    @php
        $alertTone = $topAlert ? ($isEmergency ? 'darurat' : 'waspada') : null;
        $showReadProgress = request()->routeIs('education.show', 'announcement.show');
    @endphp
    <header id="site-header" @class(['site-header sticky top-0 z-40 shadow-lg', 'has-alert-'.$alertTone => $alertTone]) style="top: env(safe-area-inset-top, 0px)">
        {{-- Bar status di dalam navbar, supaya posisinya sama di setiap halaman --}}
        @if ($topAlert)
            <div role="alert" @class(['site-ribbon text-white', 'bg-danger' => $isEmergency, 'bg-alert' => ! $isEmergency])>
                <div class="wrap flex items-center justify-between gap-3 py-2 text-sm">
                    <div class="flex min-w-0 items-center gap-2.5">
                        <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border border-white/30 bg-white/20 px-2.5 py-0.5 text-[0.7rem] font-black uppercase tracking-wider">
                            <span class="relative flex size-1.5" aria-hidden="true">
                                <span class="absolute inline-flex size-full animate-ping rounded-full bg-white opacity-80"></span>
                                <span class="relative inline-flex size-1.5 rounded-full bg-white"></span>
                            </span>
                            {{ $topAlert->level->getLabel() }}
                        </span>
                        <a href="{{ route('announcement.show', $topAlert) }}" class="truncate font-bold underline decoration-white/50 underline-offset-4 hover:decoration-white">{{ $topAlert->title }}</a>
                    </div>
                    @if ($activeAlerts->count() > 1)
                        <a href="{{ route('emergency') }}" class="shrink-0 rounded-lg bg-white/15 px-2.5 py-1 text-xs font-semibold transition-colors hover:bg-white/25">+{{ $activeAlerts->count() - 1 }}<span class="hidden sm:inline"> peringatan lain</span></a>
                    @endif
                </div>
            </div>
        @else
            <div class="site-topbar">
                <div class="wrap flex items-center justify-between gap-3 py-1.5 text-xs">
                    <span class="inline-flex min-w-0 items-center gap-2 font-semibold text-river-100">
                        <span class="relative flex size-2 shrink-0" aria-hidden="true">
                            <span class="absolute inline-flex size-full animate-ping rounded-full bg-safe opacity-75"></span>
                            <span class="relative inline-flex size-2 rounded-full bg-safe"></span>
                        </span>
                        <span class="truncate"><span class="hidden sm:inline">Status desa: </span><span class="font-bold text-white">Aman &amp; terkendali</span></span>
                    </span>
                    <div class="flex shrink-0 items-center gap-3">
                        <span class="hidden items-center gap-1.5 text-river-200 lg:inline-flex">
                            <svg viewBox="0 0 24 24" class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 20l6-10 4 6 3-4 5 8z"/></svg>
                            {{ $site['landscape_note'] }}
                        </span>
                        <span class="hidden h-3.5 w-px bg-white/15 lg:block" aria-hidden="true"></span>
                        <a href="https://www.bmkg.go.id" target="_blank" rel="noopener" class="site-weather-chip" title="Buka prakiraan cuaca di situs BMKG">
                            <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><g stroke="#fcd34d"><path d="M12 3v1.5M5.6 5.6l1 1M3 12h1.5M18.4 5.6l-1 1"/><path d="M8.5 11.2a3.5 3.5 0 016.6-1.4"/></g><path stroke="#e6f4f1" d="M8 20h9a3.5 3.5 0 00.4-7 5 5 0 00-9.6 1.2A3 3 0 008 20z"/></svg>
                            <span><span class="hidden sm:inline">Prakiraan cuaca </span>BMKG</span>
                            <svg viewBox="0 0 24 24" class="size-3 opacity-70" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17L17 7M9 7h8v8"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <div class="site-header__bar wrap flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="site-logo group flex items-center gap-3 min-w-0" aria-label="LONJOBOKO RESCUE, ke beranda">
                <span class="site-logo__mark grid place-items-center size-10 shrink-0 rounded-xl bg-gradient-to-br from-emerald-600 to-river-800 text-white shadow-md shadow-black/30" aria-hidden="true">
                    <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path class="site-logo__wave" pathLength="1" d="M3 18c2 0 2-1.5 4.5-1.5S10 18 12 18s2-1.5 4.5-1.5S19 18 21 18"/>
                        <path class="site-logo__mountain" pathLength="1" d="M4 13l5-7 3 4 2-2 6 5"/>
                    </svg>
                </span>
                <span class="min-w-0 leading-tight">
                    <span class="block truncate font-black tracking-tight text-white text-[0.95rem] sm:text-base">LONJOBOKO RESCUE</span>
                    <span class="site-logo__sub block text-xs text-river-100 truncate font-semibold">Desa {{ $site['village'] }} Tangguh Bencana</span>
                </span>
            </a>

            <div class="flex items-center gap-2">
            {{-- Menu laptop: penanda hijau meluncur ke menu aktif, sorotan tipis mengikuti kursor --}}
            <nav aria-label="Menu utama" class="hidden lg:block">
                <ul class="site-nav relative flex items-center gap-1.5 text-sm font-semibold">
                    <li class="site-nav__hover" role="presentation" aria-hidden="true"></li>
                    @foreach ($menu as $item)
                        @php $active = request()->routeIs($item['route']); @endphp
                        <li>
                            <a href="{{ $item['href'] ?? route($item['route']) }}"
                               @class([
                                   'site-nav__link relative z-[1] inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl transition-colors duration-200 font-semibold',
                                   'is-active text-white' => $active,
                                   'text-river-100 hover:text-white' => ! $active,
                               ])
                               @if ($active) aria-current="page" @endif>
                                @if ($active)
                                    {{-- Kotak hijau menu aktif; saat pindah halaman, browser menggesernya ke menu baru --}}
                                    <span class="site-nav__pill" aria-hidden="true"></span>
                                @endif
                                {{-- Tulisan menu punya lapisan transisi sendiri supaya tidak tertutup kotak hijau yang lewat --}}
                                <span class="site-nav__label" style="view-transition-name: nav-label-{{ $loop->index }}">
                                    {{ $item['label'] }}
                                    @if ($item['route'] === 'emergency' && $alertTone)
                                        <span class="site-nav__alert-dot is-{{ $alertTone }}" aria-label="ada peringatan aktif"></span>
                                    @endif
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>


            {{-- Lapor kejadian: selalu terlihat, di laptop maupun HP --}}
            <a href="{{ route('report') }}" @class(['site-report-btn', 'is-active' => request()->routeIs('report')]) title="Laporkan kejadian atau tanda bahaya">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 11v2a1 1 0 001 1h2l5 4V6L6 10H4a1 1 0 00-1 1z"/><path d="M15.5 8.5a5 5 0 010 7M18.5 5.5a9 9 0 010 13"/></svg>
                <span>Lapor</span>
            </a>

            {{-- Tombol tampilan siang / malam --}}
            <button type="button" id="theme-toggle" class="site-theme-switch" role="switch" aria-checked="false" aria-label="Tampilan malam" title="Ganti ke tampilan malam">
                <span class="site-theme-switch__knob" aria-hidden="true">
                    <svg class="site-theme-switch__sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="12" cy="12" r="4"/><path d="M12 2.5v2M12 19.5v2M4.6 4.6l1.4 1.4M18 18l1.4 1.4M2.5 12h2M19.5 12h2M4.6 19.4L6 18M18 6l1.4-1.4"/></svg>
                    <svg class="site-theme-switch__moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round"><path d="M20.5 14.3A8.5 8.5 0 019.7 3.5a8.5 8.5 0 1010.8 10.8z"/></svg>
                </span>
            </button>

            {{-- Menu HP --}}
            <details class="site-menu lg:hidden relative">
                <summary class="list-none cursor-pointer inline-flex h-[2.4rem] items-center gap-2.5 rounded-xl border border-white/25 bg-river-800 text-white px-3 sm:px-3.5 text-sm font-bold shadow-xs hover:bg-river-700 [&::-webkit-details-marker]:hidden" aria-label="Buka menu">
                    <span class="site-menu__burger" aria-hidden="true"><span></span><span></span><span></span></span>
                    <span class="hidden sm:inline">Menu</span>
                    @if ($alertTone)
                        <span class="site-nav__alert-dot is-{{ $alertTone }}" aria-hidden="true"></span>
                    @endif
                </summary>
                <nav aria-label="Menu utama" class="site-menu__panel absolute right-0 mt-2 w-64 rounded-2xl border border-white/20 bg-[#062b29] p-2.5 shadow-2xl ring-1 ring-black/50 text-white z-50">
                    <ul class="grid gap-1 text-base font-semibold">
                        @foreach ($menu as $item)
                            @php $active = request()->routeIs($item['route']); @endphp
                            <li style="--i: {{ $loop->index }}">
                                <a href="{{ $item['href'] ?? route($item['route']) }}"
                                   @class([
                                       'site-menu__link flex items-center justify-between gap-2 px-3.5 py-2.5 rounded-xl transition-colors',
                                       'is-active bg-emerald-600 text-white' => $active,
                                       'text-river-100 hover:bg-white/10 hover:text-white' => ! $active,
                                   ])
                                   @if ($active) aria-current="page" @endif>
                                    {{ $item['label'] }}
                                    @if ($item['route'] === 'emergency' && $alertTone)
                                        <span class="site-nav__alert-dot is-{{ $alertTone }}" aria-label="ada peringatan aktif"></span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            </details>
            </div>
        </div>

        @if ($showReadProgress)
            <div class="read-progress" aria-hidden="true"><span id="read-progress-bar"></span></div>
        @endif

        {{-- Garis memuat: muncul kalau halaman tujuan belum datang setelah menu diklik --}}
        <div class="nav-loading" aria-hidden="true"><span></span></div>
    </header>

    <main id="konten" class="flex-1">
        @yield('content')
    </main>

    <footer class="mt-20 site-footer relative">
        @php
            $phoneIcon = '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/>';
            $waIcon = '<path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8s-.4-.1-.6.1-.7.8-.8 1-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.2-.4.2-.4.7-1.2.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.7 11.8 11.8 0 0 0 4.5 4c1.7.7 2.3.8 3.2.6a2.7 2.7 0 0 0 1.8-1.2 2.2 2.2 0 0 0 .1-1.3c0-.1-.2-.2-.5-.3z"/>';
        @endphp

        <div class="wrap grid grid-cols-1 gap-10 py-14 text-sm lg:grid-cols-12 lg:gap-8">
            {{-- Identitas & WhatsApp desa --}}
            <section class="grid grid-cols-1 gap-4 content-start min-w-0 lg:col-span-4" aria-labelledby="footer-identitas">
                <div class="flex items-center gap-3">
                    <span class="grid place-items-center size-10 shrink-0 rounded-xl bg-emerald-600 text-white shadow-sm" aria-hidden="true">
                        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 18c2 0 2-1.5 4.5-1.5S10 18 12 18s2-1.5 4.5-1.5S19 18 21 18"/>
                            <path d="M4 13l5-7 3 4 2-2 6 5"/>
                        </svg>
                    </span>
                    <div class="leading-tight">
                        <p id="footer-identitas" class="font-black text-white text-lg tracking-tight">LONJOBOKO RESCUE</p>
                        <p class="text-xs font-semibold text-river-200">Desa {{ $site['village'] }} Tangguh Bencana</p>
                    </div>
                </div>
                <p class="text-river-100 leading-relaxed max-w-sm">Sistem informasi dan edukasi mitigasi banjir & tanah longsor untuk warga Desa {{ $site['village'] }}, Kecamatan {{ $site['district'] }}, Kabupaten {{ $site['regency'] }}.</p>

                @if ($footer['whatsapp'])
                    <a href="{{ $footer['whatsapp']['href'] }}" target="_blank" rel="noopener"
                       class="group mt-1 flex items-center gap-3.5 rounded-2xl bg-[#1f9d55] p-3.5 pr-4 text-white shadow-lg shadow-black/25 ring-1 ring-white/15 transition-all duration-200 hover:-translate-y-0.5 hover:bg-[#1a8a4a] max-w-sm">
                        <span class="grid place-items-center size-11 shrink-0 rounded-xl bg-white/15" aria-hidden="true">
                            <svg viewBox="0 0 24 24" class="size-6" fill="currentColor">{!! $waIcon !!}</svg>
                        </span>
                        <span class="min-w-0 flex-1 leading-tight">
                            <span class="block text-xs font-semibold text-white/80">Lapor / tanya via WhatsApp</span>
                            <span class="block font-black text-base num tracking-wide">{{ $footer['whatsapp']['number'] }}</span>
                            <span class="block truncate text-xs text-white/80">{{ $footer['whatsapp']['label'] }}</span>
                        </span>
                        <svg viewBox="0 0 24 24" class="size-5 shrink-0 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                @endif

                @if ($site['office_phone'])
                    <a href="{{ \App\Support\Phone::telHref($site['office_phone']) }}" class="inline-flex items-center gap-2 text-river-100 hover:text-white transition-colors w-fit">
                        <svg viewBox="0 0 24 24" class="size-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $phoneIcon !!}</svg>
                        Kantor desa: <span class="font-bold text-white num">{{ $site['office_phone'] }}</span>
                    </a>
                @endif

                {{-- Pasang web sebagai aplikasi di HP (muncul bila browser mendukung) --}}
                <div class="grid gap-1.5">
                    <button type="button" id="install-app" class="site-install-btn" hidden>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="6" y="2" width="12" height="20" rx="2.5"/><path d="M12 7v7M9 11l3 3 3-3M11 18h2"/></svg>
                        Pasang di HP, bisa dibuka tanpa internet
                    </button>
                    <p id="install-ios-hint" class="text-xs text-river-200 leading-relaxed" hidden>Di iPhone: ketuk tombol <strong class="text-white">Bagikan</strong>, lalu pilih <strong class="text-white">Tambahkan ke Layar Utama</strong>.</p>
                </div>
            </section>

            {{-- Kontak darurat --}}
            <section class="grid grid-cols-1 gap-4 content-start min-w-0 lg:col-span-4" aria-labelledby="footer-darurat">
                <div class="flex items-center justify-between gap-3">
                    <p id="footer-darurat" class="font-bold text-white text-base flex items-center gap-2">
                        <span class="relative flex size-2" aria-hidden="true">
                            <span class="absolute inline-flex size-full rounded-full bg-red-400 opacity-75 animate-ping"></span>
                            <span class="relative inline-flex size-2 rounded-full bg-red-500"></span>
                        </span>
                        Kontak Darurat
                    </p>
                    <span class="text-xs text-river-200 shrink-0">Ketuk untuk menelepon</span>
                </div>

                @if ($footer['contacts']->isNotEmpty())
                    <ul class="grid grid-cols-1 gap-2">
                        @foreach ($footer['contacts'] as $contact)
                            <li class="flex min-w-0 items-center gap-2">
                                <a href="{{ $contact->telHref() }}" class="group flex min-w-0 flex-1 items-center gap-3 rounded-xl bg-white/[0.06] px-3 py-2.5 ring-1 ring-white/10 transition-colors hover:bg-white/[0.12]">
                                    <span class="grid place-items-center size-8 shrink-0 rounded-lg bg-red-500/15 text-red-300 group-hover:bg-red-500 group-hover:text-white transition-colors" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $phoneIcon !!}</svg>
                                    </span>
                                    <span class="min-w-0 flex-1 truncate text-river-100 group-hover:text-white" title="{{ $contact->name }}">{{ $contact->name }}</span>
                                    <span class="shrink-0 font-black text-white text-base num">{{ $contact->phone }}</span>
                                </a>
                                @if ($contact->whatsappHref())
                                    <a href="{{ $contact->whatsappHref() }}" target="_blank" rel="noopener" class="grid place-items-center size-11 shrink-0 rounded-xl bg-[#1f9d55]/15 text-[#4ade80] ring-1 ring-[#4ade80]/25 hover:bg-[#1f9d55] hover:text-white transition-colors" aria-label="WhatsApp {{ $contact->name }}" title="WhatsApp">
                                        <svg viewBox="0 0 24 24" class="size-5" fill="currentColor" aria-hidden="true">{!! $waIcon !!}</svg>
                                    </a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-river-100">Nomor darurat nasional: <span class="font-bold text-white num">112</span> (terpadu), <span class="font-bold text-white num">119</span> (ambulans), <span class="font-bold text-white num">115</span> (Basarnas).</p>
                @endif

                <a href="{{ route('emergency') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-300 hover:text-white transition-colors w-fit">
                    {{ $footer['contact_count'] > $footer['contacts']->count() ? 'Lihat semua '.$footer['contact_count'].' kontak' : 'Info darurat & cara melapor' }}
                    <svg viewBox="0 0 24 24" class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
            </section>

            {{-- Peta lokasi desa --}}
            <section class="grid grid-cols-1 gap-4 content-start min-w-0 lg:col-span-4" aria-labelledby="footer-lokasi">
                <p id="footer-lokasi" class="font-bold text-white text-base flex items-center gap-2">
                    <svg viewBox="0 0 24 24" class="size-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"/><circle cx="12" cy="10" r="3"/></svg>
                    Lokasi Desa
                </p>
                <div class="overflow-hidden rounded-2xl ring-1 ring-white/15 shadow-lg shadow-black/30 bg-river-950">
                    <iframe
                        src="{{ $footer['map_embed'] }}"
                        title="Peta lokasi Desa {{ $site['village'] }} di Google Maps"
                        class="block h-52 w-full border-0"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        allowfullscreen
                    ></iframe>
                </div>
                <p class="text-river-100 text-xs leading-relaxed">{{ $site['office_address'] ? $site['office_address'].' · ' : '' }}{{ $footer['place'] }} {{ config('lonjoboko.postal_code') }}</p>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ $footer['map_directions'] }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm hover:bg-emerald-500 transition-colors">
                        <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 11l19-9-9 19-2-8-8-2z"/></svg>
                        Petunjuk arah
                    </a>
                    <a href="{{ $footer['map_open'] }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-xl bg-white/10 px-3.5 py-2 text-xs font-bold text-white ring-1 ring-white/20 hover:bg-white/20 transition-colors">
                        Buka di Google Maps
                        <svg viewBox="0 0 24 24" class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.25" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14L21 3"/></svg>
                    </a>
                </div>
            </section>
        </div>

        {{-- Catatan penting & akses pengelola --}}
        <div class="border-t border-white/10">
            <div class="wrap flex flex-col gap-4 py-5 text-xs md:flex-row md:items-center md:justify-between">
                <p class="flex items-start gap-2.5 text-river-100 leading-relaxed max-w-3xl">
                    <span class="mt-0.5 grid place-items-center size-5 shrink-0 rounded-full bg-amber-400/15 text-amber-300 font-black" aria-hidden="true">!</span>
                    <span>Web ini sarana informasi dan edukasi, <strong class="text-white">bukan pengganti instruksi tanggap darurat resmi</strong>. Saat bencana, ikuti arahan aparat desa, BPBD, dan BMKG. Peta dasar &copy; kontributor OpenStreetMap.</span>
                </p>
                <a href="{{ url('/admin') }}" class="inline-flex shrink-0 items-center gap-2 font-bold px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white transition-colors ring-1 ring-white/15 w-fit">
                    <svg viewBox="0 0 24 24" class="size-4 text-emerald-300" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Masuk Dasbor Pengelola
                </a>
            </div>
        </div>

        <div class="site-footer-bar py-4 text-xs">
            <div class="wrap flex flex-wrap items-center justify-between gap-2">
                <p class="font-semibold text-white">&copy; {{ date('Y') }} LONJOBOKO RESCUE · Pengabdian Masyarakat & Pemerintah Desa {{ $site['village'] }}</p>
                <p class="text-river-100 font-medium">{{ $site['landscape_note'] }}</p>
            </div>
        </div>
    </footer>

    {{-- Penanda koneksi: muncul saat offline, dan sekali saat web siap dibuka tanpa internet --}}
    <div id="net-status" class="net-status" role="status" aria-live="polite" hidden>
        <svg class="net-status__off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 2l20 20M8.5 16.4a5 5 0 017 0M5 12.9a10 10 0 015.2-2.7M19 12.9a10 10 0 00-2.4-1.7M1.4 9a15 15 0 014.1-2.6M22.6 9A15 15 0 0011 5.1M12 20h.01"/></svg>
        <svg class="net-status__ok" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
        <span class="net-status__text"></span>
    </div>

    {{-- Tombol kembali ke atas; lingkarannya menunjukkan seberapa jauh halaman sudah digulir --}}
    <button type="button" id="back-to-top" class="back-to-top" aria-label="Kembali ke atas" title="Kembali ke atas">
        <svg class="back-to-top__ring" viewBox="0 0 48 48" aria-hidden="true">
            <circle cx="24" cy="24" r="22" pathLength="1" class="back-to-top__track"/>
            <circle id="back-to-top-progress" cx="24" cy="24" r="22" pathLength="1" class="back-to-top__progress"/>
        </svg>
        <svg class="back-to-top__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
    </button>

    <x-emergency-popup :alerts="$activeAlerts" />

    @stack('scripts')
</body>
</html>
