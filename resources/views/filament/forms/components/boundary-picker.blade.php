@php
    $statePath = $getStatePath();
    $isDisabled = $isDisabled();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        wire:ignore
        x-data="lrBoundaryPicker({
            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }},
            defaultRing: @js($getDefaultRing()),
            center: @js($getCenter()),
            zoom: @js($getZoom()),
            tileUrl: @js(config('lonjoboko.map.tile_url')),
            attribution: @js(config('lonjoboko.map.attribution')),
            disabled: @js($isDisabled),
        })"
        class="lr-field"
    >
        <div
            x-ref="map"
            class="lr-field__map"
            style="height: 460px"
            role="application"
            aria-label="Peta untuk menggambar batas wilayah desa"
        ></div>

        @unless ($isDisabled)
            <div class="lr-field__toolbar">
                <x-filament::button type="button" size="sm" icon="heroicon-m-arrows-pointing-out" x-on:click="toggleEdit()" x-show="hasShape() && ! drawing">
                    <span x-text="editing ? 'Selesai merapikan' : 'Geser titik batas'"></span>
                </x-filament::button>

                <x-filament::button type="button" size="sm" color="gray" icon="heroicon-m-pencil" x-on:click="startDraw()" x-show="! drawing">
                    Gambar ulang dari awal
                </x-filament::button>

                <x-filament::button type="button" size="sm" color="success" icon="heroicon-m-check" x-on:click="finishDraw()" x-show="drawing">
                    Selesai menggambar
                </x-filament::button>

                <x-filament::button type="button" size="sm" color="gray" outlined icon="heroicon-m-arrow-uturn-left" x-on:click="resetDefault()" x-show="! drawing">
                    Kembalikan batas bawaan
                </x-filament::button>
            </div>
        @endunless

        <p class="lr-field__hint" x-text="message"></p>
        <p class="lr-field__meta" x-show="hasShape()" x-text="summary"></p>
    </div>
</x-dynamic-component>
