// Skrip umum halaman publik. Sengaja minimal supaya web tetap ringan di sinyal desa.
// Semua animasi memakai transform/opacity dan mati otomatis bila pengguna memilih "kurangi gerakan".

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/* ------------------------------------------------------------------
 * 1, 6 & tombol ke atas: semua yang bergantung pada posisi gulir
 * ------------------------------------------------------------------ */
function initScrollEffects() {
    const header = document.getElementById('site-header');
    const progressBar = document.getElementById('read-progress-bar');
    const toTop = document.getElementById('back-to-top');
    const toTopRing = document.getElementById('back-to-top-progress');

    let ticking = false;

    const update = () => {
        ticking = false;

        const y = window.scrollY;
        const max = Math.max(1, document.documentElement.scrollHeight - window.innerHeight);
        const ratio = Math.min(1, Math.max(0, y / max));

        // 1. Navbar menyusut setelah digulir sedikit
        header?.classList.toggle('is-scrolled', y > 24);

        // 6. Bilah progres baca (halaman artikel)
        if (progressBar) {
            progressBar.style.transform = `scaleX(${ratio})`;
        }

        // Tombol ke atas muncul setelah satu layar
        if (toTop) {
            toTop.classList.toggle('is-visible', y > window.innerHeight * 0.8);
            toTopRing?.style.setProperty('stroke-dashoffset', String(1 - ratio));
        }
    };

    const onScroll = () => {
        if (!ticking) {
            ticking = true;
            window.requestAnimationFrame(update);
        }
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    update();

    toTop?.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
        // Pindahkan fokus ke awal halaman untuk pengguna keyboard/pembaca layar.
        document.querySelector('.site-logo')?.focus({ preventScroll: true });
    });
}

/* ------------------------------------------------------------------
 * 2. Sorotan tipis yang mengikuti kursor di menu (laptop).
 *    Kotak hijau menu aktif tidak diatur di sini: saat pindah halaman,
 *    browser menggesernya lewat View Transitions (lihat app.css).
 * ------------------------------------------------------------------ */
function initNavHover() {
    const nav = document.querySelector('.site-nav');
    const hover = nav?.querySelector('.site-nav__hover');

    if (!nav || !hover) {
        return;
    }

    const place = (target) => {
        hover.style.width = `${target.offsetWidth}px`;
        hover.style.height = `${target.offsetHeight}px`;
        hover.style.transform = `translate(${target.offsetLeft}px, ${target.offsetTop}px)`;
    };

    nav.querySelectorAll('.site-nav__link').forEach((link) => {
        link.addEventListener('mouseenter', () => {
            if (link.classList.contains('is-active')) {
                hover.classList.remove('is-visible');

                return;
            }

            if (!hover.classList.contains('is-visible')) {
                // Muncul di tempat dulu, baru meluncur untuk sorotan berikutnya.
                hover.style.transition = 'opacity .2s ease';
                place(link);
                hover.getBoundingClientRect();
                hover.style.transition = '';
            } else {
                place(link);
            }

            hover.classList.add('is-visible');
        });
    });

    nav.addEventListener('mouseleave', () => hover.classList.remove('is-visible'));
}

/* ------------------------------------------------------------------
 * Klik menu: respons langsung tanpa menunggu server.
 * Kotak hijau langsung meluncur ke menu yang diklik dan garis memuat
 * muncul kalau halaman tujuan lambat datang. Saat halaman baru tiba,
 * kotaknya sudah di tempat, jadi View Transitions tinggal merapikan.
 * ------------------------------------------------------------------ */
