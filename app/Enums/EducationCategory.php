<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

/** Jenis konten edukasi. Tiap jenis punya isian yang berbeda di panel admin. */
enum EducationCategory: string implements HasColor, HasIcon, HasLabel
{
    case Modul = 'modul';
    case Video = 'video';
    case Infografis = 'infografis';
    case Panduan = 'panduan';

    public function getLabel(): string
    {
        return match ($this) {
            self::Modul => 'Modul',
            self::Video => 'Video',
            self::Infografis => 'Infografis',
            self::Panduan => 'Panduan',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Modul => 'primary',
            self::Video => 'danger',
            self::Infografis => 'success',
            self::Panduan => 'warning',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Modul => 'heroicon-o-book-open',
            self::Video => 'heroicon-o-play-circle',
            self::Infografis => 'heroicon-o-photo',
            self::Panduan => 'heroicon-o-list-bullet',
        };
    }

    /** Penjelasan singkat di bawah pilihan jenis konten. */
    public function hint(): string
    {
        return match ($this) {
            self::Modul => 'Materi bacaan yang agak panjang, misalnya "Mengenali tanda-tanda longsor". Isi teksnya di sini; PDF versi cetak boleh dilampirkan.',
            self::Video => 'Video dari YouTube. Cukup tempel tautannya, videonya tidak diunggah ke web supaya hosting tetap ringan.',
            self::Infografis => 'Satu gambar berisi informasi, misalnya poster "Tas Siaga Bencana". Unggah gambarnya, penjelasan boleh ditambahkan.',
            self::Panduan => 'Langkah-langkah yang harus dilakukan warga, misalnya "Saat banjir datang". Tulis sebagai daftar bernomor.',
        };
    }

    /**
     * Isian yang dipakai jenis ini. Isian lain disembunyikan di formulir
     * dan dikosongkan saat disimpan supaya tidak tampil di web.
     *
     * @return list<string>
     */
    public function fields(): array
    {
        return match ($this) {
            self::Video => ['video_url', 'body'],
            self::Infografis => ['image', 'body'],
            self::Modul, self::Panduan => ['image', 'body', 'attachment'],
        };
    }

    public function uses(string $field): bool
    {
        return in_array($field, $this->fields(), true);
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->getLabel()])
            ->all();
    }
}
