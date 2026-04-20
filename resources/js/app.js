import './bootstrap';

import Alpine from 'alpinejs';

Alpine.data('heroExperience', (config = {}) => ({
    activeMode: 'slides',
    slide: 0,
    slideTimer: null,
    slideLoopTimer: null,
    hasVideo: Boolean(config.hasVideo),
    hasSlides: Boolean(config.hasSlides),
    slideCount: Number(config.slideCount || 0),
    slideDuration: Number(config.slideDuration || 6000),
    init() {
        if (this.hasVideo) {
            this.playVideo();
            return;
        }

        this.startSlides();
    },
    resetTimers() {
        if (this.slideTimer) {
            window.clearInterval(this.slideTimer);
            this.slideTimer = null;
        }

        if (this.slideLoopTimer) {
            window.clearTimeout(this.slideLoopTimer);
            this.slideLoopTimer = null;
        }
    },
    playVideo() {
        this.resetTimers();
        this.activeMode = 'video';

        this.$nextTick(() => {
            const video = this.$refs.heroVideo;

            if (!video) {
                this.startSlides();
                return;
            }

            video.currentTime = 0;

            const playback = video.play();

            if (playback && typeof playback.catch === 'function') {
                playback.catch(() => this.startSlides());
            }
        });
    },
    startSlides() {
        if (!this.hasSlides || this.slideCount < 1) {
            if (this.hasVideo) {
                this.playVideo();
            }

            return;
        }

        this.resetTimers();
        this.activeMode = 'slides';
        this.slide = 0;

        if (this.slideCount > 1) {
            this.slideTimer = window.setInterval(() => {
                this.slide = (this.slide + 1) % this.slideCount;
            }, this.slideDuration);
        }

        this.slideLoopTimer = window.setTimeout(() => {
            if (this.hasVideo) {
                this.playVideo();
                return;
            }

            this.startSlides();
        }, this.slideDuration * this.slideCount);
    },
    handleVideoEnded() {
        this.startSlides();
    },
}));

Alpine.data('adminSectionBrowser', (config = {}) => ({
    query: '',
    activeCategory: 'all',
    categories: Array.isArray(config.categories) ? config.categories : [],
    sections: Array.isArray(config.sections) ? config.sections : [],
    normalize(value = '') {
        return String(value).trim().toLowerCase();
    },
    sectionMatches(section) {
        if (!section) {
            return true;
        }

        if (this.activeCategory !== 'all' && section.category !== this.activeCategory) {
            return false;
        }

        const needle = this.normalize(this.query);

        if (needle === '') {
            return true;
        }

        const haystack = [
            section.title,
            section.description,
            ...(Array.isArray(section.keywords) ? section.keywords : []),
        ]
            .filter(Boolean)
            .join(' ')
            .toLowerCase();

        return haystack.includes(needle);
    },
    findSection(id) {
        return this.sections.find((section) => section.id === id) ?? null;
    },
    matchesSection(id) {
        return this.sectionMatches(this.findSection(id));
    },
    matchesAny(ids = []) {
        return ids.some((id) => this.matchesSection(id));
    },
    categoryCount(category) {
        return this.sections.filter((section) => {
            if (section.category !== category) {
                return false;
            }

            const needle = this.normalize(this.query);

            if (needle === '') {
                return true;
            }

            const haystack = [
                section.title,
                section.description,
                ...(Array.isArray(section.keywords) ? section.keywords : []),
            ]
                .filter(Boolean)
                .join(' ')
                .toLowerCase();

            return haystack.includes(needle);
        }).length;
    },
    get visibleCount() {
        return this.sections.filter((section) => this.sectionMatches(section)).length;
    },
    get hasActiveFilters() {
        return this.activeCategory !== 'all' || this.normalize(this.query) !== '';
    },
    resetFilters() {
        this.query = '';
        this.activeCategory = 'all';
    },
}));

Alpine.data('contactField', (config = {}) => ({
    target: config.target ?? '',
    supported: typeof navigator !== 'undefined'
        && !!navigator.contacts
        && typeof navigator.contacts.select === 'function',
    async pick() {
        if (!this.supported || !this.target) {
            return;
        }

        try {
            const contacts = await navigator.contacts.select(['name', 'tel'], { multiple: false });
            const contact = contacts?.[0];
            const number = contact?.tel?.[0];

            if (!number) {
                return;
            }

            const input = document.getElementById(this.target);
            if (!input) {
                return;
            }

            input.value = number;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        } catch (error) {
            console.warn('Contact picker unavailable', error);
        }
    },
}));

window.Alpine = Alpine;

Alpine.start();
