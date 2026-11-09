<?php

namespace Database\Seeders;

use App\Enums\FacilityCategory;
use App\Enums\HazardType;
use App\Enums\ReportStatus;
use App\Enums\VerificationStatus;
use App\Models\Activity;
use App\Models\DisasterEvent;
use App\Models\Facility;
use App\Models\HamletPopulation;
use App\Models\IncidentReport;
use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Data DUMMY untuk melihat tampilan fitur data penduduk, riwayat kejadian,
 * laporan warga, kegiatan Karang Taruna, dan jam layanan faskes.
 * Hanya untuk laptop / presentasi. JANGAN dijalankan di server online.
 *
 *   php artisan db:seed --class=DemoVillageRecordsSeeder
 */
class DemoVillageRecordsSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->error('Seeder dummy ini tidak boleh dijalankan di production.');

            return;
        }

        $hamlets = [
            ['Bontoloe', 812, 231, 402, 410, 73, 66, 9, 5, -5.2702, 119.7068],
            ['Galesong', 645, 184, 318, 327, 58, 51, 7, 3, -5.2655, 119.7224],
            ['Tombongi', 754, 210, 371, 383, 67, 60, 8, 4, -5.2651, 119.7082],
            ['Lantaboko', 598, 169, 296, 302, 49, 47, 6, 2, -5.2738, 119.7552],
        ];
        foreach ($hamlets as $i => [$name, $pop, $kk, $m, $f, $old, $kid, $preg, $dis, $lat, $lng]) {
            HamletPopulation::updateOrCreate(['name' => $name], [
                'population' => $pop, 'households' => $kk, 'male' => $m, 'female' => $f,
                'elderly' => $old, 'toddlers' => $kid, 'pregnant' => $preg, 'disabled' => $dis,
                'location' => ['lat' => $lat, 'lng' => $lng], 'sort_order' => $i + 1,
            ]);
        }
        Setting::putMany(['population_source' => 'Data dummy untuk contoh tampilan']);

        $event = DisasterEvent::updateOrCreate(['title' => 'Banjir luapan sungai di Dusun Tombongi'], [
            'hazard_type' => HazardType::Banjir,
            'occurred_on' => '2025-12-28',
            'hamlet' => 'Tombongi',
            'location' => ['lat' => -5.2651, 'lng' => 119.7082],
            'description' => 'Hujan deras sejak sore membuat sungai meluap ke permukiman di tepi sungai. Air setinggi lutut orang dewasa masuk ke rumah warga selama sekitar 6 jam.',
            'impact' => 'Jalan dusun tergenang, satu jembatan kayu rusak.',
            'deaths' => 0, 'injured' => 2, 'missing' => 0, 'displaced' => 37, 'houses_damaged' => 12,
            'source' => 'Laporan kader Karang Taruna',
            'status' => VerificationStatus::Terverifikasi,
        ]);

        $reports = [
            ['tanda_bahaya', 'Ada retakan tanah baru di kebun belakang rumah, panjangnya sekitar 5 meter, setelah hujan semalam.', 'Bontoloe', 'Dekat Masjid Ar Rahman', 'Daeng Rani', '0812 0000 1111', ReportStatus::Baru, null, null],
            ['banjir', 'Air sungai naik cepat dan warnanya keruh, sudah hampir masuk ke halaman rumah warga di pinggir sungai.', 'Tombongi', 'Jembatan kayu dusun', null, null, ReportStatus::Dicek, 'Benar, air naik sekitar 50 cm dari biasanya. Warga di pinggir sungai sudah diminta bersiap.', null],
            ['longsor', 'Tanah dari tebing jatuh ke jalan poros, sebagian jalan tertutup. Motor masih bisa lewat pelan-pelan.', 'Galesong', 'Jalan Poros Malino Km 58', 'Ahmad', '0813 0000 2222', ReportStatus::Sah, 'Timbunan sekitar 1 meter, menutup setengah badan jalan.', 'Sudah dilaporkan ke BPBD Gowa, alat berat dijadwalkan besok pagi.'],
            ['lainnya', 'Pohon besar miring ke arah rumah warga setelah angin kencang.', 'Lantaboko', null, null, null, ReportStatus::TidakSesuai, 'Sudah dicek, pohon tidak miring, hanya dahannya patah.', 'Dahan sudah dipotong warga, tidak perlu tindak lanjut.'],
        ];
        foreach ($reports as [$kind, $desc, $hamlet, $landmark, $name, $phone, $status, $check, $admin]) {
            IncidentReport::firstOrCreate(['description' => $desc], [
                'kind' => $kind, 'hamlet' => $hamlet, 'landmark' => $landmark,
                'reporter_name' => $name, 'reporter_phone' => $phone,
                'status' => $status, 'check_note' => $check, 'admin_note' => $admin,
            ]);
        }
        IncidentReport::where('description', 'like', 'Air sungai naik cepat%')->update(['disaster_event_id' => $event->id]);

        $activities = [
            ['Simulasi evakuasi longsor bersama warga', '2026-10-18', 'Dusun Bontoloe', 'Karang Taruna dan warga berlatih berjalan dari rumah ke titik kumpul mengikuti jalur evakuasi. Lansia dan balita didahulukan.'],
            ['Kerja bakti membersihkan saluran air', '2026-10-04', 'Dusun Tombongi', 'Membersihkan sampah dan lumpur di saluran air menjelang musim hujan supaya air tidak meluap ke rumah warga.'],
            ['Sosialisasi web LONJOBOKO RESCUE', '2026-09-27', 'Kantor Desa Lonjoboko', 'Kader mengajari warga membuka web, melihat peta rawan, dan mengirim laporan lewat tombol Lapor.'],
            ['Pendataan warga rentan per dusun', '2026-09-13', 'Semua dusun', 'Kader mendata lansia, balita, ibu hamil, dan penyandang disabilitas supaya diprioritaskan saat evakuasi.'],
        ];
        foreach ($activities as [$title, $date, $place, $desc]) {
            Activity::firstOrCreate(['title' => $title], [
                'held_on' => $date, 'place' => $place, 'description' => $desc, 'is_published' => true,
            ]);
        }

        Facility::where('category', FacilityCategory::Kesehatan->value)->whereNull('opening_hours')
            ->each(fn (Facility $f) => $f->update([
                'opening_hours' => str_contains($f->name, 'Puskesmas')
                    ? 'Senin–Kamis 08.00–14.00, Jumat 08.00–11.00, Sabtu 08.00–13.00. UGD 24 jam'
                    : 'Senin–Sabtu 08.00–14.00',
                'phone' => $f->phone ?: '0812 0000 3333',
            ]));

        $this->command?->info('Data dummy ditambahkan: penduduk 4 dusun, 1 kejadian, 4 laporan warga, 4 kegiatan, jam layanan faskes.');
    }
}
