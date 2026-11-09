<?php

namespace App\Support;

use App\Enums\EducationCategory;
use App\Enums\EducationTopic;
use App\Enums\HazardType;
use App\Enums\VerificationStatus;
use App\Models\DisasterEvent;
use App\Models\EducationContent;
use App\Models\Setting;
use Database\Seeders\EducationContentSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Melengkapi profil desa (hanya kolom yang masih kosong) dan konten edukasi.
 *
 * Nomor WhatsApp desa yang diisi di sini adalah NOMOR DUMMY dan harus
 * diganti nomor posko yang aktif sebelum web disebarkan ke warga.
 *
 * Konten edukasi yang dibuat dicatat di pengaturan "village_education_ids"
 * dan ikut terhapus oleh:  php artisan lonjoboko:hapus-data-desa
 */
class VillageContent
{
    public const SETTING = 'village_education_ids';

    public const DUMMY_WHATSAPP = '0812-0000-0000';

    public const EVENTS_SETTING = 'village_event_ids';

    /**
     * Riwayat kejadian awal dari berita yang bisa dicek. Angka korban
     * mengikuti sumber yang disebut; pemerintah desa sebaiknya memastikan.
     *
     * @return int jumlah kejadian baru
     */
    public static function installEvents(): int
    {
        $ids = json_decode((string) Setting::get(self::EVENTS_SETTING), true) ?: [];
        $added = 0;

        foreach (self::events() as $event) {
            if (DisasterEvent::where('title', $event['title'])->exists()) {
                continue;
            }

            $ids[] = DisasterEvent::create($event + ['status' => VerificationStatus::Terverifikasi])->id;
            $added++;
        }

        Setting::putMany([self::EVENTS_SETTING => json_encode(array_values(array_unique($ids)))]);

        return $added;
    }

    public static function removeEvents(): int
    {
        $ids = json_decode((string) Setting::get(self::EVENTS_SETTING), true);

        if (! is_array($ids) || $ids === []) {
            return 0;
        }

        $total = DisasterEvent::whereIn('id', $ids)->delete();
        Setting::putMany([self::EVENTS_SETTING => null]);

        return $total;
    }

    /** @return list<array<string, mixed>> */
    private static function events(): array
    {
        return [
            [
                'title' => 'Longsor di Jalan Poros Malino, Borong Sapiri dan Kunyika',
                'hazard_type' => HazardType::Longsor,
                'occurred_on' => '2022-11-16',
                'hamlet' => 'Bontoloe dan Galesong',
                'description' => "Rabu petang sekitar pukul 18.00 WITA, setelah hujan deras, tanah longsor terjadi di Jalan Poros Malino Km 62 (Kampung Borong Sapiri, Dusun Bontoloe) dan Km 58 (Kampung Kunyika, Dusun Galesong). Tanah dan lumpur turun dari bukit yang sangat terjal. Sebuah mobil terseret ke jurang dan jalan poros tertutup total.\n\nMenurut Polres Gowa (ANTARA, 17 Nov 2022), 2 orang meninggal dan 2 orang dicari. Basarnas Sulawesi Selatan kemudian melaporkan 9 korban: 5 meninggal, 2 masih dicari, dan 2 selamat.",
                'impact' => '5 rumah warga rusak, 3 mobil rusak, dan Jalan Poros Malino tertutup total.',
                'deaths' => 5,
                'injured' => 0,
                'missing' => 2,
                'displaced' => 0,
                'houses_damaged' => 5,
                'source' => 'ANTARA Makassar (Polres Gowa) dan Basarnas Sulsel',
                'source_url' => 'https://makassar.antaranews.com/berita/442909/dua-warga-meninggal-akibat-tanah-longsor-di-poros-malino-gowa',
            ],
            [
                'title' => 'Sungai meluap di Jembatan Lebong',
                'hazard_type' => HazardType::Banjir,
                'occurred_on' => '2022-11-16',
                'hamlet' => 'Bontoloe',
                'description' => 'Pada hujan deras yang sama dengan longsor di Poros Malino, air sungai meluap di Jembatan Lebong, Kampung Lebong, Dusun Bontoloe.',
                'impact' => null,
                'source' => 'ANTARA Makassar (Polres Gowa)',
                'source_url' => 'https://makassar.antaranews.com/berita/442909/dua-warga-meninggal-akibat-tanah-longsor-di-poros-malino-gowa',
            ],
            [
                'title' => "Longsor memutus Jalan Poros Malino di Mala'lang",
                'hazard_type' => HazardType::Longsor,
                'occurred_on' => '2022-12-24',
                'hamlet' => "Mala'lang",
                'description' => "Setelah hujan deras beberapa hari, tanah longsor menutup Jalan Poros Malino Km 60 di Mala'lang, Desa Lonjoboko, sehingga akses jalan terputus. Saat berita terbit, ada atau tidaknya korban belum diketahui. Seorang warga menyebut 6 rumah tertimbun, tetapi angka ini belum dipastikan.",
                'impact' => 'Akses Jalan Poros Malino terputus dan perlu alat untuk membersihkan timbunan.',
                'source' => 'Tribun Timur',
                'source_url' => 'https://makassar.tribunnews.com/2022/12/24/akses-jalan-poros-malino-di-desa-lonjoboko-gowa-tak-bisa-diakses-pasca-longsor',
            ],
        ];
    }

