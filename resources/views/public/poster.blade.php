@php
    $village = $site['village'];
    $host = parse_url(url('/'), PHP_URL_HOST);
    $updated = now('Asia/Makassar')->locale('id')->translatedFormat('j F Y');
    $waHref = $whatsapp ? \App\Support\Phone::whatsappHref($whatsapp) : null;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Poster Siaga Bencana · Desa {{ $village }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    @vite(['resources/css/poster.css', 'resources/js/poster.js'])
</head>
<body>
    {{-- Bilah alat: hanya tampil di layar, tidak ikut tercetak --}}
    <div class="poster-toolbar">
        <a href="{{ route('emergency') }}" class="poster-toolbar__back">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
            Kembali
        </a>
        <p class="poster-toolbar__hint">Ukuran A4 tegak. Pilih <strong>Simpan sebagai PDF</strong> untuk dikirim, atau cetak langsung.</p>
        <button type="button" class="poster-toolbar__print" onclick="window.print()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9V3h12v6M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><path d="M6 14h12v7H6z"/></svg>
            Cetak / simpan PDF
        </button>
    </div>

    <main class="sheet">
        {{-- Kepala poster --}}
        <header class="p-head">
            <div class="p-head__text">
                <p class="p-head__eyebrow">Siaga banjir &amp; tanah longsor</p>
                <h1 class="p-head__title">Peta Evakuasi &amp; Nomor Darurat</h1>
                <p class="p-head__place">Desa {{ $village }} · Kec. {{ $site['district'] }} · Kab. {{ $site['regency'] }}</p>
            </div>
            <div class="p-qr">
                <div class="p-qr__code" data-qr="{{ url('/') }}" aria-label="Kode QR ke {{ $host }}"></div>
                <p class="p-qr__text">Pindai untuk peta &amp;<br>info terbaru</p>
            </div>
        </header>

        {{-- Peta skematis --}}
        <section class="p-map">
            <svg viewBox="0 0 {{ $map['width'] }} {{ $map['height'] }}" class="p-map__svg" preserveAspectRatio="xMidYMid meet" role="img" aria-label="Peta skematis Desa {{ $village }}">
                <defs>
                    <pattern id="grid" width="50" height="50" patternUnits="userSpaceOnUse">
                        <path d="M50 0H0V50" fill="none" stroke="#dbe7e4" stroke-width="1"/>
                    </pattern>
                    <marker id="arrow" viewBox="0 0 10 10" refX="6" refY="5" markerWidth="5" markerHeight="5" orient="auto-start-reverse">
                        <path d="M0 0L10 5L0 10z" fill="#c2185b"/>
                    </marker>
                </defs>
                <rect width="100%" height="100%" fill="#f6faf9"/>
                <rect width="100%" height="100%" fill="url(#grid)"/>

                @foreach ($map['boundary'] as $ring)
                    <polygon points="{{ $ring }}" fill="#e3f3ec" stroke="#0e6b66" stroke-width="3" stroke-dasharray="10 6" stroke-linejoin="round"/>
                @endforeach

                @foreach ($map['hazards'] as $h)
                    <circle cx="{{ $h['x'] }}" cy="{{ $h['y'] }}" r="{{ $h['r'] }}"
                        fill="{{ $h['type'] === 'banjir' ? '#1d6fd1' : '#c2620f' }}" fill-opacity="0.22"
                        stroke="{{ $h['type'] === 'banjir' ? '#1d6fd1' : '#c2620f' }}" stroke-width="2"/>
                @endforeach

                @foreach ($map['routes'] as $r)
                    <polyline points="{{ $r['points'] }}" fill="none" stroke="#ffffff" stroke-width="9" stroke-linecap="round" stroke-linejoin="round"/>
                    <polyline points="{{ $r['points'] }}" fill="none" stroke="#c2185b" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" marker-end="url(#arrow)"/>
                @endforeach

                @foreach ($map['points'] as $p)
                    <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="17" fill="#16825d" stroke="#ffffff" stroke-width="4"/>
                    <text x="{{ $p['x'] }}" y="{{ $p['y'] + 6 }}" text-anchor="middle" font-size="18" font-weight="800" fill="#ffffff" font-family="Plus Jakarta Sans, Arial, sans-serif">{{ $p['number'] }}</text>
                @endforeach

                {{-- Arah utara & skala --}}
                <g transform="translate({{ $map['width'] - 56 }}, 30)">
                    <path d="M0 0L12 34L0 26L-12 34z" fill="#10201f"/>
                    <text x="0" y="56" text-anchor="middle" font-size="18" font-weight="800" fill="#10201f" font-family="Plus Jakarta Sans, Arial, sans-serif">U</text>
                </g>
                <g transform="translate(28, {{ $map['height'] - 28 }})">
                    <rect x="-8" y="-30" width="{{ $map['scale']['px'] + 16 }}" height="44" rx="8" fill="#ffffff" fill-opacity="0.85"/>
                    <path d="M0 0H{{ $map['scale']['px'] }}" stroke="#10201f" stroke-width="4"/>
                    <path d="M0 -7V7M{{ $map['scale']['px'] }} -7V7" stroke="#10201f" stroke-width="3"/>
                    <text x="{{ $map['scale']['px'] / 2 }}" y="-12" text-anchor="middle" font-size="16" font-weight="700" fill="#10201f" font-family="Plus Jakarta Sans, Arial, sans-serif">{{ $map['scale']['label'] }}</text>
                </g>
            </svg>

            <ul class="p-legend">
                <li><span class="p-legend__line" style="border-color:#0e6b66"></span>Batas desa (perkiraan)</li>
                <li><span class="p-legend__route"></span>Jalur evakuasi</li>
                <li><span class="p-legend__dot" style="background:#16825d">1</span>Titik kumpul</li>
                <li><span class="p-legend__area" style="--c:#1d6fd1"></span>Rawan banjir</li>
                <li><span class="p-legend__area" style="--c:#c2620f"></span>Rawan longsor</li>
            </ul>
        </section>

        <div class="p-cols">
            {{-- Titik kumpul & jalur --}}
            <section class="p-card">
                <h2 class="p-card__title"><span class="p-card__icon" style="background:#16825d">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 21V4M5 4h11l-2 4 2 4H5"/></svg>
                </span>Titik kumpul</h2>
                @if ($points->isEmpty())
                    <p class="p-empty">Belum ada titik kumpul terverifikasi.</p>
                @else
                    <ol class="p-points">
                        @foreach ($points->take(8) as $point)
                            <li>
                                <span class="p-num">{{ $loop->iteration }}</span>
                                <span><strong>{{ $point->name }}</strong>@if ($point->capacity)<small> · ±{{ number_format($point->capacity, 0, ',', '.') }} orang</small>@endif</span>
                            </li>
                        @endforeach
                    </ol>
                @endif

                @if ($routes->isNotEmpty())
                    <h3 class="p-card__sub">Jalur evakuasi</h3>
                    <ul class="p-routes">
                        @foreach ($routes->take(6) as $route)
                            <li><span class="p-routes__line"></span><span>{{ $route->name }}@if ($route->assemblyPoint) <small>→ {{ $route->assemblyPoint->name }}</small>@endif</span></li>
                        @endforeach
                    </ul>
                @endif
            </section>

            {{-- Nomor darurat --}}
            <section class="p-card p-card--red">
                <h2 class="p-card__title"><span class="p-card__icon" style="background:#b91c1c">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg>
                </span>Nomor darurat</h2>
                <ul class="p-phones">
                    @if ($whatsapp)
                        <li class="p-phones__main">
                            <span>{{ $whatsappLabel ?: 'Posko Desa '.$village }} <small>(WhatsApp)</small></span>
                            <strong>{{ $whatsapp }}</strong>
                        </li>
                    @endif
                    @if ($officePhone)
                        <li><span>Kantor desa</span><strong>{{ $officePhone }}</strong></li>
                    @endif
                    @forelse ($contacts as $contact)
                        <li><span>{{ $contact->name }}</span><strong>{{ $contact->phone }}</strong></li>
                    @empty
                        <li><span>Panggilan darurat terpadu</span><strong>112</strong></li>
                        <li><span>Ambulans</span><strong>119</strong></li>
                        <li><span>Basarnas</span><strong>115</strong></li>
                    @endforelse
                </ul>
            </section>
        </div>

        {{-- Langkah singkat --}}
        <div class="p-cols p-cols--steps">
            <section class="p-steps" style="--c:#1d6fd1">
                <h2>Saat banjir</h2>
                <ol>
                    @foreach (array_slice(\App\Support\SafetyGuide::floodSteps(), 0, 4) as $line)
                        <li><span class="p-clamp">{{ $line }}</span></li>
                    @endforeach
                </ol>
            </section>
            <section class="p-steps" style="--c:#c2620f">
                <h2>Saat tanah longsor</h2>
                <ol>
                    @foreach (array_slice(\App\Support\SafetyGuide::landslideSteps(), 0, 4) as $line)
                        <li><span class="p-clamp">{{ $line }}</span></li>
                    @endforeach
                </ol>
            </section>
        </div>

        <footer class="p-foot">
            <p><strong>{{ $host }}</strong> · Diperbarui {{ $updated }} dari data terverifikasi pemerintah desa.</p>
            <p>Bukan pengganti arahan resmi. Saat bencana, ikuti aparat desa, BPBD, dan BMKG.</p>
        </footer>
    </main>
</body>
</html>
