/*
 * Service worker LONJOBOKO RESCUE: supaya web tetap bisa dibuka saat sinyal
 * lemah atau putus.
 *
 * - Halaman penting disimpan saat pertama kali dipasang (Beranda, Info Darurat,
 *   Jalur Evakuasi, Peta Risiko, Edukasi, Poster).
 * - Halaman lain disimpan setelah pernah dibuka.
 * - Halaman: ambil dari internet dulu (data terbaru); kalau gagal atau terlalu
 *   lama, pakai salinan tersimpan.
 * - CSS/JS/gambar: pakai salinan tersimpan (nama file build sudah berversi).
 * - Ubin peta OpenStreetMap: hanya yang pernah dilihat warga yang disimpan
 *   (sesuai kebijakan OSM, tidak mengunduh massal).
 * - Panel admin tidak disimpan.
 */
const VERSION = 'v1';
const PAGES = `lr-pages-${VERSION}`;
const ASSETS = `lr-assets-${VERSION}`;
const TILES = `lr-tiles-${VERSION}`;
const FONTS = `lr-fonts-${VERSION}`;
const KEEP = [PAGES, ASSETS, TILES, FONTS];

const CORE_PAGES = ['/', '/darurat', '/jalur-evakuasi', '/peta-risiko', '/edukasi', '/poster'];
const CORE_FILES = [
    '/images/hero-mountains-day.jpg',
    '/images/hero-mountains-night.jpg',
    '/icons/icon-192.png',
    '/icons/favicon.svg',
    '/manifest.webmanifest',
];
// Entri build Vite yang dipakai halaman publik (bukan admin).
const PUBLIC_ENTRIES = [
    'resources/css/app.css',
    'resources/js/app.js',
    'resources/js/public-map.js',
    'resources/css/poster.css',
    'resources/js/poster.js',
];
const MAX_TILES = 1500;
const NETWORK_TIMEOUT = 4500;

self.addEventListener('install', (event) => {
    event.waitUntil(
        (async () => {
            const assets = await caches.open(ASSETS);
            await Promise.allSettled(CORE_FILES.map((url) => assets.add(url)));

            // File build (nama berversi) dibaca dari manifest Vite.
            try {
                const manifest = await (await fetch('/build/manifest.json', { cache: 'no-store' })).json();
                const files = new Set();
                const collect = (key) => {
                    const entry = manifest[key];
                    if (!entry || files.has(entry.file)) return;
                    files.add(entry.file);
                    (entry.css || []).forEach((css) => files.add(css));
                    (entry.assets || []).forEach((asset) => files.add(asset));
                    (entry.imports || []).forEach(collect);
                };
                PUBLIC_ENTRIES.forEach(collect);
                await Promise.allSettled([...files].map((file) => assets.add(`/build/${file}`)));
            } catch (e) {}

            const pages = await caches.open(PAGES);
            await Promise.allSettled(CORE_PAGES.map((url) => pages.add(new Request(url, { credentials: 'same-origin' }))));

            await self.skipWaiting();
        })(),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        (async () => {
            const names = await caches.keys();
            await Promise.all(names.filter((n) => n.startsWith('lr-') && !KEEP.includes(n)).map((n) => caches.delete(n)));
            await self.clients.claim();
        })(),
    );
});

const isAdmin = (url) => /^\/(admin|livewire|filament|up)(\/|$)/.test(url.pathname);

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') return;

    const url = new URL(request.url);

    // Halaman
    if (request.mode === 'navigate') {
        if (url.origin !== self.location.origin || isAdmin(url)) return;
        event.respondWith(networkFirstPage(request));

        return;
    }

    if (url.origin === self.location.origin) {
        if (isAdmin(url)) return;

        if (/^\/(build|images|icons|storage|vendor)\//.test(url.pathname) || url.pathname === '/manifest.webmanifest') {
            event.respondWith(cacheFirst(request, ASSETS));
        }

        return;
    }

    // Ubin peta OpenStreetMap
    if (/(^|\.)tile\.openstreetmap\.org$/.test(url.hostname)) {
        event.respondWith(cacheFirst(request, TILES, MAX_TILES));

        return;
    }

    // Font Google
    if (url.hostname === 'fonts.googleapis.com' || url.hostname === 'fonts.gstatic.com') {
        event.respondWith(staleWhileRevalidate(request, FONTS));
    }
});

