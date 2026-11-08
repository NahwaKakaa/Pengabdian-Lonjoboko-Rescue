/**
 * Peta di halaman publik: Peta Risiko dan Jalur Evakuasi.
 *
 * Data dibaca dari <script type="application/json" id="map-data">
 * yang hanya berisi data terverifikasi.
 */
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import {
    COLORS,
    RISK_OPACITY,
    createBaseMap,
    escapeHtml,
    formatDistance,
    pinIcon,
    polylineLength,
    walkingMinutes,
} from './map/shared';

const FACILITY_GLYPH = { kesehatan: '+', pendidikan: 'S', ibadah: 'I', pemerintahan: 'K', umum: 'U' };

function popup(title, rows = [], photo = null, actions = []) {
    const body = rows
        .filter(([, value]) => value !== null && value !== undefined && value !== '')
        .map(([label, value]) => `<div style="margin-top:2px"><span style="color:#64748b;font-size:11.5px">${escapeHtml(label)}:</span> <span style="font-size:12.5px;font-weight:600;color:#0f172a">${escapeHtml(value)}</span></div>`)
        .join('');
    const img = photo ? `<img src="${escapeHtml(photo)}" alt="" style="width:100%;max-width:240px;border-radius:10px;margin-top:8px;box-shadow:0 2px 6px rgba(0,0,0,0.15)" loading="lazy">` : '';
    const actionHtml = actions.length > 0 
        ? `<div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:10px;padding-top:10px;border-top:1px solid #e2e8f0">${actions.join('')}</div>` 
        : '';
    return `<div style="min-width:220px;max-width:280px"><strong style="font-size:14.5px;display:block;margin-bottom:6px;color:#062b29;line-height:1.25">${escapeHtml(title)}</strong>${body}${img}${actionHtml}</div>`;
}

