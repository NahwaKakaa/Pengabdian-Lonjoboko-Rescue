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
use Illuminate\Support\Facades\Storage;

/**
 * Data SEMENTARA (dummy) untuk fitur data penduduk, riwayat kejadian,
 * laporan warga, kegiatan Karang Taruna, dan jam layanan faskes.
 * Data yang sudah ada tidak ditimpa. Semua yang ditambahkan dicatat,
 * jadi bisa dihapus bersih nanti dengan: php artisan lonjoboko:hapus-data-sementara
 *
 *   php artisan lonjoboko:isi-data-sementara
 */
class DemoVillageRecordsSeeder extends Seeder
{
    public function run(): void
    {
        $added = self::recorded();
        $track = function (string $type, $model) use (&$added) {
            if ($model->wasRecentlyCreated) {
                $added[$type][] = $model->getKey();
            }
        };

        $hamlets = [
            ['Bontoloe', 812, 231, 402, 410, 73, 66, 9, 5, -5.2702, 119.7068],
            ['Galesong', 645, 184, 318, 327, 58, 51, 7, 3, -5.2655, 119.7224],
            ['Tombongi', 754, 210, 371, 383, 67, 60, 8, 4, -5.2651, 119.7082],
            ['Lantaboko', 598, 169, 296, 302, 49, 47, 6, 2, -5.2738, 119.7552],
        ];
        foreach ($hamlets as $i => [$name, $pop, $kk, $m, $f, $old, $kid, $preg, $dis, $lat, $lng]) {
            $track('hamlets', HamletPopulation::firstOrCreate(['name' => $name], [
                'population' => $pop, 'households' => $kk, 'male' => $m, 'female' => $f,
                'elderly' => $old, 'toddlers' => $kid, 'pregnant' => $preg, 'disabled' => $dis,
                'location' => ['lat' => $lat, 'lng' => $lng], 'sort_order' => $i + 1,
            ]));
        }
        if (! Setting::get('population_source')) {
            Setting::putMany(['population_source' => self::POPULATION_SOURCE]);
        }

        $event = DisasterEvent::firstOrCreate(['title' => 'Banjir luapan sungai di Dusun Tombongi'], [
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
        $track('events', $event);

        $reports = [
            ['tanda_bahaya', 'Ada retakan tanah baru di kebun belakang rumah, panjangnya sekitar 5 meter, setelah hujan semalam.', 'Bontoloe', 'Dekat Masjid Ar Rahman', 'Daeng Rani', '0812 0000 1111', ReportStatus::Baru, null, null],
            ['banjir', 'Air sungai naik cepat dan warnanya keruh, sudah hampir masuk ke halaman rumah warga di pinggir sungai.', 'Tombongi', 'Jembatan kayu dusun', null, null, ReportStatus::Dicek, 'Benar, air naik sekitar 50 cm dari biasanya. Warga di pinggir sungai sudah diminta bersiap.', null],
            ['longsor', 'Tanah dari tebing jatuh ke jalan poros, sebagian jalan tertutup. Motor masih bisa lewat pelan-pelan.', 'Galesong', 'Jalan Poros Malino Km 58', 'Ahmad', '0813 0000 2222', ReportStatus::Sah, 'Timbunan sekitar 1 meter, menutup setengah badan jalan.', 'Sudah dilaporkan ke BPBD Gowa, alat berat dijadwalkan besok pagi.'],
            ['lainnya', 'Pohon besar miring ke arah rumah warga setelah angin kencang.', 'Lantaboko', null, null, null, ReportStatus::TidakSesuai, 'Sudah dicek, pohon tidak miring, hanya dahannya patah.', 'Dahan sudah dipotong warga, tidak perlu tindak lanjut.'],
        ];
        foreach ($reports as [$kind, $desc, $hamlet, $landmark, $name, $phone, $status, $check, $admin]) {
            $track('reports', IncidentReport::firstOrCreate(['description' => $desc], [
                'kind' => $kind, 'hamlet' => $hamlet, 'landmark' => $landmark,
                'reporter_name' => $name, 'reporter_phone' => $phone,
                'status' => $status, 'check_note' => $check, 'admin_note' => $admin,
            ]));
        }
        IncidentReport::where('description', 'like', 'Air sungai naik cepat%')->update(['disaster_event_id' => $event->id]);

        $activities = [
            ['Simulasi evakuasi longsor bersama warga', '2026-10-18', 'Dusun Bontoloe', 'Karang Taruna dan warga berlatih berjalan dari rumah ke titik kumpul mengikuti jalur evakuasi. Lansia dan balita didahulukan.'],
            ['Kerja bakti membersihkan saluran air', '2026-10-04', 'Dusun Tombongi', 'Membersihkan sampah dan lumpur di saluran air menjelang musim hujan supaya air tidak meluap ke rumah warga.'],
            ['Sosialisasi web LONJOBOKO RESCUE', '2026-09-27', 'Kantor Desa Lonjoboko', 'Kader mengajari warga membuka web, melihat peta rawan, dan mengirim laporan lewat tombol Lapor.'],
            ['Pendataan warga rentan per dusun', '2026-09-13', 'Semua dusun', 'Kader mendata lansia, balita, ibu hamil, dan penyandang disabilitas supaya diprioritaskan saat evakuasi.'],
        ];
        // Foto contoh (pemandangan desa), disalin ke storage supaya tampil di web.
        $photos = collect(range(1, 8))->map(function (int $i) {
            $path = "kegiatan/contoh-{$i}.jpg";
            if (! Storage::disk('public')->exists($path)) {
                Storage::disk('public')->put($path, file_get_contents(__DIR__."/demo-foto/kegiatan-{$i}.jpg"));
            }

            return $path;
        });
        $sets = [[1, 2, 7, 8], [3, 4, 5], [5, 6, 1], [7, 8, 3, 4, 2]];

        foreach ($activities as $n => [$title, $date, $place, $desc]) {
            $activity = Activity::firstOrCreate(['title' => $title], [
                'held_on' => $date, 'place' => $place, 'description' => $desc, 'is_published' => true,
            ]);
            $track('activities', $activity);

            if ($activity->wasRecentlyCreated && ! $activity->photo) {
                $set = collect($sets[$n])->map(fn ($i) => $photos[$i - 1]);
                $activity->update(['photo' => $set->first(), 'gallery' => $set->slice(1)->values()->all()]);
            }
        }

        if (! file_exists(public_path('storage'))) {
            $this->command?->call('storage:link');
        }

        Facility::where('category', FacilityCategory::Kesehatan->value)->whereNull('opening_hours')
            ->each(function (Facility $f) use (&$added) {
                $f->update([
                    'opening_hours' => str_contains($f->name, 'Puskesmas')
                        ? 'Senin–Kamis 08.00–14.00, Jumat 08.00–11.00, Sabtu 08.00–13.00. UGD 24 jam'
                        : 'Senin–Sabtu 08.00–14.00',
                ]);
                $added['facility_hours'][] = $f->id;
            });

        Setting::putMany([self::SETTING => json_encode(array_map(fn ($ids) => array_values(array_unique($ids)), $added))]);

        $this->command?->info('Data sementara ditambahkan: penduduk 4 dusun, 1 kejadian, 4 laporan warga, 4 kegiatan dengan foto, jam layanan faskes.');
    }

    public const SETTING = 'temporary_record_ids';

    public const POPULATION_SOURCE = 'Data sementara, menunggu data resmi dari kantor desa';

    /** @return array<string, list<int>> */
    public static function recorded(): array
    {
        $data = json_decode((string) Setting::get(self::SETTING), true);

        return array_merge(
            ['hamlets' => [], 'events' => [], 'reports' => [], 'activities' => [], 'facility_hours' => []],
            is_array($data) ? $data : [],
        );
    }

    /** Hapus semua data sementara yang pernah ditambahkan seeder ini. */
    public static function remove(): int
    {
        $ids = self::recorded();
        $total = HamletPopulation::whereKey($ids['hamlets'])->delete()
            + IncidentReport::whereKey($ids['reports'])->delete()
            + DisasterEvent::whereKey($ids['events'])->delete();

        $total += Activity::whereKey($ids['activities'])->delete();
        Storage::disk('public')->delete(collect(range(1, 8))->map(fn ($i) => "kegiatan/contoh-{$i}.jpg")->all());

        Facility::whereKey($ids['facility_hours'])->update(['opening_hours' => null]);

        if (Setting::get('population_source') === self::POPULATION_SOURCE) {
            Setting::putMany(['population_source' => null]);
        }
        Setting::putMany([self::SETTING => null]);

        return $total;
    }
}
