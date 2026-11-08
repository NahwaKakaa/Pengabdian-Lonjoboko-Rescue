#!/usr/bin/env bash
#
# Deploy LONJOBOKO-RESCUE di server hosting, dijalankan oleh Cron Job.
#
# Cara kerja:
#   - Mengecek branch `deploy` di GitHub (dibuat otomatis oleh GitHub Actions).
#   - Kalau ada versi baru: mode pemeliharaan, tarik kode, pasang paket,
#     migrasi, optimasi, lalu web dibuka kembali.
#   - Hasilnya dicatat di storage/logs/deploy.log dan dikirim ke Telegram
#     kalau TELEGRAM_BOT_TOKEN dan TELEGRAM_CHAT_ID diisi di .env.
#   - Versi yang gagal tidak dicoba ulang terus-menerus. Push perbaikan,
#     atau jalankan skrip ini dengan --force untuk mencoba lagi.
#
# Contoh Cron Job (tiap 5 menit):
#   */5 * * * * /bin/bash $HOME/lonjoboko-rescue/deploy/server-deploy.sh >/dev/null 2>&1
#
# Menjalankan manual:
#   bash ~/lonjoboko-rescue/deploy/server-deploy.sh           # deploy kalau ada versi baru
#   bash ~/lonjoboko-rescue/deploy/server-deploy.sh --force   # deploy ulang walau tidak ada yang baru
#   bash ~/lonjoboko-rescue/deploy/server-deploy.sh --test-telegram

# Seluruh isi dibungkus fungsi main supaya bash membaca skrip sampai habis
# sebelum menjalankannya. Penting, karena `git reset` di tengah proses
# bisa mengganti file skrip ini sendiri.
main() {
    set -uo pipefail

    APP_DIR="${APP_DIR:-$HOME/lonjoboko-rescue}"
    # Di Cron Job, perintah `php` bisa mengarah ke versi PHP yang berbeda,
    # jadi pakai PHP 8.3 dari PHP Selector kalau tersedia.
    if [ -z "${PHP_BIN:-}" ]; then
        if [ -x /opt/alt/php83/usr/bin/php ]; then
            PHP_BIN=/opt/alt/php83/usr/bin/php
        else
            PHP_BIN=php
        fi
    fi
    COMPOSER_BIN="${COMPOSER_BIN:-$HOME/bin/composer}"
    BRANCH="${DEPLOY_BRANCH:-deploy}"
    STATE_DIR="$HOME/.lonjoboko-deploy"
    LOCK_DIR="$STATE_DIR/lock"
    FAILED_FILE="$STATE_DIR/failed-commit"
    STEP_OUTPUT="$STATE_DIR/last-step.log"
    LOG_FILE="$APP_DIR/storage/logs/deploy.log"

    mkdir -p "$STATE_DIR"
    cd "$APP_DIR" || { echo "Folder aplikasi tidak ditemukan: $APP_DIR"; exit 1; }

    load_telegram_config

    if [ "${1:-}" = "--test-telegram" ]; then
        if [ -z "$TG_TOKEN" ] || [ -z "$TG_CHAT" ]; then
            echo "TELEGRAM_BOT_TOKEN atau TELEGRAM_CHAT_ID belum diisi di .env"
            exit 1
        fi
        notify "🔔 Tes notifikasi deploy LONJOBOKO-RESCUE berhasil. Server: $(hostname)"
        echo "Pesan tes dikirim. Cek Telegram kamu."
        exit 0
    fi

    # Cegah dua deploy berjalan bersamaan (misalnya cron + manual).
    if ! mkdir "$LOCK_DIR" 2>/dev/null; then
        # Kunci yang tertinggal lebih dari 30 menit dianggap sisa proses yang macet.
        if [ -n "$(find "$LOCK_DIR" -maxdepth 0 -mmin +30 2>/dev/null)" ]; then
            rmdir "$LOCK_DIR" 2>/dev/null
            mkdir "$LOCK_DIR" 2>/dev/null || exit 0
        else
            exit 0
        fi
    fi
    trap 'rmdir "$LOCK_DIR" 2>/dev/null' EXIT

    if ! git fetch -q origin "$BRANCH" 2>"$STEP_OUTPUT"; then
        log "Gagal mengecek GitHub: $(tail -n 1 "$STEP_OUTPUT")"
        exit 0
    fi

    local current target subject
    current="$(git rev-parse HEAD)"
    target="$(git rev-parse "origin/$BRANCH")"

    if [ "${1:-}" != "--force" ]; then
        [ "$current" = "$target" ] && exit 0
        if [ -f "$FAILED_FILE" ] && [ "$(cat "$FAILED_FILE")" = "$target" ]; then
            exit 0
        fi
    fi

    subject="$(git log -1 --pretty=%s "$target")"
    local started
    started="$(date +%s)"
    log "Mulai deploy ${target:0:7}: $subject"

    "$PHP_BIN" artisan down --retry=15 >/dev/null 2>&1 || true

    run_step "Tarik kode terbaru" git reset --hard "origin/$BRANCH" || return_failure "$target" "$subject" "Tarik kode terbaru"
    run_step "Pasang paket Composer" "$PHP_BIN" "$COMPOSER_BIN" install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress --no-scripts \
        || return_failure "$target" "$subject" "Pasang paket Composer"
    run_step "Daftarkan paket" "$PHP_BIN" artisan package:discover --ansi || return_failure "$target" "$subject" "Daftarkan paket"
    run_step "Perbarui aset Filament" "$PHP_BIN" artisan filament:upgrade || return_failure "$target" "$subject" "Perbarui aset Filament"
    run_step "Migrasi database" "$PHP_BIN" artisan migrate --force || return_failure "$target" "$subject" "Migrasi database"
    run_step "Optimasi" "$PHP_BIN" artisan optimize || return_failure "$target" "$subject" "Optimasi"
    "$PHP_BIN" artisan filament:optimize >/dev/null 2>&1 || true

    "$PHP_BIN" artisan up >/dev/null 2>&1 || true
    rm -f "$FAILED_FILE"

    local duration=$(( $(date +%s) - started ))
    log "Deploy ${target:0:7} berhasil (${duration} detik)"
    notify "🚀 LONJOBOKO-RESCUE: web live sudah terupdate.
Versi: ${target:0:7} · ${subject}
Durasi: ${duration} detik
https://lonjoboko-rescue.web.id"
}

