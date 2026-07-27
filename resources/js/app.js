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

/* ---------------- Navbar: scroll state + scroll-spy + drawer ---------------- */
Alpine.data('siteNav', (sections = []) => ({
    scrolled: false,
    open: false,
    active: sections[0] ?? '',
    init() {
        let ticking = false;

        const updateNav = () => {
            this.scrolled = window.scrollY > 24;
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

/* ---------------- Musik latar: konfigurasi admin + preferensi pengunjung ---------------- */
Alpine.data('siteMusicPlayer', ({
    defaultPlaying = true,
    volume = 50,
    preferenceVersion = 1,
} = {}) => ({
    playing: Boolean(defaultPlaying),
    actuallyPlaying: false,
    playbackBlocked: false,
    volume: Math.min(100, Math.max(0, Number(volume) || 0)),
    preferenceVersion: Math.max(1, Number.parseInt(preferenceVersion, 10) || 1),
    unlockHandler: null,
    audioHandlers: null,
    init() {
        const storedPreference = this.readStoredPreference();
        const stored = storedPreference?.playing ?? null;
        const storedVersion = storedPreference?.version ?? null;
        const preferenceIsCurrent = (
            (stored === '0' || stored === '1')
            && storedVersion === this.preferenceVersion
        );

        this.playing = preferenceIsCurrent ? stored === '1' : Boolean(defaultPlaying);

        if (!preferenceIsCurrent) {
            this.persistPreference();
        }

        this.$watch('playing', (value) => {
            this.persistPreference(value);
        });
        this.$nextTick(() => {
            this.bindAudioEvents();

            // Pasang listener sebelum play() pertama agar interaksi pengguna
            // tidak terlewat ketika browser menolak autoplay setelah refresh.
            if (this.playing) this.listenForUnlock();

            this.sync();
        });
    },
    readStoredPreference() {
        try {
            return {
                playing: localStorage.getItem('fpk-music-playing'),
                version: Number.parseInt(
                    localStorage.getItem('fpk-music-preference-version'),
                    10
                ),
            };
        } catch {
            // Storage bisa ditolak oleh mode privasi; default admin tetap dipakai.
            return null;
        }
    },
    persistPreference(value = this.playing) {
        try {
            localStorage.setItem('fpk-music-playing', value ? '1' : '0');
            localStorage.setItem(
                'fpk-music-preference-version',
                String(this.preferenceVersion)
            );
        } catch {
            // Pemutaran tetap berfungsi walau browser menolak Web Storage.
        }
    },
    async sync() {
        const audio = this.$refs.audio;

        if (!audio) return;

        audio.volume = this.volume / 100;

        if (this.playing) {
            try {
                await audio.play();
                this.actuallyPlaying = true;
                this.playbackBlocked = false;
                this.removeUnlockListeners();
            } catch {
                // Autoplay bersuara umumnya ditolak sampai ada interaksi pengguna.
                // Listener sudah aktif sehingga sentuhan/tombol pertama langsung
                // memulai audio tanpa perlu menekan ikon musik dua kali.
                this.actuallyPlaying = false;
                this.playbackBlocked = true;
                this.listenForUnlock();
            }
        } else {
            this.playbackBlocked = false;
            this.actuallyPlaying = false;
            audio.pause();
            this.removeUnlockListeners();
        }
    },
    toggle() {
        // Jika preferensi sudah On tetapi autoplay diblokir, klik pada ikon
        // harus memulai musik—bukan malah mengubah preferensi menjadi Off.
        if (this.playing && !this.actuallyPlaying) {
            this.sync();

            return;
        }

        this.playing = !this.playing;
        this.sync();
    },
    bindAudioEvents() {
        const audio = this.$refs.audio;

        if (!audio || this.audioHandlers) return;

        this.audioHandlers = {
            playing: () => {
                this.actuallyPlaying = true;
                this.playbackBlocked = false;
                this.removeUnlockListeners();
            },
            pause: () => {
                this.actuallyPlaying = false;
            },
            error: () => {
                this.actuallyPlaying = false;
                this.playbackBlocked = this.playing;
            },
        };

        Object.entries(this.audioHandlers).forEach(([event, handler]) => {
            audio.addEventListener(event, handler);
        });
    },
    listenForUnlock() {
        if (this.unlockHandler) return;

        this.unlockHandler = () => {
            this.removeUnlockListeners();
            if (this.playing) this.sync();
        };

        document.addEventListener('pointerdown', this.unlockHandler, { once: true });
        document.addEventListener('pointerup', this.unlockHandler, { once: true });
        document.addEventListener('touchend', this.unlockHandler, { once: true });
        document.addEventListener('wheel', this.unlockHandler, { once: true, passive: true });
        document.addEventListener('keydown', this.unlockHandler, { once: true });
    },
    removeUnlockListeners() {
        if (!this.unlockHandler) return;

        document.removeEventListener('pointerdown', this.unlockHandler);
        document.removeEventListener('pointerup', this.unlockHandler);
        document.removeEventListener('touchend', this.unlockHandler);
        document.removeEventListener('wheel', this.unlockHandler);
        document.removeEventListener('keydown', this.unlockHandler);
        this.unlockHandler = null;
    },
    destroy() {
        this.removeUnlockListeners();

        const audio = this.$refs.audio;
        if (audio && this.audioHandlers) {
            Object.entries(this.audioHandlers).forEach(([event, handler]) => {
                audio.removeEventListener(event, handler);
            });
        }

        this.audioHandlers = null;
    },
}));

/* ---------------- Carousel pengurus: tombol + swipe native ---------------- */
Alpine.data('memberCarousel', () => ({
    canPrevious: false,
    canNext: false,
    init() {
        this.$nextTick(() => this.sync());
    },
    positions() {
        const track = this.$refs.track;
        const cards = [...(track?.querySelectorAll('[data-member-card]') || [])];

        if (!track || !cards.length) return [];

        const trackRect = track.getBoundingClientRect();
        const styles = window.getComputedStyle(track);
        const scrollPadding = Number.parseFloat(styles.scrollPaddingLeft) || 0;
        const maxScroll = Math.max(track.scrollWidth - track.clientWidth, 0);

        return cards.map((card) => {
            const cardRect = card.getBoundingClientRect();
            const position = track.scrollLeft + cardRect.left - trackRect.left - scrollPadding;

            return Math.min(Math.max(position, 0), maxScroll);
        });
    },
    currentIndex(positions = this.positions()) {
        const track = this.$refs.track;

        if (!track || !positions.length) return 0;

        return positions.reduce((nearest, position, index) => (
            Math.abs(position - track.scrollLeft) < Math.abs(positions[nearest] - track.scrollLeft)
                ? index
                : nearest
        ), 0);
    },
    move(direction) {
        const track = this.$refs.track;
        const positions = this.positions();

        if (!track || !positions.length) return;

        const index = this.currentIndex(positions);
        const targetIndex = Math.min(Math.max(index + direction, 0), positions.length - 1);

        track.scrollTo({
            left: positions[targetIndex],
            behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
        });
    },
    sync() {
        const track = this.$refs.track;

        if (!track) return;

        const maxScroll = Math.max(track.scrollWidth - track.clientWidth, 0);
        this.canPrevious = track.scrollLeft > 2;
        this.canNext = track.scrollLeft + track.clientWidth < track.scrollWidth - 2;

        if (maxScroll <= 2) {
            this.canPrevious = false;
            this.canNext = false;
        }
    },
}));

Alpine.data('passwordField', () => ({
    visible: false,
    toggle() {
        this.visible = !this.visible;
    },
}));

Alpine.data('adminPinGate', ({ initialSeconds = 0, setupRequired = false } = {}) => ({
    pin: '',
    setupRequired: Boolean(setupRequired),
    remaining: Math.max(0, Number.parseInt(initialSeconds, 10) || 0),
    timer: null,
    init() {
        if (this.remaining <= 0) return;

        this.timer = window.setInterval(() => {
            this.remaining = Math.max(0, this.remaining - 1);
            if (this.remaining === 0) this.stopTimer();
        }, 1000);
    },
    get formattedTime() {
        const minutes = Math.floor(this.remaining / 60);
        const seconds = this.remaining % 60;

        return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    },
    push(digit) {
        if (this.remaining > 0 || this.pin.length >= 6) return;
        this.pin += String(digit);
    },
    backspace() {
        if (this.remaining > 0) return;
        this.pin = this.pin.slice(0, -1);
    },
    clear() {
        if (this.remaining > 0) return;
        this.pin = '';
    },
    handleKey(event) {
        const target = event.target;
        const isEditableTarget = target instanceof HTMLElement
            && (target.matches('input, textarea, select') || target.isContentEditable);

        if (this.setupRequired || isEditableTarget || this.remaining > 0) return;

        if (/^\d$/.test(event.key)) {
            event.preventDefault();
            this.push(event.key);
        } else if (event.key === 'Backspace') {
            event.preventDefault();
            this.backspace();
        } else if (event.key === 'Escape') {
            event.preventDefault();
            this.clear();
        } else if (event.key === 'Enter' && this.pin.length === 6) {
            event.preventDefault();
            this.$refs.pinForm?.requestSubmit();
        }
    },
    stopTimer() {
        if (!this.timer) return;
        window.clearInterval(this.timer);
        this.timer = null;
    },
    destroy() {
        this.stopTimer();
    },
}));

Alpine.data('imagePreview', ({ initialUrl = '', initialState = 'empty' } = {}) => ({
    initialUrl,
    initialState,
    previewUrl: initialUrl,
    state: initialState,
    fileName: '',
    objectUrl: null,
    previewFailed: false,
    selectFile(event) {
        this.revokeObjectUrl();

        const file = event.target.files?.[0];

        if (!file) {
            this.restoreInitialPreview();
            return;
        }

        this.objectUrl = URL.createObjectURL(file);
        this.previewUrl = this.objectUrl;
        this.fileName = file.name;
        this.state = 'selected';
        this.previewFailed = false;
    },
    markPreviewFailed() {
        this.previewFailed = true;
    },
    restoreInitialPreview() {
        this.previewUrl = this.initialUrl;
        this.fileName = '';
        this.state = this.initialState;
        this.previewFailed = false;
    },
    revokeObjectUrl() {
        if (!this.objectUrl) return;

        URL.revokeObjectURL(this.objectUrl);
        this.objectUrl = null;
    },
    statusLabel() {
        if (this.state === 'selected') return 'Preview file baru';
        if (this.state === 'current') return 'Gambar saat ini';
        if (this.state === 'default') return 'Gambar bawaan';

        return 'Belum ada gambar';
    },
    destroy() {
        this.revokeObjectUrl();
    },
}));

Alpine.data('multiImagePreview', () => ({
    files: [],
    selectFiles(event) {
        this.revokeObjectUrls();
        this.files = [...(event.target.files || [])].map((file) => ({
            name: file.name,
            url: URL.createObjectURL(file),
        }));
    },
    revokeObjectUrls() {
        this.files.forEach((file) => URL.revokeObjectURL(file.url));
        this.files = [];
    },
    destroy() {
        this.revokeObjectUrls();
    },
}));

Alpine.start();

/* ---------------- Motion system: reveal sekali + progress + parallax ---------------- */
const motionMedia = window.matchMedia('(prefers-reduced-motion: reduce)');

const initMotion = () => {
    if (document.documentElement.dataset.motionInitialized === 'true') return;
    document.documentElement.dataset.motionInitialized = 'true';

    document.querySelectorAll('[data-motion-children]').forEach((container) => {
        [...container.children].forEach((child, index) => {
            if (child.matches('script, style, form, .admin-table-wrap, [data-no-auto-motion]')) return;
            child.classList.add('reveal');
            child.style.setProperty('--reveal-delay', `${Math.min(index * 55, 220)}ms`);
        });
    });

    const revealTargets = [...document.querySelectorAll('.reveal')];
    const progress = document.querySelector('[data-scroll-progress]');
    const parallaxTargets = window.matchMedia('(min-width: 768px)').matches
        ? [...document.querySelectorAll('[data-parallax]')]
        : [];

    if (motionMedia.matches || !('IntersectionObserver' in window)) {
        revealTargets.forEach((el) => el.classList.add('is-visible'));
        if (progress) progress.style.transform = 'scaleX(1)';
        return;
    }

    let ticking = false;

    const updateScrollEffects = () => {
        const currentY = window.scrollY;
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
                    window.requestAnimationFrame(() => el.classList.add('is-visible'));
                    revealObserver.unobserve(el);
                }
            });
        },
        { rootMargin: '0px 0px -8% 0px', threshold: 0.08 }
    );

    revealTargets.forEach((el) => {
        revealObserver.observe(el);
    });

    document.documentElement.classList.add('motion-ready');
    window.addEventListener('scroll', requestScrollUpdate, { passive: true });
    window.addEventListener('resize', requestScrollUpdate, { passive: true });
    updateScrollEffects();
};

document.addEventListener('DOMContentLoaded', initMotion, { once: true });
if (document.readyState !== 'loading') initMotion();
