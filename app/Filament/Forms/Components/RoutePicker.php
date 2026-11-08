<?php

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;

/**
 * Field peta untuk menggambar jalur evakuasi (garis dari titik ke titik).
 *
 * Nilai disimpan sebagai [[lat, lng], [lat, lng], ...].
 * Titik kumpul dan lokasi rawan bisa ditampilkan sebagai acuan
 * lewat referencePoints().
 */
class RoutePicker extends Field
{
    protected string $view = 'filament.forms.components.route-picker';

    /** @var array<int, array{lat: float, lng: float, label: string, kind: string}>|Closure */
    protected array|Closure $referencePoints = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([]);

        $this->rule(static function (): Closure {
            return static function (string $attribute, mixed $value, Closure $fail): void {
                if (! is_array($value) || count($value) < 2) {
                    $fail('Gambar jalur minimal dari dua titik.');

                    return;
                }

                foreach ($value as $point) {
                    if (! is_array($point) || ! is_numeric($point[0] ?? null) || ! is_numeric($point[1] ?? null)) {
                        $fail('Ada titik jalur yang tidak valid. Hapus jalur lalu gambar ulang.');

                        return;
                    }
                }
            };
        });

        $this->dehydrateStateUsing(static function (mixed $state): array {
            if (! is_array($state)) {
                return [];
            }

            return array_values(array_map(
                static fn (array $point): array => [round((float) $point[0], 6), round((float) $point[1], 6)],
                array_filter($state, static fn (mixed $point): bool => is_array($point) && count($point) >= 2),
            ));
        });
    }

    public function referencePoints(array|Closure $points): static
    {
        $this->referencePoints = $points;

        return $this;
    }

    /** @return array<int, array{lat: float, lng: float, label: string, kind: string}> */
    public function getReferencePoints(): array
    {
        return array_values($this->evaluate($this->referencePoints) ?? []);
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