    /** @return list<string> kunci pengaturan yang diisi */
    public static function completeProfile(): array
    {
        $current = Setting::allValues();
        $filled = [];

        foreach (self::profile() as $key => $value) {
            if (blank($current[$key] ?? null)) {
                $filled[$key] = $value;
            }
        }

        Setting::putMany($filled);

        return array_keys($filled);
    }

    /** @return int jumlah konten edukasi baru */
    public static function installEducation(): int
    {
        return DB::transaction(function () {
            $ids = json_decode((string) Setting::get(self::SETTING), true) ?: [];

            // Empat materi umum bawaan (tanda longsor, siaga banjir, tas siaga, P3K).
            $before = EducationContent::pluck('id')->all();
            (new EducationContentSeeder)->run();
            $ids = array_merge($ids, EducationContent::whereNotIn('id', $before)->pluck('id')->all());

            $order = (int) EducationContent::max('sort_order');

            foreach (self::education() as $item) {
                if (EducationContent::where('title', $item['title'])->exists()) {
                    continue;
                }

                if (isset($item['image_file'])) {
                    $item['image'] = self::copyImage($item['image_file']);
                    unset($item['image_file']);
                }

                $ids[] = EducationContent::create($item + ['is_published' => true, 'sort_order' => ++$order])->id;
            }

            Setting::putMany([self::SETTING => json_encode(array_values(array_unique($ids)))]);

            return count($ids);
        });
    }

    public static function removeEducation(): int
    {
        $ids = json_decode((string) Setting::get(self::SETTING), true);

        if (! is_array($ids) || $ids === []) {
            return 0;
        }

        $items = EducationContent::whereIn('id', $ids)->get();

        foreach ($items as $item) {
            if ($item->image && str_starts_with($item->image, 'edukasi/data-desa-')) {
                Storage::disk('public')->delete($item->image);
            }
        }

        $total = EducationContent::whereIn('id', $ids)->delete();
        Setting::putMany([self::SETTING => null]);

        return $total;
    }

    private static function copyImage(string $file): ?string
    {
        $source = resource_path('data/edukasi/'.$file);

        if (! is_file($source)) {
            return null;
        }

        $path = 'edukasi/data-desa-'.$file;
        Storage::disk('public')->put($path, file_get_contents($source));

        return $path;
    }