async function networkFirstPage(request) {
    const cache = await caches.open(PAGES);
    const key = cacheKey(request);

    try {
        const response = await withTimeout(fetch(request), NETWORK_TIMEOUT);

        if (response.ok && response.type === 'basic') {
            cache.put(key, response.clone());
        }

        return response;
    } catch (e) {
        const cached = (await cache.match(key, { ignoreSearch: true })) || (await cache.match('/darurat')) || (await cache.match('/'));

        return cached || offlineResponse();
    }
}

async function cacheFirst(request, cacheName, limit) {
    const cache = await caches.open(cacheName);
    const cached = await cache.match(request);

    if (cached) return cached;

    try {
        const response = await fetch(request);

        if (response.ok || response.type === 'opaque') {
            cache.put(request, response.clone()).then(() => limit && trim(cacheName, limit));
        }

        return response;
    } catch (e) {
        return cached || Response.error();
    }
}

async function staleWhileRevalidate(request, cacheName) {
    const cache = await caches.open(cacheName);
    const cached = await cache.match(request);
    const network = fetch(request)
        .then((response) => {
            if (response.ok || response.type === 'opaque') cache.put(request, response.clone());

            return response;
        })
        .catch(() => cached);

    return cached || network;
}

// Halaman disimpan tanpa parameter (mis. ?sumber=aplikasi), kecuali halaman berparameter seperti ?kategori=.
function cacheKey(request) {
    const url = new URL(request.url);
    url.searchParams.delete('sumber');

    return url.toString();
}

function withTimeout(promise, ms) {
    return new Promise((resolve, reject) => {
        const timer = setTimeout(() => reject(new Error('timeout')), ms);
        promise.then(
            (value) => {
                clearTimeout(timer);
                resolve(value);
            },
            (error) => {
                clearTimeout(timer);
                reject(error);
            },
        );
    });
}

async function trim(cacheName, limit) {
    const cache = await caches.open(cacheName);
    const keys = await cache.keys();

    for (let i = 0; i < keys.length - limit; i++) {
        await cache.delete(keys[i]);
    }
}

function offlineResponse() {
    const html = `<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Sedang offline · LONJOBOKO RESCUE</title>
<style>body{margin:0;min-height:100vh;display:grid;place-items:center;background:#062b29;color:#e6f4f1;font-family:system-ui,sans-serif;padding:24px}
main{max-width:28rem;text-align:center}h1{font-size:1.4rem;margin:0 0 .5rem}p{color:#a3c9c1;line-height:1.6}
.n{display:grid;gap:.5rem;margin:1.25rem 0;text-align:left}.n a{display:flex;justify-content:space-between;padding:.8rem 1rem;border-radius:.8rem;background:rgba(255,255,255,.08);color:#fff;text-decoration:none;font-weight:700}
.n b{color:#fca5a5;font-size:1.2rem}</style></head><body><main>
<h1>Sedang tidak ada internet</h1><p>Halaman ini belum tersimpan di HP Anda. Saat darurat, hubungi nomor berikut:</p>
<div class="n"><a href="tel:112">Panggilan darurat terpadu <b>112</b></a><a href="tel:119">Ambulans <b>119</b></a><a href="tel:115">Basarnas <b>115</b></a></div>
<p>Buka lagi saat ada sinyal supaya peta dan informasi desa tersimpan untuk dibuka tanpa internet.</p></main></body></html>`;

    return new Response(html, { status: 200, headers: { 'Content-Type': 'text/html; charset=utf-8' } });
}
