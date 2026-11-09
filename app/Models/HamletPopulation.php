<?php

namespace App\Models;

use App\Models\Concerns\HasLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/** Jumlah penduduk dan warga rentan per dusun (angka agregat, tanpa nama). */
class HamletPopulation extends Model
{
    use HasLocation;

    protected $fillable = [
        'name', 'population', 'households', 'male', 'female',
        'elderly', 'toddlers', 'pregnant', 'disabled',
        'location', 'note', 'sort_order',
    ];

    public const VULNERABLE = [
        'elderly' => 'Lansia',
        'toddlers' => 'Balita',
        'pregnant' => 'Ibu hamil',
        'disabled' => 'Disabilitas',
    ];

    protected function casts(): array
    {
        return collect(['population', 'households', 'male', 'female', 'elderly', 'toddlers', 'pregnant', 'disabled', 'sort_order'])
            ->mapWithKeys(fn ($c) => [$c => 'integer'])->all();
    }

    public function vulnerableTotal(): int
    {
        return (int) collect(array_keys(self::VULNERABLE))->sum(fn ($c) => (int) $this->{$c});
    }

    /**
     * Jumlah seluruh dusun. Kolom yang kosong di semua dusun tetap null
     * supaya web tidak menampilkan angka 0 yang menyesatkan.
     *
     * @param  Collection<int, self>  $rows
     * @return array<string, int|null>
     */
    public static function totals(Collection $rows): array
    {
        $columns = ['population', 'households', 'male', 'female', ...array_keys(self::VULNERABLE)];

        return collect($columns)->mapWithKeys(function ($c) use ($rows) {
            $filled = $rows->filter(fn ($r) => $r->{$c} !== null);

            return [$c => $filled->isEmpty() ? null : (int) $filled->sum($c)];
        })->all();
    }
}
