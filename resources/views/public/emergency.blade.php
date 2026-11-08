@extends('layouts.public')

@section('title', 'Informasi Darurat')

@php
    $steps = collect(preg_split('/\r\n|\r|\n/', (string) $site['report_instructions']))
        ->map(fn ($line) => trim(preg_replace('/^\s*(\d+[.)]|[-*•])\s*/u', '', $line)))
        ->filter()
        ->values();

    if ($steps->isEmpty()) {
        $steps = collect([
            'Pastikan diri Anda sudah berada di tempat aman.',
            'Hubungi kepala dusun, aparat desa, atau kader terdekat lewat nomor di halaman ini.',
            'Sebutkan: lokasi kejadian (dusun dan patokan), jenis kejadian, jumlah warga yang terdampak, dan apakah ada yang terluka atau terjebak.',
            'Bila ada korban luka berat, hubungi juga layanan gawat darurat.',
            'Jangan menyebarkan kabar yang belum jelas sumbernya.',
        ]);
    }
@endphp

@section('content')
    <x-page-header
        eyebrow="Informasi darurat"
        title="Peringatan dan nomor penting"
        intro="Simpan nomor-nomor ini di HP Anda sebelum keadaan darurat terjadi."
    />

    <div class="wrap py-8 grid gap-10 lg:grid-cols-[1.4fr_1fr]">
        <div class="grid gap-10 content-start">
            {{-- Peringatan aktif --}}
            <section class="grid gap-4" aria-labelledby="peringatan">
                <h2 id="peringatan" class="text-2xl font-extrabold tracking-tight">Peringatan yang berlaku</h2>
                @forelse ($alerts as $alert)
                    @php $isDarurat = $alert->level === \App\Enums\AnnouncementLevel::Darurat; @endphp
                    <a href="{{ route('announcement.show', $alert) }}"
                       @class(['block rounded-2xl border-l-8 p-5', 'border-danger bg-danger-soft' => $isDarurat, 'border-alert bg-alert-soft' => ! $isDarurat])>
                        <div class="flex flex-wrap items-center gap-2 text-sm">
                            <x-hazard-badge :type="$alert->level" />
                            @if ($alert->source)
                                <span class="text-ink-soft">Sumber: {{ $alert->source }}</span>
                            @endif
                        </div>
                        <h3 class="mt-2 font-bold text-lg">{{ $alert->title }}</h3>
                        <p class="mt-1 text-sm text-ink-soft line-clamp-3">{{ $alert->body }}</p>
                        @if ($alert->expires_at)
                            <p class="mt-2 text-xs font-semibold text-ink-soft">Berlaku sampai {{ $alert->expires_at->translatedFormat('d F Y, H:i') }} WITA</p>
                        @endif
                    </a>
                @empty
                    <div class="rounded-2xl border border-line bg-card p-5 flex items-start gap-3">
                        <span class="mt-1 size-3 shrink-0 rounded-full bg-safe" aria-hidden="true"></span>
                        <p class="text-ink-soft">Tidak ada peringatan dari pemerintah desa saat ini. Tetap pantau informasi cuaca dari BMKG, terutama saat hujan deras berlangsung lama.</p>
                    </div>
                @endforelse
            </section>

            {{-- Kontak --}}
            <section class="grid gap-6" aria-labelledby="kontak">
                <h2 id="kontak" class="text-2xl font-extrabold tracking-tight">Kontak darurat</h2>
                @forelse ($contactGroups as $label => $contacts)
                    <div class="grid gap-3">
                        <h3 class="eyebrow">{{ $label }}</h3>
                        <ul class="grid gap-3">
                            @foreach ($contacts as $contact)
                                <x-contact-row :contact="$contact" />
                            @endforeach
                        </ul>
                    </div>
                @empty
                    <p class="rounded-2xl border border-dashed border-line bg-card p-6 text-ink-soft">Kontak darurat belum diisi oleh pengelola.</p>
                @endforelse
            </section>
        </div>

        <aside class="grid gap-6 content-start">
            <section class="rounded-2xl bg-river-800 text-white p-6 grid gap-4" aria-labelledby="lapor">
                <h2 id="lapor" class="text-xl font-extrabold">Cara melaporkan kejadian</h2>
                <ol class="grid gap-3">
                    @foreach ($steps as $i => $step)
                        <li class="grid grid-cols-[auto_1fr] gap-3">
                            <span class="grid place-items-center size-7 rounded-full bg-white text-river-900 text-sm font-extrabold num" aria-hidden="true">{{ $i + 1 }}</span>
                            <span class="text-river-50 leading-relaxed">{{ $step }}</span>
                        </li>
                    @endforeach
                </ol>
                @if ($site['office_phone'])
                    <p class="text-sm text-river-100 border-t border-white/15 pt-3">Kantor desa: <a href="tel:{{ preg_replace('/[^0-9+]/', '', $site['office_phone']) }}" class="font-bold text-white num">{{ $site['office_phone'] }}</a></p>
                @endif
            </section>

            <section class="rounded-2xl border border-line bg-card p-6 grid gap-3" aria-labelledby="sumber">
                <h2 id="sumber" class="text-lg font-extrabold">Sumber peringatan dini resmi</h2>
                <p class="text-sm text-ink-soft">Web desa ini meneruskan informasi dan tidak membuat prakiraan sendiri. Untuk peringatan dini, rujuk instansi berikut.</p>
                <ul class="grid gap-3">
                    @foreach ($sources as $source)
                        <li class="border-t border-line pt-3 first:border-0 first:pt-0">
                            <a href="{{ $source['url'] }}" target="_blank" rel="noopener" class="font-semibold text-river-700 underline underline-offset-4">{{ $source['name'] }}</a>
                            <p class="text-sm text-ink-soft">{{ $source['description'] }}</p>
                        </li>
                    @endforeach
                </ul>
            </section>
        </aside>
    </div>
@endsection
