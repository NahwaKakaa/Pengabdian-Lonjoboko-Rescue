<?php

namespace Database\Seeders;

use App\Enums\AssemblyPointType;
use App\Enums\FacilityCategory;
use App\Enums\HazardType;
use App\Enums\RiskLevel;
use App\Enums\RouteHazard;
use App\Enums\VerificationStatus;
use App\Models\AssemblyPoint;
use App\Models\EvacuationRoute;
use App\Models\Facility;
use App\Models\HazardZone;
use Illuminate\Database\Seeder;

/**
 * DATA CONTOH untuk latihan kader dan presentasi.
 *
 * Semua nama diawali "[CONTOH]" dan letaknya hanya di sekitar titik tengah
 * peta, BUKAN lokasi sebenarnya. Hapus sebelum web dipakai warga:
 *   php artisan lonjoboko:hapus-contoh
 */
class DemoMapSeeder extends Seeder
{
    public const PREFIX = '[CONTOH] ';

    public function run(): void
    {
        $c = config('lonjoboko.map.center');
        $at = fn (float $dLat, float $dLng) => ['lat' => round($c['lat'] + $dLat, 6), 'lng' => round($c['lng'] + $dLng, 6)];
        $verified = VerificationStatus::Terverifikasi;

        $field = AssemblyPoint::create([
            'name' => self::PREFIX.'Lapangan Desa',
            'type' => AssemblyPointType::TitikKumpul,
            'capacity' => 300,
            'address' => 'Contoh: depan kantor desa',
            'facilities' => 'Contoh: toilet umum, air bersih',
            'location' => $at(0.0040, 0.0010),
            'status' => $verified,
        ]);

        $school = AssemblyPoint::create([
            'name' => self::PREFIX.'Gedung Sekolah',
            'type' => AssemblyPointType::Pengungsian,
            'capacity' => 120,
            'address' => 'Contoh: jalan poros desa',
            'facilities' => 'Contoh: ruang kelas, dapur umum',
            'location' => $at(0.0010, 0.0065),
            'status' => $verified,
        ]);

        HazardZone::create([
            'name' => self::PREFIX.'Bantaran sungai',
            'hazard_type' => HazardType::Banjir,
            'risk_level' => RiskLevel::Tinggi,
            'description' => 'Contoh: air sungai naik saat hujan deras lebih dari 3 jam.',
            'location' => $at(-0.0035, -0.0020),
            'radius_m' => 220,
            'status' => $verified,
        ]);

        HazardZone::create([
            'name' => self::PREFIX.'Tebing jalan dusun',
            'hazard_type' => HazardType::Longsor,
            'risk_level' => RiskLevel::Sedang,
            'description' => 'Contoh: retakan tanah terlihat setelah musim hujan.',
            'location' => $at(0.0055, -0.0060),
            'radius_m' => 120,
            'status' => $verified,
        ]);

        HazardZone::create([
            'name' => self::PREFIX.'Lereng kebun (menunggu verifikasi)',
            'hazard_type' => HazardType::Longsor,
            'risk_level' => RiskLevel::Rendah,
            'description' => 'Contoh data dari kader yang belum diverifikasi admin. Tidak tampil di halaman publik.',
            'location' => $at(0.0070, 0.0030),
            'radius_m' => 80,
            'status' => VerificationStatus::Menunggu,
        ]);

        EvacuationRoute::create([
            'name' => self::PREFIX.'Bantaran sungai ke Lapangan Desa',
            'hazard_type' => RouteHazard::Banjir,
            'assembly_point_id' => $field->id,
            'description' => 'Contoh: lewat jalan setapak di samping masjid, hindari jembatan saat air naik.',
            'path' => [
                [$c['lat'] - 0.0030, $c['lng'] - 0.0016],
                [$c['lat'] - 0.0012, $c['lng'] - 0.0010],
                [$c['lat'] + 0.0012, $c['lng'] - 0.0004],
                [$c['lat'] + 0.0040, $c['lng'] + 0.0010],
            ],
            'status' => $verified,
        ]);

        EvacuationRoute::create([
            'name' => self::PREFIX.'Jalan dusun ke Gedung Sekolah',
            'hazard_type' => RouteHazard::Semua,
            'assembly_point_id' => $school->id,
            'description' => 'Contoh: ikuti jalan poros, belok di pertigaan pos ronda.',
            'path' => [
                [$c['lat'] + 0.0048, $c['lng'] - 0.0050],
                [$c['lat'] + 0.0030, $c['lng'] - 0.0015],
                [$c['lat'] + 0.0018, $c['lng'] + 0.0030],
                [$c['lat'] + 0.0010, $c['lng'] + 0.0065],
            ],
            'status' => $verified,
        ]);

        Facility::create([
            'name' => self::PREFIX.'Puskesmas Pembantu',
            'category' => FacilityCategory::Kesehatan,
            'address' => 'Contoh alamat',
            'location' => $at(0.0022, 0.0040),
            'status' => $verified,
        ]);

        Facility::create([
            'name' => self::PREFIX.'Kantor Desa',
            'category' => FacilityCategory::Pemerintahan,
            'address' => 'Contoh alamat',
            'location' => $at(0.0035, 0.0018),
            'status' => $verified,
        ]);

        $this->command?->warn('Data peta CONTOH ditambahkan. Hapus sebelum online dengan: php artisan lonjoboko:hapus-contoh');
    }
}
