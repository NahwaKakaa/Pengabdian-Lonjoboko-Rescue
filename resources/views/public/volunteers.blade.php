@extends('layouts.public')

@section('title', 'Kader & Relawan')

@php
    // Pengurus ditampilkan lebih menonjol, sisanya sebagai anggota tim.
    $leaderWords = ['kepala desa', 'ketua', 'koordinator', 'sekretaris', 'bendahara', 'penanggung jawab', 'pembina'];
    $isLeader = fn ($person) => \Illuminate\Support\Str::contains(mb_strtolower((string) $person->position), $leaderWords);
    $leaders = $volunteers->filter($isLeader)->values();
    $members = $volunteers->reject($isLeader)->values();
    $hamlets = $volunteers->pluck('hamlet')->filter()->unique()->values();

    $joinHref = filled($site['whatsapp'] ?? null)
        ? \App\Support\Phone::whatsappHref($site['whatsapp'], "Halo, saya warga Desa {$site['village']} dan ingin bergabung menjadi relawan Kelompok Tangguh Bencana. Nama saya: ")
        : null;

    $teams = [
        ['name' => 'Peringatan dini', 'icon' => 'bell', 'tone' => 'alert', 'duties' => ['Memantau hujan, sungai, dan retakan lereng', 'Menyebarkan peringatan lewat masjid dan grup WA', 'Memastikan warga paling jauh ikut tahu']],
        ['name' => 'Evakuasi & penyelamatan', 'icon' => 'route', 'tone' => 'route', 'duties' => ['Mengarahkan warga ke jalur dan titik kumpul', 'Mendahulukan lansia, anak, ibu hamil, dan disabilitas', 'Memastikan tidak ada rumah yang tertinggal']],
        ['name' => 'Kesehatan & P3K', 'icon' => 'heart', 'tone' => 'danger', 'duties' => ['Pertolongan pertama di titik kumpul', 'Mencatat warga yang sakit atau terluka', 'Menghubungi puskesmas dan ambulans']],
        ['name' => 'Logistik & dapur umum', 'icon' => 'box', 'tone' => 'safe', 'duties' => ['Menyiapkan air bersih, makanan, dan selimut', 'Mengelola bantuan yang masuk', 'Menjaga kebersihan tempat pengungsian']],
        ['name' => 'Data & informasi', 'icon' => 'map', 'tone' => 'flood', 'duties' => ['Mendata warga di pengungsian', 'Mengusulkan lokasi rawan baru di web ini', 'Melapor ke pemerintah desa dan BPBD']],
    ];

    $icons = [
        'bell' => '<path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 01-3.4 0"/>',
        'route' => '<circle cx="6" cy="19" r="2"/><circle cx="18" cy="5" r="2"/><path d="M8 19h7a3.5 3.5 0 000-7H9a3.5 3.5 0 010-7h7"/>',
        'heart' => '<path d="M19 14c1.5-1.5 3-3.2 3-5.5A5.5 5.5 0 0016.5 3c-1.8 0-3 .5-4.5 2-1.5-1.5-2.7-2-4.5-2A5.5 5.5 0 002 8.5c0 2.3 1.5 4 3 5.5l7 7z"/><path d="M12 8v6M9 11h6"/>',
        'box' => '<path d="M21 8l-9-5-9 5 9 5 9-5z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/>',
        'map' => '<path d="M9 4L3 6v14l6-2 6 2 6-2V4l-6 2-6-2z"/><path d="M9 4v14M15 6v14"/>',
    ];
@endphp

