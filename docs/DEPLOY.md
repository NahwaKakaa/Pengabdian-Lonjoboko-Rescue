# Panduan Deploy LONJOBOKO-RESCUE

Web live: **https://lonjoboko-rescue.web.id**
Hosting: Rumahweb Unlimited M (cPanel), akun `lonz8291`, server Kambaniru.

## Cara kerja

Hosting ini tidak membuka SSH dari luar dan mematikan fungsi `exec`/`proc_open`. Karena itu deploy memakai pola **jemput**: server sendiri yang mengecek GitHub secara berkala.

```
Push ke branch production
        ↓
GitHub Actions: tes → build tampilan → simpan ke branch "deploy"
        ↓  (notifikasi Telegram: ✅ build selesai / ❌ gagal)
Cron Job di server tiap 5 menit menjalankan deploy/server-deploy.sh
        ↓
Ada versi baru? → tarik kode, pasang paket, migrasi, optimasi
        ↓  (notifikasi Telegram: 🚀 web terupdate / ⚠️ gagal)
Web live terupdate (jeda maksimal ±5 menit)
```

| Branch | Fungsi |
|---|---|
| `main` | Pengembangan sehari-hari. Tidak memengaruhi web live |
| `production` | Versi yang ingin dirilis. Push ke sini memicu tes dan build |
| `deploy` | Dibuat otomatis oleh GitHub Actions (kode + hasil build). **Jangan diedit manual** |

### Merilis perubahan

```bash
git checkout production
git merge main
git push origin production
git checkout main
```

---

## Bagian 1. Setup awal server (sudah dikerjakan 8 November 2026)

Catatan ini untuk referensi kalau server perlu dipasang ulang.

1. **PHP Selector**: PHP 8.3, ekstensi `intl`, `zip`, `pdo_mysql`, `mbstring`, `fileinfo`, `gd`, `bcmath`, `dom`, `xmlwriter`, `opcache` aktif.
2. **Database**: `lonz8291_lonjoboko` dengan user `lonz8291_app` (ALL PRIVILEGES).
3. **Composer** dipasang di `~/bin/composer` (hosting tidak menyediakannya):
   ```bash
   mkdir -p ~/bin
   curl -sS https://getcomposer.org/installer -o ~/composer-setup.php
   php ~/composer-setup.php --install-dir=$HOME/bin --filename=composer
   rm ~/composer-setup.php
   echo 'export PATH="$HOME/bin:$PATH"' >> ~/.bashrc
   ```
4. **Aplikasi** di `~/lonjoboko-rescue`:
   ```bash
   git clone --branch production https://github.com/NahwaKakaa/Pengabdian-Lonjoboko-Rescue.git ~/lonjoboko-rescue
   cd ~/lonjoboko-rescue
   composer install --no-dev --prefer-dist --optimize-autoloader --no-scripts
   php artisan package:discover --ansi
   php artisan filament:upgrade
   cp .env.example .env && php artisan key:generate
   nano .env                     # APP_ENV=production, APP_DEBUG=false, APP_URL, DB_*, QUEUE_CONNECTION=sync
   php artisan migrate --seed --force
   ```
   `--no-scripts` wajib karena `proc_open` dimatikan hosting.
5. **Folder unggahan** (`php artisan storage:link` gagal karena `exec` dimatikan):
   ```bash
   ln -s ~/lonjoboko-rescue/storage/app/public ~/lonjoboko-rescue/public/storage
   ```
6. **Domain** diarahkan ke folder `public` aplikasi:
   ```bash
   cd ~ && mv public_html public_html_lama
   ln -s ~/lonjoboko-rescue/public ~/public_html
   cp -r ~/public_html_lama/.well-known ~/lonjoboko-rescue/public/
   cp ~/public_html_lama/.user.ini ~/lonjoboko-rescue/public/
   ```

---

## Bagian 2. Aktifkan deploy otomatis

### 2.1 Buat bot Telegram (opsional, untuk notifikasi)

