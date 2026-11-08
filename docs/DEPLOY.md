# Panduan Deploy LONJOBOKO-RESCUE ke Rumahweb

Panduan ini untuk hosting **Rumahweb paket Unlimited M** dengan domain **lonjoboko-rescue.web.id**.
Setup awal dilakukan **sekali saja**. Setelah itu, setiap push ke branch `production` otomatis terdeploy.

## Cara kerja branch

| Branch | Fungsi |
|---|---|
| `main` | Tempat mengembangkan dan push sehari-hari. **Tidak** memengaruhi web live |
| `production` | Isi web live. Setiap push ke sini menjalankan tes, lalu deploy otomatis |

Merilis perubahan ke web live:

```bash
git checkout production
git merge main
git push origin production
git checkout main
```

Progres deploy bisa dilihat di tab **Actions** pada repo GitHub. Kalau tes gagal, deploy dibatalkan dan web live tidak berubah.

---

## Bagian 1. Siapkan cPanel

Login cPanel dari Clientzone Rumahweb (menu Hosting → lonjoboko-rescue.web.id → Login cPanel).

### 1.1 Versi PHP
Buka **Select PHP Version** (atau **MultiPHP Manager**):
- Pilih **PHP 8.3**
- Pastikan ekstensi ini aktif: `intl`, `zip`, `pdo_mysql`, `mbstring`, `fileinfo`, `gd`, `bcmath`, `xml`, `curl`

### 1.2 Database
Buka **MySQL Databases** (atau **MariaDB Databases**):
1. Buat database, misalnya `lonjoboko` → nama lengkapnya jadi `USERCPANEL_lonjoboko`
2. Buat user database dengan kata sandi yang kuat
3. Tambahkan user ke database dengan **ALL PRIVILEGES**

Catat: nama database, nama user, dan kata sandinya.

### 1.3 SSL
Buka **SSL/TLS Status** atau **Let's Encrypt**, lalu aktifkan untuk `lonjoboko-rescue.web.id` dan `www.lonjoboko-rescue.web.id`.
Fitur GPS ("Lokasi saya") hanya berjalan lewat **https**.

### 1.4 Akses SSH
Buka **SSH Access** → **Manage SSH Keys**:
1. Klik **Import Key**
2. Nama: `lonjoboko_deploy`
3. Tempel isi file **`~/.ssh/lonjoboko_deploy.pub`** (kunci **publik**, yang berakhiran `.pub`) di kolom *Public Key*
4. Simpan, lalu klik **Manage → Authorize**

Cara melihat isi kunci publik di laptop (Mac):
```bash
cat ~/.ssh/lonjoboko_deploy.pub
```

Kalau menu SSH Access tidak ada atau SSH belum aktif, minta CS Rumahweb mengaktifkannya. Catat **alamat host** dan **port SSH** dari email aktivasi atau dari CS.

Coba masuk dari laptop:
```bash
ssh -p PORT -i ~/.ssh/lonjoboko_deploy USERCPANEL@HOST
```

---

## Bagian 2. Pasang aplikasi di server (sekali saja)

Masuk SSH seperti di atas, lalu jalankan:

```bash
cd ~
git clone --branch production https://github.com/NahwaKakaa/Pengabdian-Lonjoboko-Rescue.git lonjoboko-rescue
cd lonjoboko-rescue

php -v              # pastikan 8.2 atau lebih baru
composer -V         # pastikan Composer tersedia

composer install --no-dev --prefer-dist --optimize-autoloader
cp .env.example .env
php artisan key:generate
nano .env           # isi sesuai bagian 2.1
```

Kalau `php -v` menampilkan versi lama, coba `/opt/alt/php83/usr/bin/php -v`. Kalau berhasil, pakai perintah itu sebagai pengganti `php`, dan isi variabel `PHP_BIN` di GitHub (bagian 3) dengan path tersebut.

Kalau `composer` tidak ditemukan, pasang di folder akun:
```bash
cd ~ && curl -sS https://getcomposer.org/installer | php
# lalu gunakan: php ~/composer.phar install ...
```
dan isi variabel `COMPOSER_BIN` di GitHub dengan `php /home/USERCPANEL/composer.phar`.

### 2.1 Isi `.env` produksi

