@php
    $statePath = $getStatePath();
    $isDisabled = $isDisabled();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        wire:ignore
        x-data="lrLocationPicker({
            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }},
            center: @js($getCenter()),
            zoom: @js($getZoom()),
            tileUrl: @js(config('lonjoboko.map.tile_url')),
            attribution: @js(config('lonjoboko.map.attribution')),
            boundary: @js(\App\Support\VillageBoundary::geojson()),
            disabled: @js($isDisabled),
        })"
        class="lr-field"
    >
        <div
            x-ref="map"
            class="lr-field__map"
            style="height: {{ $getMapHeight() }}px"
            role="application"
            aria-label="Peta untuk memilih lokasi"
        ></div>

        @unless ($isDisabled)
            <div class="lr-field__toolbar">
                <x-filament::button
                    type="button"
                    size="sm"
                    icon="heroicon-m-map-pin"
                    x-on:click="useMyLocation()"
                    x-bind:disabled="locating"
                >
                    <span x-text="locating ? 'Mencari lokasi…' : 'Gunakan lokasi saya'"></span>
                </x-filament::button>

                <x-filament::button
                    type="button"
                    size="sm"
                    color="gray"
                    icon="heroicon-m-x-mark"
                    x-on:click="clearPoint()"
                    x-show="hasPoint()"
                >
                    Hapus titik
                </x-filament::button>
            </div>
        @endunless

        <p class="lr-field__hint" x-text="message"></p>

        <div class="lr-field__coords">
            <label class="lr-field__coord">
                <span>Lintang (latitude)</span>
                <x-filament::input.wrapper :disabled="$isDisabled">
                    <x-filament::input
                        type="number"
                        step="any"
                        inputmode="decimal"
                        placeholder="-5.28xxxx"
                        x-model="lat"
                        x-on:change="setFromInputs()"
                        :disabled="$isDisabled"
                    />
                </x-filament::input.wrapper>
            </label>
            <label class="lr-field__coord">
                <span>Bujur (longitude)</span>
                <x-filament::input.wrapper :disabled="$isDisabled">
                    <x-filament::input
                        type="number"
                        step="any"
                        inputmode="decimal"
                        placeholder="119.6xxxxx"
                        x-model="lng"
                        x-on:change="setFromInputs()"
                        :disabled="$isDisabled"
                    />
                </x-filament::input.wrapper>
            </label>
        </div>
    </div>
</x-dynamic-component>