    /** @return array<string, string> */
    private static function profile(): array
    {
        return [
            'office_address' => 'Jl. Poros Sungguminasa–Malino',
            'whatsapp_number' => self::DUMMY_WHATSAPP,
            'whatsapp_label' => 'Posko Desa Lonjoboko',
            'maps_url' => 'https://www.google.com/maps/search/?api=1&query=-5.274331,119.755215',
            'landscape_note' => 'Lembah Sungai Jeneberang di Jalan Poros Malino',
            'village_profile' => 'Desa Lonjoboko berada di Kecamatan Parangloe, Kabupaten Gowa, di sepanjang Jalan Poros Sungguminasa–Malino. Wilayahnya berbukit dengan ketinggian sekitar 100–600 mdpl dan termasuk Daerah Aliran Sungai Jeneberang. Desa ini terdiri atas empat dusun: Bontoloe, Galesong, Tombongi, dan Lantaboko. Permukiman warga banyak berada di tepi jalan poros dan dekat sungai, sehingga tanah longsor di tebing jalan dan luapan sungai saat hujan deras menjadi ancaman utama.',
            'population_note' => 'Lansia, ibu hamil, bayi dan balita, serta penyandang disabilitas yang tinggal di tepi sungai atau di bawah tebing jalan poros perlu didahulukan saat evakuasi. Kader di setiap dusun mencatat rumah yang perlu dibantu tanpa menampilkan nama atau alamat di web ini.',
            'report_instructions' => implode("\n", [
                'Pastikan Anda dan keluarga sudah di tempat aman sebelum melapor.',
                'Hubungi kader dusun atau WhatsApp Posko Desa Lonjoboko.',
                'Sebutkan lokasi (dusun, kampung, patokan terdekat) dan jenis kejadian: banjir, longsor, atau jalan tertutup.',
                'Sampaikan jumlah orang yang perlu dibantu, terutama lansia, anak, dan warga yang sakit.',
                'Kirim foto bila aman. Jangan mendekat ke lereng atau sungai hanya untuk memotret.',
                'Untuk korban luka atau tertimbun, segera hubungi Ambulans 119, Basarnas 115, atau BPBD Gowa 0411-8211547.',
            ]),
        ];
    }

    /**
     * Video dari kanal YouTube resmi BNPB Indonesia (@bnpb_indonesia).
     * Semua sudah dicek boleh diputar di web lain (embed).
     *
     * @return list<array<string, mixed>>
     */
    private static function videos(): array
    {
        $video = fn (string $title, EducationTopic $topic, string $id, string $summary, string $body) => [
            'title' => $title,
            'category' => EducationCategory::Video,
            'topic' => $topic,
            'video_url' => 'https://www.youtube.com/watch?v='.$id,
            'summary' => $summary,
            'body' => $body."\n\nSumber: kanal YouTube resmi BNPB Indonesia.",
        ];

        return [
            $video('SiAGA Tanah Longsor', EducationTopic::Longsor, 'JFK7A1n6JJI',
                'Animasi BNPB tentang tanah longsor: penyebab, tanda-tanda, dan cara menyelamatkan diri. Durasi ±5 menit.',
                'Cocok ditonton bersama keluarga dan anak-anak, terutama warga yang tinggal di bawah tebing atau di lereng.'),
            $video('Edukasi Bencana Tanah Longsor', EducationTopic::Longsor, 'UAPce7ywdeI',
                'Video edukasi BNPB tentang tanah longsor, juga dibuat untuk teman-teman Tuli. Durasi ±3,5 menit.',
                'Bisa diputar saat penyuluhan di balai desa atau sekolah.'),
            $video('SiAGA Banjir', EducationTopic::Banjir, 'nJeNMDZvcrQ',
                'Animasi BNPB tentang banjir: apa yang perlu disiapkan dan dilakukan. Durasi ±4,5 menit.',
                'Tonton sebelum musim hujan, lalu cocokkan dengan jalur evakuasi dan titik kumpul di desa.'),
            $video('Edukasi Bencana Banjir', EducationTopic::Banjir, 'sJsC6zQCfNI',
                'Penyebab banjir yang sering melanda Indonesia dan hal yang harus dilakukan saat banjir tiba. Durasi ±2 menit.',
                'Video ini juga dibuat untuk teman-teman Tuli.'),
            $video('SiAGA Perlengkapan Bencana', EducationTopic::Umum, '73_zCmA5yWg',
                'Animasi BNPB tentang perlengkapan yang perlu disiapkan keluarga sebelum bencana. Durasi ±4 menit.',
                'Lengkapi dengan infografis "Isi tas siaga bencana" di halaman ini.'),
            $video('Tas Siaga Bencana Saat Banjir', EducationTopic::Banjir, 'KPbmlF0ODg0',
                'Video pendek nominator Tangguh Awards 2024 yang dipublikasikan BNPB, tentang tas siaga saat banjir. Durasi ±4 menit.',
                'Karya Siti Kurnia Indah Safitri untuk kompetisi Tangguh Awards 2024.'),
        ];
    }

