<?php

namespace App\Support;

use App\Models\Setting;
use Throwable;

/**
 * Panduan keselamatan yang tampil di Beranda, Jalur Evakuasi, poster cetak,
 * dan popup darurat. Diisi admin di menu Profil Desa; kalau kosong, pakai
 * teks bawaan di bawah ini.
 */
class SafetyGuide
{
    /** @return list<array{title: string, body: string}> */
    public static function defaultFirstSteps(): array
    {
        return [
            ['title' => 'Selamatkan diri, bukan barang', 'body' => 'Bawa tas siaga bencana, segera matikan listrik dari meteran utama bila aman, dan utamakan anak-anak, lansia, ibu hamil, serta penyandang disabilitas.'],
            ['title' => 'Ikuti jalur ke titik kumpul', 'body' => 'Jangan menyeberangi arus banjir deras dan jauhi lereng perbukitan yang retak atau longsor. Ikuti jalur evakuasi menuju titik kumpul terdekat.'],
            ['title' => 'Segera lapor aparat desa', 'body' => 'Laporkan keadaan terkini, warga yang membutuhkan evakuasi, atau korban luka melalui kontak darurat desa.'],
        ];
    }

    /** @return list<string> */
    public static function defaultFloodSteps(): array
    {
        return [
            'Jangan berjalan atau berkendara melewati arus banjir. Air setinggi lutut yang mengalir sudah bisa menyeret orang dewasa.',
            'Matikan listrik dari meteran sebelum air masuk rumah, bila masih aman dilakukan.',
            'Jauhi tepi sungai, selokan besar, dan jembatan yang tergenang.',
            'Pindahkan dokumen penting ke tempat tinggi atau bawa dalam tas siaga.',
        ];
    }

    /** @return list<string> */
    public static function defaultLandslideSteps(): array
    {
        return [
            'Segera menjauh dari lereng bila muncul retakan baru, pohon atau tiang miring, atau suara gemuruh.',
            'Bergerak ke samping menjauhi arah luncuran, jangan searah aliran longsor.',
            'Waspadai longsor susulan, terutama saat hujan masih turun. Jangan kembali mengambil barang.',
            'Hindari jalan di kaki tebing selama dan sesudah hujan deras.',
        ];
    }

    public static function defaultPopupMessage(): string
    {
        return 'Tetap tenang. Ikuti arahan aparat desa dan petugas. Jika diminta mengungsi, segera menuju titik kumpul terdekat.';
    }

    /** @return list<array{title: string, body: string}> */
    public static function firstSteps(): array
    {
        $steps = collect(json_decode((string) self::setting('safety_first_steps'), true) ?: [])
            ->filter(fn ($s) => is_array($s) && filled($s['title'] ?? null))
            ->map(fn ($s) => ['title' => trim((string) $s['title']), 'body' => trim((string) ($s['body'] ?? ''))])
            ->values()
            ->all();

        return $steps ?: self::defaultFirstSteps();
    }

    /** @return list<string> */
    public static function floodSteps(): array
    {
        return self::lines(self::setting('safety_flood_steps')) ?: self::defaultFloodSteps();
    }

    /** @return list<string> */
    public static function landslideSteps(): array
    {
        return self::lines(self::setting('safety_landslide_steps')) ?: self::defaultLandslideSteps();
    }

    public static function popupMessage(): string
    {
        return trim((string) self::setting('safety_popup_message')) ?: self::defaultPopupMessage();
    }

    /** Satu langkah per baris; nomor/tanda di depan baris dibuang. @return list<string> */
    public static function lines(?string $text): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $text))
            ->map(fn ($line) => trim(preg_replace('/^\s*(\d+[.)]|[-*•])\s*/u', '', $line)))
            ->filter()
            ->values()
            ->all();
    }

    private static function setting(string $key): ?string
    {
        try {
            return Setting::get($key);
        } catch (Throwable) {
            return null;
        }
    }
}
