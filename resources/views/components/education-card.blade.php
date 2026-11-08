@props(['item'])

<a href="{{ route('education.show', $item) }}" class="group flex flex-col rounded-2xl border border-line bg-card overflow-hidden hover:border-river-600 transition-colors">
    @if ($item->image)
        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->image) }}" alt="" loading="lazy" class="aspect-[16/9] w-full object-cover">
    @elseif ($item->youtubeId())
        <img src="https://i.ytimg.com/vi/{{ $item->youtubeId() }}/hqdefault.jpg" alt="" loading="lazy" class="aspect-[16/9] w-full object-cover">
    @else
        <div @class([
            'aspect-[16/9] w-full grid place-items-center text-5xl font-extrabold',
            'bg-flood-soft text-flood/40' => $item->topic->value === 'banjir',
            'bg-slide-soft text-slide/40' => $item->topic->value === 'longsor',
            'bg-river-50 text-river-600/30' => $item->topic->value === 'umum',
        ]) aria-hidden="true">{{ mb_strtoupper(mb_substr($item->category->getLabel(), 0, 1)) }}</div>
    @endif
    <div class="flex flex-1 flex-col gap-2 p-4">
        <div class="flex flex-wrap items-center gap-2 text-xs font-bold uppercase tracking-wider text-ink-soft">
            <span>{{ $item->category->getLabel() }}</span>
            <span aria-hidden="true">·</span>
            <span>{{ $item->topic->getLabel() }}</span>
        </div>
        <h3 class="font-bold text-lg leading-snug group-hover:text-river-700">{{ $item->title }}</h3>
        @if ($item->summary)
            <p class="text-sm text-ink-soft leading-relaxed">{{ $item->summary }}</p>
        @endif
    </div>
</a>
