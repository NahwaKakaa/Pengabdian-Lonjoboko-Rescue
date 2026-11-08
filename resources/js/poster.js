// Membuat kode QR di poster cetak (menuju alamat web desa).
import QRCode from 'qrcode';

document.querySelectorAll('[data-qr]').forEach(async (el) => {
    try {
        el.innerHTML = await QRCode.toString(el.dataset.qr, {
            type: 'svg',
            margin: 0,
            errorCorrectionLevel: 'M',
            color: { dark: '#062b29', light: '#ffffff' },
        });
    } catch (e) {
        el.textContent = el.dataset.qr;
    }
});
