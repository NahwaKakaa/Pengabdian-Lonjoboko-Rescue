<?php

namespace App\Support;

use App\Enums\AnnouncementLevel;
use App\Enums\AssemblyPointType;
use App\Enums\ContactCategory;
use App\Enums\FacilityCategory;
use App\Enums\HazardType;
use App\Enums\RiskLevel;
use App\Enums\RouteHazard;
use App\Enums\VerificationStatus;
use App\Models\Announcement;
use App\Models\AssemblyPoint;
use App\Models\EmergencyContact;
use App\Models\EvacuationRoute;
use App\Models\Facility;
use App\Models\HazardZone;
use App\Models\Setting;
use App\Models\Volunteer;
use Database\Seeders\DemoMapSeeder;
use Illuminate\Support\Facades\DB;

/**
 * Data awal Desa Lonjoboko untuk mengisi web (peta, pengumuman, relawan).
 *
 * Bangunan, kampung, jalan, dan sungai diambil dari OpenStreetMap
 * (© kontributor OpenStreetMap, lisensi ODbL) dan berita kejadian 2022.
 * Batas zona rawan, tingkat risiko, kapasitas, dan jalur evakuasi masih
 * PERKIRAAN, belum hasil survei lapangan bersama pemerintah desa/BPBD.
 *
 * Semua ID yang dibuat dicatat di pengaturan "village_data_ids" supaya bisa
 * dihapus bersih:  php artisan lonjoboko:hapus-data-desa
 * Kontak resmi Pemkab Gowa tidak ikut dihapus karena nomornya nyata.
 */
class VillageData
{
    public const SETTING = 'village_data_ids';

    /** Model yang dicatat dan dihapus, urutan aman untuk relasi. */
    private const MODELS = [
        'routes' => EvacuationRoute::class,
        'hazards' => HazardZone::class,
        'facilities' => Facility::class,
        'points' => AssemblyPoint::class,
        'announcements' => Announcement::class,
        'volunteers' => Volunteer::class,
    ];

    public static function isInstalled(): bool
    {
        return filled(Setting::get(self::SETTING));
    }

    /** @return array<string, int> jumlah data per jenis */
    public static function install(): array
    {
        return DB::transaction(function () {
            self::removeDemo();

            $ids = array_fill_keys(array_keys(self::MODELS), []);
            $ok = VerificationStatus::Terverifikasi;

            // ---- Titik kumpul & pengungsian (bangunan nyata dari OpenStreetMap)
            $p = [];
            foreach (self::assemblyPoints() as $key => $data) {
                $p[$key] = AssemblyPoint::create($data + ['status' => $ok]);
                $ids['points'][] = $p[$key]->id;
            }

            // ---- Lokasi rawan
            foreach (self::hazardZones() as $data) {
                $ids['hazards'][] = HazardZone::create($data)->id;
            }

            // ---- Jalur evakuasi (mengikuti Jalan Poros Malino & jalan kampung)
            foreach (self::routes() as $data) {
                $data['assembly_point_id'] = $p[$data['assembly_point_id']]->id;
                $ids['routes'][] = EvacuationRoute::create($data + ['status' => $ok])->id;
            }

            // ---- Fasilitas penting
            foreach (self::facilities() as $data) {
                $ids['facilities'][] = Facility::create($data + ['status' => $ok])->id;
            }

            // ---- Pengumuman
            foreach (self::announcements() as $data) {
                $ids['announcements'][] = Announcement::create($data)->id;
            }

            // ---- Relawan / pengurus
            foreach (self::volunteers() as $data) {
                $ids['volunteers'][] = Volunteer::create($data)->id;
            }

            // ---- Kontak resmi Pemkab Gowa (nyata, tidak dicatat untuk dihapus)
            foreach (self::officialContacts() as $data) {
                EmergencyContact::firstOrCreate(['phone' => $data['phone']], $data + ['is_active' => true]);
            }

            Setting::putMany([self::SETTING => json_encode($ids)]);

            return array_map('count', $ids);
        });
    }

    /** @return int jumlah data yang dihapus */
    public static function remove(): int
    {
        $ids = json_decode((string) Setting::get(self::SETTING), true);

        if (! is_array($ids)) {
            return 0;
        }

        $total = 0;
        foreach (self::MODELS as $key => $model) {
            if (! empty($ids[$key])) {
                $total += $model::whereIn('id', $ids[$key])->delete();
            }
        }

        Setting::putMany([self::SETTING => null]);

        return $total;
    }

    /** Hapus data latihan lama yang namanya diawali [CONTOH]. */
    private static function removeDemo(): void
    {
        foreach ([EvacuationRoute::class, HazardZone::class, Facility::class, AssemblyPoint::class] as $model) {
            $model::where('name', 'like', DemoMapSeeder::PREFIX.'%')->delete();
        }
    }

