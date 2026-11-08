<?php

namespace Database\Seeders;

use App\Enums\EducationCategory;
use App\Enums\EducationTopic;
use App\Models\EducationContent;
use Illuminate\Database\Seeder;

/**
 * Konten edukasi umum tentang kesiapsiagaan banjir dan longsor.
 * Video dan infografis ditambahkan admin lewat panel (unggah video ke
 * YouTube, lalu tempel tautannya).
 */
class EducationContentSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->contents() as $i => $content) {
            EducationContent::firstOrCreate(
                ['title' => $content['title']],
                $content + ['is_published' => true, 'sort_order' => $i + 1],
            );
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function contents(): array
    {
        return [
            [
                'title' => 'Mengenali tanda-tanda tanah longsor',
                'category' => EducationCategory::Modul,
                'topic' => EducationTopic::Longsor,
                'summary' => 'Longsor sering didahului tanda-tanda yang bisa dilihat dan didengar. Kenali agar sempat menyelamatkan diri.',
                'body' => <<<'MD'
Tanah longsor paling sering terjadi saat atau setelah **hujan deras yang berlangsung lama**, terutama di lereng yang curam, gundul, atau baru dipotong untuk jalan dan rumah.

## Tanda-tanda yang perlu diwaspadai

- Muncul **retakan baru** di tanah, lantai, atau dinding rumah, terutama yang memanjang searah lereng.
- **Pohon, tiang listrik, atau pagar miring** ke arah bawah lereng.
- Pintu dan jendela tiba-tiba **sulit dibuka atau ditutup**.
- Muncul **mata air baru** atau rembesan air keruh dari lereng.
- Air sumur atau mata air yang biasanya jernih mendadak **keruh**.
- Terdengar **suara gemuruh** atau ranting patah dari arah bukit.
- Batu-batu kecil mulai **berjatuhan** dari tebing.

## Bila tanda-tanda itu muncul

1. Segera beri tahu keluarga, tetangga, dan aparat desa.
2. Tinggalkan rumah di bawah atau di atas lereng tersebut, terutama saat hujan masih turun.
3. Menjauh ke arah samping dari jalur luncuran, menuju titik kumpul.
4. Jangan kembali sebelum dinyatakan aman oleh aparat.

> Retakan kecil di lereng bisa ditutup sementara dengan tanah liat agar air hujan tidak masuk, tetapi tetap laporkan ke aparat desa.

## Mencegah longsor di sekitar rumah

- Tanam pohon berakar dalam di lereng, jangan menebang pohon di tebing.
- Buat saluran air agar air hujan tidak menggenang di lereng.
- Jangan memotong lereng menjadi tegak lurus saat membangun rumah atau jalan.
MD,
            ],
            [
                'title' => 'Siaga banjir: sebelum, saat, dan sesudah',
                'category' => EducationCategory::Modul,
                'topic' => EducationTopic::Banjir,
                'summary' => 'Langkah sederhana yang bisa dilakukan keluarga untuk mengurangi dampak banjir.',
                'body' => <<<'MD'
## Sebelum banjir

- Ketahui **jalur evakuasi** dan **titik kumpul** terdekat dari rumah. Lihat menu Jalur Evakuasi di web ini.
- Siapkan **tas siaga bencana** dan taruh di tempat yang mudah dijangkau.
- Simpan dokumen penting dalam plastik kedap air.
- Bersihkan selokan dan jangan membuang sampah ke sungai.
- Pantau **peringatan cuaca BMKG**, terutama bila hujan deras turun berjam-jam.
- Perhatikan tanda bahaya: air sungai **naik cepat**, **berubah keruh**, atau membawa banyak sampah dan kayu dari hulu.

## Saat banjir

- **Matikan listrik** dari meteran bila masih aman.
- Segera menuju titik kumpul. Dahulukan anak, lansia, ibu hamil, dan penyandang disabilitas.
- **Jangan berjalan atau berkendara melewati arus banjir.** Arus setinggi lutut bisa menjatuhkan orang dewasa.
- Jauhi kabel listrik dan tiang yang terendam.

## Sesudah banjir

- Kembali ke rumah hanya setelah aparat menyatakan aman.
- Periksa instalasi listrik sebelum menyalakan kembali.
- Bersihkan rumah dengan sarung tangan dan sepatu bot.
- Rebus air minum dan buang makanan yang terendam air banjir.
- Waspadai penyakit setelah banjir seperti diare dan leptospirosis. Segera ke puskesmas bila demam.
MD,
            ],
            [
                'title' => 'Isi tas siaga bencana keluarga',
                'category' => EducationCategory::Panduan,
                'topic' => EducationTopic::Umum,
                'summary' => 'Tas berisi kebutuhan dasar untuk bertahan sekitar 3 hari, siap dibawa saat harus mengungsi.',
                'body' => <<<'MD'
Tas siaga bencana adalah tas yang **disiapkan sebelum bencana** dan langsung dibawa saat harus mengungsi. Siapkan satu tas untuk setiap keluarga, idealnya cukup untuk kebutuhan sekitar **3 hari**.

## Isi yang disarankan

- **Dokumen penting** (salinan KTP, KK, akta, surat tanah, kartu BPJS) dalam plastik kedap air.
- **Air minum** dan **makanan tahan lama** (biskuit, roti kering, makanan kaleng).
- **Obat-obatan pribadi** dan kotak P3K.
- **Senter** dan baterai cadangan.
- **Pakaian ganti**, jas hujan, dan selimut tipis.
- **Perlengkapan kebersihan**: sabun, sikat gigi, pembalut, popok bayi bila perlu.
- **Uang tunai** secukupnya.
- **Peluit** untuk memberi tanda bila terjebak.
- **Power bank** dan kabel pengisi daya HP.
- Catatan **nomor penting** keluarga dan aparat desa di kertas.

## Tips

- Simpan tas di dekat pintu keluar.
- Periksa isinya setiap 6 bulan, ganti makanan dan obat yang kedaluwarsa.
- Pastikan semua anggota keluarga tahu letak tas siaga.
MD,
            ],
            [
                'title' => 'Pertolongan pertama dasar saat bencana',
                'category' => EducationCategory::Panduan,
                'topic' => EducationTopic::Umum,
                'summary' => 'Langkah awal menolong korban luka sambil menunggu tenaga kesehatan.',
                'body' => <<<'MD'
Pertolongan pertama bertujuan **menjaga korban tetap aman** sampai tenaga kesehatan datang. Utamakan keselamatan diri sendiri sebelum menolong.

## Langkah umum

1. **Pastikan lokasi aman** dari arus air, reruntuhan, atau longsor susulan.
2. **Panggil bantuan.** Hubungi aparat desa atau layanan gawat darurat **119**.
3. **Periksa kesadaran** korban: panggil dan tepuk bahunya.
4. **Periksa napas.** Bila tidak bernapas dan Anda terlatih, lakukan resusitasi jantung paru (RJP).

## Luka berdarah

- Tekan luka dengan kain bersih secara kuat dan terus-menerus.
- Bila memungkinkan, posisikan bagian yang luka lebih tinggi dari jantung.

## Dugaan patah tulang

- Jangan memindahkan bagian yang patah. Buat bidai dari papan atau kayu dan ikat di atas dan di bawah bagian yang patah.

## Korban tenggelam atau hanyut

- Keluarkan korban dari air bila aman. Jangan masuk ke arus deras tanpa alat.
- Baringkan, periksa napas, dan jaga tubuhnya tetap hangat.

## Kedinginan (hipotermia)

- Ganti pakaian basah dengan yang kering, selimuti, dan beri minuman hangat bila korban sadar.

> Panduan ini tidak menggantikan pelatihan P3K. Ikuti pelatihan dari PMI atau puskesmas bila ada kesempatan.
MD,
            ],
        ];
    }
}