```env
APP_NAME="LONJOBOKO-RESCUE"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://lonjoboko-rescue.web.id
APP_TIMEZONE=Asia/Makassar

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=USERCPANEL_lonjoboko
DB_USERNAME=USERCPANEL_namauser
DB_PASSWORD=katasandi-database

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=public

ADMIN_NAME="Admin Desa Lonjoboko"
ADMIN_EMAIL=email-admin-desa@contoh.com
ADMIN_PASSWORD=katasandi-awal-yang-kuat

MAP_CENTER_LAT=-5.xxxx      # koordinat kantor desa
MAP_CENTER_LNG=119.xxxx
MAP_ZOOM=15
```

Simpan di nano: `Ctrl+O`, Enter, lalu `Ctrl+X`.

### 2.2 Database dan penyimpanan

```bash
php artisan migrate --seed --force
php artisan storage:link
php artisan optimize
```

**Jangan** menjalankan `DemoMapSeeder` di server produksi, kecuali untuk latihan kader. Kalau terlanjur, hapus dengan `php artisan lonjoboko:hapus-contoh`.

### 2.3 Arahkan domain ke folder `public`

Domain utama akun cPanel memakai folder `public_html`. Ganti folder itu dengan tautan ke folder `public` aplikasi:

```bash
cd ~
mv public_html public_html_lama
ln -s ~/lonjoboko-rescue/public ~/public_html
```

Dengan begitu, file seperti `.env` tidak bisa dibuka dari browser.

### 2.4 Tampilan (CSS/JS)

Deploy pertama lewat GitHub Actions akan mengunggah folder `public/build` secara otomatis. Sebelum itu, halaman bisa tampil error karena file tampilan belum ada. Itu normal.

### 2.5 Cron job (opsional)

Buka **Cron Jobs** di cPanel, tambahkan (setiap menit):
```
cd /home/USERCPANEL/lonjoboko-rescue && php artisan schedule:run >> /dev/null 2>&1
```

---

## Bagian 3. Hubungkan GitHub ke server

Buka repo di GitHub → **Settings** → **Secrets and variables** → **Actions**.

### 3.1 Secret (tab *Secrets*)

| Nama | Isi |
|---|---|
| `SSH_PRIVATE_KEY` | Seluruh isi file **`~/.ssh/lonjoboko_deploy`** (kunci **rahasia**, tanpa `.pub`), termasuk baris `-----BEGIN ...` dan `-----END ...` |

Cara menyalin di Mac:
```bash
pbcopy < ~/.ssh/lonjoboko_deploy
```

Kunci rahasia hanya ditempel di GitHub Secrets. Jangan kirim lewat chat, email, atau simpan di repo.

### 3.2 Variables (tab *Variables*)

| Nama | Contoh isi |
|---|---|
| `SSH_HOST` | alamat server dari Rumahweb |
| `SSH_PORT` | port SSH dari Rumahweb |
| `SSH_USER` | username cPanel |
| `APP_PATH` | `/home/USERCPANEL/lonjoboko-rescue` |
| `PHP_BIN` | *(opsional)* isi hanya kalau `php` di server bukan versi 8.2+ |
| `COMPOSER_BIN` | *(opsional)* isi hanya kalau `composer` tidak tersedia |

### 3.3 Uji deploy

Buka tab **Actions** → **Deploy ke hosting** → **Run workflow** (pilih branch `production`).
Kalau hijau semua, buka https://lonjoboko-rescue.web.id.

---

## Masalah yang sering terjadi

| Gejala | Penyebab dan solusi |
|---|---|
| Actions gagal di langkah "Periksa pengaturan" | Ada secret/variable yang belum diisi. Pesan error menyebutkan namanya |
| `Permission denied (publickey)` | Kunci publik belum di-*Authorize* di cPanel, atau secret `SSH_PRIVATE_KEY` tidak lengkap |
| `Connection timed out` | Port SSH salah, atau SSH belum diaktifkan oleh Rumahweb |
| Halaman putih / error 500 | Lihat log: `tail -n 50 ~/lonjoboko-rescue/storage/logs/laravel.log` |
| Tampilan berantakan tanpa warna | Folder `public/build` belum terunggah. Jalankan ulang workflow deploy |
| Foto yang diunggah tidak muncul | Jalankan `php artisan storage:link` di server |
| Web tertahan di mode pemeliharaan | Masuk SSH, jalankan `cd ~/lonjoboko-rescue && php artisan up` |
