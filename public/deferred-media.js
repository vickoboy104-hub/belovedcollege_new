(() => {
    'use strict';

    const videos = Array.from(document.querySelectorAll('video[data-deferred-autoplay]'));
    if (!videos.length) {
        return;
    }

    const safePlay = (video) => {
        if (document.hidden) {
            return;
        }

        const promise = video.play();
        if (promise && typeof promise.catch === 'function') {
            promise.catch(() => {});
        }
    };

    const syncHeroVideos = () => {
        videos.forEach((video) => {
            const slide = video.closest('.beloved-slide');
            if (!slide) {
                return;
            }

            if (slide.classList.contains('is-active')) {
                safePlay(video);
            } else {
                video.pause();
            }
        });
    };

    const hero = document.querySelector('.beloved-hero');
    if (hero) {
        const observer = new MutationObserver(syncHeroVideos);
        observer.observe(hero, {
            subtree: true,
            attributes: true,
            attributeFilter: ['class'],
        });
        window.requestAnimationFrame(syncHeroVideos);
    }

    const standaloneVideos = videos.filter((video) => !video.closest('.beloved-slide'));
    if (standaloneVideos.length && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting && entry.intersectionRatio >= 0.2) {
                    safePlay(entry.target);
                } else {
                    entry.target.pause();
                }
            });
        }, { threshold: [0, 0.2, 0.5] });

        standaloneVideos.forEach((video) => observer.observe(video));
    }

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            videos.forEach((video) => video.pause());
            return;
        }

        syncHeroVideos();
    });
})();
