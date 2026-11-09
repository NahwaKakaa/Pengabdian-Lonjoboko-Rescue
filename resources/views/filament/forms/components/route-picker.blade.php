@php
    $statePath = $getStatePath();
    $isDisabled = $isDisabled();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        wire:ignore
        x-data="lrRoutePicker({
            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }},
            center: @js($getCenter()),
            zoom: @js($getZoom()),
            tileUrl: @js(config('lonjoboko.map.tile_url')),
            attribution: @js(config('lonjoboko.map.attribution')),
            boundary: @js(\App\Support\VillageBoundary::geojson()),
            references: @js($getReferencePoints()),
            disabled: @js($isDisabled),
        })"
        class="lr-field"
    >
        <div
            x-ref="map"
            class="lr-field__map"
            style="height: 420px"
            role="application"
            aria-label="Peta untuk menggambar jalur evakuasi"
        ></div>

        @unless ($isDisabled)
            <div class="lr-field__toolbar">
                <x-filament::button
                    type="button"
                    size="sm"
                    icon="heroicon-m-pencil"
                    x-on:click="startDraw()"
                    x-show="! drawing"
                >
                    <span x-text="hasRoute() ? 'Gambar ulang jalur' : 'Gambar jalur'"></span>
                </x-filament::button>

                <x-filament::button
                    type="button"
                    size="sm"
                    color="success"
                    icon="heroicon-m-check"
                    x-on:click="finishDraw()"
                    x-show="drawing"
                >
                    Selesai menggambar
                </x-filament::button>

                <x-filament::button
                    type="button"
                    size="sm"
                    color="gray"
                    icon="heroicon-m-arrows-pointing-out"
                    x-on:click="toggleEdit()"
                    x-show="hasRoute() && ! drawing"
                >
                    <span x-text="editing ? 'Selesai mengubah' : 'Geser titik jalur'"></span>
                </x-filament::button>

                <x-filament::button
                    type="button"
                    size="sm"
                    color="danger"
                    outlined
                    icon="heroicon-m-trash"
                    x-on:click="clearRoute()"
                    x-show="hasRoute() && ! drawing"
                >
                    Hapus jalur
                </x-filament::button>
            </div>
        @endunless

        <p class="lr-field__hint" x-text="message"></p>
        <p class="lr-field__meta" x-show="hasRoute()" x-text="summary"></p>

        <ul class="lr-field__legend" x-show="references.length">
            <li><span class="lr-dot lr-dot--safe"></span> Titik kumpul / pengungsian</li>
            <li><span class="lr-dot lr-dot--hazard"></span> Lokasi rawan</li>
        </ul>
    </div>
</x-dynamic-component>