function initNavClick() {
    const header = document.getElementById('site-header');
    const nav = document.querySelector('.site-nav');
    const menu = document.querySelector('.site-menu');

    if (!header) {
        return;
    }

    const INACTIVE = ['text-river-100', 'hover:text-white'];
    const INACTIVE_MOBILE = ['text-river-100', 'hover:bg-white/10', 'hover:text-white'];
    const ACTIVE_MOBILE = ['bg-emerald-600', 'text-white'];

    // Simpan keadaan awal untuk dipulihkan kalau warga kembali lewat tombol Back.
    const initialDesktop = nav?.querySelector('.site-nav__link.is-active') ?? null;
    const initialMobile = menu?.querySelector('.site-menu__link.is-active') ?? null;

    const isPlainClick = (event, link) =>
        !event.defaultPrevented &&
        event.button === 0 &&
        !event.metaKey &&
        !event.ctrlKey &&
        !event.shiftKey &&
        !event.altKey &&
        link.target !== '_blank' &&
        link.origin === location.origin &&
        link.pathname !== location.pathname;

    const setDesktopActive = (link, active) => {
        link.classList.toggle('is-active', active);
        link.classList.toggle('text-white', active);
        INACTIVE.forEach((c) => link.classList.toggle(c, !active));
        active ? link.setAttribute('aria-current', 'page') : link.removeAttribute('aria-current');
    };

    const setMobileActive = (link, active) => {
        link.classList.toggle('is-active', active);
        ACTIVE_MOBILE.forEach((c) => link.classList.toggle(c, active));
        INACTIVE_MOBILE.forEach((c) => link.classList.toggle(c, !active));
    };

    const movePill = (target) => {
        const current = nav.querySelector('.site-nav__link.is-active');
        let pill = nav.querySelector('.site-nav__pill');

        nav.querySelector('.site-nav__hover')?.classList.remove('is-visible');

        if (!pill || !current) {
            // Halaman tanpa menu aktif: kotak hijau muncul di menu tujuan.
            pill = document.createElement('span');
            pill.className = 'site-nav__pill is-entering';
            pill.setAttribute('aria-hidden', 'true');
            target.prepend(pill);
        } else if (current !== target) {
            // Memakai left/width (bukan transform) supaya posisi yang terekam
            // browser saat halaman berganti sama dengan yang terlihat di layar.
            pill.classList.remove('is-moving');
            pill.style.right = 'auto';
            pill.style.left = '0px';
            pill.style.width = `${current.offsetWidth}px`;
            pill.getBoundingClientRect();

            pill.classList.add('is-moving');
            pill.style.left = `${target.offsetLeft - current.offsetLeft}px`;
            pill.style.width = `${target.offsetWidth}px`;
        }

        if (current && current !== target) {
            setDesktopActive(current, false);
        }

        setDesktopActive(target, true);
    };

    const startLoading = () => header.classList.add('is-navigating');

    nav?.querySelectorAll('.site-nav__link').forEach((link) => {
        link.addEventListener('click', (event) => {
            if (!isPlainClick(event, link)) {
                return;
            }

            movePill(link);
            startLoading();
        });
    });

    menu?.querySelectorAll('.site-menu__link').forEach((link) => {
        link.addEventListener('click', (event) => {
            if (!isPlainClick(event, link)) {
                return;
            }

            menu.querySelectorAll('.site-menu__link.is-active').forEach((other) => setMobileActive(other, false));
            setMobileActive(link, true);
            startLoading();

            // Biarkan pilihan terlihat sebentar, lalu tutup panel dengan halus.
            setTimeout(() => {
                if (!menu.open) {
                    return;
                }

                menu.classList.add('is-closing');
                setTimeout(() => {
                    menu.open = false;
                    menu.classList.remove('is-closing');
                }, reduceMotion ? 0 : 190);
            }, reduceMotion ? 0 : 140);
        });
    });

    // Tepat sebelum browser merekam halaman lama untuk transisi: bekukan kotak
    // hijau di posisi yang sedang terlihat, supaya transisi melanjutkan dari situ.
    window.addEventListener('pageswap', () => {
        const pill = nav?.querySelector('.site-nav__pill.is-moving');

        if (!pill) {
            return;
        }

        const { left, width } = getComputedStyle(pill);
        pill.classList.remove('is-moving');
        pill.style.left = left;
        pill.style.width = width;
    });

    // Kembali lewat tombol Back (halaman diambil dari cache browser): pulihkan menu.
    window.addEventListener('pageshow', (event) => {
        if (!event.persisted) {
            return;
        }

        header.classList.remove('is-navigating');

        if (nav) {
            nav.querySelectorAll('.site-nav__pill.is-entering').forEach((pill) => pill.remove());
            nav.querySelectorAll('.site-nav__link').forEach((link) => setDesktopActive(link, link === initialDesktop));

            const pill = initialDesktop?.querySelector('.site-nav__pill');
            if (pill) {
                pill.classList.remove('is-moving');
                pill.removeAttribute('style');
            }
        }

        menu?.querySelectorAll('.site-menu__link').forEach((link) => setMobileActive(link, link === initialMobile));
    });
}

/* ------------------------------------------------------------------
 * 3. Menu HP: tutup saat klik di luar, tekan Esc, atau memilih menu
 * ------------------------------------------------------------------ */
function initMobileMenu() {
    const menu = document.querySelector('.site-menu');

    if (!menu) {
        return;
    }

    const summary = menu.querySelector('summary');

    menu.addEventListener('toggle', () => {
        summary.setAttribute('aria-label', menu.open ? 'Tutup menu' : 'Buka menu');
    });

    document.addEventListener('click', (event) => {
        if (menu.open && !menu.contains(event.target)) {
            menu.open = false;
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && menu.open) {
            menu.open = false;
            summary.focus();
        }
    });
}

/* ------------------------------------------------------------------
 * Tampilan siang / malam
 * ------------------------------------------------------------------ */
