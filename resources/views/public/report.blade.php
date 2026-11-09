@extends('layouts.public')

@section('title', 'Lapor Kejadian')

@php
    $reported = session('reported');
    $icons = [
        'banjir' => '<path d="M3 15c2 0 2-1.5 4.5-1.5S10 15 12 15s2-1.5 4.5-1.5S19 15 21 15M3 20c2 0 2-1.5 4.5-1.5S10 20 12 20s2-1.5 4.5-1.5S19 20 21 20"/><path d="M12 3s4 4.5 4 7a4 4 0 01-8 0c0-2.5 4-7 4-7z"/>',
        'longsor' => '<path d="M3 20h18M4 20l6-10 3 4 2-2 5 8"/><circle cx="16" cy="6" r="1.5"/><circle cx="19" cy="9" r="1"/>',
        'tanda_bahaya' => '<path d="M12 9v4M12 17h.01"/><path d="M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/>',
        'lainnya' => '<circle cx="12" cy="12" r="9"/><path d="M8 12h.01M12 12h.01M16 12h.01"/>',
    ];
@endphp

@section('content')
    <x-page-header
        eyebrow="Lapor kejadian"
        title="Laporkan kejadian atau tanda bahaya"
        intro="Laporan Anda diterima pemerintah desa dan dicek oleh kader Karang Taruna. Tidak perlu akun."
    />

    <section class="wrap py-8 sm:py-10">
        <div class="rep-urgent" role="note">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg>
            <p><strong>Ada yang terluka, tertimbun, atau hanyut?</strong> Telepon dulu, baru lapor di sini:
                <a href="tel:119">Ambulans 119</a> · <a href="tel:115">Basarnas 115</a> · <a href="tel:112">Darurat 112</a></p>
        </div>

        @if ($reported)
            <div class="rep-done" role="status">
                <span class="rep-done__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg></span>
                <div>
                    <h2>Terima kasih, laporan sudah terkirim.</h2>
                    <p>Nomor laporan Anda: <strong class="num">{{ $reported }}</strong>. Simpan nomor ini bila ditanya kader atau perangkat desa.</p>
                    <ol>
                        <li>Kader Karang Taruna akan mengecek ke lokasi bila aman.</li>
                        <li>Pemerintah desa menindaklanjuti dan mengumumkan bila perlu.</li>
                        <li>Tetap jauhi lokasi berbahaya sambil menunggu.</li>
                    </ol>
                    @if ($whatsappHref)
                        <div class="rep-wa">
                            <p><strong>Agar lebih cepat ditanggapi,</strong> kirim juga ringkasan laporan ini ke WhatsApp Posko Desa. Pesannya sudah terisi, tinggal tekan kirim.</p>
                            <a href="{{ $whatsappHref }}" target="_blank" rel="noopener" class="rep-wa__btn">
                                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 00-8.6 15.1L2 22l5-1.3A10 10 0 1012 2zm0 18.2c-1.5 0-3-.4-4.3-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1112 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1a6.7 6.7 0 01-3.3-2.9c-.2-.4.2-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.4.1-.6.3-.2.2-.8.8-.8 2s.8 2.3 1 2.5c.1.2 1.6 2.5 4 3.5 1.5.6 2 .7 2.8.6.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.1-1.2l-.4-.2z"/></svg>
                                Kirim juga ke WA Posko
                            </a>
                        </div>
                    @endif
                    <a href="{{ route('report') }}" class="action-link mt-2"><span>Kirim laporan lain</span></a>
                </div>
            </div>
        @else
            <form method="POST" action="{{ route('report.store') }}" enctype="multipart/form-data" class="rep-form" id="report-form" novalidate>
                @csrf

                {{-- Jebakan robot, tidak terlihat oleh warga --}}
                <div class="sr-only" aria-hidden="true">
                    <label>Situs web <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>

                @if ($errors->any())
                    <div class="rep-errors" role="alert">
                        <p>Laporan belum terkirim:</p>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <fieldset class="rep-step">
                    <legend><span class="num">1</span> Apa yang terjadi?</legend>
                    <div class="rep-kinds">
                        @foreach ($kinds as $kind)
                            <label class="rep-kind rep-kind--{{ $kind->value }}">
                                <input type="radio" name="kind" value="{{ $kind->value }}" @checked(old('kind') === $kind->value) required>
                                <span class="rep-kind__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $icons[$kind->value] !!}</svg></span>
                                <span class="rep-kind__label">{{ $kind->getLabel() }}</span>
                            </label>
                        @endforeach
                    </div>
                    <label class="rep-field">
                        <span>Ceritakan singkat</span>
                        <textarea name="description" rows="4" maxlength="2000" required placeholder="Contoh: Ada retakan tanah baru di kebun belakang rumah, sepanjang kira-kira 5 meter, setelah hujan semalam.">{{ old('description') }}</textarea>
                    </label>
                </fieldset>

                <fieldset class="rep-step">
                    <legend><span class="num">2</span> Di mana?</legend>
                    <div class="rep-row">
                        <label class="rep-field">
                            <span>Dusun</span>
                            <select name="hamlet">
                                <option value="">Pilih dusun</option>
                                @foreach ($hamletNames as $name)
                                    <option value="{{ $name }}" @selected(old('hamlet') === $name)>{{ $name }}</option>
                                @endforeach
                                <option value="Tidak tahu" @selected(old('hamlet') === 'Tidak tahu')>Tidak tahu</option>
                            </select>
                        </label>
                        <label class="rep-field">
                            <span>Patokan terdekat</span>
                            <input type="text" name="landmark" maxlength="255" value="{{ old('landmark') }}" placeholder="Contoh: dekat Masjid Ar Rahman">
                        </label>
                    </div>
                    <input type="hidden" name="lat" value="{{ old('lat') }}" data-gps-lat>
                    <input type="hidden" name="lng" value="{{ old('lng') }}" data-gps-lng>
                    <div class="rep-gps">
                        <button type="button" class="rep-gps__btn" data-gps>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/><circle cx="12" cy="12" r="7"/></svg>
                            <span>Pakai lokasi saya sekarang</span>
                        </button>
                        <p class="rep-gps__status" data-gps-status aria-live="polite">{{ old('lat') ? 'Lokasi sudah tersimpan.' : 'Opsional. Tekan bila Anda sedang berada di dekat lokasi kejadian.' }}</p>
                    </div>
                </fieldset>

                <fieldset class="rep-step">
                    <legend><span class="num">3</span> Foto <small>(opsional)</small></legend>
                    <label class="rep-photo">
                        <input type="file" name="photo" accept="image/*" data-photo>
                        <span class="rep-photo__box" data-photo-box>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 8a2 2 0 012-2h2l2-2h6l2 2h2a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><circle cx="12" cy="13" r="4"/></svg>
                            <span data-photo-label>Ambil atau pilih foto</span>
                        </span>
                    </label>
                    <p class="rep-hint">Jangan mendekati lereng atau sungai hanya untuk memotret.</p>
                </fieldset>

                <fieldset class="rep-step">
                    <legend><span class="num">4</span> Nama dan nomor HP <small>(opsional)</small></legend>
                    <div class="rep-row">
                        <label class="rep-field">
                            <span>Nama</span>
                            <input type="text" name="reporter_name" maxlength="100" value="{{ old('reporter_name') }}" autocomplete="name">
                        </label>
                        <label class="rep-field">
                            <span>Nomor HP / WhatsApp</span>
                            <input type="tel" name="reporter_phone" maxlength="30" value="{{ old('reporter_phone') }}" autocomplete="tel" inputmode="tel">
                        </label>
                    </div>
                    <p class="rep-hint">Supaya kader bisa menghubungi Anda bila perlu. Tidak ditampilkan di web.</p>
                </fieldset>

                <div class="rep-submit">
                    <button type="submit" class="rep-submit__btn">Kirim laporan</button>
                    <p class="rep-hint" data-offline-note hidden>Anda sedang offline. Laporan belum bisa dikirim; telepon nomor darurat bila mendesak.</p>
                </div>
            </form>
        @endif
    </section>

    <script>
        (() => {
            const form = document.getElementById('report-form');
            if (!form) return;

            // Lokasi GPS
            const btn = form.querySelector('[data-gps]');
            const status = form.querySelector('[data-gps-status]');
            btn?.addEventListener('click', () => {
                if (!navigator.geolocation) {
                    status.textContent = 'HP ini tidak mendukung GPS di browser. Isi patokan lokasi saja.';
                    return;
                }
                status.textContent = 'Mencari lokasi Anda…';
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        form.querySelector('[data-gps-lat]').value = pos.coords.latitude.toFixed(6);
                        form.querySelector('[data-gps-lng]').value = pos.coords.longitude.toFixed(6);
                        status.textContent = `Lokasi tersimpan (akurasi sekitar ${Math.round(pos.coords.accuracy)} m).`;
                        btn.classList.add('is-done');
                    },
                    () => { status.textContent = 'Lokasi tidak bisa diambil. Izinkan akses lokasi, atau isi patokan saja.'; },
                    { enableHighAccuracy: true, timeout: 15000 },
                );
            });

            // Foto diperkecil di HP sebelum dikirim, supaya cepat di sinyal lemah.
            const input = form.querySelector('[data-photo]');
            const label = form.querySelector('[data-photo-label]');
            const box = form.querySelector('[data-photo-box]');
            input?.addEventListener('change', async () => {
                const file = input.files?.[0];
                if (!file) return;
                label.textContent = file.name;
                box.classList.add('has-file');
                if (!file.type.startsWith('image/') || file.size < 600 * 1024 || typeof createImageBitmap !== 'function') return;
                try {
                    const bitmap = await createImageBitmap(file);
                    const scale = Math.min(1, 1600 / Math.max(bitmap.width, bitmap.height));
                    const canvas = document.createElement('canvas');
                    canvas.width = Math.round(bitmap.width * scale);
                    canvas.height = Math.round(bitmap.height * scale);
                    canvas.getContext('2d').drawImage(bitmap, 0, 0, canvas.width, canvas.height);
                    const blob = await new Promise((r) => canvas.toBlob(r, 'image/jpeg', 0.82));
                    if (blob && blob.size < file.size && typeof DataTransfer === 'function') {
                        const dt = new DataTransfer();
                        dt.items.add(new File([blob], file.name.replace(/\.\w+$/, '') + '.jpg', { type: 'image/jpeg' }));
                        input.files = dt.files;
                        label.textContent = `${file.name} (diperkecil ke ${Math.round(blob.size / 1024)} KB)`;
                    }
                } catch (e) { /* kirim apa adanya */ }
            });

            // Offline: jangan biarkan warga mengira laporan terkirim.
            const note = form.querySelector('[data-offline-note]');
            form.addEventListener('submit', (event) => {
                if (!navigator.onLine) {
                    event.preventDefault();
                    note.hidden = false;
                    return;
                }
                form.querySelector('.rep-submit__btn').textContent = 'Mengirim…';
            });
        })();
    </script>
@endsection
