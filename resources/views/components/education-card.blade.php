@props(['item'])

<a href="{{ route('education.show', $item) }}" class="elevation-card group flex flex-col rounded-3xl overflow-hidden">
    <div class="relative overflow-hidden aspect-[16/9] w-full bg-river-100">
        @if ($item->image)
            {{-- Infografis biasanya tegak: tampilkan bagian atasnya (judul) --}}
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->image) }}" alt="" loading="lazy" @class([
                'w-full h-full object-cover group-hover:scale-105 transition-transform duration-500',
                'object-top' => $item->category === \App\Enums\EducationCategory::Infografis,
            ])>
        @elseif ($item->youtubeId())
            <img src="https://i.ytimg.com/vi/{{ $item->youtubeId() }}/hqdefault.jpg" alt="" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            <span class="absolute inset-0 grid place-items-center bg-black/20 group-hover:bg-black/10 transition-colors">
                <span class="grid place-items-center size-10 rounded-full bg-white/90 text-river-900 shadow-md">
                    <svg viewBox="0 0 24 24" class="size-5 fill-current ml-0.5"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                </span>
            </span>
        @else
            <div @class([
                'w-full h-full grid place-items-center text-5xl font-extrabold',
                'bg-flood-soft text-flood/40' => $item->topic->value === 'banjir',
                'bg-slide-soft text-slide/40' => $item->topic->value === 'longsor',
                'bg-river-50 text-river-600/30' => $item->topic->value === 'umum',
            ]) aria-hidden="true">{{ mb_strtoupper(mb_substr($item->category->getLabel(), 0, 1)) }}</div>
        @endif
        
        <div class="absolute top-3 left-3">
            <span class="px-2.5 py-1 rounded-full bg-white/90 backdrop-blur-xs text-[0.7rem] font-bold uppercase tracking-wider text-river-800 shadow-xs border border-white/60">
                {{ $item->category->getLabel() }}
            </span>
        </div>
    </div>
    
    <div class="flex flex-1 flex-col gap-2 p-5 bg-card">
        <div class="flex flex-wrap items-center gap-2 text-sm font-semibold text-ink-soft">
            <span class="text-river-600 font-bold uppercase tracking-wider text-[0.7rem]">{{ $item->topic->getLabel() }}</span>
        </div>
        <h3 class="font-bold text-lg leading-snug text-ink group-hover:text-river-700 transition-colors">{{ $item->title }}</h3>
        @if ($item->summary)
            <p class="text-sm text-ink-soft leading-relaxed line-clamp-2 mt-auto pt-1">{{ $item->summary }}</p>
        @endif
    </div>
</a>
