<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Identitas
    |--------------------------------------------------------------------------
    |
    | Nilai bawaan. Nama desa dan teks profil bisa diubah lewat menu
    | "Profil Desa" di panel admin tanpa menyentuh kode.
    |
    */

    'village' => 'Lonjoboko',
    'district' => 'Parangloe',
    'regency' => 'Gowa',
    'province' => 'Sulawesi Selatan',

    /*
    |--------------------------------------------------------------------------
    | Akun admin pertama (dipakai oleh AdminUserSeeder)
    |--------------------------------------------------------------------------
    */

    'admin' => [
        'name' => env('ADMIN_NAME', 'Admin Desa Lonjoboko'),
        'email' => env('ADMIN_EMAIL', 'admin@lonjoboko.test'),
        'password' => env('ADMIN_PASSWORD', 'ganti-password-ini'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Peta
    |--------------------------------------------------------------------------
    |
    | Titik tengah peta adalah PERKIRAAN. Sesuaikan lewat .env dengan
    | koordinat kantor desa yang sebenarnya.
    |
    */

    'map' => [
        'center' => [
            'lat' => (float) env('MAP_CENTER_LAT', -5.2641),
            'lng' => (float) env('MAP_CENTER_LNG', 119.7366),
        ],
        'zoom' => (int) env('MAP_ZOOM', 13),
        'tile_url' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        'attribution' => '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    ],

    /*
    |--------------------------------------------------------------------------
    | Sumber peringatan dini resmi
    |--------------------------------------------------------------------------
    |
    | Ditampilkan di menu Informasi Darurat. Web ini bukan pengganti
    | sistem peringatan dini resmi.
    |
    */

    'official_sources' => [
        [
            'name' => 'BMKG',
            'description' => 'Peringatan dini cuaca (hujan lebat, petir, angin kencang) dan info gempa.',
            'url' => 'https://www.bmkg.go.id',
        ],
        [
            'name' => 'InaRISK BNPB',
            'description' => 'Peta potensi dan risiko bencana per wilayah.',
            'url' => 'https://inarisk.bnpb.go.id',
        ],
        [
            'name' => 'BNPB',
            'description' => 'Informasi kebencanaan nasional.',
            'url' => 'https://bnpb.go.id',
        ],
    ],

];
