<?php

namespace App\Filament\Forms\Components;

use App\Support\VillageBoundary;
use Closure;
use Filament\Forms\Components\Field;

/**
 * Field peta untuk menggambar ulang atau merapikan batas wilayah desa.
 * Nilai disimpan sebagai satu cincin titik [[lat, lng], ...] (minimal 3 titik).
 */
class BoundaryPicker extends Field
{
    protected string $view = 'filament.forms.components.boundary-picker';

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([]);

        $this->rule(static function (): Closure {
            return static function (string $attribute, mixed $value, Closure $fail): void {
                if ($value === null || $value === []) {
                    return;
                }

                if (VillageBoundary::normalizeRing($value) === null) {
                    $fail('Batas desa minimal terdiri dari tiga titik. Gambar ulang atau kembalikan batas bawaan.');
                }
            };
        });

        $this->dehydrateStateUsing(static fn (mixed $state): array => VillageBoundary::normalizeRing($state) ?? []);
    }

    /** @return list<array{0: float, 1: float}> */
    public function getDefaultRing(): array
    {
        return VillageBoundary::defaultRing();
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
