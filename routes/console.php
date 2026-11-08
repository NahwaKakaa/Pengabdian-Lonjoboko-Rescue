<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('lonjoboko:hapus-contoh', function () {
    $prefix = \Database\Seeders\DemoMapSeeder::PREFIX;
    $models = [
        \App\Models\EvacuationRoute::class,
        \App\Models\HazardZone::class,
        \App\Models\Facility::class,
        \App\Models\AssemblyPoint::class,
    ];

    $total = 0;
    foreach ($models as $model) {
        $total += $model::where('name', 'like', $prefix.'%')->delete();
    }

    $this->info("{$total} data peta contoh dihapus.");
})->purpose('Hapus data peta contoh ([CONTOH]) sebelum web dipakai warga');
