import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import 'trix';
import 'trix/dist/trix.css';

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
    active: sections[0] ?? '',
    init() {
        const onScroll = () => (this.scrolled = window.scrollY > 24);
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });

        this.$watch('open', (value) => {
            document.body.classList.toggle('overflow-hidden', value);
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

Alpine.start();

/* ---------------- Reveal-on-scroll ---------------- */
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const revealTargets = () => document.querySelectorAll('.reveal:not(.is-visible)');

if (prefersReducedMotion || !('IntersectionObserver' in window)) {
    // Tampilkan langsung tanpa animasi.
    document.addEventListener('DOMContentLoaded', () => {
        revealTargets().forEach((el) => el.classList.add('is-visible'));
    });
} else {
    const revealObserver = new IntersectionObserver(
        (entries, observer) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { rootMargin: '0px 0px -8% 0px', threshold: 0.08 }
    );

    const observeReveals = () => revealTargets().forEach((el) => revealObserver.observe(el));
    document.addEventListener('DOMContentLoaded', observeReveals);
    // Jaga-jaga bila skrip dimuat setelah DOMContentLoaded.
    if (document.readyState !== 'loading') observeReveals();
}
