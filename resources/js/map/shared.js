import L from 'leaflet';

/**
 * Warna dan ikon peta yang dipakai di halaman publik dan panel admin,
 * supaya warga dan kader membaca simbol yang sama.
 */
export const COLORS = {
    banjir: '#1d6fd1',
    longsor: '#b4580e',
    safe: '#16825d',
    route: '#c2185b',
    facility: '#475569',
    vulnerable: '#7c3aed',
};

export const RISK_OPACITY = { rendah: 0.18, sedang: 0.3, tinggi: 0.45 };

export function round6(value) {
    return Math.round(Number(value) * 1e6) / 1e6;
}

export function createBaseMap(element, { center, zoom, tileUrl, attribution, scrollWheelZoom = true, boundaryOverlay = null }) {
    const map = L.map(element, { 
        scrollWheelZoom: true,
        doubleClickZoom: true,
        touchZoom: true,
        boxZoom: true,
        zoomControl: true,
        // Langkah zoom setengah supaya wilayah desa mengisi peta lebih penuh.
        zoomSnap: 0.5,
    }).setView([center.lat, center.lng], zoom);

    // crossOrigin: ubin bisa disimpan service worker untuk dibuka offline.
    L.tileLayer(tileUrl, { attribution, maxZoom: 19, crossOrigin: true }).addTo(map);

    // Lingkup area desa sebagai latar (tidak bisa diklik, tidak ikut tergambar/tersnap).
    // Hanya peta form admin yang mengirim boundaryOverlay; peta publik menggambar batasnya sendiri.
    if (boundaryOverlay) {
        map.lrBoundary = addBoundaryOverlay(map, boundaryOverlay);
    }

    // Peta di dalam tab, modal, atau bagian yang dilipat perlu dihitung ulang ukurannya.
    if ('ResizeObserver' in window) {
        new ResizeObserver(() => map.invalidateSize()).observe(element);
    }

    return map;
}

/** Penanda berbentuk pin (tanpa file gambar, aman untuk bundler). */
export function pinIcon(color, glyph = '') {
    return L.divIcon({
        className: 'lr-pin',
        html: `<span class="lr-pin__body" style="--pin:${color}"><span class="lr-pin__glyph">${glyph}</span></span>`,
        iconSize: [30, 40],
        iconAnchor: [15, 38],
        popupAnchor: [0, -34],
        tooltipAnchor: [0, -30],
    });
}

export function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

/** Panjang garis dalam meter. */
export function polylineLength(latlngs) {
    let total = 0;
    for (let i = 1; i < latlngs.length; i++) {
        total += L.latLng(latlngs[i - 1]).distanceTo(L.latLng(latlngs[i]));
    }
    return total;
}

export function formatDistance(meters) {
    if (meters >= 1000) {
        return `${(meters / 1000).toLocaleString('id-ID', { maximumFractionDigits: 2 })} km`;
    }
    return `${Math.round(meters).toLocaleString('id-ID')} m`;
}

/** Perkiraan waktu jalan kaki (±4 km/jam, kondisi darurat). */
export function walkingMinutes(meters) {
    return Math.max(1, Math.round(meters / (4000 / 60)));
}

/**
 * Garis batas desa putus-putus + arsiran tipis, untuk peta form di admin.
 * interactive:false supaya ketukan tetap sampai ke peta (memilih titik / menggambar).
 */
export function addBoundaryOverlay(map, geojson) {
    const layer = L.geoJSON(geojson, {
        interactive: false,
        pmIgnore: true,
        snapIgnore: true,
        style: {
            color: '#0e6b66',
            weight: 2.5,
            opacity: 0.9,
            dashArray: '6, 6',
            fillColor: '#10b981',
            fillOpacity: 0.1,
        },
    }).addTo(map);

    if (!layer.getLayers().length) {
        map.removeLayer(layer);
        return null;
    }

    const legend = L.control({ position: 'bottomleft' });
    legend.onAdd = () => {
        const el = L.DomUtil.create('div', 'lr-boundary-legend');
        el.innerHTML = '<span class="lr-boundary-legend__swatch"></span>Lingkup Area Lonjoboko';
        return el;
    };
    legend.addTo(map);

    return layer;
}

/** Arahkan peta ke seluruh wilayah desa (dipakai saat form masih kosong). */
export function fitBoundary(map, padding = 20) {
    if (!map.lrBoundary) {
        return false;
    }

    const fit = () => map.fitBounds(map.lrBoundary.getBounds(), { padding: [padding, padding] });
    fit();

    // Di form Filament ukuran peta baru final setelah halaman selesai ditata.
    // Ulangi sekali, kecuali pengguna sudah menggeser atau memilih titik.
    let touched = false;
    map.once('dragstart zoomstart click', () => { touched = true; });
    setTimeout(() => {
        if (!touched) {
            map.invalidateSize();
            fit();
        }
    }, 350);

    return true;
}
