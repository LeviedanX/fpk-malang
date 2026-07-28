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
    menuTrigger: null,
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
            if (value) {
                this.$nextTick(() => this.$refs.mobileMenu
                    ?.querySelector('a, button')
                    ?.focus());
            }
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
    toggleMenu(event) {
        if (this.open) {
            this.closeMenu();
            return;
        }

        this.menuTrigger = event.currentTarget;
        this.open = true;
    },
    closeMenu(restoreFocus = true) {
        if (!this.open) return;

        this.open = false;
        if (restoreFocus) this.$nextTick(() => this.menuTrigger?.focus());
    },
    trapMenuFocus(event) {
        const focusable = [...(this.$refs.mobileMenu?.querySelectorAll('a, button') || [])];
        if (!focusable.length) return;

        const first = focusable[0];
        const last = focusable.at(-1);

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    },
    destroy() {
        document.body.classList.remove('overflow-hidden');
    },
}));

/* ---------------- Musik latar: konfigurasi admin + preferensi pengunjung ---------------- */
Alpine.data('siteMusicPlayer', ({
    volume = 50,
    preferenceVersion = 1,
} = {}) => ({
    playing: false,
    actuallyPlaying: false,
    playbackBlocked: false,
    volume: Math.min(100, Math.max(0, Number(volume) || 0)),
    preferenceVersion: Math.max(1, Number.parseInt(preferenceVersion, 10) || 1),
    audioHandlers: null,
    init() {
        this.$watch('playing', (value) => {
            this.persistPreference(value);
        });
        this.$nextTick(() => {
            this.bindAudioEvents();
            if (this.$refs.audio) this.$refs.audio.volume = this.volume / 100;
        });
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
            } catch {
                // Musik hanya pernah dimulai lewat klik pengguna (autoplay
                // dimatikan), jadi penolakan di sini jarang terjadi—misalnya
                // Safari mode hemat daya. Tandai saja: toggle() akan mencoba
                // memutar ulang pada klik berikutnya alih-alih mematikan
                // preferensi yang sudah On.
                this.actuallyPlaying = false;
                this.playbackBlocked = true;
            }
        } else {
            this.playbackBlocked = false;
            this.actuallyPlaying = false;
            audio.pause();
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
    destroy() {
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

/* ---------------- Chat tamu: polling adaptif + unggah gambar ----------------

   Situs publik berjalan tanpa sesi agar HTML-nya tetap bisa di-cache, sehingga
   identitas tamu berupa token acak yang dikembalikan server saat pesan pertama
   terkirim. Token disimpan di localStorage (bukan cookie) dan dikirim lewat
   header X-Chat-Token, jadi browser tidak pernah melampirkannya pada permintaan
   lintas situs.

   Biaya polling ditekan berlapis: server menjawab 204 tanpa body ketika tidak
   ada pesan baru, interval melebar otomatis saat percakapan sepi, dan polling
   berhenti total ketika tab disembunyikan lalu langsung menyusul begitu tab
   kembali aktif. */
const CHAT_TOKEN_KEY = 'fpk-chat-token';

/* Tangga interval polling dalam milidetik. Naik satu anak tangga setiap kali
   server menjawab "tidak ada yang baru"; kembali ke anak tangga pertama begitu
   ada aktivitas. */
const CHAT_INTERVALS_OPEN = [2500, 4000, 8000, 15000, 30000];
const CHAT_INTERVAL_CLOSED = 60000;

Alpine.data('guestChat', ({ endpoints, maxImageSize = 2097152 } = {}) => ({
    open: false,
    loading: false,
    loadingOlder: false,
    sending: false,
    hasMore: false,
    booted: false,
    messages: [],
    unread: 0,
    draft: '',
    error: '',
    imageFile: null,
    imagePreview: null,
    lastId: 0,
    timer: null,
    step: 0,
    inFlight: false,
    launcher: null,

    init() {
        this.launcher = this.$root.querySelector('.chat-launcher');

        /* Tamu yang pernah menulis punya token: cek sekali (hemat, karena
           server menjawab 204 bila tidak ada apa pun) supaya lencana pesan
           belum dibaca sudah benar sebelum panel dibuka. */
        if (this.token) {
            this.schedule(1500);
        }

        this.onVisibility = () => {
            if (document.hidden) {
                this.stop();
                return;
            }
            /* Kembali ke tab: susul segera, lalu lanjut dari interval tercepat. */
            this.step = 0;
            this.schedule(300);
        };

        document.addEventListener('visibilitychange', this.onVisibility);
    },

    get token() {
        try {
            return localStorage.getItem(CHAT_TOKEN_KEY) || '';
        } catch {
            /* Mode privasi ketat: chat tetap jalan untuk sesi ini saja. */
            return this.memoryToken || '';
        }
    },

    set token(value) {
        this.memoryToken = value;
        try {
            localStorage.setItem(CHAT_TOKEN_KEY, value);
        } catch {
            /* Diabaikan: token sudah tersimpan di memori komponen. */
        }
    },

    get statusText() {
        if (this.sending) return 'Mengirim…';
        if (this.error) return 'Gangguan koneksi';

        return 'Biasanya dibalas pada jam kerja';
    },

    headers(extra = {}) {
        const headers = { Accept: 'application/json', ...extra };
        const token = this.token;

        if (token) headers['X-Chat-Token'] = token;

        return headers;
    },

    async toggle() {
        if (this.open) {
            this.close();
            return;
        }

        this.open = true;
        this.step = 0;

        if (!this.booted) await this.boot();

        this.$nextTick(() => {
            this.scrollToBottom(true);
            this.$refs.input?.focus();
        });

        this.markSeen();
        this.schedule(CHAT_INTERVALS_OPEN[0]);
    },

    close() {
        if (!this.open) return;

        this.open = false;
        this.step = 0;
        /* Panel tertutup tetap memantau balasan admin, tetapi jauh lebih jarang. */
        this.schedule(CHAT_INTERVAL_CLOSED);
        this.launcher?.focus();
    },

    async boot() {
        if (!this.token) {
            this.booted = true;
            return;
        }

        this.loading = true;

        try {
            const response = await fetch(`${endpoints.show}?seen=1`, {
                headers: this.headers(),
                credentials: 'omit',
            });

            if (response.status === 403) {
                this.forget();
                return;
            }

            if (!response.ok) throw new Error('gagal');

            const data = await response.json();

            this.messages = data.messages || [];
            this.hasMore = Boolean(data.has_more);
            this.lastId = this.messages.length ? this.messages.at(-1).id : 0;
            this.unread = 0;
            this.booted = true;
            this.error = '';
        } catch {
            this.error = 'Percakapan gagal dimuat. Periksa koneksi Anda.';
        } finally {
            this.loading = false;
        }
    },

    async loadOlder() {
        const oldest = this.messages.find((message) => !message.pending);

        if (!oldest || this.loadingOlder) return;

        this.loadingOlder = true;
        const log = this.$refs.log;
        const previousHeight = log?.scrollHeight ?? 0;

        try {
            const response = await fetch(`${endpoints.history}?before=${oldest.id}`, {
                headers: this.headers(),
                credentials: 'omit',
            });

            if (!response.ok) throw new Error('gagal');

            const data = await response.json();
            this.messages = [...(data.messages || []), ...this.messages];
            this.hasMore = Boolean(data.has_more);

            /* Pertahankan posisi baca: geser scroll sebesar tinggi yang baru
               ditambahkan agar pesan yang sedang dilihat tidak melompat. */
            this.$nextTick(() => {
                if (log) log.scrollTop = log.scrollHeight - previousHeight;
            });
        } catch {
            this.error = 'Riwayat lama gagal dimuat.';
        } finally {
            this.loadingOlder = false;
        }
    },

    schedule(delay) {
        this.stop();

        if (document.hidden) return;

        this.timer = window.setTimeout(() => this.poll(), delay);
    },

    stop() {
        if (!this.timer) return;

        window.clearTimeout(this.timer);
        this.timer = null;
    },

    async poll() {
        if (this.inFlight || !this.token) {
            this.schedule(this.nextDelay(false));
            return;
        }

        this.inFlight = true;

        try {
            const seen = this.open && !document.hidden ? '&seen=1' : '';
            const response = await fetch(`${endpoints.poll}?after=${this.lastId}${seen}`, {
                headers: this.headers(),
                credentials: 'omit',
            });

            if (response.status === 403) {
                this.forget();
                return;
            }

            /* 204 = tidak ada pesan baru. Jalur ini yang paling sering diambil,
               dan sengaja tidak menyentuh DOM sama sekali. */
            if (response.status === 204) {
                this.schedule(this.nextDelay(false));
                return;
            }

            if (!response.ok) throw new Error('gagal');

            const data = await response.json();
            this.merge(data.messages || []);
            this.error = '';
            this.schedule(this.nextDelay(true));
        } catch {
            /* Kegagalan jaringan tidak memunculkan galat—cukup melambat supaya
               tidak membanjiri server yang sedang bermasalah. */
            this.schedule(this.nextDelay(false));
        } finally {
            this.inFlight = false;
        }
    },

    nextDelay(hadActivity) {
        if (!this.open) return CHAT_INTERVAL_CLOSED;

        this.step = hadActivity ? 0 : Math.min(this.step + 1, CHAT_INTERVALS_OPEN.length - 1);

        return CHAT_INTERVALS_OPEN[this.step];
    },

    merge(incoming) {
        if (!incoming.length) return;

        const atBottom = this.isAtBottom();
        const known = new Set(this.messages.map((message) => message.id));

        incoming.forEach((message) => {
            if (known.has(message.id)) return;

            this.messages.push(message);
            this.lastId = Math.max(this.lastId, message.id);

            if (message.sender === 'admin' && !this.open) this.unread += 1;
        });

        /* Hanya ikut turun bila pengguna memang sedang di dasar transkrip,
           supaya tidak menyeret orang yang sedang membaca ke atas. */
        if (atBottom) this.$nextTick(() => this.scrollToBottom());
    },

    async send() {
        const body = this.draft.trim();

        if (this.sending || (!body && !this.imageFile)) return;

        this.sending = true;
        this.error = '';

        /* Optimistic UI: gelembung muncul seketika dengan id sementara negatif
           agar tidak pernah bentrok dengan id dari server. */
        const optimistic = {
            id: -Date.now(),
            sender: 'guest',
            body: body || null,
            image: this.imagePreview,
            image_width: null,
            image_height: null,
            time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
            date: new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }),
            pending: true,
        };

        this.messages.push(optimistic);
        this.$nextTick(() => this.scrollToBottom());

        const payload = new FormData();
        if (body) payload.append('body', body);
        if (this.imageFile) payload.append('image', this.imageFile);

        const sentPreview = this.imagePreview;
        this.draft = '';
        this.imageFile = null;
        this.imagePreview = null;
        if (this.$refs.image) this.$refs.image.value = '';
        this.$nextTick(() => this.autoGrow());

        try {
            const response = await fetch(endpoints.store, {
                method: 'POST',
                headers: this.headers(),
                body: payload,
                credentials: 'omit',
            });

            if (response.status === 429) throw new Error('Terlalu banyak pesan. Coba lagi sebentar lagi.');

            if (response.status === 422) {
                const data = await response.json();
                throw new Error(Object.values(data.errors || {}).flat()[0] || 'Pesan tidak valid.');
            }

            if (response.status === 403) {
                this.forget();
                throw new Error('Percakapan ini telah ditutup oleh admin.');
            }

            if (!response.ok) throw new Error('Pesan gagal terkirim.');

            const data = await response.json();

            if (data.token) this.token = data.token;

            /* Ganti gelembung sementara dengan baris asli dari server. */
            const index = this.messages.findIndex((message) => message.id === optimistic.id);
            if (index !== -1) this.messages.splice(index, 1, data.message);
            this.lastId = Math.max(this.lastId, data.message.id);
            this.booted = true;

            /* Balasan biasanya datang tepat setelah tamu menulis: kembali ke
               interval tercepat. */
            this.step = 0;
            this.schedule(CHAT_INTERVALS_OPEN[0]);
        } catch (exception) {
            const failed = this.messages.find((message) => message.id === optimistic.id);
            if (failed) {
                failed.pending = false;
                failed.failed = true;
            }
            this.error = exception.message || 'Pesan gagal terkirim.';
        } finally {
            if (sentPreview) URL.revokeObjectURL(sentPreview);
            this.sending = false;
            this.$nextTick(() => this.$refs.input?.focus());
        }
    },

    selectImage(event) {
        const file = event.target.files?.[0];

        if (!file) return;

        if (file.size > maxImageSize) {
            this.error = `Ukuran gambar maksimal ${Math.round(maxImageSize / 1024 / 1024)} MB.`;
            event.target.value = '';
            return;
        }

        this.clearImage(false);
        this.imageFile = file;
        this.imagePreview = URL.createObjectURL(file);
        this.error = '';
    },

    clearImage(resetInput = true) {
        if (this.imagePreview) URL.revokeObjectURL(this.imagePreview);

        this.imageFile = null;
        this.imagePreview = null;
        if (resetInput && this.$refs.image) this.$refs.image.value = '';
    },

    markSeen() {
        this.unread = 0;
    },

    showDaySeparator(index) {
        if (index === 0) return true;

        return this.messages[index].date !== this.messages[index - 1].date;
    },

    isAtBottom() {
        const log = this.$refs.log;

        if (!log) return true;

        return log.scrollHeight - log.scrollTop - log.clientHeight < 80;
    },

    onScroll() {
        const log = this.$refs.log;

        if (this.hasMore && !this.loadingOlder && log && log.scrollTop < 40) {
            this.loadOlder();
        }
    },

    scrollToBottom(instant = false) {
        const log = this.$refs.log;

        if (!log) return;

        log.scrollTo({
            top: log.scrollHeight,
            behavior: instant || window.matchMedia('(prefers-reduced-motion: reduce)').matches
                ? 'auto'
                : 'smooth',
        });
    },

    insertNewline() {
        this.draft += '\n';
        this.$nextTick(() => this.autoGrow());
    },

    autoGrow() {
        const input = this.$refs.input;

        if (!input) return;

        input.style.height = 'auto';
        input.style.height = `${Math.min(input.scrollHeight, 120)}px`;
    },

    trapFocus(event) {
        const focusable = [...this.$refs.panel.querySelectorAll(
            'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), label.chat-icon-button'
        )].filter((element) => element.offsetParent !== null);

        if (!focusable.length) return;

        const first = focusable[0];
        const last = focusable.at(-1);

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    },

    /* Percakapan dihapus atau diblokir admin: buang token supaya tamu bisa
       memulai percakapan baru dari nol. */
    forget() {
        try {
            localStorage.removeItem(CHAT_TOKEN_KEY);
        } catch {
            /* Abaikan. */
        }

        this.memoryToken = '';
        this.messages = [];
        this.lastId = 0;
        this.unread = 0;
        this.hasMore = false;
        this.booted = true;
        this.stop();
    },

    destroy() {
        this.stop();
        this.clearImage(false);
        document.removeEventListener('visibilitychange', this.onVisibility);
    },
}));

/* ---------------- Chat admin: transkrip langsung + balasan ----------------

   Sisi admin memakai sesi biasa, jadi permintaannya menyertakan cookie dan
   token CSRF. Pola pollingnya sama dengan sisi tamu: server menjawab 204 saat
   sepi, interval melebar sendiri, dan berhenti ketika tab tidak terlihat. */
const ADMIN_CHAT_INTERVALS = [3000, 5000, 10000, 20000, 40000];

Alpine.data('adminChatThread', ({
    endpoints,
    seed = [],
    lastId = 0,
    hasMore = false,
    maxImageSize = 2097152,
    csrf = '',
} = {}) => ({
    messages: seed,
    lastId,
    hasMore,
    loadingOlder: false,
    sending: false,
    draft: '',
    error: '',
    imageFile: null,
    imagePreview: null,
    timer: null,
    step: 0,
    inFlight: false,

    init() {
        this.$nextTick(() => this.scrollToBottom(true));

        this.onVisibility = () => {
            if (document.hidden) {
                this.stop();
                return;
            }
            this.step = 0;
            this.schedule(300);
        };

        document.addEventListener('visibilitychange', this.onVisibility);
        this.schedule(ADMIN_CHAT_INTERVALS[0]);
    },

    schedule(delay) {
        this.stop();

        if (document.hidden) return;

        this.timer = window.setTimeout(() => this.poll(), delay);
    },

    stop() {
        if (!this.timer) return;

        window.clearTimeout(this.timer);
        this.timer = null;
    },

    async poll() {
        if (this.inFlight) {
            this.schedule(this.nextDelay(false));
            return;
        }

        this.inFlight = true;

        try {
            const response = await fetch(`${endpoints.poll}?after=${this.lastId}`, {
                headers: { Accept: 'application/json' },
            });

            if (response.status === 204) {
                this.schedule(this.nextDelay(false));
                return;
            }

            if (!response.ok) throw new Error('gagal');

            const data = await response.json();
            this.merge(data.messages || []);
            this.schedule(this.nextDelay(true));
        } catch {
            this.schedule(this.nextDelay(false));
        } finally {
            this.inFlight = false;
        }
    },

    nextDelay(hadActivity) {
        this.step = hadActivity ? 0 : Math.min(this.step + 1, ADMIN_CHAT_INTERVALS.length - 1);

        return ADMIN_CHAT_INTERVALS[this.step];
    },

    merge(incoming) {
        if (!incoming.length) return;

        const atBottom = this.isAtBottom();
        const known = new Set(this.messages.map((message) => message.id));

        incoming.forEach((message) => {
            if (known.has(message.id)) return;

            this.messages.push(message);
            this.lastId = Math.max(this.lastId, message.id);
        });

        if (atBottom) this.$nextTick(() => this.scrollToBottom());
    },

    async loadOlder() {
        const oldest = this.messages.find((message) => !message.pending);

        if (!oldest || this.loadingOlder) return;

        this.loadingOlder = true;
        const log = this.$refs.log;
        const previousHeight = log?.scrollHeight ?? 0;

        try {
            const response = await fetch(`${endpoints.history}?before=${oldest.id}`, {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) throw new Error('gagal');

            const data = await response.json();
            this.messages = [...(data.messages || []), ...this.messages];
            this.hasMore = Boolean(data.has_more);

            this.$nextTick(() => {
                if (log) log.scrollTop = log.scrollHeight - previousHeight;
            });
        } catch {
            this.error = 'Riwayat lama gagal dimuat.';
        } finally {
            this.loadingOlder = false;
        }
    },

    async send() {
        const body = this.draft.trim();

        if (this.sending || (!body && !this.imageFile)) return;

        this.sending = true;
        this.error = '';

        const optimistic = {
            id: -Date.now(),
            sender: 'admin',
            body: body || null,
            image: this.imagePreview,
            image_width: null,
            image_height: null,
            time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
            date: new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }),
            pending: true,
        };

        this.messages.push(optimistic);
        this.$nextTick(() => this.scrollToBottom());

        const payload = new FormData();
        if (body) payload.append('body', body);
        if (this.imageFile) payload.append('image', this.imageFile);

        const sentPreview = this.imagePreview;
        this.draft = '';
        this.imageFile = null;
        this.imagePreview = null;
        if (this.$refs.image) this.$refs.image.value = '';
        this.$nextTick(() => this.autoGrow());

        try {
            const response = await fetch(endpoints.reply, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                body: payload,
            });

            if (response.status === 422) {
                const data = await response.json();
                throw new Error(Object.values(data.errors || {}).flat()[0] || 'Balasan tidak valid.');
            }

            if (!response.ok) throw new Error('Balasan gagal terkirim.');

            const data = await response.json();
            const index = this.messages.findIndex((message) => message.id === optimistic.id);
            if (index !== -1) this.messages.splice(index, 1, data.message);
            this.lastId = Math.max(this.lastId, data.message.id);

            this.step = 0;
            this.schedule(ADMIN_CHAT_INTERVALS[0]);
        } catch (exception) {
            const failed = this.messages.find((message) => message.id === optimistic.id);
            if (failed) {
                failed.pending = false;
                failed.failed = true;
            }
            this.error = exception.message || 'Balasan gagal terkirim.';
        } finally {
            if (sentPreview) URL.revokeObjectURL(sentPreview);
            this.sending = false;
            this.$nextTick(() => this.$refs.input?.focus());
        }
    },

    selectImage(event) {
        const file = event.target.files?.[0];

        if (!file) return;

        if (file.size > maxImageSize) {
            this.error = `Ukuran gambar maksimal ${Math.round(maxImageSize / 1024 / 1024)} MB.`;
            event.target.value = '';
            return;
        }

        this.clearImage(false);
        this.imageFile = file;
        this.imagePreview = URL.createObjectURL(file);
        this.error = '';
    },

    clearImage(resetInput = true) {
        if (this.imagePreview) URL.revokeObjectURL(this.imagePreview);

        this.imageFile = null;
        this.imagePreview = null;
        if (resetInput && this.$refs.image) this.$refs.image.value = '';
    },

    showDaySeparator(index) {
        if (index === 0) return true;

        return this.messages[index].date !== this.messages[index - 1].date;
    },

    isAtBottom() {
        const log = this.$refs.log;

        if (!log) return true;

        return log.scrollHeight - log.scrollTop - log.clientHeight < 80;
    },

    onScroll() {
        const log = this.$refs.log;

        if (this.hasMore && !this.loadingOlder && log && log.scrollTop < 40) {
            this.loadOlder();
        }
    },

    scrollToBottom(instant = false) {
        const log = this.$refs.log;

        if (!log) return;

        log.scrollTo({
            top: log.scrollHeight,
            behavior: instant || window.matchMedia('(prefers-reduced-motion: reduce)').matches
                ? 'auto'
                : 'smooth',
        });
    },

    insertNewline() {
        this.draft += '\n';
        this.$nextTick(() => this.autoGrow());
    },

    autoGrow() {
        const input = this.$refs.input;

        if (!input) return;

        input.style.height = 'auto';
        input.style.height = `${Math.min(input.scrollHeight, 120)}px`;
    },

    destroy() {
        this.stop();
        this.clearImage(false);
        document.removeEventListener('visibilitychange', this.onVisibility);
    },
}));