load_telegram_config() {
    TG_TOKEN=""
    TG_CHAT=""
    [ -f .env ] || return 0
    TG_TOKEN="$(grep -E '^TELEGRAM_BOT_TOKEN=' .env | tail -n 1 | cut -d= -f2- | tr -d '"'"'"' \r')"
    TG_CHAT="$(grep -E '^TELEGRAM_CHAT_ID=' .env | tail -n 1 | cut -d= -f2- | tr -d '"'"'"' \r')"
}

notify() {
    [ -n "$TG_TOKEN" ] && [ -n "$TG_CHAT" ] || return 0
    curl -s -o /dev/null --max-time 15 \
        "https://api.telegram.org/bot${TG_TOKEN}/sendMessage" \
        --data-urlencode "chat_id=${TG_CHAT}" \
        --data-urlencode "text=$1" \
        --data-urlencode "disable_web_page_preview=true" || log "Gagal mengirim notifikasi Telegram"
}

log() {
    mkdir -p "$(dirname "$LOG_FILE")"
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOG_FILE"
}

run_step() {
    local name="$1"
    shift
    log "→ $name"
    if "$@" >"$STEP_OUTPUT" 2>&1; then
        return 0
    fi
    cat "$STEP_OUTPUT" >> "$LOG_FILE"
    return 1
}

return_failure() {
    local target="$1" subject="$2" step="$3"
    "$PHP_BIN" artisan up >/dev/null 2>&1 || true
    echo "$target" > "$FAILED_FILE"
    log "Deploy ${target:0:7} GAGAL di langkah: $step"

    local detail
    detail="$(tail -n 8 "$STEP_OUTPUT" | cut -c1-200)"
    notify "⚠️ LONJOBOKO-RESCUE: deploy gagal di langkah \"${step}\".
Versi: ${target:0:7} · ${subject}
Web tetap bisa diakses, tapi mungkin belum lengkap terupdate.

Pesan error terakhir:
${detail}

Log lengkap: storage/logs/deploy.log"
    exit 1
}

main "$@"; exit
