/**
 * Komponen peta untuk form di panel admin (Filament).
 *
 * - lrLocationPicker: memilih satu titik (lokasi rawan, titik kumpul, fasilitas)
 * - lrRoutePicker: menggambar jalur evakuasi
 *
 * Objek Leaflet sengaja disimpan di luar data Alpine. Kalau dibungkus
 * reaktivitas Alpine, Leaflet berhenti bekerja dengan benar.
 */
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import '@geoman-io/leaflet-geoman-free';
import '@geoman-io/leaflet-geoman-free/dist/leaflet-geoman.css';
import '../css/map.css';
import '../css/admin-map.css';
import {
    COLORS,
    createBaseMap,
    escapeHtml,
    formatDistance,
    pinIcon,
    polylineLength,
    round6,
    walkingMinutes,
} from './map/shared';

function locationPicker(config) {
    let map = null;
    let marker = null;

    return {
        state: config.state,
        lat: '',
        lng: '',
        locating: false,
        message: '',

        init() {
            this.syncFromState();

            map = createBaseMap(this.$refs.map, config);

            if (this.hasPoint()) {
                map.setView([this.lat, this.lng], 17);
                this.renderMarker();
            }

            this.message = config.disabled
                ? ''
                : this.hasPoint()
                  ? 'Geser penanda atau ketuk peta untuk memindahkan titik.'
                  : 'Ketuk peta di lokasi yang dimaksud, atau tekan "Gunakan lokasi saya" saat berada di tempatnya.';

            if (!config.disabled) {
                map.on('click', (event) => this.setPoint(event.latlng.lat, event.latlng.lng));
            }

            this.$watch('state', () => {
                this.syncFromState();
                this.renderMarker();
            });
        },

        hasPoint() {
            return this.lat !== '' && this.lng !== '' && !Number.isNaN(Number(this.lat)) && !Number.isNaN(Number(this.lng));
        },

        syncFromState() {
            const s = this.state;
            this.lat = s && s.lat !== undefined && s.lat !== null ? round6(s.lat) : '';
            this.lng = s && s.lng !== undefined && s.lng !== null ? round6(s.lng) : '';
        },

        setPoint(lat, lng) {
            this.lat = round6(lat);
            this.lng = round6(lng);
            this.state = { lat: this.lat, lng: this.lng };
            this.renderMarker();
            this.message = 'Titik tersimpan di form. Jangan lupa tekan Simpan.';
        },

        setFromInputs() {
            const lat = Number(this.lat);
            const lng = Number(this.lng);

            if (this.lat === '' || this.lng === '' || Number.isNaN(lat) || Number.isNaN(lng)) {
                return;
            }

            this.setPoint(lat, lng);
            map.setView([lat, lng], Math.max(map.getZoom(), 16));
        },

        clearPoint() {
            this.lat = '';
            this.lng = '';
            this.state = null;
            this.renderMarker();
            this.message = 'Titik dihapus. Ketuk peta untuk memilih lokasi baru.';
        },

        renderMarker() {
            if (!map) {
                return;
            }

            if (!this.hasPoint()) {
                if (marker) {
                    map.removeLayer(marker);
                    marker = null;
                }
                return;
            }

            const position = [Number(this.lat), Number(this.lng)];

            if (marker) {
                marker.setLatLng(position);
                return;
            }

            marker = L.marker(position, {
                icon: pinIcon(COLORS.route),
                draggable: !config.disabled,
                keyboard: true,
            }).addTo(map);

            marker.on('dragend', () => {
                const p = marker.getLatLng();
                this.setPoint(p.lat, p.lng);
            });
        },

        useMyLocation() {
            if (!('geolocation' in navigator)) {
                this.message = 'Perangkat ini tidak mendukung GPS di browser. Ketuk peta untuk memilih lokasi.';
                return;
            }

            this.locating = true;
            this.message = 'Mencari lokasi Anda…';

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.locating = false;
                    const { latitude, longitude, accuracy } = position.coords;
                    this.setPoint(latitude, longitude);
                    map.setView([latitude, longitude], 18);
                    this.message = `Lokasi ditemukan (akurasi sekitar ${Math.round(accuracy)} m). Geser penanda bila kurang tepat, lalu tekan Simpan.`;
                },
                (error) => {
                    this.locating = false;
                    this.message =
                        error.code === error.PERMISSION_DENIED
                            ? 'Izin lokasi ditolak. Izinkan akses lokasi di pengaturan browser, atau ketuk peta secara manual.'
                            : 'Lokasi tidak ditemukan. Pastikan GPS menyala dan web dibuka lewat https, atau ketuk peta secara manual.';
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 },
            );
        },
    };
}