Alpine.start();

/* ---------------- Motion system: reveal sekali + progress + parallax ---------------- */
const motionMedia = window.matchMedia('(prefers-reduced-motion: reduce)');

const initMotion = () => {
    if (document.documentElement.dataset.motionInitialized === 'true') return;
    document.documentElement.dataset.motionInitialized = 'true';

    // Panel admin menampilkan kontennya sejak frame pertama, jadi bloknya tidak
    // perlu didekorasi maupun diamati sama sekali: pada halaman padat seperti
    // Pengaturan Website itu memangkas puluhan observasi IntersectionObserver
    // per perpindahan halaman. Aturan CSS-nya ada di .motion-ready .admin-shell
    // .reveal, yang tetap menjaga blok terlihat andai ada markup admin yang
    // menulis class reveal sendiri.
    const isAdminChrome = (el) => el.closest('.admin-shell') !== null;

    document.querySelectorAll('[data-motion-children]').forEach((container) => {
        if (isAdminChrome(container)) return;

        [...container.children].forEach((child, index) => {
            if (child.matches('script, style, form, .admin-table-wrap, [data-no-auto-motion]')) return;
            child.classList.add('reveal');
            child.style.setProperty('--reveal-delay', `${Math.min(index * 55, 220)}ms`);
        });
    });

    const revealTargets = [...document.querySelectorAll('.reveal')].filter((el) => !isAdminChrome(el));
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

/* ---------------- Back to top ---------------- */
const initBackToTop = () => {
    const button = document.querySelector('[data-back-to-top]');
    if (!button || button.dataset.backToTopReady === 'true') return;
    button.dataset.backToTopReady = 'true';

    // Markup mengirim tombol dalam keadaan [hidden] agar pengguna tanpa
    // JavaScript tidak melihat tombol yang tidak bisa berfungsi. Begitu skrip
    // jalan, atribut itu dilepas sekali saja: selanjutnya visibilitas diatur
    // lewat kelas supaya transisi opacity benar-benar berjalan (elemen yang
    // berpindah dari display:none tidak mentransisikan apa pun).
    button.hidden = false;

    // Ambang munculnya tombol: setelah pengguna melewati kira-kira satu layar.
    const threshold = () => Math.max(window.innerHeight * 0.6, 320);
    let ticking = false;

    const syncVisibility = () => {
        ticking = false;
        button.classList.toggle('is-visible', window.scrollY > threshold());
    };

    const requestSync = () => {
        if (ticking) return;
        ticking = true;
        window.requestAnimationFrame(syncVisibility);
    };

    button.addEventListener('click', () => {
        // Hormati preferensi pengguna yang menonaktifkan animasi.
        window.scrollTo({
            top: 0,
            behavior: motionMedia.matches ? 'auto' : 'smooth',
        });
    });

    window.addEventListener('scroll', requestSync, { passive: true });
    window.addEventListener('resize', requestSync, { passive: true });
    syncVisibility();
};

document.addEventListener('DOMContentLoaded', initBackToTop, { once: true });
if (document.readyState !== 'loading') initBackToTop();

/* ---------------- Lencana chat belum dibaca di sidebar admin ----------------

   Nilai awalnya sudah dirender server; ini hanya menyegarkannya supaya admin
   yang membiarkan satu halaman terbuka tetap melihat pesan masuk. Intervalnya
   longgar dan berhenti saat tab disembunyikan karena ini sekadar indikator. */
const initChatBadge = () => {
    const badge = document.querySelector('[data-chat-unread-badge]');
    const endpoint = document.body?.dataset.chatUnreadUrl;

    if (!badge || !endpoint || badge.dataset.ready === 'true') return;
    badge.dataset.ready = 'true';

    let timer = null;

    const sync = async () => {
        try {
            const response = await fetch(endpoint, { headers: { Accept: 'application/json' } });

            if (!response.ok) return;

            const { total = 0 } = await response.json();
            badge.textContent = total > 9 ? '9+' : String(total);
            badge.hidden = total < 1;
        } catch {
            /* Indikator opsional: kegagalan jaringan tidak perlu dilaporkan. */
        }
    };

    const schedule = () => {
        window.clearTimeout(timer);
        if (document.hidden) return;
        timer = window.setTimeout(async () => {
            await sync();
            schedule();
        }, 30000);
    };

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            window.clearTimeout(timer);
            return;
        }
        sync();
        schedule();
    });

    schedule();
};

document.addEventListener('DOMContentLoaded', initChatBadge, { once: true });
if (document.readyState !== 'loading') initChatBadge();
