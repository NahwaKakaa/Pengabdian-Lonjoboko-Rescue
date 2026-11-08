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
};

export const RISK_OPACITY = { rendah: 0.18, sedang: 0.3, tinggi: 0.45 };

export function round6(value) {
    return Math.round(Number(value) * 1e6) / 1e6;
}

export function createBaseMap(element, { center, zoom, tileUrl, attribution, scrollWheelZoom = true }) {
    const map = L.map(element, { 
        scrollWheelZoom: true,
        doubleClickZoom: true,
        touchZoom: true,
        boxZoom: true,
        zoomControl: true,
    }).setView([center.lat, center.lng], zoom);

    // crossOrigin: ubin bisa disimpan service worker untuk dibuka offline.
    L.tileLayer(tileUrl, { attribution, maxZoom: 19, crossOrigin: true }).addTo(map);

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
