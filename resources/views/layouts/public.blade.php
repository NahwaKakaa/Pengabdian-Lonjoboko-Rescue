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
    <title>{{ $pageTitle !== '' ? $pageTitle.' · ' : '' }}LONJOBOKO RESCUE</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen flex flex-col antialiased bg-scenic-ambient text-ink">
    <a href="#konten" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-50 focus:bg-white focus:text-river-900 focus:px-4 focus:py-2 focus:rounded-lg focus:shadow-lg focus:font-bold">Langsung ke konten</a>

    {{-- Pita status: selalu tampil, supaya warga langsung tahu ada peringatan atau tidak. --}}
    @if ($topAlert)
        <div role="alert" class="{{ $isEmergency ? 'bg-danger shadow-md' : 'bg-alert shadow-md' }} text-white relative z-50">
            <div class="wrap flex flex-wrap items-center justify-between gap-x-3 gap-y-1 py-2.5 text-sm">
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="inline-flex items-center gap-1.5 font-black uppercase tracking-wider text-xs px-2.5 py-0.5 rounded-full bg-white/20 border border-white/30 backdrop-blur-xs">
                        <span class="size-2 rounded-full bg-white animate-ping" aria-hidden="true"></span>
                        {{ $topAlert->level->getLabel() }}
                    </span>
                    <a href="{{ route('announcement.show', $topAlert) }}" class="font-bold underline underline-offset-4 decoration-white/60 hover:decoration-white truncate">
                        {{ $topAlert->title }}
                    </a>
                </div>
                @if ($activeAlerts->count() > 1)
                    <a href="{{ route('emergency') }}" class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-white/15 hover:bg-white/25 transition-colors">+{{ $activeAlerts->count() - 1 }} peringatan lain</a>
                @endif
            </div>
        </div>
    @else
        <div class="site-topbar">
            <div class="wrap flex flex-wrap items-center justify-between gap-x-4 gap-y-1 py-2 text-xs sm:text-sm">
                <span class="inline-flex items-center gap-2 font-semibold text-xs text-river-100">
                    <span class="relative flex size-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-safe opacity-75"></span>
                        <span class="relative inline-flex rounded-full size-2 bg-safe"></span>
                    </span>
                    Status Desa: <span class="font-bold text-white">Aman & Terkendali</span>
                </span>
                <div class="flex items-center gap-4 text-xs text-river-100">
                    <span class="hidden md:inline">Kawasan Lereng Pegunungan & Persawahan Parangloe</span>
                    <a href="https://www.bmkg.go.id" target="_blank" rel="noopener" class="text-river-100 hover:text-white underline underline-offset-4 decoration-river-100/40 inline-flex items-center gap-1 transition-colors">
                        <span>Prakiraan Cuaca BMKG</span>
                        <svg viewBox="0 0 24 24" class="size-3" fill="none" stroke="currentColor" stroke-width="2.25"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14L21 3"/></svg>
                    </a>
                </div>
            </div>
        </div>
    @endif

    <header class="sticky top-0 z-40 site-header shadow-lg" style="top: env(safe-area-inset-top, 0px)">
        <div class="wrap flex items-center justify-between gap-4 py-3.5">
            <a href="{{ route('home') }}" class="group flex items-center gap-3 min-w-0" aria-label="LONJOBOKO RESCUE, ke beranda">
                <span class="grid place-items-center size-10 shrink-0 rounded-xl bg-gradient-to-br from-emerald-600 to-river-800 text-white shadow-md shadow-black/30 group-hover:scale-105 transition-transform" aria-hidden="true">
                    <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 18c2 0 2-1.5 4.5-1.5S10 18 12 18s2-1.5 4.5-1.5S19 18 21 18"/>
                        <path d="M4 13l5-7 3 4 2-2 6 5"/>
                    </svg>
                </span>
                <span class="min-w-0 leading-tight">
                    <span class="block font-black tracking-tight text-white text-base">LONJOBOKO RESCUE</span>
                    <span class="block text-xs text-river-100 truncate font-semibold">Desa {{ $site['village'] }} Tangguh Bencana</span>
                </span>
            </a>

            <nav aria-label="Menu utama" class="hidden lg:block">
                <ul class="flex items-center gap-1.5 text-sm font-semibold">
                    @foreach ($menu as $item)
                        @php $active = request()->routeIs($item['route']); @endphp
                        <li>
                            <a href="{{ $item['href'] ?? route($item['route']) }}"
                               @class([
                                   'px-3.5 py-2 rounded-xl transition-all duration-200 font-semibold',
                                   'bg-emerald-600 text-white shadow-md ring-1 ring-emerald-400/50' => $active,
                                   'text-river-100 hover:bg-white/10 hover:text-white' => ! $active,
                               ])
                               @if ($active) aria-current="page" @endif>{{ $item['label'] }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <details class="lg:hidden relative group">
                <summary class="list-none cursor-pointer inline-flex items-center gap-2 rounded-xl border border-white/25 bg-river-800 text-white px-3.5 py-2 text-sm font-bold shadow-xs hover:bg-river-700 [&::-webkit-details-marker]:hidden">
                    <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                    Menu
                </summary>
                <nav aria-label="Menu utama" class="absolute right-0 mt-2 w-64 rounded-2xl border border-white/20 bg-[#062b29] p-2.5 shadow-2xl ring-1 ring-black/50 text-white z-50">
                    <ul class="grid gap-1 text-base font-semibold">
                        @foreach ($menu as $item)
                            @php $active = request()->routeIs($item['route']); @endphp
                            <li>
                                <a href="{{ $item['href'] ?? route($item['route']) }}"
                                   @class([
                                       'block px-3.5 py-2.5 rounded-xl transition-colors',
                                       'bg-emerald-600 text-white' => $active,
                                       'text-river-100 hover:bg-white/10 hover:text-white' => ! $active,
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

    <footer class="mt-20 site-footer relative">
        <div class="wrap grid gap-10 py-12 md:grid-cols-3 text-sm">
            <div class="grid gap-3 content-start">
                <div class="flex items-center gap-3">
                    <span class="grid place-items-center size-9 shrink-0 rounded-xl bg-emerald-600 text-white shadow-sm" aria-hidden="true">
                        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 18c2 0 2-1.5 4.5-1.5S10 18 12 18s2-1.5 4.5-1.5S19 18 21 18"/>
                            <path d="M4 13l5-7 3 4 2-2 6 5"/>
                        </svg>
                    </span>
                    <p class="font-black text-white text-lg tracking-tight">LONJOBOKO RESCUE</p>
                </div>
                <p class="text-river-100 leading-relaxed font-normal">Sistem Informasi dan Edukasi Mitigasi Bencana Desa {{ $site['village'] }}, Kecamatan {{ $site['district'] }}, Kabupaten {{ $site['regency'] }}, Sulawesi Selatan.</p>
                @if ($site['office_address'])
                    <p class="text-river-100 text-xs flex items-start gap-1.5 pt-1">
                        <svg viewBox="0 0 24 24" class="size-4 shrink-0 text-emerald-400 mt-0.5" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"/><circle cx="12" cy="10" r="3"/></svg>
                        <span>{{ $site['office_address'] }}</span>
                    </p>
                @endif
            </div>
            <div class="grid gap-3 content-start">
                <p class="font-bold text-white text-base flex items-center gap-2">
                    <span class="size-2 rounded-full bg-alert"></span>
                    Penting Diperhatikan
                </p>
                <p class="text-river-100 leading-relaxed">Web ini adalah sarana informasi kesiapsiagaan dan edukasi mitigasi kebencanaan, serta <strong class="text-white font-bold underline decoration-amber-400 underline-offset-2">bukan pengganti instruksi tanggap darurat resmi</strong>. Saat terjadi bencana, ikuti selalu komando dari aparat desa, BPBD, dan BMKG.</p>
            </div>
            <div class="grid gap-3 content-start">
                <p class="font-bold text-white text-base">Sumber Data & Pengelola</p>
                <p class="text-river-100 leading-relaxed">Data peta risiko, titik kumpul, dan jalur evakuasi dihimpun dan diverifikasi bersama pemerintah desa. Peta dasar &copy; kontributor OpenStreetMap.</p>
                <div class="pt-2">
                    <a href="{{ url('/admin') }}" class="inline-flex items-center gap-2 text-xs font-bold px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white transition-colors border border-white/20 shadow-xs">
                        <svg viewBox="0 0 24 24" class="size-4 text-emerald-300" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        Masuk Dasbor Pengelola
                    </a>
                </div>
            </div>
        </div>
        <div class="site-footer-bar py-4 text-xs">
            <div class="wrap flex flex-wrap items-center justify-between gap-2">
                <p class="font-semibold text-white">&copy; {{ date('Y') }} LONJOBOKO RESCUE · Pengabdian Masyarakat & Pemerintah Desa Lonjoboko</p>
                <p class="text-river-100 font-medium">Kawasan Lereng Pegunungan Bawakaraeng & Pertanian Sawah Lonjoboko</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