1. Di Telegram, buka **@BotFather** → `/newbot` → beri nama dan username yang diakhiri `bot`.
2. Simpan **token** yang diberikan. Jangan dibagikan ke siapa pun.
3. Buka bot kamu, tekan **Start**, kirim pesan apa saja.
4. Buka `https://api.telegram.org/bot<TOKEN>/getUpdates` di browser. Angka setelah `"chat":{"id":` adalah **chat ID**.
   Untuk grup: masukkan bot ke grup, kirim pesan di grup, lalu lihat lagi. Chat ID grup diawali tanda minus.

### 2.2 Isi Telegram di server

Di terminal cPanel:
```bash
cd ~/lonjoboko-rescue
nano .env
```
Isi dua baris ini (sudah ada di bagian bawah `.env.example`):
```env
TELEGRAM_BOT_TOKEN=token-dari-botfather
TELEGRAM_CHAT_ID=chat-id-kamu
```
Uji:
```bash
bash ~/lonjoboko-rescue/deploy/server-deploy.sh --test-telegram
```

### 2.3 Isi Telegram di GitHub

Repo → **Settings** → **Secrets and variables** → **Actions** → **New repository secret**:

| Nama | Isi |
|---|---|
| `TELEGRAM_BOT_TOKEN` | token dari BotFather |
| `TELEGRAM_CHAT_ID` | chat ID |

### 2.4 Pasang Cron Job

cPanel → **Cron Jobs** → bagian *Add New Cron Job*:
- **Common Settings**: *Once Per Five Minutes (\*/5 \* \* \* \*)*
- **Command**:
  ```
  /bin/bash /home/lonz8291/lonjoboko-rescue/deploy/server-deploy.sh >/dev/null 2>&1
  ```
- Klik **Add New Cron Job**.

Kalau email cron ingin dimatikan, kosongkan kolom *Cron Email* di bagian atas halaman.

### 2.5 Uji

1. Buka tab **Actions** di GitHub → **Deploy ke hosting** → **Run workflow** (branch `production`).
2. Tunggu notifikasi ✅ dari GitHub di Telegram.
3. Dalam ±5 menit, tunggu notifikasi 🚀 dari server.

Atau jalankan langsung di terminal cPanel tanpa menunggu cron:
```bash
bash ~/lonjoboko-rescue/deploy/server-deploy.sh
tail -n 20 ~/lonjoboko-rescue/storage/logs/deploy.log
```

---

## Perintah berguna di server

| Kebutuhan | Perintah |
|---|---|
| Lihat log deploy | `tail -n 30 ~/lonjoboko-rescue/storage/logs/deploy.log` |
| Lihat error aplikasi | `grep -E "^\[[0-9]{4}-" ~/lonjoboko-rescue/storage/logs/laravel.log \| tail -n 3 \| cut -c1-300` |
| Deploy ulang paksa | `bash ~/lonjoboko-rescue/deploy/server-deploy.sh --force` |
| Uji Telegram | `bash ~/lonjoboko-rescue/deploy/server-deploy.sh --test-telegram` |
| Buka web dari mode pemeliharaan | `cd ~/lonjoboko-rescue && php artisan up` |
| Terapkan perubahan `.env` | `cd ~/lonjoboko-rescue && php artisan optimize` |

## Masalah yang sering terjadi

| Gejala | Penyebab dan solusi |
|---|---|
| Tidak ada notifikasi 🚀 setelah ✅ | Cron Job belum dipasang atau perintahnya salah. Jalankan skrip manual dan lihat log deploy |
| ⚠️ Deploy gagal, lalu tidak dicoba lagi | Disengaja, supaya versi rusak tidak dicoba tiap 5 menit. Push perbaikan, atau jalankan dengan `--force` |
| Error 500 setelah deploy | Lihat error aplikasi (perintah di atas) |
| Tampilan berantakan tanpa warna | Branch `deploy` belum berisi `public/build`. Jalankan ulang workflow di tab Actions |
| Perubahan `.env` tidak berpengaruh | Konfigurasi di-cache. Jalankan `php artisan optimize` |
| Web tertahan di mode pemeliharaan | `cd ~/lonjoboko-rescue && php artisan up` |
