<?php

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;

/**
 * Field peta untuk memilih satu titik lokasi.
 *
 * Admin/kader bisa mengetuk peta, menggeser penanda, mengetik koordinat,
 * atau memakai GPS perangkat ("Gunakan lokasi saya").
 * Nilai disimpan sebagai ['lat' => float, 'lng' => float].
 */
class LocationPicker extends Field
{
    protected string $view = 'filament.forms.components.location-picker';

    protected int|Closure|null $mapHeight = 320;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rule(static function (): Closure {
            return static function (string $attribute, mixed $value, Closure $fail): void {
                if ($value === null || $value === []) {
                    return;
                }

                $lat = $value['lat'] ?? null;
                $lng = $value['lng'] ?? null;

                if (! is_numeric($lat) || ! is_numeric($lng)
                    || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                    $fail('Lokasi belum valid. Ketuk peta atau tekan "Gunakan lokasi saya".');
                }
            };
        });

        $this->dehydrateStateUsing(static function (mixed $state): ?array {
            if (! is_array($state) || ! isset($state['lat'], $state['lng'])) {
                return null;
            }

            return [
                'lat' => round((float) $state['lat'], 6),
                'lng' => round((float) $state['lng'], 6),
            ];
        });
    }

    public function mapHeight(int|Closure|null $height): static
    {
        $this->mapHeight = $height;

        return $this;
    }

    public function getMapHeight(): int
    {
        return (int) ($this->evaluate($this->mapHeight) ?? 320);
    }

    /** @return array{lat: float, lng: float} */
    public function getCenter(): array
    {
        return config('lonjoboko.map.center');
    }

    public function getZoom(): int
    {
        return config('lonjoboko.map.zoom');
    }
}