function routePicker(config) {
    let map = null;
    let line = null;

    return {
        state: config.state,
        references: config.references ?? [],
        drawing: false,
        editing: false,
        message: '',
        summary: '',
        ignoreNextWatch: false,

        init() {
            map = createBaseMap(this.$refs.map, config);
            this.renderReferences();
            this.renderRoute(true);

            if (!config.disabled) {
                map.pm.setLang('id');
                map.pm.setGlobalOptions({
                    pathOptions: this.lineStyle(),
                    templineStyle: { color: COLORS.route, dashArray: '6 6' },
                    hintlineStyle: { color: COLORS.route, dashArray: '2 6' },
                    snappable: true,
                    snapDistance: 20,
                });

                map.on('pm:create', (event) => {
                    if (event.shape !== 'Line') {
                        return;
                    }
                    if (line) {
                        map.removeLayer(line);
                    }
                    line = event.layer;
                    this.drawing = false;
                    this.watchEdits();
                    this.save();
                    this.message = 'Jalur tersimpan di form. Tekan "Geser titik jalur" bila perlu dirapikan, lalu tekan Simpan.';
                });
            }

            this.message = config.disabled
                ? ''
                : this.hasRoute()
                  ? 'Jalur sudah ada. Anda bisa menggeser titiknya atau menggambar ulang.'
                  : 'Tekan "Gambar jalur", lalu ketuk peta mulai dari titik awal sampai titik kumpul tujuan.';

            this.$watch('state', () => {
                if (this.ignoreNextWatch) {
                    this.ignoreNextWatch = false;
                    return;
                }
                this.renderRoute(false);
            });
        },

        lineStyle() {
            return { color: COLORS.route, weight: 5, opacity: 0.9 };
        },

        points() {
            return Array.isArray(this.state) ? this.state.filter((p) => Array.isArray(p) && p.length >= 2) : [];
        },

        hasRoute() {
            return this.points().length >= 2;
        },

        renderReferences() {
            this.references.forEach((ref) => {
                const isSafe = ref.kind === 'safe';
                L.circleMarker([ref.lat, ref.lng], {
                    radius: 7,
                    color: '#fff',
                    weight: 2,
                    fillColor: isSafe ? COLORS.safe : COLORS.longsor,
                    fillOpacity: 0.95,
                    pmIgnore: true,
                    snapIgnore: !isSafe,
                })
                    .bindTooltip(escapeHtml(ref.label))
                    .addTo(map);
            });
        },

        renderRoute(fit) {
            if (line) {
                map.removeLayer(line);
                line = null;
            }

            const pts = this.points();

            if (pts.length >= 2) {
                line = L.polyline(pts, this.lineStyle()).addTo(map);
                this.watchEdits();
                if (fit) {
                    map.fitBounds(line.getBounds(), { padding: [30, 30] });
                }
            }

            this.updateSummary();
        },

        watchEdits() {
            if (!line || config.disabled) {
                return;
            }
            line.on('pm:edit', () => this.save());
        },

        startDraw() {
            if (line) {
                map.removeLayer(line);
                line = null;
                this.writeState([]);
            }
            if (this.editing) {
                this.toggleEdit();
            }
            map.pm.enableDraw('Line', { finishOn: null });
            this.drawing = true;
            this.message = 'Ketuk peta titik demi titik mengikuti jalan. Tekan "Selesai menggambar" atau ketuk titik terakhir sekali lagi.';
        },

        finishDraw() {
            const drawer = map.pm.Draw.Line;

            if (drawer && typeof drawer._finishShape === 'function' && drawer._layer && drawer._layer.getLatLngs().length >= 2) {
                drawer._finishShape();
                return;
            }

            map.pm.disableDraw();
            this.drawing = false;
            this.message = 'Jalur belum tersimpan karena kurang dari dua titik. Tekan "Gambar jalur" untuk mencoba lagi.';
        },

        toggleEdit() {
            if (!line) {
                return;
            }
            this.editing = !this.editing;
            if (this.editing) {
                line.pm.enable({ allowSelfIntersection: true });
                this.message = 'Geser titik putih untuk merapikan jalur. Titik tengah yang samar bisa ditarik untuk menambah belokan.';
            } else {
                line.pm.disable();
                this.save();
                this.message = 'Perubahan jalur tersimpan di form. Jangan lupa tekan Simpan.';
            }
        },

        clearRoute() {
            if (line) {
                map.removeLayer(line);
                line = null;
            }
            this.editing = false;
            this.writeState([]);
            this.updateSummary();
            this.message = 'Jalur dihapus. Tekan "Gambar jalur" untuk menggambar ulang.';
        },

        save() {
            if (!line) {
                return;
            }
            const latlngs = line.getLatLngs().flat(Infinity);
            this.writeState(latlngs.map((p) => [round6(p.lat), round6(p.lng)]));
            this.updateSummary();
        },

        writeState(value) {
            this.ignoreNextWatch = true;
            this.state = value;
        },

        updateSummary() {
            const pts = this.points();
            if (pts.length < 2) {
                this.summary = '';
                return;
            }
            const meters = polylineLength(pts);
            this.summary = `${pts.length} titik · panjang ${formatDistance(meters)} · sekitar ${walkingMinutes(meters)} menit jalan kaki`;
        },
    };
}

document.addEventListener('alpine:init', () => {
    window.Alpine.data('lrLocationPicker', locationPicker);
    window.Alpine.data('lrRoutePicker', routePicker);
});
