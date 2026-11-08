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
 * 2. Penanda menu yang meluncur (laptop)
 * ------------------------------------------------------------------ */
function initSlidingNav() {
    const nav = document.querySelector('.site-nav');

    if (!nav) {
        return;
    }

    const active = nav.querySelector('.site-nav__active');
    const hover = nav.querySelector('.site-nav__hover');
    const current = nav.querySelector('.site-nav__link.is-active');
    const STORE_KEY = 'lr-nav-active';

    const place = (el, target) => {
        el.style.width = `${target.offsetWidth}px`;
        el.style.height = `${target.offsetHeight}px`;
        el.style.transform = `translate(${target.offsetLeft}px, ${target.offsetTop}px)`;
    };

    nav.classList.add('is-enhanced');

    if (current) {
        // Mulai dari posisi menu halaman sebelumnya, lalu meluncur ke menu sekarang.
        let previous = null;

        try {
            previous = JSON.parse(sessionStorage.getItem(STORE_KEY) || 'null');
        } catch (e) {}

        if (previous && !reduceMotion) {
            active.style.transition = 'none';
            active.style.width = `${previous.w}px`;
            active.style.height = `${current.offsetHeight}px`;
            active.style.transform = `translate(${previous.x}px, ${current.offsetTop}px)`;
            active.getBoundingClientRect(); // paksa browser menggambar posisi awal
            active.style.transition = '';
        }

        requestAnimationFrame(() => {
            place(active, current);
            active.classList.add('is-ready');
        });

        try {
            sessionStorage.setItem(STORE_KEY, JSON.stringify({ x: current.offsetLeft, w: current.offsetWidth }));
        } catch (e) {}

        window.addEventListener('resize', () => place(active, current), { passive: true });
    }

    // Sorotan tipis mengikuti kursor
    nav.querySelectorAll('.site-nav__link').forEach((link) => {
        link.addEventListener('mouseenter', () => {
            if (link.classList.contains('is-active')) {
                hover.classList.remove('is-visible');

                return;
            }

            if (!hover.classList.contains('is-visible')) {
                // Muncul di tempat dulu, baru meluncur untuk sorotan berikutnya.
                hover.style.transition = 'opacity .2s ease';
                place(hover, link);
                hover.getBoundingClientRect();
                hover.style.transition = '';
            } else {
                place(hover, link);
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

initScrollEffects();
initSlidingNav();
initMobileMenu();
