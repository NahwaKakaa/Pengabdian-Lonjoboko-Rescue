<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        // Hanya mengisi yang belum ada, supaya tidak menimpa isian admin.
        $defaults = [
            'village_name' => config('lonjoboko.village'),
            'district' => config('lonjoboko.district'),
            'regency' => config('lonjoboko.regency'),
            'program_description' => 'LONJOBOKO-RESCUE adalah inovasi teknologi informasi berbasis web yang menjadi media informasi, edukasi, dan komunikasi kebencanaan bagi masyarakat Desa Lonjoboko. Program ini diawali penyuluhan penguatan kapasitas masyarakat dalam mitigasi banjir dan tanah longsor, dilanjutkan dengan penyiapan sistem informasi ini, dan pembentukan Kader Karang Taruna Tangguh Bencana.',
        ];

        $existing = Setting::pluck('key')->all();

        Setting::putMany(array_diff_key($defaults, array_flip($existing)));
    }
}
