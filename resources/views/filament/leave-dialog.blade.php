{{--
    Dialog konfirmasi saat meninggalkan formulir yang belum disimpan,
    pengganti kotak confirm() bawaan browser. Gaya: resources/css/admin.css (lr-dialog).

    Catatan: saat tab ditutup atau halaman dimuat ulang, browser hanya
    mengizinkan pesan bawaannya sendiri, jadi untuk kasus itu tetap muncul
    peringatan browser.
--}}
<dialog id="lr-leave-dialog" class="lr-dialog" aria-labelledby="lr-leave-title" aria-describedby="lr-leave-text">
    <form method="dialog" class="lr-dialog__panel">
        <span class="lr-dialog__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01"/><path d="M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/></svg>
        </span>
        <h2 id="lr-leave-title" class="lr-dialog__title">Tinggalkan halaman ini?</h2>
        <p id="lr-leave-text" class="lr-dialog__text">Ada perubahan yang belum disimpan. Kalau keluar sekarang, isian yang sudah Anda ketik akan hilang.</p>
        <div class="lr-dialog__actions">
            <button type="submit" value="stay" class="lr-dialog__btn lr-dialog__btn--primary" autofocus>Tetap di sini</button>
            <button type="submit" value="leave" class="lr-dialog__btn lr-dialog__btn--ghost">Buang perubahan</button>
        </div>
    </form>
</dialog>

<script data-navigate-once>
    (() => {
        if (window.lrLeaveGuardReady) {
            return
        }

        window.lrLeaveGuardReady = true

        // Lewati pemeriksaan satu kali setelah pengguna memilih "Tinggalkan".
        let bypass = false

        const hasUnsavedChanges = () =>
            (window.lrUnsavedGuards || []).some((guard) => guard.alive() && guard.dirty())

        // Tampilkan dialog, hasilnya true kalau pengguna memilih keluar.
        window.lrConfirmLeave = () =>
            new Promise((resolve) => {
                const dialog = document.getElementById('lr-leave-dialog')

                if (!dialog || typeof dialog.showModal !== 'function') {
                    resolve(window.confirm('Ada perubahan yang belum disimpan. Tinggalkan halaman ini?'))

                    return
                }

                dialog.returnValue = ''
                dialog.addEventListener('close', () => resolve(dialog.returnValue === 'leave'), { once: true })
                dialog.showModal()
            })

        const leave = (go) => {
            window.lrConfirmLeave().then((ok) => {
                if (!ok) {
                    return
                }

                bypass = true
                go()
            })
        }

        // Klik di luar kotak dialog = tetap di halaman.
        document.addEventListener('click', (event) => {
            const dialog = document.getElementById('lr-leave-dialog')

            if (event.target === dialog) {
                dialog.close('stay')
            }
        })

        // 1. Pindah halaman di dalam panel (menu sidebar, tombol, tautan SPA).
        document.addEventListener('livewire:navigate', (event) => {
            if (bypass) {
                bypass = false

                return
            }

            if (!hasUnsavedChanges()) {
                return
            }

            event.preventDefault()

            const url = event.detail?.url

            leave(() => window.Livewire.navigate(String(url?.href ?? url)))
        })

        // 2. Tautan biasa di tab yang sama (misalnya ke web publik).
        document.addEventListener(
            'click',
            (event) => {
                const link = event.target.closest?.('a[href]')

                if (
                    !link ||
                    event.defaultPrevented ||
                    event.button !== 0 ||
                    event.metaKey || event.ctrlKey || event.shiftKey || event.altKey ||
                    link.target === '_blank' ||
                    link.hasAttribute('download') ||
                    [...link.attributes].some((attr) => attr.name.startsWith('wire:navigate')) ||
                    !/^https?:$/.test(link.protocol) ||
                    (link.pathname === location.pathname && link.hash) ||
                    !hasUnsavedChanges()
                ) {
                    return
                }

                event.preventDefault()
                event.stopImmediatePropagation()

                leave(() => (window.location.href = link.href))
            },
            true,
        )

        // 3. Formulir biasa yang membuka halaman lain, misalnya tombol Keluar.
        document.addEventListener(
            'submit',
            (event) => {
                const form = event.target

                if (
                    bypass ||
                    !(form instanceof HTMLFormElement) ||
                    form.method === 'dialog' ||
                    !form.hasAttribute('action') ||
                    !hasUnsavedChanges()
                ) {
                    return
                }

                event.preventDefault()
                event.stopImmediatePropagation()

                leave(() => form.submit())
            },
            true,
        )

        // 4. Menutup tab atau memuat ulang: browser tidak mengizinkan dialog sendiri.
        window.addEventListener('beforeunload', (event) => {
            if (bypass || !hasUnsavedChanges()) {
                return
            }

            event.preventDefault()
            event.returnValue = true
        })
    })()
</script>
