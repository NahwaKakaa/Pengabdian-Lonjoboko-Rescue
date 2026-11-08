@extends('layouts.public')

@section('title', 'Edukasi')

@section('content')
    <x-page-header
        eyebrow="Edukasi kebencanaan"
        title="Belajar bersiap menghadapi banjir dan longsor"
        intro="Modul, video, infografis, dan panduan yang bisa dibaca bersama keluarga."
    >
        <nav aria-label="Saring jenis konten" class="flex flex-wrap gap-2 pt-2">
            <a href="{{ route('education') }}"
               @class(['rounded-full border px-3 py-1.5 text-sm font-semibold', 'bg-river-800 border-river-800 text-white' => ! $activeCategory, 'border-line hover:bg-river-50' => $activeCategory])
               @if (! $activeCategory) aria-current="page" @endif>Semua</a>
            @foreach (\App\Enums\EducationCategory::cases() as $category)
                @php $active = $activeCategory === $category; @endphp
                <a href="{{ route('education', ['kategori' => $category->value]) }}"
                   @class(['rounded-full border px-3 py-1.5 text-sm font-semibold', 'bg-river-800 border-river-800 text-white' => $active, 'border-line hover:bg-river-50' => ! $active])
                   @if ($active) aria-current="page" @endif>{{ $category->getLabel() }}</a>
            @endforeach
        </nav>
    </x-page-header>

    <section class="wrap py-8 grid gap-6">
        @if ($items->isNotEmpty())
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($items as $item)
                    <x-education-card :item="$item" />
                @endforeach
            </div>
            {{ $items->links() }}
        @else
            <p class="rounded-2xl border border-dashed border-line bg-card p-6 text-ink-soft">
                Belum ada konten {{ $activeCategory ? strtolower($activeCategory->getLabel()) : 'edukasi' }}.
            </p>
        @endif
    </section>
@endsection
