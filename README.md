# LONJOBOKO-RESCUE WEB

Sistem informasi dan edukasi mitigasi bencana **banjir dan tanah longsor** berbasis web untuk **Desa Lonjoboko**, Kecamatan Parangloe, Kabupaten Gowa.

Bagian dari program pengabdian masyarakat *LONJOBOKO-RESCUE WEB: Pengembangan Sistem Informasi dan Edukasi Mitigasi Bencana Longsor dan Banjir Berbasis Web sebagai Upaya Mewujudkan Desa Lonjoboko Tangguh Bencana*.

> Web ini adalah sarana penyebaran informasi dan **bukan pengganti sistem peringatan dini resmi** (BMKG, BPBD, BNPB).

## Fitur

### Halaman publik (warga)

| Menu | Isi |
|---|---|
| **Beranda** | Pita status peringatan, ringkasan data peta, langkah saat darurat, informasi terkini, kontak cepat, profil desa dan program |
| **Peta Risiko** | Peta lokasi rawan banjir dan longsor (lingkaran perkiraan area, warna sesuai tingkat kerawanan), titik kumpul, jalur evakuasi, fasilitas umum dan kesehatan, tombol "Lokasi saya" |
| **Jalur Evakuasi** | Peta jalur dan titik kumpul, saring per jenis bencana, panjang jalur dan perkiraan waktu jalan kaki, titik kumpul terdekat dari posisi pengguna, petunjuk keselamatan |
| **Edukasi** | Modul, video (YouTube), infografis, dan panduan. Sudah berisi 4 konten awal |
| **Info Darurat** | Peringatan yang berlaku, kontak darurat (klik untuk menelepon / WhatsApp), cara melapor, tautan sumber resmi |
| **Relawan** | Daftar Kader Karang Taruna Tangguh Bencana |

### Panel admin (`/admin`)

- **Input peta tanpa coding**: ketuk peta, geser penanda, atau tekan **"Gunakan lokasi saya"** (GPS HP) saat berada di lokasi. Jalur evakuasi digambar titik demi titik di peta.
- **Alur verifikasi**: data dari kader berstatus *Menunggu Verifikasi* dan baru tampil di web setelah disetujui admin. Admin bisa menolak dengan catatan.
- **Dua peran**:
  - **Admin** (perangkat desa / koordinator): verifikasi data peta, kelola informasi, edukasi, kontak, relawan, profil desa, dan akun.
  - **Kader**: menambah dan memperbarui data peta (lokasi rawan, titik kumpul, jalur, fasilitas).
- Dasbor berisi jumlah data yang tampil dan yang menunggu verifikasi.

## Teknologi

- [Laravel 12](https://laravel.com) (PHP 8.2+)
- [Filament 3](https://filamentphp.com) untuk panel admin
- Blade + [Tailwind CSS 4](https://tailwindcss.com) untuk halaman publik
- [Leaflet](https://leafletjs.com) + [Leaflet-Geoman](https://geoman.io) + peta dasar [OpenStreetMap](https://www.openstreetmap.org)
- MySQL / MariaDB (produksi) atau SQLite (pengembangan)

## Menjalankan di laptop

Kebutuhan: PHP 8.2+, Composer, Node.js 20+.

```bash
git clone https://github.com/NahwaKakaa/Pengabdian-Lonjoboko-Rescue.git
cd Pengabdian-Lonjoboko-Rescue

composer install
cp .env.example .env
php artisan key:generate

touch database/database.sqlite        # Windows: buat file kosong database/database.sqlite
php artisan migrate --seed
php artisan storage:link

npm install
npm run build                          # atau `npm run dev` saat mengembangkan

php artisan serve
```

Buka:
- Halaman publik: http://localhost:8000
- Panel admin: http://localhost:8000/admin

Akun admin pertama dibuat seeder dari isian `ADMIN_EMAIL` dan `ADMIN_PASSWORD` di `.env`. **Isi keduanya dengan nilai sendiri sebelum menjalankan seeder.** Setelah akun dibuat, ubah email dan kata sandi lewat panel admin (menu Pengaturan → Akun Admin & Kader), bukan lewat `.env`.

### Data peta contoh (untuk latihan dan presentasi)

```bash
php artisan db:seed --class=DemoMapSeeder
```

Semua data contoh diawali **[CONTOH]** dan letaknya hanya di sekitar titik tengah peta, bukan lokasi sebenarnya. **Hapus sebelum web dipakai warga:**

```bash
php artisan lonjoboko:hapus-contoh
```

### Menjalankan tes

```bash
php artisan test
```

## Pengaturan penting

| `.env` | Keterangan |
|---|---|
| `MAP_CENTER_LAT`, `MAP_CENTER_LNG` | Titik tengah peta. Nilai bawaan adalah **perkiraan**, ganti dengan koordinat kantor desa |
| `MAP_ZOOM` | Tingkat zoom awal peta (bawaan 14) |
| `ADMIN_NAME`, `ADMIN_EMAIL`, `ADMIN_PASSWORD` | Akun admin pertama yang dibuat seeder |
| `APP_TIMEZONE` | Bawaan `Asia/Makassar` (WITA) |

Nama desa, profil, teks program, catatan kelompok rentan, dan cara melapor diubah lewat menu **Pengaturan → Profil Desa** di panel admin.

## Deploy

Web live: **https://lonjoboko-rescue.web.id** (Rumahweb, paket Unlimited M).

| Branch | Fungsi |
|---|---|
| `main` | Pengembangan sehari-hari, tidak memengaruhi web live |
| `production` | Setiap push ke sini menjalankan tes lalu **deploy otomatis** lewat GitHub Actions |

Merilis perubahan:

```bash
git checkout production && git merge main && git push origin production && git checkout main
```

Setup server pertama kali, pengaturan GitHub Secrets, dan solusi masalah umum ada di **[docs/DEPLOY.md](docs/DEPLOY.md)**.

Serahkan akun hosting dan domain atas nama desa atau tim, bukan akun pribadi, supaya mudah diteruskan setelah program selesai. Hosting dan domain berlaku sampai **8 November 2027** dan perlu diperpanjang sebelum tanggal itu.

## Struktur penting

```
app/
├── Enums/                      Jenis bencana, tingkat kerawanan, status verifikasi, dll.
├── Filament/
│   ├── Forms/Components/       LocationPicker (pilih titik) & RoutePicker (gambar jalur)
│   ├── Pages/VillageProfile    Halaman Profil Desa
│   ├── Resources/              Menu panel admin
│   └── Widgets/                Ringkasan di dasbor
├── Http/Controllers/PublicController.php
├── Models/Concerns/HasVerification.php   Aturan verifikasi data peta
└── Policies/                   Hak akses admin & kader
resources/
├── js/admin-map.js             Peta di form admin (Leaflet + Geoman)
├── js/public-map.js            Peta di halaman publik
└── views/public/               Halaman untuk warga
database/seeders/               Data awal & data contoh
```

## Rencana pengembangan

- Menampilkan peringatan dini cuaca BMKG (format CAP) untuk Kecamatan Parangloe secara otomatis.
- Menampilkan layer peta risiko InaRISK BNPB (layanan ArcGIS REST) di Peta Risiko.

## Sumber data

- Data peta desa dihimpun dan diverifikasi bersama pemerintah desa dan kader.
- Peta dasar © kontributor OpenStreetMap.
- Konten edukasi bersifat umum. Untuk peringatan dini, rujuk BMKG, BPBD Kabupaten Gowa, dan BNPB.
