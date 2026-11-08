@props(['type'])

@php
    $value = $type instanceof \BackedEnum ? $type->value : $type;
    $label = $type instanceof \Filament\Support\Contracts\HasLabel ? $type->getLabel() : ucfirst((string) $value);
    $classes = match ($value) {
        'banjir' => 'bg-flood-soft text-flood',
        'longsor' => 'bg-slide-soft text-slide',
        'titik_kumpul', 'pengungsian' => 'bg-safe-soft text-safe',
        'tinggi' => 'bg-danger-soft text-danger',
        'sedang' => 'bg-alert-soft text-alert',
        'rendah' => 'bg-safe-soft text-safe',
        'waspada' => 'bg-alert-soft text-alert',
        'darurat' => 'bg-danger-soft text-danger',
        default => 'bg-river-50 text-river-700',
    };
@endphp

<span {{ $attributes->class(['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold', $classes]) }}>{{ $slot->isEmpty() ? $label : $slot }}</span>
