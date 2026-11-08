@extends('layouts.public')

@section('title', 'Kader & Relawan')

@section('content')
    <x-page-header
        eyebrow="Karang Taruna Tangguh Bencana"
        title="Kader dan relawan desa"
        intro="Kader membantu warga bersiap, mendampingi evakuasi, dan memperbarui data peta desa di web ini."
    />

    <section class="wrap py-8">
        @if ($volunteers->isNotEmpty())
            <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($volunteers as $person)
                    <li class="elevation-card flex items-center gap-4 rounded-2xl p-5">
                        @if ($person->photo)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($person->photo) }}" alt="" class="size-16 shrink-0 rounded-2xl object-cover shadow-sm">
                        @else
                            <span class="grid place-items-center size-16 shrink-0 rounded-2xl bg-gradient-to-br from-river-700 to-river-900 text-white text-lg font-black shadow-sm" aria-hidden="true">{{ $person->initials() }}</span>
                        @endif
                        <div class="min-w-0">
                            <p class="font-extrabold text-base text-ink leading-snug">{{ $person->name }}</p>
                            @if ($person->position)
                                <p class="text-xs text-river-700 font-bold uppercase tracking-wider mt-0.5">{{ $person->position }}</p>
                            @endif
                            @if ($person->hamlet)
                                <p class="text-xs text-ink-soft">Dusun {{ $person->hamlet }}</p>
                            @endif
                            @if ($person->show_phone && $person->phone)
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $person->phone) }}" class="text-xs font-bold text-river-700 hover:text-river-900 num underline underline-offset-2 mt-1 block">{{ $person->phone }}</a>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="rounded-2xl border border-dashed border-line bg-card p-6 text-ink-soft">Daftar kader dan relawan akan diumumkan setelah pembentukan Kader Karang Taruna Tangguh Bencana.</p>
        @endif
    </section>
@endsection
