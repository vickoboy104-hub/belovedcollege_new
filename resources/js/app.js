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

Alpine.data('portalNavigation', (config = {}) => ({
    open: false,
    openGroup: '',
    activeSection: 'overview',
    navSearch: '',
    init() {
        this.openGroup = config.defaultOpenGroup || localStorage.getItem('sms_nav_group') || '';
        this.activeSection = new URLSearchParams(window.location.search).get('section') || config.activeSection || 'overview';
    },
    normalize(value = '') {
        return String(value)
            .toLowerCase()
            .replace(/\s+/g, ' ')
            .trim();
    },
    hasNavSearch() {
        return this.navSearch.length > 0;
    },
    matchesNav(...values) {
        if (!this.hasNavSearch()) {
            return true;
        }

        return values.some((value) => this.normalize(value).includes(this.navSearch));
    },
    setNavSearch(query = '') {
        this.navSearch = this.normalize(query);
    },
    toggleOpenGroup(group) {
        this.openGroup = this.openGroup === group ? '' : group;
        localStorage.setItem('sms_nav_group', this.openGroup);
    },
    navGroupExpanded(group, searchText = '') {
        return this.openGroup === group || (this.hasNavSearch() && this.matchesNav(searchText));
    },
}));

Alpine.data('portalSidebarSearch', (config = {}) => ({
    query: '',
    clearSearch() {
        this.query = '';
        this.setNavSearch('');
    },
    runSearch() {
        this.setNavSearch(this.query);
    }
}));

Alpine.data('globalSearch', (items = []) => ({
    open: false,
    query: '',
    items: items,
    selectedIndex: 0,
    get filteredItems() {
        if (this.query.trim() === '') {
            return [];
        }
        
        const needleWords = this.query.toLowerCase().trim().split(/\s+/);
        return this.items.filter(item => {
            const haystack = [item.label, item.context, item.trail, item.keywords, item.type].filter(Boolean).join(' ').toLowerCase();
            return needleWords.every(word => haystack.includes(word));
        }).slice(0, 12);
    },
    toggle() {
        this.open = !this.open;
        if (this.open) {
            this.query = '';
            this.selectedIndex = 0;
            this.$nextTick(() => {
                this.$refs.searchInput?.focus();
            });
        }
    },
    close() {
        this.open = false;
    },
    onKeydown(e) {
        if (!this.open) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                this.toggle();
            }
            return;
        }

        const itemsLength = this.filteredItems.length;

        if (e.key === 'Escape') {
            this.close();
        } else if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (itemsLength > 0) {
                this.selectedIndex = (this.selectedIndex + 1) % itemsLength;
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (itemsLength > 0) {
                this.selectedIndex = (this.selectedIndex - 1 + itemsLength) % itemsLength;
            }
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (itemsLength > 0 && this.filteredItems[this.selectedIndex]) {
                window.location.href = this.filteredItems[this.selectedIndex].href;
            }
        }
    },
    init() {
        this.$watch('query', () => {
            this.selectedIndex = 0;
        });
        
        // Listen to global keydown events
        window.addEventListener('keydown', this.onKeydown.bind(this));
    },
    destroy() {
        window.removeEventListener('keydown', this.onKeydown.bind(this));
    }
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

Alpine.data('belovedLanding', (config = {}) => ({
    current: 0,
    paused: false,
    progressRunning: false,
    backVisible: false,
    timer: null,
    slideCount: Number(config.slideCount || 0),
    duration: Number(config.duration || 9000),
    init() {
        this.observeReveals();
        this.onScroll();
        window.addEventListener('scroll', () => this.onScroll(), { passive: true });

        if (this.slideCount > 1) {
            this.restartProgress();
            this.startAutoplay();
        }
    },
    startAutoplay() {
        window.clearInterval(this.timer);

        if (this.paused || this.slideCount < 2) {
            return;
        }

        this.timer = window.setInterval(() => this.next(), this.duration);
    },
    restartProgress() {
        this.progressRunning = false;

        if (this.paused || this.slideCount < 2) {
            return;
        }

        this.$nextTick(() => {
            window.requestAnimationFrame(() => {
                this.progressRunning = true;
            });
        });
    },
    goTo(index) {
        if (this.slideCount < 1) {
            return;
        }

        const nextIndex = (Number(index) + this.slideCount) % this.slideCount;

        if (nextIndex === this.current) {
            return;
        }

        this.current = nextIndex;
        this.restartProgress();
        this.startAutoplay();
    },
    next() {
        this.goTo(this.current + 1);
    },
    previous() {
        this.goTo(this.current - 1);
    },
    togglePause() {
        this.paused = !this.paused;

        if (this.paused) {
            window.clearInterval(this.timer);
            this.progressRunning = false;
            return;
        }

        this.restartProgress();
        this.startAutoplay();
    },
    observeReveals() {
        const revealEls = document.querySelectorAll('.beloved-reveal');

        if (!revealEls.length) {
            return;
        }

        if (!('IntersectionObserver' in window)) {
            revealEls.forEach((el) => el.classList.add('is-visible'));
            return;
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.14 });

        revealEls.forEach((el) => observer.observe(el));
    },
    onScroll() {
        this.backVisible = window.scrollY > 480;
    },
    scrollTop() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    },
}));

window.Alpine = Alpine;

Alpine.start();