    /** @return list<array<string, mixed>> */
    private static function education(): array
    {
        return [...self::infoAndGuides(), ...self::videos()];
    }

    /** @return list<array<string, mixed>> */
    private static function infoAndGuides(): array
    {
        return [
            [
                'title' => 'Infografis: isi tas siaga bencana',
                'category' => EducationCategory::Infografis,
                'topic' => EducationTopic::Umum,
                'summary' => 'Delapan barang yang perlu ada di tas siaga keluarga, siap dibawa saat harus mengungsi.',
                'body' => 'Simpan atau cetak gambar ini dan tempel di dekat pintu rumah. Periksa isi tas setiap 6 bulan.',
                'image_file' => 'infografis-tas.jpg',
            ],
            [
                'title' => 'Infografis: tanda-tanda tanah longsor',
                'category' => EducationCategory::Infografis,
                'topic' => EducationTopic::Longsor,
                'summary' => 'Enam tanda longsor yang bisa dilihat dan didengar warga, beserta tiga hal yang harus segera dilakukan.',
                'body' => 'Bagikan gambar ini ke grup WhatsApp keluarga dan dusun, terutama menjelang musim hujan.',
                'image_file' => 'infografis-longsor.jpg',
            ],
            [
                'title' => 'Jalur evakuasi dan titik kumpul di Desa Lonjoboko',
                'category' => EducationCategory::Panduan,
                'topic' => EducationTopic::Umum,
                'summary' => 'Ke mana harus pergi dari setiap kampung saat banjir atau longsor, dan apa yang perlu dibawa.',
                'body' => <<<'MD'
Titik kumpul di Desa Lonjoboko dipilih dari bangunan yang mudah dijangkau dari jalan poros dan berada di luar zona rawan. Lihat posisinya di menu **Jalur Evakuasi**.

## Titik kumpul terdekat

1. **Lonjo' Boko** – Masjid Nurul Hidayah.
2. **Tombongi** – Masjid Ar Rahman. Warga di tepi Sungai Jeneberang dan area tambang pasir naik ke jalan poros lebih dulu.
3. **Kampung Beru** – Masjid Abdurrahman Hamid Isa.
4. **Mala'lang** – Masjid Nurul Halal, atau SD Inpres Mala'lang bila harus menginap.
5. **Sekitar kantor desa** – halaman Kantor Desa Lonjoboko.

## Saat harus mengungsi

1. Matikan listrik dan kompor, kunci rumah.
2. Bawa tas siaga, dokumen penting, dan obat rutin.
3. Lewati jalur yang ditandai, jangan memotong lewat tepi sungai atau di bawah tebing.
4. Dahulukan lansia, anak, ibu hamil, dan penyandang disabilitas.
5. Lapor ke kader di titik kumpul supaya semua warga terdata.

Jangan kembali ke rumah sebelum ada arahan dari kader atau pemerintah desa.
MD,
            ],
            [
                'title' => 'Aman melintas di Jalan Poros Malino saat musim hujan',
                'category' => EducationCategory::Panduan,
                'topic' => EducationTopic::Longsor,
                'summary' => 'Ruas jalan poros di Lonjoboko pernah tertutup longsor. Ini yang perlu diperhatikan pengendara dan pejalan kaki.',
                'body' => <<<'MD'
Pada November dan Desember 2022, longsor di beberapa titik Jalan Poros Malino wilayah Desa Lonjoboko menutup jalan, menyeret kendaraan, dan menimbulkan korban jiwa. Sebagian besar terjadi saat hujan deras berlangsung lama.

## Sebelum berangkat

1. Cek kabar cuaca BMKG dan informasi di web ini.
2. Tunda perjalanan bila hujan deras sudah berlangsung lebih dari 3 jam.
3. Isi bahan bakar dan pastikan lampu kendaraan menyala baik.

## Saat melintas

1. Kurangi kecepatan di tikungan dan di bawah tebing.
2. Jangan berhenti atau parkir di bawah tebing dan di pinggir jurang.
3. Waspadai batu kecil, lumpur, atau air keruh yang turun ke badan jalan. Itu tanda lereng mulai bergerak.
4. Bila jalan sudah tertutup, putar balik. Jangan menerobos timbunan tanah.

## Bila melihat longsor

1. Menjauh dari lereng dan beri tanda kepada pengendara lain.
2. Hubungi BPBD Gowa 0411-8211547 atau Polres Gowa 0411-861110.
3. Bila ada korban, hubungi Basarnas 115 dan Ambulans 119.
MD,
            ],
            [
                'title' => 'Sungai Jeneberang dan risiko banjir di Lonjoboko',
                'category' => EducationCategory::Modul,
                'topic' => EducationTopic::Banjir,
                'summary' => 'Air sungai bisa naik walau di desa tidak hujan. Kenali penyebab dan tanda-tandanya.',
                'body' => <<<'MD'
Desa Lonjoboko termasuk Daerah Aliran Sungai (DAS) Jeneberang. Sungai ini mengalir dari daerah pegunungan di sekitar Malino ke arah Makassar dan melintasi wilayah desa di sepanjang jalan poros.

## Mengapa air bisa naik tiba-tiba

- **Hujan di hulu.** Hujan deras di daerah atas mengalirkan air ke Lonjoboko beberapa jam kemudian, walaupun di desa cuaca cerah.
- **Lereng gundul.** Tanah yang tidak tertahan akar pohon ikut terbawa air sehingga sungai cepat keruh dan meluap.
- **Alur sungai berubah.** Pengambilan pasir dan batu di tepi sungai bisa mengubah arah arus dan menggerus tebing sungai.

## Tanda air akan naik

1. Air berubah keruh kecokelatan dan arusnya makin deras.
2. Terdengar gemuruh dari arah hulu.
3. Ranting, sampah, atau batang kayu mulai hanyut.
4. Permukaan air naik cepat dalam waktu singkat.

## Yang harus dilakukan

1. Segera menjauh dari tepi sungai, termasuk penambang, anak-anak yang bermain, dan warga yang mencuci.
2. Pindahkan ternak dan barang berharga ke tempat tinggi.
3. Ikuti jalur evakuasi menuju titik kumpul terdekat.
4. Jangan menyeberangi sungai atau jalan yang tergenang air deras. Air setinggi lutut sudah bisa menyeret orang dewasa.
MD,
            ],
            [
                'title' => 'Peran kader dan relawan saat bencana',
                'category' => EducationCategory::Modul,
                'topic' => EducationTopic::Umum,
                'summary' => 'Apa saja tugas kader Karang Taruna Tangguh Bencana sebelum, saat, dan sesudah bencana.',
                'body' => <<<'MD'
Kader dan relawan desa adalah warga yang paling dekat dengan tetangganya. Saat bencana, merekalah yang pertama bergerak sebelum bantuan dari luar datang.

## Sebelum bencana

1. Mengenal rumah warga rentan di dusunnya.
2. Mengecek jalur evakuasi dan titik kumpul secara berkala.
3. Melaporkan retakan tanah atau tebing sungai yang tergerus lewat menu admin web ini.
4. Ikut latihan evakuasi dan pertolongan pertama.

## Saat bencana

1. Menyebarkan peringatan lewat pengeras suara masjid dan grup WhatsApp.
2. Mengarahkan warga ke titik kumpul, mendahulukan yang rentan.
3. Memberi pertolongan pertama dan menghubungi layanan darurat.
4. Mencatat warga yang sudah dan belum sampai di titik kumpul.

## Sesudah bencana

1. Mendata kerusakan rumah, jalan, dan kebutuhan warga.
2. Membantu dapur umum dan pembagian bantuan.
3. Memperbarui data lokasi rawan di web bersama pemerintah desa.
MD,
            ],
        ];
    }
}
