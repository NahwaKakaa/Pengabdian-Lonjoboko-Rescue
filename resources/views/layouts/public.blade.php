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
    <meta name="theme-color" content="#093f3c">
    <meta name="description" content="@yield('description', 'Sistem informasi dan edukasi mitigasi bencana banjir dan tanah longsor Desa '.$site['village'].'.')">
    @php $pageTitle = trim($__env->yieldContent('title')); @endphp
    <title>{{ $pageTitle !== '' ? $pageTitle.' · ' : '' }}LONJOBOKO-RESCUE</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen flex flex-col antialiased">
    <a href="#konten" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-50 focus:bg-white focus:px-4 focus:py-2 focus:rounded-lg">Langsung ke konten</a>

    {{-- Pita status: selalu tampil, supaya warga langsung tahu ada peringatan atau tidak. --}}
    @if ($topAlert)
        <div role="alert" class="{{ $isEmergency ? 'bg-danger' : 'bg-alert' }} text-white">
            <div class="wrap flex flex-wrap items-center gap-x-3 gap-y-1 py-2.5 text-sm">
                <span class="inline-flex items-center gap-2 font-extrabold uppercase tracking-wider text-xs">
                    <span class="size-2 rounded-full bg-white animate-pulse" aria-hidden="true"></span>
                    {{ $topAlert->level->getLabel() }}
                </span>
                <a href="{{ route('announcement.show', $topAlert) }}" class="font-semibold underline underline-offset-4 decoration-white/50 hover:decoration-white min-w-0">
                    {{ $topAlert->title }}
                </a>
                @if ($activeAlerts->count() > 1)
                    <a href="{{ route('emergency') }}" class="ml-auto text-white/90 hover:text-white">+{{ $activeAlerts->count() - 1 }} peringatan lain</a>
                @endif
            </div>
        </div>
    @else
        <div class="bg-river-900 text-river-100">
            <div class="wrap flex flex-wrap items-center gap-x-3 gap-y-1 py-2 text-xs sm:text-sm">
                <span class="inline-flex items-center gap-2 font-bold uppercase tracking-wider text-[0.7rem]">
                    <span class="size-2 rounded-full bg-safe" aria-hidden="true"></span>
                    Tidak ada peringatan desa saat ini
                </span>
                <a href="https://www.bmkg.go.id" target="_blank" rel="noopener" class="text-river-100/80 hover:text-white underline underline-offset-4 decoration-river-100/30">Cek peringatan cuaca di BMKG</a>
            </div>
        </div>
    @endif

    <header class="sticky top-0 z-40 bg-paper/95 backdrop-blur border-b border-line" style="top: env(safe-area-inset-top, 0px)">
        <div class="wrap flex items-center justify-between gap-4 py-3">
            <a href="{{ route('home') }}" class="flex items-center gap-3 min-w-0" aria-label="LONJOBOKO-RESCUE, ke beranda">
                <span class="grid place-items-center size-10 shrink-0 rounded-xl bg-river-800 text-white" aria-hidden="true">
                    <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 18c2 0 2-1.5 4.5-1.5S10 18 12 18s2-1.5 4.5-1.5S19 18 21 18"/>
                        <path d="M4 13l5-7 3 4 2-2 6 5"/>
                    </svg>
                </span>
                <span class="min-w-0 leading-tight">
                    <span class="block font-extrabold tracking-tight">LONJOBOKO<span class="text-alert">-</span>RESCUE</span>
                    <span class="block text-xs text-ink-soft truncate">Desa {{ $site['village'] }} Tangguh Bencana</span>
                </span>
            </a>

            <nav aria-label="Menu utama" class="hidden lg:block">
                <ul class="flex items-center gap-1 text-sm font-semibold">
                    @foreach ($menu as $item)
                        @php $active = request()->routeIs($item['route']); @endphp
                        <li>
                            <a href="{{ $item['href'] ?? route($item['route']) }}"
                               @class([
                                   'px-3 py-2 rounded-lg transition-colors',
                                   'bg-river-800 text-white' => $active,
                                   'text-ink-soft hover:bg-river-50 hover:text-ink' => ! $active,
                               ])
                               @if ($active) aria-current="page" @endif>{{ $item['label'] }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <details class="lg:hidden relative group">
                <summary class="list-none cursor-pointer inline-flex items-center gap-2 rounded-lg border border-line bg-card px-3 py-2 text-sm font-semibold [&::-webkit-details-marker]:hidden">
                    <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                    Menu
                </summary>
                <nav aria-label="Menu utama" class="absolute right-0 mt-2 w-64 rounded-xl border border-line bg-card p-2 shadow-xl">
                    <ul class="grid gap-1 text-base font-semibold">
                        @foreach ($menu as $item)
                            @php $active = request()->routeIs($item['route']); @endphp
                            <li>
                                <a href="{{ $item['href'] ?? route($item['route']) }}"
                                   @class([
                                       'block px-3 py-2.5 rounded-lg',
                                       'bg-river-800 text-white' => $active,
                                       'hover:bg-river-50' => ! $active,
                                   ])
                                   @if ($active) aria-current="page" @endif>{{ $item['label'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            </details>
        </div>
    </header>

    <main id="konten" class="flex-1">
        @yield('content')
    </main>

    <footer class="mt-16 bg-river-900 text-river-100">
        <div class="wrap grid gap-8 py-10 md:grid-cols-3 text-sm">
            <div class="grid gap-2 content-start">
                <p class="font-extrabold text-white text-base">LONJOBOKO-RESCUE</p>
                <p class="text-river-100/80">Sistem Informasi dan Edukasi Mitigasi Bencana Desa {{ $site['village'] }}, Kecamatan {{ $site['district'] }}, Kabupaten {{ $site['regency'] }}.</p>
                @if ($site['office_address'])
                    <p class="text-river-100/80">{{ $site['office_address'] }}</p>
                @endif
            </div>
            <div class="grid gap-2 content-start">
                <p class="font-bold text-white">Penting</p>
                <p class="text-river-100/80">Web ini adalah sarana informasi dan <strong class="text-white">bukan pengganti sistem peringatan dini resmi</strong>. Saat keadaan darurat, ikuti arahan aparat desa, BPBD, dan BMKG.</p>
            </div>
            <div class="grid gap-2 content-start">
                <p class="font-bold text-white">Sumber data</p>
                <p class="text-river-100/80">Data peta dihimpun dan diverifikasi bersama pemerintah desa. Peta dasar &copy; kontributor OpenStreetMap.</p>
                <a href="{{ url('/admin') }}" class="text-river-100/70 hover:text-white underline underline-offset-4 decoration-river-100/30 w-fit">Masuk pengelola</a>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
