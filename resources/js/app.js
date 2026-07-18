import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

/* Muat editor berat hanya pada form yang benar-benar memakainya. */
if (document.querySelector('trix-editor')) {
    Promise.all([
        import('trix'),
        import('trix/dist/trix.css'),
    ]).catch((error) => {
        console.error('Editor gagal dimuat.', error);
    });
}

Alpine.plugin(collapse);
window.Alpine = Alpine;

/* Trix: nonaktifkan lampiran berkas (tidak menangani upload dari editor). */
window.addEventListener('trix-file-accept', (event) => event.preventDefault());

/* ---------------- Theme store (dark / light) ---------------- */
Alpine.store('theme', {
    dark: document.documentElement.classList.contains('dark'),
    toggle() {
        this.dark = !this.dark;
        document.documentElement.classList.toggle('dark', this.dark);
        try {
            localStorage.setItem('theme', this.dark ? 'dark' : 'light');
        } catch (e) {
            /* abaikan storage error */
        }
    },
});

/* ---------------- Navbar: scroll state + scroll-spy + drawer ---------------- */
Alpine.data('siteNav', (sections = []) => ({
    scrolled: false,
    open: false,
    hidden: false,
    active: sections[0] ?? '',
    init() {
        let previousY = window.scrollY;
        let ticking = false;

        const updateNav = () => {
            const currentY = window.scrollY;
            const delta = currentY - previousY;

            this.scrolled = currentY > 24;

            if (!this.open) {
                if (currentY < 120 || delta < -6) {
                    this.hidden = false;
                } else if (currentY > 180 && delta > 8) {
                    this.hidden = true;
                }
            }

            previousY = currentY;
            ticking = false;
        };

        const onScroll = () => {
            if (!ticking) {
                window.requestAnimationFrame(updateNav);
                ticking = true;
            }
        };

        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });

        this.$watch('open', (value) => {
            document.body.classList.toggle('overflow-hidden', value);
            if (value) this.hidden = false;
        });

        if (sections.length && 'IntersectionObserver' in window) {
            const spy = new IntersectionObserver(
                (entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) this.active = entry.target.id;
                    });
                },
                { rootMargin: '-45% 0px -50% 0px', threshold: 0 }
            );
            sections.forEach((id) => {
                const el = document.getElementById(id);
                if (el) spy.observe(el);
            });
        }
    },
    isActive(id) {
        return this.active === id;
    },
}));

/* ---------------- Carousel pengurus: tombol + swipe native ---------------- */
Alpine.data('memberCarousel', () => ({
    canPrevious: false,
    canNext: false,
    init() {
        this.$nextTick(() => this.sync());
    },
    move(direction) {
        const track = this.$refs.track;
        const card = track?.querySelector('[data-member-card]');

        if (!track || !card) return;

        const styles = window.getComputedStyle(track);
        const gap = Number.parseFloat(styles.columnGap || styles.gap) || 24;
        const distance = card.getBoundingClientRect().width + gap;

        track.scrollBy({ left: direction * distance, behavior: 'smooth' });
    },
    sync() {
        const track = this.$refs.track;

        if (!track) return;

        this.canPrevious = track.scrollLeft > 2;
        this.canNext = track.scrollLeft + track.clientWidth < track.scrollWidth - 2;
    },
}));

Alpine.data('passwordField', () => ({
    visible: false,
    toggle() {
        this.visible = !this.visible;
    },
}));

Alpine.start();

/* ---------------- Motion system: reveal dua arah + progress + parallax ---------------- */
const motionMedia = window.matchMedia('(prefers-reduced-motion: reduce)');

const initMotion = () => {
    if (document.documentElement.dataset.motionInitialized === 'true') return;
    document.documentElement.dataset.motionInitialized = 'true';

    document.querySelectorAll('[data-motion-children]').forEach((container) => {
        [...container.children].forEach((child, index) => {
            if (child.matches('script, style, form, .admin-table-wrap, [data-no-auto-motion]')) return;
            child.classList.add('reveal');
            child.style.setProperty('--reveal-delay', `${Math.min(index * 65, 260)}ms`);
        });
    });

    const revealTargets = [...document.querySelectorAll('.reveal')];
    const progress = document.querySelector('[data-scroll-progress]');
    const parallaxTargets = [...document.querySelectorAll('[data-parallax]')];

    if (motionMedia.matches || !('IntersectionObserver' in window)) {
        revealTargets.forEach((el) => el.classList.add('is-visible'));
        if (progress) progress.style.transform = 'scaleX(1)';
        return;
    }

    let previousY = window.scrollY;
    let scrollDirection = 'down';
    let ticking = false;

    const updateScrollEffects = () => {
        const currentY = window.scrollY;
        const delta = currentY - previousY;

        if (Math.abs(delta) > 3) {
            scrollDirection = delta > 0 ? 'down' : 'up';
            document.documentElement.dataset.scrollDirection = scrollDirection;
        }

        if (progress) {
            const scrollable = Math.max(document.documentElement.scrollHeight - window.innerHeight, 1);
            const ratio = Math.min(Math.max(currentY / scrollable, 0), 1);
            progress.style.transform = `scaleX(${ratio})`;
        }

        parallaxTargets.forEach((el) => {
            const rect = el.getBoundingClientRect();
            if (rect.bottom < -120 || rect.top > window.innerHeight + 120) return;

            const speed = Number.parseFloat(el.dataset.parallax || '0.04');
            const distanceFromCenter = rect.top + rect.height / 2 - window.innerHeight / 2;
            const shift = Math.max(-28, Math.min(28, distanceFromCenter * speed * -1));
            el.style.setProperty('--parallax-shift', `${shift.toFixed(2)}px`);
        });

        previousY = currentY;
        ticking = false;
    };

    const requestScrollUpdate = () => {
        if (!ticking) {
            window.requestAnimationFrame(updateScrollEffects);
            ticking = true;
        }
    };

    const revealObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                const el = entry.target;

                if (entry.isIntersecting) {
                    el.dataset.revealOrigin = scrollDirection === 'up' ? 'top' : 'bottom';
                    window.requestAnimationFrame(() => el.classList.add('is-visible'));
                    return;
                }

                const rect = entry.boundingClientRect;
                if (rect.bottom <= 0) {
                    el.dataset.revealOrigin = 'top';
                    el.classList.remove('is-visible');
                } else if (rect.top >= window.innerHeight) {
                    el.dataset.revealOrigin = 'bottom';
                    el.classList.remove('is-visible');
                }
            });
        },
        { threshold: 0.01 }
    );

    revealTargets.forEach((el) => {
        el.dataset.revealOrigin = el.getBoundingClientRect().bottom < 0 ? 'top' : 'bottom';
        revealObserver.observe(el);
    });

    document.documentElement.classList.add('motion-ready');
    document.documentElement.dataset.scrollDirection = scrollDirection;
    window.addEventListener('scroll', requestScrollUpdate, { passive: true });
    window.addEventListener('resize', requestScrollUpdate, { passive: true });
    updateScrollEffects();
};

document.addEventListener('DOMContentLoaded', initMotion, { once: true });
if (document.readyState !== 'loading') initMotion();
