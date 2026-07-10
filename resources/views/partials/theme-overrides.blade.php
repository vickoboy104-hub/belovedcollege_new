<style>
    body {
        background: var(--theme-bg) !important;
        color: var(--theme-text) !important;
    }

    .auth-shell {
        background:
            linear-gradient(135deg, rgba(15, 23, 42, 0.94), rgba(20, 33, 61, 0.92)),
            radial-gradient(circle at 12% 14%, color-mix(in srgb, var(--theme-primary) 22%, transparent), transparent 24%),
            radial-gradient(circle at 82% 80%, color-mix(in srgb, var(--theme-accent) 20%, transparent), transparent 28%) !important;
    }

    .beloved-home {
        --beloved-emerald: var(--theme-primary) !important;
        --beloved-emerald-dark: var(--theme-secondary) !important;
        --beloved-emerald-mid: color-mix(in srgb, var(--theme-primary) 82%, var(--theme-secondary)) !important;
        --beloved-emerald-light: color-mix(in srgb, var(--theme-primary) 78%, #ffffff) !important;
        --beloved-mist: var(--theme-soft) !important;
        --beloved-gold: var(--theme-highlight) !important;
        --beloved-gold-light: color-mix(in srgb, var(--theme-highlight) 78%, #ffffff) !important;
        --beloved-gold-pale: color-mix(in srgb, var(--theme-highlight) 14%, #ffffff) !important;
        --beloved-cream: var(--theme-surface) !important;
        --beloved-charcoal: var(--theme-text) !important;
        --beloved-slate: color-mix(in srgb, var(--theme-text) 62%, #64748b) !important;
        --beloved-border: color-mix(in srgb, var(--theme-primary) 16%, #e5e7eb) !important;
        --beloved-card: color-mix(in srgb, var(--theme-surface) 18%, #ffffff) !important;
        --beloved-card-tint: color-mix(in srgb, var(--theme-surface) 56%, #ffffff) !important;
        --beloved-dark-shadow: color-mix(in srgb, var(--theme-secondary) 28%, transparent) !important;
        --beloved-media-shade: color-mix(in srgb, var(--theme-secondary) 68%, transparent) !important;
        --beloved-media-shade-soft: color-mix(in srgb, var(--theme-primary) 18%, transparent) !important;
        --beloved-shadow-sm: 0 1px 4px color-mix(in srgb, var(--theme-secondary) 8%, transparent) !important;
        --beloved-shadow-md: 0 4px 20px color-mix(in srgb, var(--theme-secondary) 10%, transparent) !important;
        --beloved-shadow-lg: 0 12px 40px color-mix(in srgb, var(--theme-secondary) 13%, transparent) !important;
        --beloved-shadow-xl: 0 24px 72px color-mix(in srgb, var(--theme-secondary) 16%, transparent) !important;
    }

    .beloved-slide__bg {
        background-color: var(--theme-secondary) !important;
        background-image:
            linear-gradient(105deg, color-mix(in srgb, var(--theme-secondary) 94%, transparent) 0%, color-mix(in srgb, var(--theme-secondary) 64%, transparent) 44%, color-mix(in srgb, var(--theme-primary) 22%, transparent) 78%, transparent 100%),
            radial-gradient(circle at 18% 22%, color-mix(in srgb, var(--theme-highlight) 28%, transparent), transparent 30%),
            radial-gradient(circle at 82% 72%, color-mix(in srgb, var(--theme-accent) 34%, transparent), transparent 32%),
            var(--slide-img, linear-gradient(145deg, var(--theme-secondary) 0%, var(--theme-primary) 52%, color-mix(in srgb, var(--theme-accent) 72%, #ffffff) 130%)) !important;
    }

    .beloved-slide--4 .beloved-slide__bg {
        background-image:
            linear-gradient(105deg, color-mix(in srgb, var(--theme-secondary) 88%, var(--theme-highlight)) 0%, color-mix(in srgb, var(--theme-secondary) 62%, transparent) 46%, color-mix(in srgb, var(--theme-highlight) 18%, transparent) 80%, transparent 100%),
            radial-gradient(circle at 22% 28%, color-mix(in srgb, var(--theme-highlight) 28%, transparent), transparent 32%),
            radial-gradient(circle at 78% 68%, color-mix(in srgb, var(--theme-primary) 32%, transparent), transparent 34%),
            var(--slide-img, linear-gradient(145deg, var(--theme-secondary) 0%, var(--theme-primary) 56%, var(--theme-highlight) 150%)) !important;
    }

    .beloved-slide-thumb__bg,
    .beloved-video-thumb__cover,
    .beloved-school-card__bg,
    .beloved-gallery-item__bg {
        background-color: var(--theme-secondary) !important;
        background-image:
            linear-gradient(135deg, color-mix(in srgb, var(--theme-secondary) 22%, transparent), color-mix(in srgb, var(--theme-primary) 18%, transparent)),
            radial-gradient(circle at 20% 20%, color-mix(in srgb, var(--theme-highlight) 32%, transparent), transparent 28%),
            radial-gradient(circle at 78% 72%, color-mix(in srgb, var(--theme-accent) 28%, transparent), transparent 32%),
            var(--card-img, linear-gradient(135deg, var(--theme-secondary), var(--theme-primary) 58%, color-mix(in srgb, var(--theme-highlight) 72%, #ffffff))) !important;
    }

    .hero-media-stage,
    .gallery-frame,
    .immersive-band,
    .content-media-card {
        background-color: color-mix(in srgb, var(--theme-primary) 14%, #ffffff) !important;
        background-image:
            radial-gradient(circle at 18% 18%, color-mix(in srgb, var(--theme-highlight) 22%, transparent), transparent 30%),
            radial-gradient(circle at 82% 72%, color-mix(in srgb, var(--theme-accent) 18%, transparent), transparent 34%),
            var(--content-card-bg, linear-gradient(135deg, color-mix(in srgb, var(--theme-secondary) 88%, #020617), color-mix(in srgb, var(--theme-primary) 74%, #020617))) !important;
    }

    .beloved-video-thumb__play {
        background: color-mix(in srgb, #ffffff 94%, var(--theme-highlight)) !important;
        box-shadow: 0 4px 18px color-mix(in srgb, var(--theme-secondary) 28%, transparent) !important;
    }

    .beloved-video-thumb__play::before {
        border-left-color: var(--theme-primary) !important;
    }

    .beloved-video-thumb__label,
    .beloved-gallery-item__overlay {
        background: linear-gradient(transparent, color-mix(in srgb, var(--theme-secondary) 76%, transparent)) !important;
    }

    .classic-announce,
    .beloved-btn--gold,
    .beloved-band-btn--gold,
    .beloved-slider-progress span,
    .beloved-slider-dot.is-active::after,
    .beloved-event-item__date,
    .beloved-events-cta-box,
    .beloved-testi-card__author > span,
    .beloved-back-to-top {
        background: var(--theme-primary) !important;
        color: #ffffff !important;
    }

    .beloved-admission-band,
    .beloved-stats-bar,
    .beloved-newsletter-band,
    .beloved-newsletter-band__inner {
        background: linear-gradient(135deg, var(--theme-secondary), var(--theme-primary)) !important;
        color: #ffffff !important;
        border: none !important;
    }

    .beloved-newsletter-band__inner h2 {
        color: var(--public-band-text, #0f172a) !important;
    }
    .beloved-newsletter-band__inner p {
        color: var(--public-text-muted, rgba(71,85,105,0.9)) !important;
    }

    .beloved-home .beloved-eyebrow,
    .beloved-home .beloved-sec-title,
    .beloved-home .beloved-sec-title em,
    .beloved-home .beloved-welcome__lead,
    .beloved-home .beloved-school-card__body h3,
    .beloved-home .beloved-school-card__body > span,
    .beloved-home .beloved-event-item h3,
    .beloved-home .beloved-event-item div div span {
        color: var(--theme-secondary) !important;
    }

    .beloved-home .beloved-badge,
    .beloved-home .beloved-event-item div div span,
    .public-nav-pill:hover,
    .public-nav-pill.is-active {
        background: var(--theme-soft) !important;
    }

    /* High Contrast welcome address cards */
    .beloved-home .beloved-principal-card {
        background: #ffffff !important;
        border: 1.5px solid var(--border-soft) !important;
        box-shadow: var(--beloved-shadow-sm) !important;
    }
    .beloved-home .beloved-principal-card span {
        color: var(--theme-secondary) !important;
        font-weight: 800 !important;
    }
    .beloved-home .beloved-principal-card small {
        color: var(--theme-text) !important;
        opacity: 0.8 !important;
    }

    .beloved-home .beloved-director-cta {
        background: #ffffff !important;
        border: 1.5px solid var(--border-soft) !important;
        box-shadow: var(--beloved-shadow-sm) !important;
        color: var(--theme-text) !important;
    }
    .beloved-home .beloved-director-cta small {
        color: var(--theme-primary) !important;
        font-weight: 700 !important;
    }
    .beloved-home .beloved-director-cta strong {
        color: var(--theme-secondary) !important;
        font-weight: 800 !important;
    }
    .beloved-home .beloved-director-cta i {
        border: 1px solid var(--border-soft) !important;
        background: var(--theme-soft) !important;
        color: var(--theme-primary) !important;
    }
    .beloved-home .beloved-director-cta:hover i {
        background: var(--theme-primary) !important;
        color: #ffffff !important;
    }

    /* Ensure dashboard announcement panels use readable text on dark backgrounds */
    .dashboard-announcements-panel :is(.card-title, .card-description, .section-title, .card) {
        color: var(--dashboard-announcement-text, var(--dashboard-card-text, #ffffff)) !important;
    }

    .public-staff-login-button,
    .public-nav-pill.public-staff-login-button,
    .public-drawer-link.public-staff-login-button {
        border-color: #061f4a !important;
        background: #082f6f !important;
        color: #ffffff !important;
        box-shadow: 0 14px 28px rgba(8, 47, 111, 0.24) !important;
    }

    .public-staff-login-button:hover,
    .public-nav-pill.public-staff-login-button:hover,
    .public-drawer-link.public-staff-login-button:hover {
        background: #061f4a !important;
        color: #ffffff !important;
    }

    .beloved-home .beloved-badge,
    .beloved-home .beloved-principal-card,
    .beloved-home .beloved-school-card:hover,
    .beloved-home .beloved-event-item:hover {
        border-color: color-mix(in srgb, var(--theme-primary) 34%, #ffffff) !important;
    }

    .beloved-sec-rule {
        background: linear-gradient(to right, var(--theme-primary), var(--theme-highlight)) !important;
    }

    .beloved-slide__accent {
        background: linear-gradient(to bottom, transparent 0%, var(--theme-highlight) 50%, transparent 100%) !important;
    }

    .beloved-slide-thumb.is-active {
        border-color: var(--theme-highlight) !important;
    }

    .beloved-btn--gold,
    .beloved-band-btn--gold,
    .beloved-newsletter-band__form button {
        background: var(--theme-highlight) !important;
        color: var(--theme-secondary) !important;
    }

    .beloved-btn--outline {
        border-color: var(--theme-primary) !important;
        color: var(--theme-primary) !important;
    }

    .beloved-btn--outline:hover {
        background: var(--theme-primary) !important;
        color: #ffffff !important;
    }

    .beloved-back-to-top,
    .beloved-director-cta,
    .beloved-newsletter-band {
        box-shadow: 0 12px 32px color-mix(in srgb, var(--theme-secondary) 24%, transparent) !important;
    }

    .public-shell .border-emerald-200,
    .public-shell .border-green-200 {
        border-color: color-mix(in srgb, var(--theme-primary) 24%, #bfdbfe) !important;
    }

    .public-shell .bg-emerald-50,
    .public-shell .bg-green-50 {
        background-color: color-mix(in srgb, var(--theme-primary) 8%, #eff6ff) !important;
    }

    .public-shell .text-emerald-700,
    .public-shell .text-emerald-900,
    .public-shell .text-green-600 {
        color: var(--theme-secondary) !important;
    }

    /* Professional compact interface refresh */
    :root {
        --ui-ink: #101827;
        --ui-muted: #5f6b7a;
        --ui-border: #d8e0ea;
        --ui-card: #ffffff;
        --ui-page: #f5f7fb;
        --ui-navy: var(--theme-secondary);
        --ui-blue: var(--theme-primary);
        --ui-red: var(--theme-accent);
        --ui-gold: var(--theme-highlight);
        --ui-radius: 0.72rem;
        --ui-radius-sm: 0.48rem;
    }

    body {
        color: var(--ui-ink) !important;
        background: var(--ui-page) !important;
    }

    html,
    body {
        max-width: 100% !important;
        overflow-x: hidden !important;
    }

    .classic-announce {
        display: none !important;
    }

    .public-shell {
        --public-header-height: 6rem !important;
        --app-topbar-height: 4rem !important;
    }

    .classic-public-header {
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.07) !important;
    }

    .classic-topbar {
        background: var(--ui-navy) !important;
        color: rgba(255, 255, 255, 0.78) !important;
        font-size: 0.72rem !important;
    }

    .classic-topbar__inner {
        min-height: 1.85rem !important;
    }

    .public-topbar {
        background: rgba(255, 255, 255, 0.98) !important;
        border-color: var(--ui-border) !important;
    }

    .public-topbar-row {
        min-height: 4.15rem !important;
    }

    .public-desktop-nav {
        gap: 0.25rem !important;
        margin-left: 1rem !important;
    }

    .public-nav-pill {
        min-height: 2.18rem !important;
        border-radius: var(--ui-radius-sm) !important;
        padding: 0.48rem 0.72rem !important;
        font-size: 0.74rem !important;
        letter-spacing: 0.035em !important;
        color: #1f2937 !important;
    }

    .public-nav-pill:hover,
    .public-nav-pill.is-active {
        background: #edf3fb !important;
        color: var(--ui-blue) !important;
    }

    .public-nav-pill.is-strong,
    .public-staff-login-button,
    .public-nav-pill.public-staff-login-button,
    .public-drawer-link.public-staff-login-button {
        border-color: var(--ui-navy) !important;
        background: var(--ui-navy) !important;
        color: #ffffff !important;
        box-shadow: 0 8px 18px rgba(8, 36, 63, 0.18) !important;
    }

    .nav-brand-title {
        font-size: 0.9rem !important;
        line-height: 1.1 !important;
    }

    .nav-brand-subtitle {
        color: #667085 !important;
        font-size: 0.68rem !important;
        letter-spacing: 0.16em !important;
    }

    .public-content-shell {
        padding-top: var(--public-header-height) !important;
    }

    .beloved-hero {
        height: min(640px, calc(88svh - var(--public-header-height))) !important;
        min-height: 470px !important;
        background: var(--ui-navy) !important;
    }

    .beloved-slide__bg {
        background-color: var(--ui-navy) !important;
        background-image:
            linear-gradient(96deg, rgba(5, 20, 42, 0.88) 0%, rgba(8, 36, 63, 0.72) 42%, rgba(8, 36, 63, 0.18) 100%),
            var(--slide-img, linear-gradient(135deg, var(--ui-navy), var(--ui-blue))) !important;
    }

    .beloved-slide__bg--video {
        background-color: transparent !important;
        background-image: linear-gradient(96deg, rgba(5, 20, 42, 0.88) 0%, rgba(8, 36, 63, 0.72) 42%, rgba(8, 36, 63, 0.18) 100%) !important;
    }

    .beloved-slide--4 .beloved-slide__bg {
        background-image:
            linear-gradient(96deg, rgba(5, 20, 42, 0.88) 0%, rgba(8, 36, 63, 0.68) 44%, rgba(180, 35, 24, 0.12) 100%),
            var(--slide-img, linear-gradient(135deg, var(--ui-navy), var(--ui-blue))) !important;
    }

    .beloved-slide__decor,
    .beloved-slide-counter,
    .beloved-slide-thumbs {
        display: none !important;
    }

    .beloved-slide__content {
        max-width: 760px !important;
        padding: 0 7.5% 70px !important;
    }

    .beloved-slide__eyebrow {
        margin-bottom: 0.85rem !important;
        color: rgba(242, 201, 76, 0.94) !important;
        font-size: 0.68rem !important;
        letter-spacing: 0.16em !important;
    }

    .beloved-slide__title {
        max-width: 720px !important;
        margin-bottom: 1rem !important;
        font-family: 'Montserrat', sans-serif !important;
        font-size: 3.72rem !important;
        font-weight: 800 !important;
        line-height: 1.06 !important;
    }

    .beloved-slide__title em {
        color: #dbeafe !important;
        font-style: normal !important;
    }

    .beloved-slide__sub {
        max-width: 540px !important;
        margin-bottom: 1.45rem !important;
        color: rgba(255, 255, 255, 0.92) !important;
        font-size: 0.95rem !important;
        line-height: 1.65 !important;
        text-shadow: 0 1px 18px rgba(5, 20, 42, 0.42) !important;
    }

    .beloved-slider-bar {
        height: 52px !important;
        background: rgba(8, 36, 63, 0.92) !important;
        padding: 0 2rem !important;
    }

    .beloved-slider-progress {
        height: 2px !important;
    }

    .beloved-slider-labels button {
        font-size: 0.66rem !important;
        letter-spacing: 0.045em !important;
    }

    .beloved-btn,
    .beloved-band-btn,
    .theme-button,
    .theme-button-secondary {
        min-height: 2.42rem !important;
        border-radius: var(--ui-radius-sm) !important;
        padding: 0.68rem 1rem !important;
        font-size: 0.78rem !important;
        letter-spacing: 0.02em !important;
        box-shadow: none !important;
    }

    .beloved-btn--gold,
    .beloved-band-btn--gold {
        background: var(--ui-gold) !important;
        color: var(--ui-navy) !important;
    }

    .beloved-admission-band,
    .beloved-stats-bar,
    .beloved-newsletter-band,
    .beloved-newsletter-band__inner {
        background: linear-gradient(135deg, var(--theme-secondary), var(--theme-primary)) !important;
        color: var(--public-band-text, #0f172a) !important;
        border: none !important;
    }
    .beloved-admission-band p,
    .beloved-home .beloved-admission-band p {
        font-size: 0.8rem !important;
        line-height: 1.5 !important;
        color: #ffffff !important;
    }

    .beloved-stats-bar__item {
        padding: 1rem 1rem !important;
    }

    .beloved-stats-bar__item span {
        font-size: 1.65rem !important;
    }

    .beloved-section {
        padding: 3.25rem 0 !important;
    }

    .beloved-sec-hdr {
        margin-bottom: 1.9rem !important;
    }

    .beloved-sec-title {
        font-size: 2rem !important;
        line-height: 1.18 !important;
    }

    .beloved-sec-sub,
    .beloved-welcome__body,
    .beloved-school-card__body p,
    .beloved-event-item p,
    .beloved-testi-card p {
        font-size: 0.84rem !important;
        line-height: 1.62 !important;
    }

    .beloved-welcome-grid {
        gap: 2.25rem !important;
    }

    .beloved-welcome__lead {
        margin-bottom: 0.75rem !important;
        font-size: 0.98rem !important;
        line-height: 1.55 !important;
    }

    .beloved-principal-card {
        margin-top: 1.1rem !important;
        border-radius: var(--ui-radius) !important;
        padding: 0.8rem 0.95rem !important;
    }

    .beloved-principal-card__avatar {
        width: 4.4rem !important;
        height: 4.4rem !important;
    }

    .beloved-director-cta {
        margin-top: 1.1rem !important;
        border-radius: var(--ui-radius) !important;
        padding: 1rem 1.15rem !important;
    }

    .beloved-video-thumb,
    .beloved-school-card,
    .beloved-event-item,
    .beloved-gallery-item,
    .beloved-testi-card {
        border-radius: var(--ui-radius) !important;
    }

    .beloved-schools-grid,
    .beloved-testi-grid {
        gap: 0.9rem !important;
    }

    .beloved-school-card__img {
        height: 138px !important;
    }

    .beloved-school-card__body {
        padding: 0.9rem 1rem !important;
    }

    .beloved-school-card__body h3 {
        font-size: 0.95rem !important;
    }

    .beloved-badge {
        border-radius: 999px !important;
        padding: 0.22rem 0.62rem !important;
        font-size: 0.58rem !important;
    }

    .beloved-school-card--wide {
        grid-template-columns: 160px 1fr !important;
    }

    .beloved-events-layout {
        gap: 1.5rem !important;
        grid-template-columns: 1fr 300px !important;
    }

    .beloved-events-list,
    .beloved-events-sidebar {
        gap: 0.75rem !important;
    }

    .beloved-event-item {
        padding: 0.85rem !important;
    }

    .beloved-event-item__date {
        flex-basis: 46px !important;
        border-radius: var(--ui-radius-sm) !important;
    }

    .beloved-events-cta-box {
        border-radius: var(--ui-radius) !important;
        padding: 1.25rem !important;
    }

    .beloved-gallery-masonry {
        grid-auto-rows: 168px !important;
        gap: 0.75rem !important;
    }

    .beloved-testi-card {
        padding: 1.1rem !important;
    }

    .beloved-newsletter-band {
        padding: 2rem 0 !important;
    }

    .public-content-shell > section.mx-auto {
        padding-top: 2.6rem !important;
        padding-bottom: 2.6rem !important;
    }

    .public-content-shell > section.mx-auto + section.mx-auto {
        padding-top: 0 !important;
    }

    .public-content-shell .mesh-card,
    .public-content-shell .section-card,
    .public-content-shell [class*="rounded-[1.75rem]"][class*="border"],
    .public-content-shell [class*="rounded-[2rem]"][class*="border"] {
        border-radius: var(--ui-radius) !important;
        border-color: var(--ui-border) !important;
        background: rgba(255, 255, 255, 0.98) !important;
        padding: 1.25rem !important;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.045) !important;
    }

    .public-content-shell h1.display-font {
        font-size: 2.2rem !important;
        line-height: 1.16 !important;
    }

    .public-content-shell h2.display-font {
        font-size: 1.22rem !important;
        line-height: 1.25 !important;
    }

    .public-content-shell p,
    .public-content-shell li {
        color: var(--ui-muted) !important;
        line-height: 1.62 !important;
    }

    .auth-shell {
        padding: 0 !important;
        background: #0b1733 !important;
    }

    .auth-layout {
        min-height: 100svh !important;
        grid-template-columns: minmax(0, 1fr) minmax(390px, 0.52fr) !important;
        border: 0 !important;
        border-radius: 0 !important;
        background: #0b1733 !important;
        box-shadow: none !important;
    }

    .auth-copy-panel {
        position: relative !important;
        overflow: hidden !important;
        padding: 2.2rem 4rem !important;
        background-color: var(--ui-navy) !important;
        background-image:
            linear-gradient(90deg, rgba(5, 20, 42, 0.86), rgba(5, 20, 42, 0.64)),
            var(--auth-bg-image, linear-gradient(135deg, var(--ui-navy), var(--ui-blue))) !important;
        background-position: center !important;
        background-size: cover !important;
    }

    .auth-copy-panel > * {
        position: relative !important;
        z-index: 1 !important;
    }

    .auth-copy-body {
        margin-bottom: 2.25rem !important;
    }

    .auth-copy-body h1 {
        max-width: 36rem !important;
        font-size: 2.55rem !important;
        line-height: 1.12 !important;
    }

    .auth-copy-panel .auth-copy-body p:not(.auth-kicker),
    .auth-brand .text-white\/68 {
        color: rgba(255, 255, 255, 0.78) !important;
    }

    .auth-form-panel {
        background: #f6f8fb !important;
        padding: 1.5rem !important;
    }

    .auth-card {
        width: min(100%, 28rem) !important;
        border-radius: var(--ui-radius) !important;
        padding: 1.35rem !important;
        box-shadow: 0 18px 48px rgba(15, 23, 42, 0.10) !important;
    }

    .auth-login-header {
        padding-bottom: 1rem !important;
    }

    .auth-login-header h1 {
        margin-top: 0.45rem !important;
        font-size: 1.78rem !important;
    }

    .auth-login-header p {
        margin-top: 0.5rem !important;
        line-height: 1.55 !important;
    }

    .auth-card form {
        margin-top: 1.1rem !important;
    }

    .auth-card form.space-y-5 > :not([hidden]) ~ :not([hidden]) {
        margin-top: 0.85rem !important;
    }

    .auth-input,
    .auth-submit {
        min-height: 2.75rem !important;
    }

    .auth-notice {
        margin-top: 1rem !important;
        padding: 0.85rem !important;
    }

    .app-shell {
        --app-topbar-height: 3.45rem !important;
        --app-sidebar-width: 15.65rem !important;
        --portal-page-bg: #edf2f9;
        --portal-card-bg: #ffffff;
        --portal-card-bg-soft: #ffffff;
        --portal-card-border: #c8d6ea;
        --portal-card-border-hover: #fbbf24;
        --portal-card-accent: #0b5ed7;
        --portal-card-accent-hover: #fbbf24;
        --portal-card-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
        --portal-card-shadow-hover: 0 16px 35px rgba(15, 23, 42, 0.14);
        --portal-card-glow: rgba(251, 191, 36, 0.2);
        background: var(--portal-page-bg) !important;
    }

    .app-content-shell {
        padding-top: var(--app-topbar-height) !important;
    }

    .app-content-inner {
        max-width: 1440px !important;
    }

    .app-main {
        padding-top: 1.05rem !important;
        padding-bottom: 1.6rem !important;
        background: transparent !important;
    }

    .app-topbar-row {
        min-height: var(--app-topbar-height) !important;
        padding-top: 0.35rem !important;
        padding-bottom: 0.35rem !important;
    }

    .app-topbar .h-10,
    .app-topbar .h-11 {
        height: 2.25rem !important;
        width: 2.25rem !important;
    }

    .app-shell .nav-brand-title {
        font-size: 0.86rem !important;
    }

    .app-shell .nav-brand-subtitle {
        color: rgba(255, 255, 255, 0.7) !important;
        font-size: 0.66rem !important;
    }

    /* Card Layout System - Elevated White Cards */
    .app-shell .app-header-card,
    .app-shell .section-card,
    .app-shell .mesh-card,
    .app-shell .stat-tile,
    .app-shell .mobile-record-card,
    .app-shell .management-module-card,
    .app-shell .section-nav,
    .app-shell [class*="rounded-[1.75rem]"][class*="border"],
    .app-shell [class*="rounded-[1.5rem]"][class*="border"],
    .app-shell [class*="rounded-[2rem]"][class*="border"],
    .app-shell article[class*="rounded-3xl"][class*="border"],
    .app-shell div[class*="rounded-3xl"][class*="border"],
    .app-shell a[class*="rounded-3xl"][class*="border"],
    .app-shell form[class*="rounded-3xl"][class*="border"],
    .app-shell div[class*="rounded-2xl"][class*="border"],
    .app-shell article[class*="rounded-2xl"][class*="border"],
    .app-shell label[class*="rounded-2xl"][class*="border"] {
        position: relative !important;
        overflow: hidden !important;
        border-radius: 18px !important;
        border: 1px solid var(--theme-border) !important;
        background: var(--theme-card) !important;
        padding: 1.25rem !important;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.11), 0 2px 10px rgba(15, 23, 42, 0.04) !important;
        transition: all 0.25s ease !important;
        transform: none !important;
    }

    /* Hide legacy colored stripe helpers to keep white cards clean */
    .app-shell .app-header-card::before,
    .app-shell .section-card::before,
    .app-shell .mesh-card::before,
    .app-shell .stat-tile::before,
    .app-shell .mobile-record-card::before,
    .app-shell .management-module-card::before,
    .app-shell .section-nav::before {
        display: none !important;
    }

    .app-shell .app-header-card,
    .app-shell .section-card {
        padding: 1.25rem !important;
    }

    .app-shell .app-header-card h1,
    .app-shell main h1 {
        font-size: 1.42rem !important;
        line-height: 1.22 !important;
        color: var(--theme-text) !important;
        font-weight: 800 !important;
    }

    .app-shell main h2 {
        font-size: 1.12rem !important;
        color: var(--theme-text) !important;
        font-weight: 700 !important;
    }

    .app-shell main h3 {
        font-size: 0.98rem !important;
        color: var(--theme-text) !important;
        font-weight: 600 !important;
    }

    .app-shell .app-header-card p,
    .app-shell main p,
    .app-shell main .text-sm {
        line-height: 1.5 !important;
        color: var(--theme-muted) !important;
    }

    .app-shell .mt-8 { margin-top: 2.2rem !important; }
    .app-shell .mt-7 { margin-top: 1.9rem !important; }
    .app-shell .mt-6 { margin-top: 1.6rem !important; }
    .app-shell .mt-5 { margin-top: 1.3rem !important; }
    .app-shell .mt-4 { margin-top: 1.05rem !important; }
    .app-shell .mt-3 { margin-top: 0.78rem !important; }
    .app-shell .mt-2 { margin-top: 0.5rem !important; }

    .app-shell .gap-8 { gap: 2.15rem !important; }
    .app-shell .gap-6 { gap: 1.75rem !important; }
    .app-shell .gap-5 { gap: 1.45rem !important; }
    .app-shell .gap-4 { gap: 1.2rem !important; }
    .app-shell .gap-3 { gap: 0.95rem !important; }

    .app-shell [x-show*="includes(activeSection)"].grid,
    .app-shell [x-show*="includes(activeFinanceSection)"].grid,
    .app-shell [x-show*="includes(activeFinanceRecordsSection)"].grid {
        grid-template-columns: minmax(0, 1fr) !important;
    }

    .app-shell .space-y-6 > :not([hidden]) ~ :not([hidden]) { margin-top: 1.9rem !important; }
    .app-shell .space-y-5 > :not([hidden]) ~ :not([hidden]) { margin-top: 1.55rem !important; }
    .app-shell .space-y-4 > :not([hidden]) ~ :not([hidden]) { margin-top: 1.3rem !important; }
    .app-shell .space-y-3 > :not([hidden]) ~ :not([hidden]) { margin-top: 1rem !important; }

    /* Stat Tiles Redesign */
    .app-shell .stat-tile {
        position: relative !important;
        overflow: hidden !important;
        min-height: 5.5rem !important;
        padding: 1.15rem !important;
    }

    .app-shell .stat-tile .display-font {
        margin-top: 0.45rem !important;
        font-size: 1.75rem !important;
        line-height: 1.1 !important;
        color: var(--theme-text) !important;
        font-weight: 800 !important;
    }

    /* Management Module Cards Redesign */
    .app-shell .management-module-card {
        position: relative !important;
        overflow: hidden !important;
        min-height: 9.5rem !important;
        padding: 1.25rem !important;
    }

    .app-shell .management-module-card h3 {
        margin-top: 0.85rem !important;
    }

    .app-shell .management-module-card p {
        margin-top: 0.45rem !important;
        line-height: 1.5 !important;
        color: var(--theme-muted) !important;
    }

    .app-shell .management-module-badge {
        min-height: 1.85rem !important;
        border-radius: 999px !important;
        padding: 0.25rem 0.75rem !important;
        font-size: 0.72rem !important;
        background: var(--theme-surface-soft) !important;
        border: 1px solid var(--theme-border) !important;
        color: var(--theme-text) !important;
    }

    .app-shell .border-dashed {
        border: 1.5px dashed var(--theme-border) !important;
        background: var(--theme-surface-soft) !important;
        box-shadow: none !important;
    }

    .app-shell video[class*="rounded-3xl"],
    .app-shell img[class*="rounded-3xl"] {
        box-shadow: none !important;
        transform: none !important;
    }

    .app-shell .portal-card-head {
        display: flex !important;
        align-items: flex-start !important;
        justify-content: space-between !important;
        gap: 1rem !important;
    }

    .app-shell .portal-card-kicker,
    .app-shell .portal-meta-row,
    .app-shell .portal-action-row {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        gap: 0.65rem !important;
    }

    /* Portal Subject and Meta Badges */
    .app-shell .portal-subject-badge,
    .app-shell .portal-meta-chip {
        display: inline-flex !important;
        min-height: 1.65rem !important;
        align-items: center !important;
        justify-content: center !important;
        border-radius: 999px !important;
        border: 1.5px solid var(--theme-border) !important;
        background: var(--theme-surface-soft) !important;
        padding: 0.3rem 0.75rem !important;
        color: var(--theme-text) !important;
        font-size: 0.72rem !important;
        font-weight: 700 !important;
        line-height: 1.1 !important;
        letter-spacing: 0.04em !important;
    }

    .app-shell .portal-subject-badge {
        border-color: var(--theme-info) !important;
        background: var(--theme-card-soft) !important;
        color: var(--theme-info) !important;
    }

    /* Unified Cohesive Badge System */
    .app-shell .portal-status-pill,
    .app-shell .badge,
    .app-shell span[class*="bg-green-"],
    .app-shell span[class*="bg-emerald-"],
    .app-shell span[class*="bg-red-"],
    .app-shell span[class*="bg-rose-"],
    .app-shell span[class*="bg-amber-"],
    .app-shell span[class*="bg-yellow-"],
    .app-shell span[class*="bg-blue-"],
    .app-shell span[class*="bg-sky-"],
    .app-shell span[class*="bg-slate-"],
    .app-shell span[class*="bg-gray-"] {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        border-radius: 9999px !important;
        padding: 0.25rem 0.75rem !important;
        font-size: 0.7rem !important;
        font-weight: 700 !important;
        letter-spacing: 0.05em !important;
        text-transform: uppercase !important;
        border: 1.5px solid transparent !important;
    }

    /* Success Badge States */
    .app-shell .portal-status-pill.is-success,
    .app-shell span[class*="bg-green-"],
    .app-shell span[class*="bg-emerald-"] {
        background-color: #d1fae5 !important;
        color: #065f46 !important;
        border-color: #a7f3d0 !important;
    }

    /* Danger Badge States */
    .app-shell .portal-status-pill.is-danger,
    .app-shell span[class*="bg-red-"],
    .app-shell span[class*="bg-rose-"] {
        background-color: #fee2e2 !important;
        color: #991b1b !important;
        border-color: #fecaca !important;
    }

    /* Warning Badge States */
    .app-shell .portal-status-pill.is-warning,
    .app-shell span[class*="bg-yellow-"],
    .app-shell span[class*="bg-amber-"],
    .app-shell span[class*="bg-orange-"] {
        background-color: #fef3c7 !important;
        color: #92400e !important;
        border-color: #fde68a !important;
    }

    /* Info Badge States */
    .app-shell .portal-status-pill.is-muted,
    .app-shell span[class*="bg-blue-"],
    .app-shell span[class*="bg-sky-"],
    .app-shell span[class*="bg-slate-"],
    .app-shell span[class*="bg-gray-"] {
        background-color: #e0f2fe !important;
        color: #0369a1 !important;
        border-color: #bae6fd !important;
    }

    .app-shell .portal-card-title {
        margin-top: 0.65rem !important;
        color: var(--theme-text) !important;
        font-size: 1.05rem !important;
        font-weight: 800 !important;
        line-height: 1.3 !important;
    }

    .app-shell .portal-card-description {
        margin-top: 0.72rem !important;
        color: var(--theme-muted) !important;
        font-size: 0.85rem !important;
        line-height: 1.6 !important;
    }

    .app-shell .portal-card-description.is-compact {
        margin-top: 0.5rem !important;
        line-height: 1.5 !important;
    }

    .app-shell .portal-card-divider {
        margin-top: 1rem !important;
        border-top: 1px solid var(--theme-border) !important;
        padding-top: 0.95rem !important;
    }

    .app-shell .portal-metric-grid {
        display: grid !important;
        grid-template-columns: repeat(auto-fit, minmax(6.5rem, 1fr)) !important;
        gap: 0.65rem !important;
        margin-top: 0.9rem !important;
    }

    .app-shell .portal-metric {
        border-radius: 10px !important;
        border: 1px solid var(--theme-border) !important;
        background: var(--theme-surface-soft) !important;
        padding: 0.65rem 0.85rem !important;
    }

    .app-shell .portal-metric-label {
        color: var(--theme-muted) !important;
        font-size: 0.65rem !important;
        font-weight: 800 !important;
        letter-spacing: 0.05em !important;
    }

    .app-shell .portal-metric-value {
        margin-top: 0.28rem !important;
        color: var(--theme-text) !important;
        font-size: 0.95rem !important;
        font-weight: 800 !important;
        line-height: 1.15 !important;
    }

    .app-shell .portal-progress-track {
        overflow: hidden !important;
        width: 100% !important;
        height: 0.55rem !important;
        border-radius: 999px !important;
        border: 1px solid var(--theme-border) !important;
        background: var(--theme-surface-soft) !important;
    }

    .app-shell .portal-progress-fill {
        height: 100% !important;
        border-radius: inherit !important;
        background: linear-gradient(90deg, var(--theme-primary) 0%, var(--theme-primary-hover) 62%, var(--theme-accent) 100%) !important;
        box-shadow: 0 3px 10px rgba(11, 94, 215, 0.2) !important;
    }

    .app-shell .portal-progress-caption {
        display: flex !important;
        justify-content: space-between !important;
        gap: 0.75rem !important;
        margin-top: 0.45rem !important;
        color: var(--theme-muted) !important;
        font-size: 0.7rem !important;
        font-weight: 700;
    }

    .app-shell .portal-score-pill {
        display: inline-flex !important;
        min-width: 4.25rem !important;
        align-items: center !important;
        justify-content: center !important;
        border-radius: 10px !important;
        border: 1px solid var(--theme-border) !important;
        background: var(--theme-surface-soft) !important;
        padding: 0.45rem 0.65rem !important;
        color: var(--theme-text) !important;
        font-size: 0.88rem !important;
        font-weight: 800 !important;
        line-height: 1.1 !important;
    }

    .app-shell .portal-action-row {
        margin-top: 1rem !important;
    }

    .app-shell .portal-card-head > .portal-action-row {
        margin-top: 0 !important;
    }

    /* Actions Links */
    .app-shell .portal-action-link {
        display: inline-flex !important;
        min-height: 2.28rem !important;
        align-items: center !important;
        justify-content: center !important;
        border-radius: 12px !important;
        border: 1.5px solid var(--theme-primary) !important;
        background: var(--theme-card) !important;
        padding: 0.52rem 0.95rem !important;
        color: var(--theme-primary) !important;
        font-size: 0.78rem !important;
        font-weight: 800 !important;
        line-height: 1.1 !important;
        text-align: center !important;
        transition: transform 180ms ease, border-color 180ms ease, box-shadow 180ms ease, background-color 180ms ease !important;
    }

    .app-shell .portal-action-link.is-primary {
        border-color: var(--theme-button-bg) !important;
        background: var(--theme-button-bg) !important;
        color: var(--theme-button-text) !important;
    }

    .app-shell .portal-action-link:hover {
        border-color: var(--theme-accent) !important;
        box-shadow: 0 9px 18px rgba(15, 23, 42, 0.1) !important;
        transform: translateY(-2px) !important;
    }

    .app-shell .portal-action-link.is-primary:hover {
        background: var(--theme-primary-hover) !important;
        border-color: var(--theme-primary-hover) !important;
    }

    .app-shell .portal-action-link:disabled {
        cursor: not-allowed !important;
        opacity: 0.55 !important;
        transform: none !important;
    }

    .app-shell .portal-payment-choice {
        display: inline-flex !important;
        align-items: center !important;
        gap: 0.55rem !important;
        border-radius: 10px !important;
        border: 1.5px solid var(--theme-border) !important;
        background: var(--theme-card) !important;
        padding: 0.5rem 0.85rem !important;
        color: var(--theme-text) !important;
        font-size: 0.78rem !important;
        font-weight: 700 !important;
    }

    .app-shell .portal-card-empty {
        border-radius: 14px !important;
        border: 1.5px dashed var(--theme-border) !important;
        background: var(--theme-surface-soft) !important;
        padding: 1.25rem !important;
        color: var(--theme-muted) !important;
        font-size: 0.85rem !important;
        line-height: 1.6 !important;
    }

    /* Elevated White Card Hover Animation System */
    @media (hover: hover) and (pointer: fine) {
        .app-shell .app-header-card:hover,
        .app-shell .section-card:hover,
        .app-shell .mesh-card:hover,
        .app-shell .stat-tile:hover,
        .app-shell .mobile-record-card:hover,
        .app-shell .management-module-card:hover,
        .app-shell .section-nav:hover,
        .app-shell [class*="rounded-[1.75rem]"][class*="border"]:hover,
        .app-shell [class*="rounded-[1.5rem]"][class*="border"]:hover,
        .app-shell [class*="rounded-[2rem]"][class*="border"]:hover,
        .app-shell article[class*="rounded-3xl"][class*="border"]:hover,
        .app-shell div[class*="rounded-3xl"][class*="border"]:hover,
        .app-shell a[class*="rounded-3xl"][class*="border"]:hover,
        .app-shell form[class*="rounded-3xl"][class*="border"]:hover,
        .app-shell div[class*="rounded-2xl"][class*="border"]:hover,
        .app-shell article[class*="rounded-2xl"][class*="border"]:hover,
        .app-shell label[class*="rounded-2xl"][class*="border"]:hover {
            border-color: #fbbf24 !important;
            background: #ffffff !important;
            box-shadow: 0 16px 35px rgba(15, 23, 42, 0.14) !important;
            transform: translateY(-3px) !important;
        }

        .app-shell .border-dashed:hover {
            border-color: #fbbf24 !important;
        }

        .app-shell video[class*="rounded-3xl"]:hover,
        .app-shell img[class*="rounded-3xl"]:hover {
            box-shadow: none !important;
            transform: none !important;
        }
    }

    /* Form Input Controls Redesign */
    .app-shell .theme-input,
    .app-shell input[type="text"],
    .app-shell input[type="email"],
    .app-shell input[type="password"],
    .app-shell input[type="number"],
    .app-shell input[type="date"],
    .app-shell input[type="search"],
    .app-shell select,
    .app-shell textarea {
        min-height: 2.5rem !important;
        background-color: #ffffff !important;
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 12px !important;
        padding: 0.65rem 0.85rem !important;
        color: #0f172a !important;
        font-size: 0.875rem !important;
        line-height: 1.5 !important;
        transition: border-color 0.2s ease, box-shadow 0.2s ease !important;
        width: 100% !important;
    }

    .app-shell textarea.theme-input,
    .app-shell textarea {
        min-height: 6rem !important;
    }

    .app-shell .theme-input:focus,
    .app-shell input[type="text"]:focus,
    .app-shell input[type="email"]:focus,
    .app-shell input[type="password"]:focus,
    .app-shell input[type="number"]:focus,
    .app-shell input[type="date"]:focus,
    .app-shell input[type="search"]:focus,
    .app-shell select:focus,
    .app-shell textarea:focus {
        border-color: #0b5ed7 !important;
        outline: none !important;
        box-shadow: 0 0 0 4px rgba(11, 94, 215, 0.15) !important;
    }

    .app-shell input::placeholder,
    .app-shell textarea::placeholder {
        color: #94a3b8 !important;
    }

    /* Button System Redesign */
    .app-shell .theme-button,
    .app-shell button[type="submit"]:not(.app-sidebar-logout):not(.theme-button-secondary),
    .app-shell button.bg-slate-900,
    .app-shell button.bg-blue-600 {
        min-height: 2.45rem !important;
        background: #071833 !important;
        color: #ffffff !important;
        border: 1.5px solid #071833 !important;
        border-radius: 12px !important;
        font-size: 0.825rem !important;
        font-weight: 700 !important;
        padding: 0.55rem 1rem !important;
        box-shadow: none !important;
        transition: all 0.2s ease !important;
    }

    .app-shell .theme-button:hover,
    .app-shell button[type="submit"]:not(.app-sidebar-logout):not(.theme-button-secondary):hover,
    .app-shell button.bg-slate-900:hover,
    .app-shell button.bg-blue-600:hover {
        background: #0b1f3a !important;
        border-color: #0b1f3a !important;
        transform: translateY(-1px) !important;
    }

    /* Semantic Button Colors */
    .app-shell .btn-success,
    .app-shell button.btn-success,
    .app-shell button[type="submit"].btn-success {
        background: #059669 !important; /* Emerald 600 */
        border-color: #059669 !important;
        color: #ffffff !important;
    }
    .app-shell .btn-success:hover,
    .app-shell button.btn-success:hover,
    .app-shell button[type="submit"].btn-success:hover {
        background: #047857 !important; /* Emerald 700 */
        border-color: #047857 !important;
        transform: translateY(-1px) !important;
    }
    .app-shell .btn-danger,
    .app-shell button.btn-danger,
    .app-shell button[type="submit"].btn-danger {
        background: #e11d48 !important; /* Rose 600 */
        border-color: #e11d48 !important;
        color: #ffffff !important;
    }
    .app-shell .btn-danger:hover,
    .app-shell button.btn-danger:hover,
    .app-shell button[type="submit"].btn-danger:hover {
        background: #be123c !important; /* Rose 700 */
        border-color: #be123c !important;
        transform: translateY(-1px) !important;
    }

    .app-shell .theme-button-secondary,
    .app-shell button.border {
        min-height: 2.45rem !important;
        background: #ffffff !important;
        border: 1.5px solid #0b5ed7 !important;
        color: #0b5ed7 !important;
        border-radius: 12px !important;
        font-size: 0.825rem !important;
        font-weight: 700 !important;
        padding: 0.55rem 1rem !important;
        box-shadow: none !important;
        transition: all 0.2s ease !important;
    }

    .app-shell .theme-button-secondary:hover,
    .app-shell button.border:hover {
        background: #f0f6ff !important;
        border-color: #1d4ed8 !important;
        color: #1d4ed8 !important;
        transform: translateY(-1px) !important;
    }

    /* Polished Table Design System */
    .app-shell table {
        width: 100% !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
        background-color: #ffffff !important;
        border-radius: 14px !important;
        overflow: hidden !important;
        border: 1px solid #cbd6ea !important;
    }

    .app-shell table th {
        background-color: #f8fafc !important;
        color: #475569 !important;
        font-size: 0.75rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        padding: 0.85rem 1.15rem !important;
        border-bottom: 2px solid #cbd6ea !important;
        text-align: left !important;
    }

    .app-shell table td {
        padding: 0.95rem 1.15rem !important;
        color: #334155 !important;
        font-size: 0.825rem !important;
        border-bottom: 1px solid #e2e8f0 !important;
        vertical-align: middle !important;
    }

    .app-shell table tbody tr {
        transition: background-color 0.15s ease !important;
    }

    .app-shell table tbody tr:hover {
        background-color: #f8fafc !important;
    }

    .app-shell table tbody tr:last-child td {
        border-bottom: none !important;
    }

    /* Sidebar Shell Rework */
    .app-sidebar,
    .mobile-sidebar-panel {
        background: linear-gradient(180deg, #071833 0%, #0b1f3a 100%) !important;
        border-right: 1px solid rgba(255, 255, 255, 0.08) !important;
        box-shadow: 6px 0 30px rgba(7, 24, 51, 0.15) !important;
    }

    .app-shell .app-sidebar-scroll {
        padding: 0.85rem 0.65rem !important;
    }

    .app-shell .app-sidebar-group + .app-sidebar-group {
        margin-top: 1.1rem !important;
    }

    .app-shell .app-sidebar-label {
        font-size: 0.68rem !important;
        font-weight: 700 !important;
        letter-spacing: 0.12em !important;
        color: rgba(255, 255, 255, 0.45) !important;
    }

    .app-shell .app-sidebar-link {
        min-height: 2.45rem !important;
        border-radius: 12px !important;
        padding: 0.55rem 0.75rem !important;
        font-size: 0.825rem !important;
        color: #9fb3d1 !important;
        transition: all 0.2s ease !important;
        border-left: 4px solid transparent !important;
    }

    .app-shell .app-sidebar-link:hover {
        background: rgba(255, 255, 255, 0.05) !important;
        color: #ffffff !important;
        padding-left: 0.85rem !important;
    }

    /* Active Sidebar Links System */
    .app-shell .app-sidebar-link.is-active {
        background: #1d4ed8 !important;
        color: #ffffff !important;
        border-left: 4px solid #fbbf24 !important;
        border-radius: 0 10px 10px 0 !important;
        padding-left: 0.65rem !important;
        box-shadow: 0 8px 16px rgba(29, 78, 216, 0.25) !important;
    }

    .app-shell .app-sidebar-link-icon {
        width: 1.75rem !important;
        height: 1.75rem !important;
        border-radius: 8px !important;
        background: rgba(255, 255, 255, 0.06) !important;
        color: #9fb3d1 !important;
        transition: all 0.2s ease !important;
    }

    .app-shell .app-sidebar-link.is-active .app-sidebar-link-icon {
        background: rgba(255, 255, 255, 0.18) !important;
        color: #ffffff !important;
    }

    .result-checker-page {
        min-height: 100svh;
        background:
            linear-gradient(180deg, rgba(246, 248, 251, 0.96), rgba(232, 238, 247, 0.96)),
            radial-gradient(circle at 18% 12%, color-mix(in srgb, var(--ui-blue) 12%, transparent), transparent 30%),
            linear-gradient(90deg, color-mix(in srgb, var(--ui-blue) 8%, transparent), transparent 34%, color-mix(in srgb, var(--ui-red) 5%, transparent)) !important;
    }

    .result-checker-shell {
        min-height: 100svh;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        padding-top: 2.85rem !important;
        padding-bottom: 2.85rem !important;
    }

    .result-checker-card {
        border-radius: var(--ui-radius) !important;
        border-color: var(--ui-border) !important;
        padding: 1.25rem !important;
        box-shadow: 0 12px 34px rgba(15, 23, 42, 0.08) !important;
    }

    .result-checker-card .display-font {
        font-size: clamp(1.45rem, 2vw, 2rem) !important;
        line-height: 1.14 !important;
    }

    .result-checker-card .theme-input {
        min-height: 2.45rem !important;
        border-radius: var(--ui-radius-sm) !important;
        padding: 0.58rem 0.72rem !important;
    }

    @media (max-width: 1023px) {
        .public-shell {
            --public-header-height: 4.35rem !important;
        }

        .classic-topbar {
            display: none !important;
        }

        .beloved-hero {
            height: 540px !important;
            min-height: 440px !important;
        }

        .beloved-slide__title {
            font-size: 2.9rem !important;
        }

        .beloved-events-layout {
            grid-template-columns: 1fr !important;
        }

        .auth-layout {
            display: block !important;
            min-height: 100svh !important;
        }

        .auth-copy-panel {
            display: none !important;
        }
    }

    @media (max-width: 767px) {
        .public-shell,
        .public-content-shell,
        .beloved-home,
        .beloved-container,
        .auth-shell,
        .auth-layout,
        .auth-form-panel,
        .app-shell,
        .app-content-shell,
        .app-content-inner,
        .app-main {
            width: 100% !important;
            max-width: 100vw !important;
            overflow-x: hidden !important;
            box-sizing: border-box !important;
        }

        .beloved-container {
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }

        .public-topbar-row {
            min-height: 4.35rem !important;
        }

        .public-content-shell > section.mx-auto {
            padding-top: 1.25rem !important;
            padding-bottom: 1.25rem !important;
        }

        .beloved-hero {
            height: 500px !important;
            min-height: 420px !important;
        }

        .beloved-slide__content {
            width: 100% !important;
            max-width: 100% !important;
            padding: 0 1rem 4.5rem !important;
        }

        .beloved-slide__title {
            width: min(100%, 20.5rem) !important;
            max-width: calc(100vw - 2rem) !important;
            font-size: 1.82rem !important;
            line-height: 1.1 !important;
            overflow-wrap: normal !important;
        }

        .beloved-slide__sub {
            width: min(100%, 21rem) !important;
            max-width: calc(100vw - 2rem) !important;
            font-size: 0.86rem !important;
        }

        .beloved-slide__actions {
            width: 100% !important;
            max-width: calc(100vw - 2rem) !important;
        }

        .beloved-btn,
        .beloved-band-btn {
            white-space: normal !important;
        }

        .beloved-admission-band__inner {
            display: block !important;
            width: 100% !important;
            max-width: 100% !important;
            padding-inline: 1rem !important;
            text-align: center !important;
        }

        .beloved-admission-band__eyebrow,
        .beloved-admission-band__actions {
            justify-content: center !important;
        }

        .beloved-admission-band__actions {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) !important;
            gap: 0.55rem !important;
            margin-top: 0.95rem !important;
        }

        .beloved-admission-band h2,
        .beloved-admission-band p {
            margin-left: auto !important;
            margin-right: auto !important;
            max-width: 18rem !important;
            overflow-wrap: anywhere !important;
        }

        .beloved-slider-labels {
            display: none !important;
        }

        .beloved-slider-arrows {
            margin-left: auto !important;
        }

        .beloved-slider-pause {
            display: none !important;
        }

        .beloved-section {
            padding: 2.15rem 0 !important;
        }

        .beloved-sec-title {
            font-size: 1.55rem !important;
        }

        .beloved-slider-bar {
            height: 48px !important;
            padding: 0 0.9rem !important;
        }

        .auth-form-panel {
            min-height: 100svh !important;
            display: block !important;
            padding: 0.75rem !important;
            width: 100vw !important;
            max-width: 100vw !important;
            align-items: stretch !important;
        }

        .auth-mobile-brand,
        .auth-card {
            width: min(100%, 22rem) !important;
            max-width: 22rem !important;
            margin-left: 1rem !important;
            margin-right: auto !important;
        }

        .auth-card {
            padding: 1rem !important;
        }

        .auth-card *,
        .auth-mobile-brand {
            box-sizing: border-box !important;
            max-width: 100% !important;
        }

        .auth-card input,
        .auth-card textarea,
        .auth-card select,
        .auth-card button,
        .auth-card a {
            min-width: 0 !important;
            max-width: 100% !important;
        }

        .auth-mode-toggle {
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) !important;
        }

        .auth-form-options {
            align-items: flex-start !important;
            flex-direction: column !important;
            gap: 0.55rem !important;
        }

        .auth-card p,
        .auth-card label,
        .auth-card a {
            max-width: 20rem !important;
        }

        .app-shell {
            --app-topbar-height: 3.45rem !important;
        }

        .app-topbar-actions {
            gap: 0.35rem !important;
            max-width: 5.25rem !important;
        }

        .app-topbar-action {
            min-height: 2.1rem !important;
            padding: 0.45rem 0.62rem !important;
            font-size: 0.74rem !important;
        }

        .app-topbar-action:not(.app-topbar-action-strong) {
            display: none !important;
        }

        .app-shell .nav-brand-copy {
            max-width: calc(100vw - 10.25rem) !important;
        }

        .app-shell .app-content-inner {
            width: calc(100vw - 1.4rem) !important;
            max-width: calc(100vw - 1.4rem) !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        .app-shell .app-header-card,
        .app-shell .section-card,
        .app-shell .stat-tile,
        .app-shell .management-module-card,
        .app-shell main .border-dashed {
            width: 100% !important;
            max-width: 100% !important;
            overflow: hidden !important;
        }

        .app-shell main .border-dashed {
            max-width: 18.5rem !important;
        }

        .app-shell .section-nav-strip {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) !important;
            gap: 0.45rem !important;
        }

        .app-shell .section-nav-link {
            min-width: 0 !important;
            padding: 0.58rem 0.5rem !important;
            font-size: 0.72rem !important;
            white-space: normal !important;
        }

        .app-shell form .theme-button[style*="position: fixed"] {
            right: 1rem !important;
            bottom: 1rem !important;
            left: 1rem !important;
            width: auto !important;
            max-width: calc(100vw - 2rem) !important;
        }

        .app-shell main .grid {
            min-width: 0 !important;
            grid-template-columns: minmax(0, 1fr) !important;
        }

        .app-shell main .grid > * {
            min-width: 0 !important;
        }

        .app-shell main h1,
        .app-shell main h2,
        .app-shell main h3,
        .app-shell header h1,
        .app-shell header h2,
        .app-shell header h3,
        .app-shell header p,
        .app-shell main p,
        .app-shell main a,
        .app-shell main span,
        .app-shell main div {
            overflow-wrap: anywhere !important;
        }

        .app-shell header h1,
        .app-shell header p,
        .app-shell main h1,
        .app-shell main h2,
        .app-shell main h3,
        .app-shell main p {
            max-width: 18.5rem !important;
        }

        .app-shell .section-card,
        .app-shell .app-header-card {
            padding: 0.88rem !important;
        }

        .app-shell .portal-card-head {
            align-items: stretch !important;
            flex-direction: column !important;
        }

        .app-shell .portal-card-head .text-right {
            text-align: left !important;
        }

        .app-shell .portal-score-pill,
        .app-shell .portal-action-link {
            width: 100% !important;
        }

        .app-shell .stat-tile {
            min-height: 4.35rem !important;
        }

        .app-shell .stat-tile .display-font {
            font-size: 1.18rem !important;
        }

        .result-checker-shell {
            padding-top: 0.8rem !important;
        }
    }

    @media (max-width: 479px) {
        .app-shell main .grid[class*="grid-cols-2"],
        .app-shell main .grid[class*="sm:grid-cols-2"],
        .app-shell main .grid[class*="md:grid-cols-2"],
        .app-shell main .grid[class*="lg:grid-cols-2"],
        .app-shell main .grid[class*="xl:grid-cols-2"] {
            grid-template-columns: minmax(0, 1fr) !important;
        }

        .app-shell .stat-tile {
            min-height: 3.85rem !important;
        }

        .public-topbar-row {
            gap: 0.55rem !important;
        }

        .nav-brand-copy {
            max-width: calc(100vw - 7.8rem) !important;
        }
    }
</style>
