{{--
    Topbar admin: status koneksi, peringatan desa, data menunggu verifikasi,
    jam WITA, dan tombol ganti tema. Gaya ada di resources/css/admin.css (lr-top*).
--}}
@php
    $alerts = \App\Support\AdminStatus::activeAlerts();
    $hasEmergency = $alerts->contains(fn ($a) => $a->level === \App\Enums\AnnouncementLevel::Darurat);
    $pending = \App\Support\AdminStatus::pendingCount();
    $isAdmin = (bool) auth()->user()?->isAdmin();
    $alertUrl = $isAdmin ? \App\Filament\Resources\AnnouncementResource::getUrl('index') : url('/darurat');
@endphp

<div class="lr-top" x-data>
    {{-- Koneksi internet (berguna di desa dengan sinyal naik-turun) --}}
    <span
        class="lr-chip lr-chip--net"
        x-data="{ online: navigator.onLine }"
        x-on:online.window="online = true"
        x-on:offline.window="online = false"
        x-bind:class="online ? 'is-ok' : 'is-off'"
        x-bind:title="online ? 'Terhubung ke internet' : 'Tidak ada koneksi internet. Perubahan belum bisa disimpan.'"
    >
        <span class="lr-dot" aria-hidden="true"></span>
        <span class="lr-chip__text" x-text="online ? 'Online' : 'Offline'">Online</span>
    </span>

    {{-- Status peringatan desa --}}
    <a
        href="{{ $alertUrl }}"
        @class([
            'lr-chip lr-chip--alert',
            'is-danger' => $hasEmergency,
            'is-warn' => $alerts->isNotEmpty() && ! $hasEmergency,
            'is-ok' => $alerts->isEmpty(),
        ])
        title="{{ $alerts->isEmpty() ? 'Tidak ada peringatan yang berlaku' : $alerts->count().' peringatan sedang berlaku' }}"
    >
        <span class="lr-dot" aria-hidden="true"></span>
        <span class="lr-chip__text">
            @if ($alerts->isEmpty())
                Desa aman
            @else
                {{ $alerts->count() }} peringatan
            @endif
        </span>
    </a>

    {{-- Data menunggu verifikasi --}}
    @if ($pending > 0)
        <a href="{{ filament()->getUrl() }}#menunggu-verifikasi" class="lr-chip lr-chip--pending" title="{{ $pending }} data peta menunggu verifikasi">
            <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .2.08.39.22.53l3 3a.75.75 0 101.06-1.06l-2.78-2.78V5z" clip-rule="evenodd"/></svg>
            <span class="lr-chip__count">{{ $pending }}</span>
            <span class="lr-chip__text">menunggu</span>
        </a>
    @endif

    {{-- Jam WITA --}}
    <div
        class="lr-clock"
        x-data="{
            time: '', date: '', timer: null,
            tick() {
                const now = new Date();
                const tz = 'Asia/Makassar';
                this.time = new Intl.DateTimeFormat('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false, timeZone: tz }).format(now).replaceAll('.', ':');
                this.date = new Intl.DateTimeFormat('id-ID', { weekday: 'long', day: 'numeric', month: 'short', year: 'numeric', timeZone: tz }).format(now);
            },
            init() { this.tick(); this.timer = setInterval(() => this.tick(), 1000); },
            destroy() { clearInterval(this.timer); },
        }"
        title="Waktu Indonesia Tengah"
    >
        <svg class="lr-clock__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
        <span class="lr-clock__body">
            <span class="lr-clock__time"><span x-text="time">--:--:--</span> <small>WITA</small></span>
            <span class="lr-clock__date" x-text="date"></span>
        </span>
    </div>

    {{-- Ganti tema terang / gelap --}}
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
