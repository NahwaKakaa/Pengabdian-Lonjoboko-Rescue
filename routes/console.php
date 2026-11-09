<?php

use App\Models\AssemblyPoint;
use App\Models\EvacuationRoute;
use App\Models\Facility;
use App\Models\HazardZone;
use App\Support\VillageContent;
use App\Support\VillageData;
use Database\Seeders\DemoMapSeeder;
use Database\Seeders\DemoVillageRecordsSeeder;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('lonjoboko:hapus-contoh', function () {
    $prefix = DemoMapSeeder::PREFIX;
    $models = [
        EvacuationRoute::class,
        HazardZone::class,
        Facility::class,
        AssemblyPoint::class,
    ];

    $total = 0;
    foreach ($models as $model) {
        $total += $model::where('name', 'like', $prefix.'%')->delete();
    }

    $this->info("{$total} data peta contoh dihapus.");
})->purpose('Hapus data peta contoh ([CONTOH]) sebelum web dipakai warga');

Artisan::command('lonjoboko:isi-data-desa {--ulang : Hapus data desa yang pernah diisi lalu isi ulang}', function () {
    if (VillageData::isInstalled()) {
        if (! $this->option('ulang')) {
            $this->warn('Data desa sudah pernah diisi. Pakai --ulang untuk mengisi ulang.');

            return 1;
        }

        $this->info(VillageData::remove().' data lama dihapus.');
    }

    $counts = VillageData::install();

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
    $this->info(VillageData::remove().' data desa dihapus.');
    $this->info(VillageContent::removeEducation().' konten edukasi dihapus.');
    $this->info(VillageContent::removeEvents().' riwayat kejadian awal dihapus.');
    $this->line('Isian Profil Desa tidak dihapus; ubah lewat admin bila perlu.');
})->purpose('Hapus data yang diisi oleh lonjoboko:isi-data-desa dan lonjoboko:lengkapi-data');

Artisan::command('lonjoboko:lengkapi-data', function () {
    $keys = VillageContent::completeProfile();
    $this->info(count($keys).' isian Profil Desa dilengkapi'.($keys ? ': '.implode(', ', $keys) : ' (semua sudah terisi).'));

    if (in_array('whatsapp_number', $keys, true)) {
        $this->warn('Nomor WhatsApp desa diisi nomor DUMMY '.VillageContent::DUMMY_WHATSAPP.'. Ganti dengan nomor posko yang aktif lewat admin > Profil Desa sebelum web disebarkan ke warga.');
    }

    $this->info(VillageContent::installEducation().' konten edukasi dari perintah ini ada di web.');
    $this->info(VillageContent::installEvents().' riwayat kejadian awal ditambahkan.');

    // Gambar infografis disimpan di storage/app/public; web membacanya lewat public/storage.
    if (! file_exists(public_path('storage'))) {
        $this->call('storage:link');
    }

    if (! file_exists(public_path('storage'))) {
        $this->warn('Folder public/storage belum terhubung, gambar infografis belum tampil. Jalankan: php artisan storage:link');
    }
})->purpose('Lengkapi isian Profil Desa yang kosong dan tambahkan konten edukasi');

Artisan::command('lonjoboko:isi-data-sementara', function () {
    $this->call('db:seed', ['--class' => DemoVillageRecordsSeeder::class, '--force' => true]);
    $this->warn('Ini data SEMENTARA (penduduk, 1 kejadian, laporan warga, kegiatan, jam faskes). Ganti dengan data resmi secepatnya.');
    $this->line('Untuk menghapus: php artisan lonjoboko:hapus-data-sementara');
})->purpose('Isi data sementara untuk fitur penduduk, riwayat, laporan warga, kegiatan, dan jam faskes');

Artisan::command('lonjoboko:hapus-data-sementara', function () {
    $this->info(DemoVillageRecordsSeeder::remove().' data sementara dihapus.');
    $this->line('Data yang diisi sendiri lewat admin tidak ikut terhapus.');
})->purpose('Hapus data yang diisi oleh lonjoboko:isi-data-sementara');
