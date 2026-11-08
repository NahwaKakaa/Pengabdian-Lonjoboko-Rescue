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
    const button = document.getElementById('theme-toggle');
    const root = document.documentElement;

    if (!button) {
        return;
    }

    const label = () => {
        const dark = root.classList.contains('dark');
        button.setAttribute('aria-checked', dark ? 'true' : 'false');
        button.setAttribute('title', dark ? 'Ganti ke tampilan siang' : 'Ganti ke tampilan malam');
    };

    const apply = (dark) => {
        root.classList.toggle('dark', dark);

        try {
            localStorage.setItem('lr-theme', dark ? 'dark' : 'light');
        } catch (e) {}

        label();
    };

    label();

    button.addEventListener('click', () => {
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
    });

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

initThemeToggle();
initScrollEffects();
initNavHover();
initMobileMenu();