    private static function at(float $lat, float $lng): array
    {
        return ['lat' => $lat, 'lng' => $lng];
    }

    /** @return array<string, array<string, mixed>> */
    private static function assemblyPoints(): array
    {
        return [
            'kantor' => [
                'name' => 'Kantor Desa Lonjoboko',
                'type' => AssemblyPointType::TitikKumpul,
                'capacity' => 150,
                'address' => 'Jl. Poros Sungguminasa–Malino, Desa Lonjoboko',
                'facilities' => 'Halaman luas, toilet, air bersih, posko informasi desa',
                'location' => self::at(-5.274331, 119.755215),
            ],
            'sd' => [
                'name' => "SD Inpres Mala'lang",
                'type' => AssemblyPointType::Pengungsian,
                'capacity' => 120,
                'address' => "Jl. Poros Malino, Mala'lang, Desa Lonjoboko",
                'facilities' => 'Ruang kelas, toilet, halaman sekolah, air bersih',
                'location' => self::at(-5.271594, 119.737195),
            ],
            'nurulHalal' => [
                'name' => 'Masjid Nurul Halal',
                'type' => AssemblyPointType::TitikKumpul,
                'capacity' => 80,
                'address' => "Mala'lang, Desa Lonjoboko",
                'facilities' => 'Tempat wudu, toilet, pengeras suara masjid',
                'location' => self::at(-5.270468, 119.735423),
            ],
            'abdurrahman' => [
                'name' => 'Masjid Abdurrahman Hamid Isa',
                'type' => AssemblyPointType::TitikKumpul,
                'capacity' => 50,
                'address' => 'Kampung Beru, Desa Lonjoboko',
                'facilities' => 'Tempat wudu, toilet, pengeras suara masjid',
                'location' => self::at(-5.269405, 119.720109),
            ],
            'arRahman' => [
                'name' => 'Masjid Ar Rahman',
                'type' => AssemblyPointType::TitikKumpul,
                'capacity' => 70,
                'address' => 'Tombongi, Desa Lonjoboko',
                'facilities' => 'Tempat wudu, toilet, pengeras suara masjid',
                'location' => self::at(-5.264490, 119.707254),
            ],
            'nurulHidayah' => [
                'name' => 'Masjid Nurul Hidayah',
                'type' => AssemblyPointType::TitikKumpul,
                'capacity' => 70,
                'address' => "Lonjo' Boko, Desa Lonjoboko",
                'facilities' => 'Tempat wudu, toilet, pengeras suara masjid',
                'location' => self::at(-5.260930, 119.700553),
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    private static function hazardZones(): array
    {
        $ok = VerificationStatus::Terverifikasi;

        return [
            [
                'name' => "Lereng Jalan Poros Malino, Mala'lang",
                'hazard_type' => HazardType::Longsor,
                'risk_level' => RiskLevel::Tinggi,
                'description' => "Tebing curam di sisi Jalan Poros Malino. Ruas ini di wilayah Mala'lang pernah tertutup longsor pada Desember 2022 setelah hujan deras beberapa hari. Saat hujan lebih dari 3 jam, hindari berhenti di bawah tebing dan perhatikan retakan atau batu yang jatuh.",
                'location' => self::at(-5.273210, 119.742850),
                'radius_m' => 160,
                'status' => $ok,
            ],
            [
                'name' => 'Tebing Jalan Poros Malino, Kampung Beru',
                'hazard_type' => HazardType::Longsor,
                'risk_level' => RiskLevel::Sedang,
                'description' => 'Lereng terjal di tikungan jalan poros. Tanah dan batu bisa turun ke badan jalan saat musim hujan. Pengendara diminta mengurangi kecepatan dan tidak parkir di bawah tebing.',
                'location' => self::at(-5.267500, 119.715370),
                'radius_m' => 130,
                'status' => $ok,
            ],
            [
                'name' => 'Bantaran Sungai Jeneberang, Tombongi',
                'hazard_type' => HazardType::Banjir,
                'risk_level' => RiskLevel::Tinggi,
                'description' => 'Permukiman dan area tambang pasir di tepi Sungai Jeneberang. Air sungai bisa naik cepat ketika hujan deras di hulu walaupun di desa tidak hujan. Jauhi sungai bila air berubah keruh dan arusnya makin deras.',
                'location' => self::at(-5.268000, 119.708500),
                'radius_m' => 250,
                'status' => $ok,
            ],
            [
                'name' => "Tepi sungai dekat Lonjo' Boko",
                'hazard_type' => HazardType::Banjir,
                'risk_level' => RiskLevel::Sedang,
                'description' => 'Aliran sungai di selatan kampung dan unit pemecah batu. Saat hujan lebat, air meluap ke kebun dan jalan setapak di tepi sungai.',
                'location' => self::at(-5.264500, 119.701200),
                'radius_m' => 200,
                'status' => $ok,
            ],
            [
                'name' => 'Lereng kebun di utara Tombongi',
                'hazard_type' => HazardType::Longsor,
                'risk_level' => RiskLevel::Rendah,
                'description' => 'Laporan kader: ada retakan tanah di kebun warga setelah hujan. Perlu dicek bersama pemerintah desa sebelum ditampilkan.',
                'location' => self::at(-5.262500, 119.713500),
                'radius_m' => 90,
                'status' => VerificationStatus::Menunggu,
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    private static function routes(): array
    {
        return [
            [
                'name' => "Ruas longsor Mala'lang ke SD Inpres Mala'lang",
                'hazard_type' => RouteHazard::Longsor,
                'assembly_point_id' => 'sd',
                'description' => 'Ikuti Jalan Poros Malino ke arah barat, menjauh dari tebing. Belok kanan ke halaman sekolah. Jangan melintas di bawah tebing saat hujan masih deras.',
                'path' => [
                    [-5.27321, 119.74285], [-5.27243, 119.74128], [-5.27226, 119.74040], [-5.27212, 119.73899],
                    [-5.27226, 119.73831], [-5.27220, 119.73753], [-5.27206, 119.73718], [-5.27188, 119.73725],
                    [-5.27159, 119.73720],
                ],
            ],
            [
                'name' => "Permukiman Mala'lang ke Masjid Nurul Halal",
                'hazard_type' => RouteHazard::Semua,
                'assembly_point_id' => 'nurulHalal',
                'description' => 'Dari rumah warga di dekat anak sungai, naik ke jalan poros lalu masuk gang menuju masjid. Jalur pendek, cocok untuk lansia dan anak-anak.',
                'path' => [
                    [-5.27180, 119.73440], [-5.27118, 119.73470], [-5.27054, 119.73521], [-5.27047, 119.73542],
                ],
            ],
            [
                'name' => 'Tebing Kampung Beru ke Masjid Abdurrahman Hamid Isa',
                'hazard_type' => RouteHazard::Longsor,
                'assembly_point_id' => 'abdurrahman',
                'description' => 'Bergerak ke arah timur di jalan poros menjauhi tebing, lalu berkumpul di halaman masjid.',
                'path' => [
                    [-5.26750, 119.71537], [-5.26783, 119.71662], [-5.26784, 119.71791], [-5.26941, 119.71956],
                    [-5.26941, 119.72011],
                ],
            ],
            [
                'name' => 'Bantaran Jeneberang Tombongi ke Masjid Ar Rahman',
                'hazard_type' => RouteHazard::Banjir,
                'assembly_point_id' => 'arRahman',
                'description' => 'Naik dari tepi sungai ke jalan poros, lalu ikuti jalan ke arah barat sampai masjid. Jauhi jalan setapak di tepi sungai dan area tambang pasir.',
                'path' => [
                    [-5.26720, 119.70930], [-5.26605, 119.70886], [-5.26512, 119.70846], [-5.26427, 119.70728],
                    [-5.26449, 119.70725],
                ],
            ],
            [
                'name' => "Tepi sungai Lonjo' Boko ke Masjid Nurul Hidayah",
                'hazard_type' => RouteHazard::Banjir,
                'assembly_point_id' => 'nurulHidayah',
                'description' => 'Bergerak ke utara menuju jalan poros, lalu ke masjid di sisi jalan. Bawa dokumen penting dan obat-obatan seperlunya.',
                'path' => [
                    [-5.26320, 119.70100], [-5.26211, 119.70086], [-5.26120, 119.70030], [-5.26093, 119.70055],
                ],
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    private static function facilities(): array
    {
        return [
            [
                'name' => 'Kantor Desa Lonjoboko',
                'category' => FacilityCategory::Pemerintahan,
                'address' => 'Jl. Poros Sungguminasa–Malino, Desa Lonjoboko',
                'location' => self::at(-5.274331, 119.755215),
            ],
            [
                'name' => "SD Inpres Mala'lang",
                'category' => FacilityCategory::Pendidikan,
                'address' => 'Jl. Poros Malino Km 52, Malalang, Desa Lonjoboko',
                'location' => self::at(-5.271594, 119.737195),
            ],
            [
                'name' => 'Masjid Nurul Imam',
                'category' => FacilityCategory::Ibadah,
                'address' => 'Dekat Kantor Desa Lonjoboko',
                'location' => self::at(-5.273879, 119.755909),
            ],
            [
                'name' => 'Puskesdes (perbatasan Bontokassi)',
                'category' => FacilityCategory::Kesehatan,
                'address' => 'Dekat Kantor Desa Bontokassi, Jl. Poros Malino',
                'location' => self::at(-5.256303, 119.697095),
            ],
            [
                'name' => 'Puskesmas Parangloe',
                'category' => FacilityCategory::Kesehatan,
                'address' => 'Lanna, Kec. Parangloe (QM32+5FJ)',
                'location' => self::at(-5.247037, 119.651141),
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    private static function announcements(): array
    {
        return [
            [
                'title' => 'Desa Lonjoboko resmi menjadi Desa Tangguh Bencana',
                'level' => AnnouncementLevel::Informasi,
                'body' => "Desa Lonjoboko meluncurkan program Desa Tangguh Bencana (Destana) dan membentuk Kelompok Tangguh Bencana (KTB) di Kantor Desa Lonjoboko pada Selasa, 15 September 2026.\n\nKegiatan dihadiri Camat Parangloe, Kepala Desa Lonjoboko, aparat desa, Babinsa, kepala dusun, pemuda, dan warga. KTB akan membantu warga bersiap menghadapi banjir dan tanah longsor, termasuk menyiapkan jalur evakuasi dan titik kumpul.",
                'source' => 'Tekape.co',
                'source_url' => 'https://tekape.co/mahasiswa-unhas-bantu-desa-lonjoboko-jadi-tangguh-bencana-ktb-resmi-dibentuk/',
                'is_published' => true,
                'published_at' => '2026-09-15 10:00:00',
            ],
            [
                'title' => 'Peta jalur evakuasi dan titik kumpul kini bisa dibuka di HP',
                'level' => AnnouncementLevel::Informasi,
                'body' => "Warga bisa melihat lokasi rawan longsor dan banjir, jalur evakuasi, serta titik kumpul terdekat di menu Peta Risiko dan Jalur Evakuasi.\n\nSimpan web ini di HP lewat tombol \"Pasang di HP\" di bagian bawah halaman supaya tetap bisa dibuka saat sinyal lemah. Bila menemukan retakan tanah atau tanda bahaya lain, laporkan ke kader atau kantor desa.",
                'source' => 'Pemerintah Desa Lonjoboko',
                'is_published' => true,
                'published_at' => now()->subDays(2)->setTime(9, 0),
            ],
            [
                'title' => 'Memasuki musim hujan, kenali tanda-tanda longsor',
                'level' => AnnouncementLevel::Informasi,
                'body' => "Pada November dan Desember 2022, longsor di ruas Jalan Poros Malino wilayah Desa Lonjoboko menutup jalan dan menimbulkan korban jiwa. Kenali tandanya sejak dini:\n\n1. Muncul retakan baru di tanah, jalan, atau dinding rumah.\n2. Air keruh tiba-tiba keluar dari lereng.\n3. Pohon atau tiang listrik miring.\n4. Terdengar suara gemuruh dari arah bukit.\n\nBila melihat tanda-tanda ini, segera menjauh dari lereng, beri tahu tetangga, dan hubungi kader atau kantor desa.",
                'source' => 'ANTARA News Makassar',
                'source_url' => 'https://makassar.antaranews.com/berita/442909/dua-warga-meninggal-akibat-tanah-longsor-di-poros-malino-gowa',
                'is_published' => true,
                'published_at' => now()->subDay()->setTime(8, 0),
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    private static function volunteers(): array
    {
        return [
            [
                'name' => 'Kaharuddin',
                'position' => 'Kepala Desa Lonjoboko',
                'hamlet' => null,
                'phone' => null,
                'show_phone' => false,
                'is_active' => true,
                'sort_order' => 1,
            ],
        ];
    }

    /** Nomor resmi dari Smart City Kabupaten Gowa (smartcity.gowakab.go.id/telepon-penting). */
    private static function officialContacts(): array
    {
        return [
            ['name' => 'BPBD Kabupaten Gowa', 'category' => ContactCategory::Bpbd, 'phone' => '0411-8211547', 'description' => 'Badan Penanggulangan Bencana Daerah. Laporkan banjir, longsor, dan kebutuhan evakuasi.', 'show_in_footer' => true, 'sort_order' => 0],
            ['name' => 'Polres Gowa', 'category' => ContactCategory::Keamanan, 'phone' => '0411-861110', 'description' => null, 'show_in_footer' => false, 'sort_order' => 5],
            ['name' => 'RSUD Syekh Yusuf', 'category' => ContactCategory::Kesehatan, 'phone' => '0411-866536', 'description' => 'Rumah sakit umum daerah Kabupaten Gowa.', 'show_in_footer' => false, 'sort_order' => 6],
            ['name' => 'Pemadam Kebakaran Gowa', 'category' => ContactCategory::Keamanan, 'phone' => '0411-861036', 'description' => null, 'show_in_footer' => false, 'sort_order' => 7],
        ];
    }
}
