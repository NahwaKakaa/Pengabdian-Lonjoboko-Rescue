{{--
    Panel kiri halaman masuk admin: pemandangan desa, ringkasan fungsi panel,
    status peringatan, dan jam WITA. Di layar kecil panel ini disembunyikan
    dan hanya tombol tema + tautan kembali yang tampil.
    Gaya: resources/css/admin.css bagian "Halaman login".
--}}
@php
    $site = \App\Providers\AppServiceProvider::siteInfo();
    $alerts = \App\Support\AdminStatus::activeAlerts();
    $hasEmergency = $alerts->contains(fn ($a) => $a->level === \App\Enums\AnnouncementLevel::Darurat);
    $place = collect([
        $site['district'] ? 'Kec. '.$site['district'] : null,
        $site['regency'] ? 'Kab. '.$site['regency'] : null,
    ])->filter()->implode(' · ');
@endphp

<aside class="lr-auth" aria-label="Tentang panel">
    <div class="lr-auth__scene" aria-hidden="true">
        <span class="lr-auth__img lr-auth__img--day" style="background-image: url('{{ asset('images/hero-mountains-day.jpg') }}')"></span>
        <span class="lr-auth__img lr-auth__img--night" style="background-image: url('{{ asset('images/hero-mountains-night.jpg') }}')"></span>
    </div>

    <div class="lr-auth__inner">
        <header class="lr-auth__top">
            <span class="lr-auth__brand">
                <span class="lr-auth__mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 18c2 0 2-1.5 4.5-1.5S10 18 12 18s2-1.5 4.5-1.5S19 18 21 18"/>
                        <path d="M4 13l5-7 3 4 2-2 6 5"/>
                    </svg>
                </span>
                <span>
                    <strong>LONJOBOKO RESCUE</strong>
                    <small>Panel Pengelola Desa</small>
                </span>
            </span>
            <a href="{{ url('/') }}" class="lr-auth__back">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
                Web publik
            </a>
        </header>

        <div class="lr-auth__body">
            <p class="lr-auth__eyebrow">Desa {{ $site['village'] }}@if ($place) <span>· {{ $place }}</span>@endif</p>
            <h2 class="lr-auth__title">Kabar bencana desa, dikelola dari satu tempat.</h2>
            <p class="lr-auth__lead">Panel ini untuk perangkat desa dan kader siaga. Setiap data yang tampil di web warga melewati pemeriksaan di sini.</p>

            <ul class="lr-auth__list">
                <li>
                    <span class="lr-auth__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4"/><path d="M12 3l7 3v5c0 4.5-3 8.5-7 10-4-1.5-7-5.5-7-10V6z"/></svg>
                    </span>
                    <span><strong>Verifikasi data</strong><small>Usulan lokasi rawan dari kader diperiksa sebelum tampil.</small></span>
                </li>
                <li>
                    <span class="lr-auth__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="19" r="2"/><circle cx="18" cy="5" r="2"/><path d="M8 19h7a3.5 3.5 0 000-7H9a3.5 3.5 0 010-7h7"/></svg>
                    </span>
                    <span><strong>Peta &amp; jalur evakuasi</strong><small>Titik kumpul, jalur, dan batas desa langsung di peta.</small></span>
                </li>
                <li>
                    <span class="lr-auth__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 01-3.4 0"/></svg>
                    </span>
                    <span><strong>Peringatan dini</strong><small>Umumkan status siaga ke seluruh warga dalam hitungan menit.</small></span>
                </li>
            </ul>
        </div>

        <footer class="lr-auth__foot">
            <span @class([
                'lr-auth__status',
                'is-danger' => $hasEmergency,
                'is-warn' => $alerts->isNotEmpty() && ! $hasEmergency,
            ])>
                <span class="lr-auth__dot" aria-hidden="true"></span>
                @if ($alerts->isEmpty())
                    Tidak ada peringatan berlaku
                @else
                    {{ $alerts->count() }} peringatan sedang berlaku
                @endif
            </span>

            <span
                class="lr-auth__clock"
                x-data="{
                    time: '--.--', timer: null,
                    tick() { this.time = new Intl.DateTimeFormat('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false, timeZone: 'Asia/Makassar' }).format(new Date()).replace(':', '.'); },
                    init() { this.tick(); this.timer = setInterval(() => this.tick(), 15000); },
                    destroy() { clearInterval(this.timer); },
                }"
                title="Waktu Indonesia Tengah"
            >
                <span x-text="time">--.--</span> WITA
            </span>
        </footer>
    </div>
</aside>

{{-- Bilah kecil di atas formulir: kembali ke web (HP) dan ganti tema --}}
<div class="lr-auth-tools" x-data>
    <a href="{{ url('/') }}" class="lr-auth-tools__back">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
        Web publik
    </a>

    <button
        type="button"
        class="lr-theme-btn"
        x-on:click="
            document.documentElement.classList.add('lr-theme-fade');
            $dispatch('theme-changed', $store.theme === 'dark' ? 'light' : 'dark');
            setTimeout(() => document.documentElement.classList.remove('lr-theme-fade'), 400);
        "
        x-bind:aria-label="$store.theme === 'dark' ? 'Ganti ke tampilan terang' : 'Ganti ke tampilan gelap'"
        x-bind:title="$store.theme === 'dark' ? 'Tampilan terang' : 'Tampilan gelap'"
    >
        <svg class="lr-theme-btn__sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
        <svg class="lr-theme-btn__moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.8A9 9 0 1111.2 3a7 7 0 009.8 9.8z"/></svg>
    </button>
</div>
