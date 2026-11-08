@extends('layouts.public')

@section('title', $content->title)
@section('description', $content->summary ?: $content->title)

@section('content')
    <article>
        <header class="page-header-scenic text-white relative overflow-hidden border-b border-river-800 shadow-inner">
            <div class="relative wrap py-10 sm:py-12 grid gap-3.5 z-10">
                <nav aria-label="Lokasi halaman" class="text-xs font-semibold text-river-100 flex items-center gap-1.5">
                    <a href="{{ route('education') }}" class="hover:text-white underline underline-offset-4 decoration-white/40">Edukasi</a>
                    <span aria-hidden="true" class="text-river-100/60">/</span>
                    <a href="{{ route('education', ['kategori' => $content->category->value]) }}" class="hover:text-white underline underline-offset-4 decoration-white/40">{{ $content->category->getLabel() }}</a>
                </nav>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-white leading-tight max-w-[32ch]">{{ $content->title }}</h1>
                <div class="flex flex-wrap gap-2 pt-1">
                    <x-hazard-badge :type="$content->topic->value === 'umum' ? 'umum' : $content->topic">{{ $content->topic->getLabel() }}</x-hazard-badge>
                </div>
                @if ($content->summary)
                    <p class="text-base sm:text-lg text-river-100/90 max-w-[65ch] leading-relaxed">{{ $content->summary }}</p>
                @endif
            </div>
        </header>

        <div class="wrap py-8 grid gap-8">
            @if ($content->youtubeId())
                <div class="aspect-video w-full max-w-3xl overflow-hidden rounded-2xl bg-ink">
                    <iframe
                        class="h-full w-full"
                        src="https://www.youtube-nocookie.com/embed/{{ $content->youtubeId() }}"
                        title="{{ $content->title }}"
                        loading="lazy"
                        allow="accelerometer; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                    ></iframe>
                </div>
            @endif

            @if ($content->image)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($content->image) }}" alt="{{ $content->title }}" class="w-full max-w-3xl rounded-2xl border border-line">
            @endif

            @if ($content->body)
                <div class="prose-lr text-[1.0625rem]">{!! $content->bodyHtml() !!}</div>
            @endif

            @if ($content->attachment)
                <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($content->attachment) }}" target="_blank" rel="noopener"
                   class="inline-flex w-fit items-center gap-3 rounded-xl border border-line bg-card px-4 py-3 font-semibold hover:border-river-600">
                    <svg viewBox="0 0 24 24" class="size-5 text-danger" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M12 18v-6M9 15l3 3 3-3"/></svg>
                    Unduh modul (PDF)
                </a>
            @endif
        </div>
    </article>

    @if ($related->isNotEmpty())
        <section class="wrap grid gap-4">
            <h2 class="text-xl font-extrabold">Konten terkait</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($related as $item)
                    <x-education-card :item="$item" />
                @endforeach
            </div>
        </section>
    @endif
@endsection