@section('content')
    <x-page-header
        eyebrow="Karang Taruna Tangguh Bencana"
        title="Kader dan relawan desa"
        intro="Warga yang siap membantu tetangganya: memantau tanda bahaya, menyebarkan peringatan, dan mendampingi evakuasi saat banjir atau longsor."
    >
        <ul class="vol-stats">
            <li><strong class="num">{{ $volunteers->count() }}</strong> <span>orang terdaftar</span></li>
            <li><strong class="num">{{ count($teams) }}</strong> <span>tim siaga</span></li>
            @if ($hamlets->isNotEmpty())
                <li><strong class="num">{{ $hamlets->count() }}</strong> <span>dusun terwakili</span></li>
            @endif
        </ul>
    </x-page-header>

    {{-- Pengurus & anggota --}}
    <section class="wrap py-10 sm:py-12">
        <div class="vol-head">
            <div>
                <p class="vol-eyebrow">Pengurus &amp; anggota</p>
                <h2 class="vol-title">Orang-orang yang bisa Anda hubungi</h2>
            </div>
            @if ($hamlets->isNotEmpty())
                <ul class="vol-hamlets" aria-label="Dusun yang terwakili">
                    @foreach ($hamlets as $hamlet)
                        <li>Dusun {{ $hamlet }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        @if ($volunteers->isEmpty())
            <div class="vol-empty">
                <p class="font-bold text-ink">Daftar kader sedang disusun.</p>
                <p>Nama pengurus dan kader per dusun akan tampil di sini setelah ditetapkan pemerintah desa.</p>
            </div>
        @else
            <ul class="vol-grid">
                    @foreach ($leaders as $person)
                        <li class="vol-card vol-card--leader">
                            @include('public.partials.volunteer-avatar', ['person' => $person, 'size' => 'lg'])
                            <div class="min-w-0">
                                <p class="vol-role">{{ $person->position }}</p>
                                <p class="vol-name">{{ $person->name }}</p>
                                <p class="vol-meta">
                                    {{ $person->hamlet ? 'Dusun '.$person->hamlet : 'Desa '.$site['village'] }}
                                    · Penanggung jawab kesiapsiagaan bencana
                                </p>
                                @if ($person->show_phone && $person->phone)
                                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $person->phone) }}" class="vol-call">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg>
                                        <span class="num">{{ $person->phone }}</span>
                                    </a>
                                @endif
                            </div>
                        </li>
                    @endforeach

                @foreach ($members as $person)
                    <li class="vol-card">
                        @include('public.partials.volunteer-avatar', ['person' => $person, 'size' => 'md'])
                        <div class="min-w-0">
                            <p class="vol-name">{{ $person->name }}</p>
                            @if ($person->position)
                                <p class="vol-role">{{ $person->position }}</p>
                            @endif
                            @if ($person->hamlet)
                                <p class="vol-meta">Dusun {{ $person->hamlet }}</p>
                            @endif
                            @if ($person->show_phone && $person->phone)
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $person->phone) }}" class="vol-call vol-call--sm">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg>
                                    <span class="num">{{ $person->phone }}</span>
                                </a>
                            @endif
                        </div>
                    </li>
                @endforeach

                @if ($members->count() < 3)
                    <li class="vol-card vol-card--slot">
                        <span class="vol-slot-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0114 0M19 8v6M16 11h6"/></svg>
                        </span>
                        <div>
                            <p class="vol-name">Kader per dusun sedang didata</p>
                            <p class="vol-meta">Setiap dusun akan punya kader yang bisa dihubungi warga terdekat.</p>
                        </div>
                    </li>
                @endif
            </ul>
        @endif
    </section>

    {{-- Tim siaga --}}
    <section class="vol-band">
        <div class="wrap py-10 sm:py-12">
            <p class="vol-eyebrow">Pembagian tugas</p>
            <h2 class="vol-title">Lima tim siaga dan tugasnya</h2>
            <p class="vol-lead">Saat ada tanda bahaya, setiap kader bergerak sesuai timnya supaya tidak ada pekerjaan yang terlewat.</p>

            <ol class="vol-teams">
                @foreach ($teams as $team)
                    <li class="vol-team" style="--tone: var(--color-{{ $team['tone'] }})">
                        <div class="vol-team__head">
                            <span class="vol-team__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $icons[$team['icon']] !!}</svg>
                            </span>
                            <span class="vol-team__no num">0{{ $loop->iteration }}</span>
                        </div>
                        <h3 class="vol-team__name">{{ $team['name'] }}</h3>
                        <ul class="vol-team__duties">
                            @foreach ($team['duties'] as $duty)
                                <li>{{ $duty }}</li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Kegiatan Karang Taruna --}}
    <section class="wrap pt-10 sm:pt-12" id="kegiatan">
        <p class="vol-eyebrow">Kegiatan</p>
        <h2 class="vol-title">Yang sudah dilakukan Karang Taruna</h2>
        @if ($activities->isEmpty())
            <div class="vol-empty mt-5">
                <p class="font-bold text-ink">Belum ada kegiatan yang dicatat.</p>
                <p>Sosialisasi, simulasi evakuasi, dan pengecekan jalur akan tampil di sini.</p>
            </div>
        @else
            <ul class="act-grid">
                @foreach ($activities as $activity)
                    <li class="act-card">
                        @if ($activity->photo)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($activity->photo) }}" alt="" loading="lazy" class="act-card__img">
                        @endif
                        <div class="act-card__body">
                            <p class="act-card__date">{{ $activity->held_on->translatedFormat('j F Y') }}@if ($activity->place) · {{ $activity->place }}@endif</p>
                            <h3 class="act-card__title">{{ $activity->title }}</h3>
                            @if ($activity->description)
                                <p class="act-card__text">{{ \Illuminate\Support\Str::limit($activity->description, 220) }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    {{-- Ajakan bergabung --}}
    <section class="wrap py-10 sm:py-14">
        <div class="vol-join">
            <div class="vol-join__text">
                <p class="vol-join__eyebrow">Terbuka untuk warga</p>
                <h2 class="vol-join__title">Ingin menjadi relawan?</h2>
                <ul class="vol-join__list">
                    <li>Warga Desa {{ $site['village'] }}, usia 17 tahun ke atas</li>
                    <li>Bersedia ikut latihan evakuasi dan pertolongan pertama</li>
                    <li>Bisa dihubungi saat musim hujan</li>
                </ul>
            </div>
            <div class="vol-join__actions">
                @if ($joinHref)
                    <a href="{{ $joinHref }}" target="_blank" rel="noopener" class="vol-join__btn">
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 00-8.6 15.1L2 22l5-1.3A10 10 0 1012 2zm0 18.2c-1.5 0-3-.4-4.3-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1112 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1a6.7 6.7 0 01-3.3-2.9c-.2-.4.2-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.4.1-.6.3-.2.2-.8.8-.8 2s.8 2.3 1 2.5c.1.2 1.6 2.5 4 3.5 1.5.6 2 .7 2.8.6.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.1-1.2l-.4-.2z"/></svg>
                        Daftar lewat WhatsApp
                    </a>
                @endif
                <p class="vol-join__note">atau datang langsung ke Kantor Desa {{ $site['village'] }}{{ $site['office_address'] ? ', '.$site['office_address'] : '' }}.</p>
            </div>
        </div>
    </section>
@endsection