function initMap(element) {
    const dataEl = document.getElementById('map-data');
    if (!dataEl) {
        return;
    }

    const data = JSON.parse(dataEl.textContent);
    const mode = element.dataset.lrMap;
    const status = document.querySelector('[data-map-status]');
    const map = createBaseMap(element, data);

    const layers = {
        boundary: L.featureGroup(),
        banjir: L.featureGroup(),
        longsor: L.featureGroup(),
        points: L.featureGroup(),
        routes: L.featureGroup(),
        facilities: L.featureGroup(),
    };
    const index = { hazard: {}, point: {}, route: {} };

    // Lingkup area administratif Desa Lonjoboko (Polygon batas desa).
    let boundaryLayer = null;
    if (data.boundary) {
        boundaryLayer = L.geoJSON(data.boundary, {
            style: {
                color: '#0e6b66',
                weight: 2.5,
                opacity: 0.95,
                dashArray: '6, 6',
                fillColor: '#10b981',
                fillOpacity: 0.12,
            },
            onEachFeature: (feature, layer) => {
                const p = feature.properties ?? {};
                layer.bindPopup(
                    popup(
                        p.name ?? 'Desa Lonjoboko',
                        [
                            ['Kecamatan', p.district ?? 'Parangloe'],
                            ['Kabupaten', p.regency ?? 'Gowa'],
                            ['Provinsi', p.province ?? 'Sulawesi Selatan'],
                            ['Keterangan', 'Lingkup Wilayah Administratif Desa'],
                        ],
                    ),
                );
            },
        });
        boundaryLayer.addTo(layers.boundary);
    }

    // Lokasi rawan: lingkaran perkiraan area + titik tengah.
    (data.hazards ?? []).forEach((z) => {
        const color = COLORS[z.type] ?? COLORS.longsor;
        const faint = mode === 'evacuation';
        const circle = L.circle([z.lat, z.lng], {
            radius: z.radius || 100,
            color,
            weight: faint ? 1 : 2,
            fillColor: color,
            fillOpacity: faint ? 0.12 : (RISK_OPACITY[z.level] ?? 0.25),
        }).bindPopup(
            popup(
                z.name,
                [
                    ['Jenis', z.typeLabel],
                    ['Kerawanan', z.levelLabel],
                    ['Keterangan', z.description],
                ],
                z.photo,
                [
                    `<button type="button" class="popup-btn popup-btn-primary" data-action="evac-from" data-lat="${z.lat}" data-lng="${z.lng}" data-name="${escapeHtml(z.name)}"><svg viewBox="0 0 24 24" style="width:14px;height:14px" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> Evakuasi ke Titik Aman</button>`,
                    `<a href="https://www.google.com/maps/search/?api=1&query=${z.lat},${z.lng}" target="_blank" rel="noopener" class="popup-btn popup-btn-gmaps"><svg viewBox="0 0 24 24" style="width:13px;height:13px" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a8 8 0 00-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 00-8-8z"/><circle cx="12" cy="10" r="3"/></svg> Buka Google Maps</a>`,
                ],
            ),
        );
        circle.addTo(layers[z.type] ?? layers.longsor);
        index.hazard[z.id] = circle;
    });

    // Titik kumpul.
    (data.points ?? []).forEach((p) => {
        const marker = L.marker([p.lat, p.lng], {
            icon: pinIcon(COLORS.safe, '<svg viewBox="0 0 24 24" style="width:14px;height:14px;display:block" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>'),
            title: p.name,
            riseOnHover: true,
        }).bindPopup(
            popup(
                p.name,
                [
                    ['Jenis', p.typeLabel],
                    ['Daya tampung', p.capacity ? `±${p.capacity} orang` : null],
                    ['Alamat', p.address],
                    ['Fasilitas', p.facilities],
                ],
                p.photo,
                [
                    `<button type="button" class="popup-btn popup-btn-primary" data-action="route-to" data-lat="${p.lat}" data-lng="${p.lng}" data-name="${escapeHtml(p.name)}"><svg viewBox="0 0 24 24" style="width:14px;height:14px" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg> Petunjuk Jalur</button>`,
                    `<a href="https://www.google.com/maps/dir/?api=1&destination=${p.lat},${p.lng}" target="_blank" rel="noopener" class="popup-btn popup-btn-gmaps"><svg viewBox="0 0 24 24" style="width:13px;height:13px" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a8 8 0 00-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 00-8-8z"/><circle cx="12" cy="10" r="3"/></svg> Buka Google Maps</a>`,
                ],
            ),
        );
        marker.addTo(layers.points);
        index.point[p.id] = marker;
    });

    // Jalur evakuasi.
    (data.routes ?? []).forEach((r) => {
        const meters = polylineLength(r.path);
        const end = r.path[r.path.length - 1];
        const line = L.polyline(r.path, { color: COLORS.route, weight: 5, opacity: 0.9 }).bindPopup(
            popup(
                r.name,
                [
                    ['Dipakai saat', r.hazardLabel],
                    ['Tujuan', r.destination],
                    ['Panjang', `${formatDistance(meters)} (±${walkingMinutes(meters)} menit jalan kaki)`],
                    ['Petunjuk', r.description],
                ],
                null,
                [
                    `<button type="button" class="popup-btn popup-btn-primary" data-action="focus-route" data-route-id="${r.id}"><svg viewBox="0 0 24 24" style="width:14px;height:14px" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg> Sorot Jalur Ini</button>`,
                    `<a href="https://www.google.com/maps/dir/?api=1&destination=${end[0]},${end[1]}" target="_blank" rel="noopener" class="popup-btn popup-btn-gmaps"><svg viewBox="0 0 24 24" style="width:13px;height:13px" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a8 8 0 00-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 00-8-8z"/><circle cx="12" cy="10" r="3"/></svg> Buka Google Maps</a>`,
                ],
            ),
        );
        line.hazard = r.hazard;
        line.addTo(layers.routes);

        // Tanda arah di ujung jalur (tujuan).
        L.circleMarker(end, { radius: 5, color: '#fff', weight: 2, fillColor: COLORS.route, fillOpacity: 1 }).addTo(layers.routes);

        index.route[r.id] = line;
    });

    // Fasilitas.
    (data.facilities ?? []).forEach((f) => {
        L.marker([f.lat, f.lng], { icon: pinIcon(COLORS.facility, FACILITY_GLYPH[f.category] ?? 'U'), title: f.name })
            .bindPopup(
                popup(
                    f.name,
                    [
                        ['Kategori', f.categoryLabel],
                        ['Alamat', f.address],
                        ['Telepon', f.phone],
                    ],
                    null,
                    [
                        `<button type="button" class="popup-btn popup-btn-primary" data-action="route-to" data-lat="${f.lat}" data-lng="${f.lng}" data-name="${escapeHtml(f.name)}"><svg viewBox="0 0 24 24" style="width:14px;height:14px" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg> Petunjuk Arah</button>`,
                        `<a href="https://www.google.com/maps/dir/?api=1&destination=${f.lat},${f.lng}" target="_blank" rel="noopener" class="popup-btn popup-btn-gmaps"><svg viewBox="0 0 24 24" style="width:13px;height:13px" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a8 8 0 00-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 00-8-8z"/><circle cx="12" cy="10" r="3"/></svg> Buka Google Maps</a>`,
                    ],
                ),
            )
            .addTo(layers.facilities);
    });

    // Lapisan yang tampil saat pertama dibuka.
    const initial = mode === 'evacuation' 
        ? ['boundary', 'banjir', 'longsor', 'points', 'routes'] 
        : ['boundary', 'banjir', 'longsor', 'points', 'facilities'];
    initial.forEach((key) => layers[key].addTo(map));

    // Arahkan peta ke lingkup wilayah desa bila tersedia, atau ke seluruh data yang ada.
    if (boundaryLayer && boundaryLayer.getLayers().length) {
        try {
            map.fitBounds(boundaryLayer.getBounds(), { padding: [25, 25] });
        } catch {
            /* fallback */
        }
    } else {
        const visible = L.featureGroup(initial.map((key) => layers[key]));
        if (visible.getLayers().some((group) => group.getLayers().length)) {
            try {
                map.fitBounds(visible.getBounds(), { padding: [30, 30], maxZoom: 16 });
            } catch {
                /* tidak ada data dengan koordinat */
            }
        } else if (status) {
            status.textContent = 'Belum ada data peta yang diverifikasi. Peta menampilkan wilayah sekitar desa.';
        }
    }

    // Tombol lapisan (Peta Risiko).
    document.querySelectorAll('[data-layer-toggle]').forEach((input) => {
        const layer = layers[input.dataset.layerToggle];
        if (!layer) {
            return;
        }
        input.checked = map.hasLayer(layer);
        input.addEventListener('change', () => (input.checked ? layer.addTo(map) : map.removeLayer(layer)));
    });

    // Saring jalur menurut jenis bencana (Jalur Evakuasi).
    const filterButtons = document.querySelectorAll('[data-route-filter]');
    filterButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const value = button.dataset.routeFilter;
            filterButtons.forEach((b) => b.setAttribute('aria-pressed', String(b === button)));

            Object.values(index.route).forEach((line) => {
                const show = value === 'all' || line.hazard === value || line.hazard === 'semua';
                line.setStyle({ opacity: show ? 0.9 : 0.12 });
            });

            document.querySelectorAll('[data-route-card]').forEach((card) => {
                const type = card.dataset.routeCard;
                card.hidden = !(value === 'all' || type === value || type === 'semua');
            });
        });
    });

    // Tombol "Tunjukkan di peta" pada daftar.
    const focus = (layer) => {
        if (!layer) {
            return;
        }
        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
        if (layer.getBounds) {
            map.fitBounds(layer.getBounds(), { padding: [40, 40], maxZoom: 17 });
        } else {
            map.setView(layer.getLatLng(), 17);
        }
        layer.openPopup();
    };
    document.querySelectorAll('[data-focus-hazard]').forEach((b) => b.addEventListener('click', () => focus(index.hazard[b.dataset.focusHazard])));
    document.querySelectorAll('[data-focus-point]').forEach((b) => b.addEventListener('click', () => focus(index.point[b.dataset.focusPoint])));
    document.querySelectorAll('[data-focus-route]').forEach((b) => b.addEventListener('click', () => focus(index.route[b.dataset.focusRoute])));

    // Lapisan jalur aktif & penunjuk arah interaktif
    const activeRouteGroup = L.featureGroup().addTo(map);
    let currentNavBounds = null;
    let userLocation = null;

    const navCard = document.querySelector('[data-nav-card]');
    const navTitle = document.querySelector('[data-nav-title]');
    const navMeta = document.querySelector('[data-nav-meta]');
    const navGmaps = document.querySelector('[data-nav-gmaps]');
    const navClose = document.querySelector('[data-nav-close]');
    const navZoom = document.querySelector('[data-nav-zoom]');

    function showNavCard(title, meta, gmapsUrl, bounds) {
        currentNavBounds = bounds;
        if (navTitle) navTitle.textContent = title;
        if (navMeta) navMeta.textContent = meta;
        if (navGmaps) navGmaps.href = gmapsUrl;
        if (navCard) navCard.classList.remove('hidden');
    }

    function clearActiveRoute() {
        activeRouteGroup.clearLayers();
        currentNavBounds = null;
        if (navCard) navCard.classList.add('hidden');
    }

    if (navClose) {
        navClose.addEventListener('click', clearActiveRoute);
    }

    if (navZoom) {
        navZoom.addEventListener('click', () => {
            if (currentNavBounds) {
                map.fitBounds(currentNavBounds, { padding: [50, 50], maxZoom: 17 });
            }
        });
    }

    // Fungsi mengarahkan rute menuju lokasi tertentu (titik kumpul / fasilitas)
    function routeToTarget(targetLat, targetLng, targetName) {
        clearActiveRoute();
        const targetLatLng = L.latLng(targetLat, targetLng);

        // Cari apakah ada jalur evakuasi resmi yang berujung pada titik ini
        let matchingRoute = (data.routes ?? []).find((r) => {
            if (!r.path || r.path.length === 0) return false;
            const end = r.path[r.path.length - 1];
            return (
                r.destination === targetName ||
                targetLatLng.distanceTo(L.latLng(end[0], end[1])) < 65
            );
        });

        if (matchingRoute) {
            // Tampilkan jalur evakuasi resmi dengan garis bersinar dan animasi
            L.polyline(matchingRoute.path, {
                color: '#ffffff',
                weight: 9,
                opacity: 0.9,
            }).addTo(activeRouteGroup);

            const activeLine = L.polyline(matchingRoute.path, {
                color: '#c2185b',
                weight: 5,
                opacity: 1,
                className: 'route-guidance-line',
            }).addTo(activeRouteGroup);

            const meters = polylineLength(matchingRoute.path);
            const bounds = activeLine.getBounds();
            map.fitBounds(bounds, { padding: [60, 60], maxZoom: 17 });

            const metaText = `Jalur Resmi: ${matchingRoute.name} · ${formatDistance(meters)} (±${walkingMinutes(meters)} mnt jalan kaki)`;
            const gmaps = `https://www.google.com/maps/dir/?api=1&destination=${targetLat},${targetLng}`;
            showNavCard(targetName, metaText, gmaps, bounds);
            if (status) status.textContent = metaText;
            return;
        }

        // Jika tidak ada jalur terdaftar, buat garis petunjuk arah dari posisi pengguna atau pusat desa
        const startPoint = userLocation || L.latLng(data.center.lat, data.center.lng);
        const distance = startPoint.distanceTo(targetLatLng);

        L.polyline([startPoint, targetLatLng], {
            color: '#ffffff',
            weight: 8,
            opacity: 0.9,
        }).addTo(activeRouteGroup);

        const guideLine = L.polyline([startPoint, targetLatLng], {
            color: '#0e6b66',
            weight: 4.5,
            opacity: 1,
            dashArray: '8, 8',
            className: 'route-guidance-line',
        }).addTo(activeRouteGroup);

        const bounds = guideLine.getBounds();
        map.fitBounds(bounds, { padding: [60, 60], maxZoom: 17 });

        const fromLabel = userLocation ? 'Posisi Anda' : 'Pusat Desa Lonjoboko';
        const metaText = `Dari ${fromLabel} · ±${formatDistance(distance)} (${walkingMinutes(distance)} menit jalan kaki)`;
        const gmaps = `https://www.google.com/maps/dir/?api=1&destination=${targetLat},${targetLng}`;
        showNavCard(targetName, metaText, gmaps, bounds);
        if (status) status.textContent = `Menuju: ${targetName} · ${metaText}`;
    }

    // Fungsi mencari dan mengarahkan evakuasi dari area bahaya ke titik kumpul aman terdekat
    function evacFromHazard(hazardLat, hazardLng, hazardName) {
        clearActiveRoute();
        const hazardLatLng = L.latLng(hazardLat, hazardLng);

        let nearest = null;
        (data.points ?? []).forEach((p) => {
            const d = hazardLatLng.distanceTo(L.latLng(p.lat, p.lng));
            if (!nearest || d < nearest.d) {
                nearest = { p, d };
            }
        });

        if (!nearest) {
            if (status) status.textContent = 'Belum ada titik kumpul terverifikasi di sekitar lokasi rawan ini.';
            return;
        }

        const targetLatLng = L.latLng(nearest.p.lat, nearest.p.lng);
        let matchingRoute = (data.routes ?? []).find((r) => {
            if (!r.path || r.path.length < 2) return false;
            const start = r.path[0];
            const end = r.path[r.path.length - 1];
            return (
                hazardLatLng.distanceTo(L.latLng(start[0], start[1])) < 300 &&
                targetLatLng.distanceTo(L.latLng(end[0], end[1])) < 120
            );
        });

        let bounds;
        let meters;
        if (matchingRoute) {
            L.polyline(matchingRoute.path, {
                color: '#ffffff',
                weight: 9,
                opacity: 0.9,
            }).addTo(activeRouteGroup);

            const activeLine = L.polyline(matchingRoute.path, {
                color: '#c2185b',
                weight: 5,
                opacity: 1,
                className: 'route-guidance-line',
            }).addTo(activeRouteGroup);

            meters = polylineLength(matchingRoute.path);
            bounds = activeLine.getBounds();
        } else {
            L.polyline([hazardLatLng, targetLatLng], {
                color: '#ffffff',
                weight: 8,
                opacity: 0.9,
            }).addTo(activeRouteGroup);

            const guideLine = L.polyline([hazardLatLng, targetLatLng], {
                color: '#16825d',
                weight: 4.5,
                opacity: 1,
                dashArray: '8, 8',
                className: 'route-guidance-line',
            }).addTo(activeRouteGroup);

            meters = nearest.d;
            bounds = guideLine.getBounds();
        }

        map.fitBounds(bounds, { padding: [60, 60], maxZoom: 17 });
        const metaText = `Evakuasi ke: ${nearest.p.name} · ${formatDistance(meters)} (±${walkingMinutes(meters)} mnt jalan kaki)`;
        const gmaps = `https://www.google.com/maps/dir/?api=1&origin=${hazardLat},${hazardLng}&destination=${nearest.p.lat},${nearest.p.lng}`;
        showNavCard(`Evakuasi dari ${hazardName}`, metaText, gmaps, bounds);
        if (status) status.textContent = `Jalur evakuasi: ${metaText}`;
    }

    // Tangani klik tombol aksi di dalam popup secara delegasi
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-action]');
        if (!btn) return;
        const action = btn.dataset.action;

        if (action === 'route-to') {
            const lat = parseFloat(btn.dataset.lat);
            const lng = parseFloat(btn.dataset.lng);
            const name = btn.dataset.name;
            routeToTarget(lat, lng, name);
        } else if (action === 'evac-from') {
            const lat = parseFloat(btn.dataset.lat);
            const lng = parseFloat(btn.dataset.lng);
            const name = btn.dataset.name;
            evacFromHazard(lat, lng, name);
        } else if (action === 'focus-route') {
            const routeId = btn.dataset.routeId;
            const routeObj = index.route[routeId];
            if (routeObj) {
                map.fitBounds(routeObj.getBounds(), { padding: [50, 50], maxZoom: 17 });
                routeObj.openPopup();
            }
        }
    });

    // Fitur klik di mana saja pada peta untuk mencari titik kumpul terdekat dan membuat petunjuk jalur
    map.on('click', (e) => {
        // Abaikan jika klik terjadi pada elemen interaktif lain
        if (e.originalEvent && e.originalEvent.target && e.originalEvent.target.closest && e.originalEvent.target.closest('.leaflet-interactive')) {
            return;
        }

        const clickedLatLng = e.latlng;
        let nearest = null;
        (data.points ?? []).forEach((p) => {
            const d = clickedLatLng.distanceTo(L.latLng(p.lat, p.lng));
            if (!nearest || d < nearest.d) {
                nearest = { p, d };
            }
        });

        if (nearest) {
            const popupContent = popup(
                'Titik yang Anda Pilih',
                [
                    ['Titik Kumpul Terdekat', nearest.p.name],
                    ['Jarak', `${formatDistance(nearest.d)} (±${walkingMinutes(nearest.d)} mnt)`],
                ],
                null,
                [
                    `<button type="button" class="popup-btn popup-btn-primary" data-action="route-to" data-lat="${nearest.p.lat}" data-lng="${nearest.p.lng}" data-name="${escapeHtml(nearest.p.name)}"><svg viewBox="0 0 24 24" style="width:14px;height:14px" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg> Rute ke Titik Kumpul</button>`,
                    `<a href="https://www.google.com/maps/dir/?api=1&destination=${nearest.p.lat},${nearest.p.lng}" target="_blank" rel="noopener" class="popup-btn popup-btn-gmaps"><svg viewBox="0 0 24 24" style="width:13px;height:13px" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a8 8 0 00-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 00-8-8z"/><circle cx="12" cy="10" r="3"/></svg> Buka Google Maps</a>`,
                ]
            );

            L.popup()
                .setLatLng(clickedLatLng)
                .setContent(popupContent)
                .openOn(map);
        }
    });

    // Lokasi saya + titik kumpul terdekat.
    let me = null;
    document.querySelectorAll('[data-locate]').forEach((button) => {
        button.addEventListener('click', () => {
            if (!('geolocation' in navigator)) {
                if (status) status.textContent = 'Perangkat ini tidak mendukung GPS di browser.';
                return;
            }
            if (status) status.textContent = 'Mencari lokasi Anda…';

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const here = L.latLng(pos.coords.latitude, pos.coords.longitude);
                    userLocation = here;
                    if (me) {
                        map.removeLayer(me);
                    }
                    me = L.featureGroup([
                        L.circle(here, { radius: pos.coords.accuracy, color: '#2563eb', weight: 1, fillOpacity: 0.1 }),
                        L.circleMarker(here, { radius: 8, color: '#fff', weight: 3, fillColor: '#2563eb', fillOpacity: 1 }).bindTooltip('Anda di sini'),
                    ]).addTo(map);

                    let nearest = null;
                    (data.points ?? []).forEach((p) => {
                        const d = here.distanceTo([p.lat, p.lng]);
                        if (!nearest || d < nearest.d) {
                            nearest = { p, d };
                        }
                    });

                    if (nearest) {
                        map.fitBounds(L.latLngBounds([here, [nearest.p.lat, nearest.p.lng]]), { padding: [50, 50], maxZoom: 17 });
                        if (status) {
                            status.textContent = `Titik kumpul terdekat: ${nearest.p.name}, sekitar ${formatDistance(nearest.d)} garis lurus dari posisi Anda. Tekan "Petunjuk Jalur" untuk rute terarah.`;
                        }
                    } else {
                        map.setView(here, 16);
                        if (status) status.textContent = 'Posisi Anda ditampilkan di peta.';
                    }
                },
                (error) => {
                    if (status) {
                        status.textContent =
                            error.code === error.PERMISSION_DENIED
                                ? 'Izin lokasi ditolak. Izinkan akses lokasi di pengaturan browser untuk memakai fitur ini.'
                                : 'Lokasi tidak ditemukan. Pastikan GPS menyala, lalu coba lagi.';
                    }
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 30000 },
            );
        });
    });

    // Tombol "Lingkup Desa" untuk memusatkan kembali ke batas Desa Lonjoboko.
    document.querySelectorAll('[data-fit-village]').forEach((button) => {
        button.addEventListener('click', () => {
            if (boundaryLayer && boundaryLayer.getLayers().length) {
                map.fitBounds(boundaryLayer.getBounds(), { padding: [30, 30] });
                if (status) status.textContent = 'Menampilkan seluruh lingkup wilayah Desa Lonjoboko.';
            } else if (data.center) {
                map.setView([data.center.lat, data.center.lng], data.zoom ?? 13);
            }
        });
    });
}

document.querySelectorAll('[data-lr-map]').forEach(initMap);
