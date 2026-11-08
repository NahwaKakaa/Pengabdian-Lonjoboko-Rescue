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

function popup(title, rows = [], photo = null) {
    const body = rows
        .filter(([, value]) => value !== null && value !== undefined && value !== '')
        .map(([label, value]) => `<div><span style="color:#3d5250">${escapeHtml(label)}:</span> ${escapeHtml(value)}</div>`)
        .join('');
    const img = photo ? `<img src="${escapeHtml(photo)}" alt="" style="width:100%;max-width:220px;border-radius:8px;margin-top:6px" loading="lazy">` : '';
    return `<strong style="font-size:15px">${escapeHtml(title)}</strong>${body}${img}`;
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
        banjir: L.featureGroup(),
        longsor: L.featureGroup(),
        points: L.featureGroup(),
        routes: L.featureGroup(),
        facilities: L.featureGroup(),
    };
    const index = { hazard: {}, point: {}, route: {} };

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
            ),
        );
        circle.addTo(layers[z.type] ?? layers.longsor);
        index.hazard[z.id] = circle;
    });

    // Titik kumpul.
    (data.points ?? []).forEach((p) => {
        const marker = L.marker([p.lat, p.lng], { icon: pinIcon(COLORS.safe, '✓'), title: p.name, riseOnHover: true }).bindPopup(
            popup(
                p.name,
                [
                    ['Jenis', p.typeLabel],
                    ['Daya tampung', p.capacity ? `±${p.capacity} orang` : null],
                    ['Alamat', p.address],
                    ['Fasilitas', p.facilities],
                ],
                p.photo,
            ),
        );
        marker.addTo(layers.points);
        index.point[p.id] = marker;
    });

    // Jalur evakuasi.
    (data.routes ?? []).forEach((r) => {
        const meters = polylineLength(r.path);
        const line = L.polyline(r.path, { color: COLORS.route, weight: 5, opacity: 0.9 }).bindPopup(
            popup(r.name, [
                ['Dipakai saat', r.hazardLabel],
                ['Tujuan', r.destination],
                ['Panjang', `${formatDistance(meters)} (±${walkingMinutes(meters)} menit jalan kaki)`],
                ['Petunjuk', r.description],
            ]),
        );
        line.hazard = r.hazard;
        line.addTo(layers.routes);

        // Tanda arah di ujung jalur (tujuan).
        const end = r.path[r.path.length - 1];
        L.circleMarker(end, { radius: 5, color: '#fff', weight: 2, fillColor: COLORS.route, fillOpacity: 1 }).addTo(layers.routes);

        index.route[r.id] = line;
    });

    // Fasilitas.
    (data.facilities ?? []).forEach((f) => {
        L.marker([f.lat, f.lng], { icon: pinIcon(COLORS.facility, FACILITY_GLYPH[f.category] ?? 'U'), title: f.name })
            .bindPopup(
                popup(f.name, [
                    ['Kategori', f.categoryLabel],
                    ['Alamat', f.address],
                    ['Telepon', f.phone],
                ]),
            )
            .addTo(layers.facilities);
    });

    // Lapisan yang tampil saat pertama dibuka.
    const initial = mode === 'evacuation' ? ['banjir', 'longsor', 'points', 'routes'] : ['banjir', 'longsor', 'points', 'facilities'];
    initial.forEach((key) => layers[key].addTo(map));

    // Arahkan peta ke seluruh data yang ada.
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
                            status.textContent = `Titik kumpul terdekat: ${nearest.p.name}, sekitar ${formatDistance(nearest.d)} garis lurus dari posisi Anda. Ikuti jalur evakuasi, bukan garis lurus.`;
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
}

document.querySelectorAll('[data-lr-map]').forEach(initMap);
