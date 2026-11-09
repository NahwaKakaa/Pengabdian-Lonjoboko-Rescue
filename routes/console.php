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

Artisan::command('lonjoboko:isi-data-desa {--ulang : Hapus data desa yang pernah diisi lalu isi ulang}', function () {
    if (\App\Support\VillageData::isInstalled()) {
        if (! $this->option('ulang')) {
            $this->warn('Data desa sudah pernah diisi. Pakai --ulang untuk mengisi ulang.');

            return 1;
        }

        $this->info(\App\Support\VillageData::remove().' data lama dihapus.');
    }

    $counts = \App\Support\VillageData::install();

    $this->info('Data desa ditambahkan:');
    $this->table(['Jenis', 'Jumlah'], collect([
        'points' => 'Titik kumpul',
        'hazards' => 'Lokasi rawan',
        'routes' => 'Jalur evakuasi',
        'facilities' => 'Fasilitas',
        'announcements' => 'Pengumuman',
        'volunteers' => 'Relawan',
    ])->map(fn ($label, $key) => [$label, $counts[$key] ?? 0])->values()->all());
    $this->line('Kontak resmi BPBD, Polres, RSUD, dan Damkar Gowa juga ditambahkan (tidak ikut terhapus).');
    $this->line('Untuk menghapus: php artisan lonjoboko:hapus-data-desa');
})->purpose('Isi peta, pengumuman, dan relawan Desa Lonjoboko berdasarkan tempat nyata');

Artisan::command('lonjoboko:hapus-data-desa', function () {
    $this->info(\App\Support\VillageData::remove().' data desa dihapus.');
})->purpose('Hapus data yang diisi oleh lonjoboko:isi-data-desa');