function initThemeToggle() {
    // Satu tombol di navbar laptop, satu lagi di dalam Menu HP.
    const buttons = document.querySelectorAll('[data-theme-toggle]');
    const root = document.documentElement;

    if (!buttons.length) {
        return;
    }

    const label = () => {
        const dark = root.classList.contains('dark');
        buttons.forEach((button) => {
            button.setAttribute('aria-checked', dark ? 'true' : 'false');
            button.setAttribute('title', dark ? 'Ganti ke tampilan siang' : 'Ganti ke tampilan malam');
        });
    };

    const apply = (dark) => {
        root.classList.toggle('dark', dark);

        try {
            localStorage.setItem('lr-theme', dark ? 'dark' : 'light');
        } catch (e) {}

        label();
    };

    label();

    const toggle = () => {
        const dark = !root.classList.contains('dark');

        // Browser tanpa View Transitions: warna dan gambar memudar lewat CSS.
        if (!document.startViewTransition || reduceMotion) {
            root.classList.add('lr-theme-fade');
            apply(dark);
            setTimeout(() => root.classList.remove('lr-theme-fade'), 950);

            return;
        }

        // Seluruh halaman memudar bersamaan; sakelar tetap meluncur.
        root.classList.add('lr-theme-switching');

        const transition = document.startViewTransition(() => apply(dark));

        transition.finished.finally(() => root.classList.remove('lr-theme-switching'));
    };

    buttons.forEach((button) => button.addEventListener('click', toggle));

    // Ikuti pengaturan HP/laptop kalau warga belum pernah memilih sendiri.
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener?.('change', (event) => {
        let chosen = null;

        try {
            chosen = localStorage.getItem('lr-theme');
        } catch (e) {}

        if (!chosen) {
            root.classList.toggle('dark', event.matches);
            label();
        }
    });
}

/* ------------------------------------------------------------------
 * Offline (PWA): daftarkan service worker, tampilkan status koneksi,
 * dan tombol "Pasang di HP".
 * ------------------------------------------------------------------ */
function initOffline() {
    const status = document.getElementById('net-status');
    const text = status?.querySelector('.net-status__text');
    let hideTimer = null;

    const show = (mode, message, autoHide) => {
        if (!status) return;
        clearTimeout(hideTimer);
        status.dataset.mode = mode;
        text.textContent = message;
        status.hidden = false;
        requestAnimationFrame(() => status.classList.add('is-visible'));
        if (autoHide) {
            hideTimer = setTimeout(hide, autoHide);
        }
    };

    const hide = () => {
        if (!status) return;
        status.classList.remove('is-visible');
        hideTimer = setTimeout(() => (status.hidden = true), 300);
    };

    const update = () => {
        if (navigator.onLine) {
            if (status?.dataset.mode === 'offline') show('online', 'Tersambung kembali', 2500);
        } else {
            show('offline', 'Sedang offline. Menampilkan data yang tersimpan di HP.');
        }
    };

    window.addEventListener('online', update);
    window.addEventListener('offline', update);
    if (!navigator.onLine) update();

    // Service worker hanya berjalan di HTTPS (atau localhost saat pengembangan).
    const secure = location.protocol === 'https:' || ['localhost', '127.0.0.1'].includes(location.hostname);

    if ('serviceWorker' in navigator && secure) {
        const firstInstall = !navigator.serviceWorker.controller;

        navigator.serviceWorker.register('/sw.js').catch(() => {});

        navigator.serviceWorker.addEventListener('controllerchange', () => {
            let told = false;

            try {
                told = localStorage.getItem('lr-offline-ready') === '1';
                localStorage.setItem('lr-offline-ready', '1');
            } catch (e) {}

            if (firstInstall && !told) {
                show('ready', 'Siap dibuka tanpa internet', 4000);
            }
        });
    }

    // Tombol pasang aplikasi
    const installButton = document.getElementById('install-app');
    const iosHint = document.getElementById('install-ios-hint');
    const standalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
    let deferredPrompt = null;

    if (!installButton || standalone) return;

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredPrompt = event;
        installButton.hidden = false;
    });

    // iPhone/iPad tidak punya prompt otomatis: tampilkan petunjuknya.
    const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

    if (isIos) {
        installButton.hidden = false;
    }

    installButton.addEventListener('click', async () => {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            deferredPrompt = null;
            if (outcome === 'accepted') installButton.hidden = true;
        } else if (iosHint) {
            iosHint.hidden = !iosHint.hidden;
        }
    });

    window.addEventListener('appinstalled', () => {
        installButton.hidden = true;
        if (iosHint) iosHint.hidden = true;
    });
}

initOffline();
initThemeToggle();
initScrollEffects();
initNavHover();
initNavClick();
initMobileMenu();
