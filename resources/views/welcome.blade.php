@extends('layouts.public')

@section('content')
    @php
        $schoolName = $schoolSettings['school_name'] ?? 'BELOVED SCHOOLS';
        $tagline = $schoolSettings['site_tagline'] ?? 'Building Minds, Shaping Character';
        $subtitle = $schoolSettings['site_subtitle'] ?? 'Raising Future Leaders Through Knowledge and Godly Values';
        $phone = $schoolSettings['school_phone'] ?? '08067046701';
        $setting = function (string $key, mixed $default = '') use ($schoolSettings) {
            $value = $schoolSettings[$key] ?? null;

            return $value === null || trim((string) $value) === '' ? $default : $value;
        };
        $settingBool = function (string $key, bool $default = true) use ($schoolSettings): bool {
            return array_key_exists($key, $schoolSettings)
                ? filter_var($schoolSettings[$key], FILTER_VALIDATE_BOOLEAN)
                : $default;
        };

        $heroImages = collect(range(1, 5))
            ->map(fn (int $index) => ! empty($schoolSettings["hero_slide_{$index}_image"]) ? asset($schoolSettings["hero_slide_{$index}_image"]) : null)
            ->filter()
            ->values();

        $fallbackImages = collect([
            ! empty($schoolSettings['hero_intro_background_image']) ? asset($schoolSettings['hero_intro_background_image']) : null,
            ! empty($schoolSettings['section_background_1']) ? asset($schoolSettings['section_background_1']) : null,
            ! empty($schoolSettings['section_background_2']) ? asset($schoolSettings['section_background_2']) : null,
            ! empty($schoolSettings['section_background_3']) ? asset($schoolSettings['section_background_3']) : null,
            ! empty($schoolSettings['site_background_1']) ? asset($schoolSettings['site_background_1']) : null,
            ! empty($schoolSettings['site_background_2']) ? asset($schoolSettings['site_background_2']) : null,
        ])->filter()->values();

        $mediaPool = $heroImages->concat($fallbackImages)->unique()->values();
        $mediaAt = fn (int $index) => $mediaPool->get($index);
        $imageStyle = fn (?string $image) => $image ? "--slide-img: url('{$image}');" : '';
        $cardImageStyle = fn (?string $image) => $image ? "--card-img: url('{$image}');" : '';

        $slideDefaults = [
            1 => ['eyebrow' => "Welcome to {$schoolName} - Est. 2006", 'title' => 'Where Knowledge Shapes', 'emphasis' => 'Future Leaders', 'text' => $schoolSettings['hero_blurb'] ?? 'A disciplined school environment where academic excellence, character, and Godly values work together.', 'primary' => 'Apply Now', 'primary_link' => route('admissions'), 'secondary' => 'Explore Our School', 'secondary_link' => route('about'), 'label' => 'Welcome'],
            2 => ['eyebrow' => 'Academic Excellence', 'title' => 'JSS to SS3 With', 'emphasis' => 'Clear Pathways', 'text' => 'Junior and senior secondary students learn through structured classes, focused departments, and close teacher guidance.', 'primary' => 'Our Program', 'primary_link' => route('admissions'), 'secondary' => 'Contact Office', 'secondary_link' => route('contact'), 'label' => 'Academics'],
            3 => ['eyebrow' => 'Character and Discipline', 'title' => 'Strong Values for', 'emphasis' => 'Purposeful Living', 'text' => 'Students are trained to combine knowledge with discipline, integrity, responsibility, and spiritual growth.', 'primary' => 'Why Parents Choose Us', 'primary_link' => route('about'), 'secondary' => 'Student Login', 'secondary_link' => route('student.login'), 'label' => 'Values'],
            4 => ['eyebrow' => 'Admissions Open', 'title' => 'Give Your Child', 'emphasis' => 'A Strong Foundation', 'text' => 'Speak with the school office about available classes, requirements, and the next admission step.', 'primary' => 'Start Admission', 'primary_link' => route('admissions'), 'secondary' => 'Call School', 'secondary_link' => "tel:{$phone}", 'label' => 'Admissions'],
            5 => ['eyebrow' => 'Digital School Portal', 'title' => 'A Connected Campus', 'emphasis' => 'For Staff and Students', 'text' => 'The portal supports assignments, CBT, reports, finance records, and day-to-day school administration.', 'primary' => 'Staff Login', 'primary_link' => route('staff.login'), 'secondary' => 'Student Login', 'secondary_link' => route('student.login'), 'label' => 'Portal'],
        ];
        $homeSlides = collect($slideDefaults)->map(function (array $slide, int $index) use ($setting, $mediaAt, $schoolSettings) {
            foreach (['eyebrow', 'title', 'emphasis', 'text', 'primary', 'primary_link', 'secondary', 'secondary_link', 'label'] as $field) {
                $slide[$field] = $setting("landing_slide_{$index}_{$field}", $slide[$field]);
            }
            $slide['image'] = $mediaAt($index - 1);
            $slide['video'] = ! empty($schoolSettings["hero_slide_{$index}_video"]) ? asset($schoolSettings["hero_slide_{$index}_video"]) : null;

            return $slide;
        })->values();

        $useLiveStats = $settingBool('landing_use_live_stats', true);
        $liveStatDefaults = [
            1 => ['label' => 'Students', 'value' => (string) ($stats['students'] ?? 0)],
            2 => ['label' => 'Staff', 'value' => (string) ($stats['staff'] ?? 0)],
            3 => ['label' => 'Classes', 'value' => (string) ($stats['classes'] ?? 0)],
            4 => ['label' => 'Published Updates', 'value' => (string) ($stats['news'] ?? 0)],
        ];
        $manualStatDefaults = [
            1 => ['label' => 'Established', 'value' => '2006'],
            2 => ['label' => 'Location', 'value' => 'Ore'],
            3 => ['label' => 'Academic Levels', 'value' => 'JSS-SS3'],
            4 => ['label' => 'Students', 'value' => (string) ($stats['students'] ?? '60')],
        ];
        $statsCards = collect(range(1, 4))->map(function (int $index) use ($setting, $useLiveStats, $liveStatDefaults, $manualStatDefaults) {
            $defaults = $useLiveStats ? $liveStatDefaults[$index] : $manualStatDefaults[$index];
            $fallbackLabel = $setting("homepage_stat_{$index}_label", $defaults['label']);
            $fallbackValue = $setting("homepage_stat_{$index}_value", $defaults['value']);

            return [
                'label' => $setting("landing_stat_{$index}_label", $fallbackLabel),
                'value' => $useLiveStats ? $defaults['value'] : $setting("landing_stat_{$index}_value", $fallbackValue),
            ];
        });

        $programDefaults = [
            1 => ['badge' => 'Program', 'title' => 'Junior Secondary School', 'text' => 'JSS 1 to JSS 3 students build a strong academic foundation with disciplined learning routines.', 'link' => route('admissions')],
            2 => ['badge' => 'Program', 'title' => 'Senior Secondary School', 'text' => 'SS 1 to SS 3 students prepare for examinations, leadership, and higher education.', 'link' => route('admissions')],
            3 => ['badge' => 'Department', 'title' => 'Science Department', 'text' => 'Focused preparation for science, medicine, engineering, technology, and research pathways.', 'link' => route('admissions')],
            4 => ['badge' => 'Department', 'title' => 'Commercial Department', 'text' => 'Business, finance, commerce, and enterprise subjects taught with practical direction.', 'link' => route('admissions')],
            5 => ['badge' => 'Department', 'title' => 'Art Department', 'text' => 'Humanities, communication, law, social sciences, and creative pathways are supported.', 'link' => route('admissions')],
            6 => ['badge' => 'Portal', 'title' => 'Digital Learning Workspace', 'text' => 'CBT, assignments, results, records, and staff workflows are managed from one platform.', 'link' => route('staff.login')],
            7 => ['badge' => 'Values', 'title' => 'Moral and Godly Training', 'text' => 'Students are shaped through discipline, integrity, responsibility, and spiritual guidance.', 'link' => route('about')],
        ];
        $programCards = collect($programDefaults)->map(function (array $program, int $index) use ($setting, $mediaAt) {
            foreach (['badge', 'title', 'text', 'link'] as $field) {
                $program[$field] = $setting("landing_program_{$index}_{$field}", $program[$field]);
            }
            $program['image'] = $mediaAt(($index - 1) % 6);

            return $program;
        })->values();

        $manualEventDefaults = [
            1 => ['month' => 'Now', 'day' => '01', 'title' => 'Admission Enquiries', 'text' => 'Parents can contact the school office for admission guidance, requirements, and available classes.', 'tag_1' => 'Admissions', 'tag_2' => 'School Office'],
            2 => ['month' => 'Term', 'day' => '02', 'title' => 'Academic Records Update', 'text' => 'Teachers continue to upload assignments, tests, CBT activities, and reports through the school portal.', 'tag_1' => 'Portal', 'tag_2' => 'Academics'],
            3 => ['month' => 'Week', 'day' => '03', 'title' => 'Student Development', 'text' => 'Classroom instruction, discipline, and moral training continue across all departments.', 'tag_1' => 'Students', 'tag_2' => 'Values'],
            4 => ['month' => 'Open', 'day' => '04', 'title' => 'Parent Support Desk', 'text' => 'Parents can reach the school for finance records, student updates, and general support.', 'tag_1' => 'Parents', 'tag_2' => 'Support'],
        ];
        $manualEvents = collect($manualEventDefaults)->map(function (array $event, int $index) use ($setting) {
            foreach (['month', 'day', 'title', 'text', 'tag_1', 'tag_2'] as $field) {
                $event[$field] = $setting("landing_event_{$index}_{$field}", $event[$field]);
            }

            return [
                'month' => $event['month'],
                'day' => $event['day'],
                'title' => $event['title'],
                'text' => $event['text'],
                'tags' => collect([$event['tag_1'], $event['tag_2']])->filter()->values()->all(),
            ];
        });
        $announcementEvents = $settingBool('landing_use_latest_announcements', true)
            ? collect($announcements ?? [])
            ->take(4)
            ->map(function ($announcement) {
                return [
                    'month' => optional($announcement->created_at)->format('M') ?: 'Now',
                    'day' => optional($announcement->created_at)->format('d') ?: '01',
                    'title' => $announcement->title,
                    'text' => $announcement->excerpt ?: \Illuminate\Support\Str::limit($announcement->body ?? '', 140),
                    'tags' => [$announcement->category ?? 'School', 'Update'],
                ];
            })
            : collect();
        $eventItems = $announcementEvents->isNotEmpty() ? $announcementEvents : $manualEvents;

        $galleryImages = collect(range(1, 4))
            ->map(fn (int $index) => ! empty($schoolSettings["gallery_image_{$index}"]) ? asset($schoolSettings["gallery_image_{$index}"]) : null)
            ->filter()
            ->values();

        $galleryPool = $galleryImages->concat($mediaPool)->values();
        $galleryItems = collect([
            ['label' => $setting('landing_gallery_1_label', 'Classroom life'), 'image' => $galleryPool->get(0), 'class' => 'beloved-gallery-item--tall'],
            ['label' => $setting('landing_gallery_2_label', 'Student activities'), 'image' => $galleryPool->get(1), 'class' => ''],
            ['label' => $setting('landing_gallery_3_label', 'Academic focus'), 'image' => $galleryPool->get(2), 'class' => ''],
            ['label' => $setting('landing_gallery_4_label', 'School community'), 'image' => $galleryPool->get(3), 'class' => 'beloved-gallery-item--wide'],
            ['label' => $setting('landing_gallery_5_label', 'Moral development'), 'image' => $galleryPool->get(4), 'class' => ''],
            ['label' => $setting('landing_gallery_6_label', 'Leadership and service'), 'image' => $galleryPool->get(5), 'class' => ''],
        ]);

        $testimonialDefaults = [
            1 => ['initials' => 'PA', 'name' => 'Parent Testimonial', 'role' => 'Junior Secondary Parent', 'text' => 'The school gives our children structure, discipline, and academic attention. We are grateful for the care and consistency.'],
            2 => ['initials' => 'ST', 'name' => 'Student Voice', 'role' => 'Senior Secondary Student', 'text' => 'Teachers explain clearly, assignments are organized, and the portal helps me follow my academic work.'],
            3 => ['initials' => 'AL', 'name' => 'Alumni Reflection', 'role' => 'Former Student', 'text' => 'The values learned here continue to guide me. BELOVED SCHOOLS helped me grow in confidence and responsibility.'],
        ];
        $testimonials = collect($testimonialDefaults)->map(function (array $testimonial, int $index) use ($setting) {
            foreach (['initials', 'name', 'role', 'text'] as $field) {
                $testimonial[$field] = $setting("landing_testimonial_{$index}_{$field}", $testimonial[$field]);
            }

            return $testimonial;
        });

        $whatsappNumber = preg_replace('/\D+/', '', $schoolSettings['whatsapp_number'] ?? '08165587119');
        $whatsappDigits = str_starts_with($whatsappNumber, '0') ? '234'.substr($whatsappNumber, 1) : $whatsappNumber;
        $whatsappLink = $schoolSettings['whatsapp_link'] ?? "https://wa.me/{$whatsappDigits}";
        $showNewsletter = $settingBool('landing_show_newsletter', true);
    @endphp

    <div class="beloved-home" x-data="belovedLanding({ slideCount: {{ $homeSlides->count() }}, duration: 9000 })" style="--hero-duration: 9000ms;">
        <section class="beloved-hero" aria-label="{{ $schoolName }} hero slideshow">
            @foreach ($homeSlides as $index => $slide)
                <article
                    class="beloved-slide beloved-slide--{{ $index + 1 }}"
                    :class="{ 'is-active': current === {{ $index }} }"
                    style="{{ $imageStyle($slide['image']) }}"
                    aria-label="Slide {{ $index + 1 }} of {{ $homeSlides->count() }}"
                >
                    <div class="beloved-slide__bg {{ ! empty($slide['video']) ? 'beloved-slide__bg--video' : '' }}">
                        @if (! empty($slide['video']))
                            <video autoplay muted loop playsinline preload="auto" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; z-index: -1; pointer-events: none;">
                                <source src="{{ $slide['video'] }}">
                            </video>
                        @endif
                    </div>
                    <div class="beloved-slide__decor" aria-hidden="true">
                        <span class="beloved-decor-ring beloved-decor-ring--one"></span>
                        <span class="beloved-decor-ring beloved-decor-ring--two"></span>
                    </div>
                    <div class="beloved-slide__accent" aria-hidden="true"></div>
                    <div class="beloved-slide__content">
                        <div class="beloved-slide__eyebrow">
                            <span></span>
                            {{ $slide['eyebrow'] }}
                        </div>
                        <h1 class="beloved-slide__title">
                            {{ $slide['title'] }}<br>
                            <em>{{ $slide['emphasis'] }}</em>
                        </h1>
                        <p class="beloved-slide__sub">{{ $slide['text'] }}</p>
                        <div class="beloved-slide__actions">
                            <a href="{{ $slide['primary_link'] }}" class="beloved-btn beloved-btn--gold">
                                {{ $slide['primary'] }}
                                <span aria-hidden="true">&rarr;</span>
                            </a>
                            <a href="{{ $slide['secondary_link'] }}" class="beloved-btn beloved-btn--ghost">
                                {{ $slide['secondary'] }}
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach

            <div class="beloved-slide-thumbs" aria-hidden="true">
                @foreach ($homeSlides as $index => $slide)
                    <button
                        type="button"
                        class="beloved-slide-thumb"
                        :class="{ 'is-active': current === {{ $index }} }"
                        @click="goTo({{ $index }})"
                        style="{{ $imageStyle($slide['image']) }}"
                    >
                        <span class="beloved-slide-thumb__bg"></span>
                        <span class="beloved-slide-thumb__num">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                    </button>
                @endforeach
            </div>

            <div class="beloved-slide-counter" aria-hidden="true">
                <span x-text="String(current + 1).padStart(2, '0')">01</span>
                <div>
                    <i :class="{ 'is-running': progressRunning }"></i>
                </div>
                <span>{{ str_pad((string) $homeSlides->count(), 2, '0', STR_PAD_LEFT) }}</span>
            </div>

            <div class="beloved-slider-controls">
                <div class="beloved-slider-progress">
                    <span :class="{ 'is-running': progressRunning }"></span>
                </div>
                <div class="beloved-slider-bar">
                    <div class="beloved-slider-dots" role="tablist" aria-label="Go to slide">
                        @foreach ($homeSlides as $index => $slide)
                            <button
                                type="button"
                                class="beloved-slider-dot"
                                :class="{ 'is-active': current === {{ $index }} }"
                                @click="goTo({{ $index }})"
                                aria-label="Show {{ $slide['label'] }} slide"
                            ></button>
                        @endforeach
                    </div>

                    <div class="beloved-slider-labels" aria-hidden="true">
                        @foreach ($homeSlides as $index => $slide)
                            <button type="button" :class="{ 'is-active': current === {{ $index }} }" @click="goTo({{ $index }})">{{ $slide['label'] }}</button>
                        @endforeach
                    </div>

                    <div class="beloved-slider-arrows">
                        <button type="button" @click="previous()" aria-label="Previous slide">&lsaquo;</button>
                        <button type="button" @click="next()" aria-label="Next slide">&rsaquo;</button>
                        <button type="button" class="beloved-slider-pause" @click="togglePause()" :aria-label="paused ? 'Play slideshow' : 'Pause slideshow'">
                            <span x-text="paused ? 'Play' : 'Pause'">Pause</span>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <section class="beloved-admission-band" id="admissions">
            <div class="beloved-container beloved-admission-band__inner">
                <div>
                    <div class="beloved-admission-band__eyebrow">
                        <span></span>
                        {{ $setting('landing_admission_kicker', 'Admissions in Progress') }}
                    </div>
                    <h2>{{ $setting('landing_admission_title', 'Apply for the Current Academic Session') }}</h2>
                    <p>{{ $setting('landing_admission_text', "Admission into {$schoolName} is open. Speak with the school office for entrance guidance and available classes.") }}</p>
                </div>
                <div class="beloved-admission-band__actions">
                    <a href="{{ route('admissions') }}" class="beloved-band-btn beloved-band-btn--ghost">{{ $setting('landing_admission_primary_text', 'Admission Details') }}</a>
                    <a href="{{ route('contact') }}" class="beloved-band-btn beloved-band-btn--ghost">{{ $setting('landing_admission_support_text', 'Request Support') }}</a>
                    <a href="{{ $whatsappLink }}" target="_blank" rel="noopener noreferrer" class="beloved-band-btn beloved-band-btn--gold">{{ $setting('landing_admission_whatsapp_text', 'Chat on WhatsApp') }}</a>
                </div>
            </div>
        </section>

        <section class="beloved-stats-bar" aria-label="School highlights">
            <div class="beloved-container beloved-stats-bar__inner">
                @foreach ($statsCards as $card)
                    <div class="beloved-stats-bar__item">
                        <span>{{ $card['value'] }}</span>
                        <p>{{ $card['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="beloved-section" id="welcome">
            <div class="beloved-container beloved-welcome-grid">
                <div>
                    <span class="beloved-eyebrow">{{ $setting('landing_welcome_kicker', 'Welcome Address') }}</span>
                    <span class="beloved-sec-rule beloved-reveal"></span>
                    <p class="beloved-welcome__lead beloved-reveal beloved-delay-1">{{ $setting('landing_welcome_title', $subtitle) }}</p>
                    <p class="beloved-welcome__body beloved-reveal beloved-delay-2">{{ $setting('landing_welcome_text_1', "Founded in 2006 and located in Ore, Ondo State, {$schoolName} is committed to raising educated, disciplined, and God-fearing students.") }}</p>
                    <p class="beloved-welcome__body beloved-reveal beloved-delay-2">{{ $setting('landing_welcome_text_2', 'With professional teachers, focused learning, and strong moral foundations, the school prepares students for academic success and responsible living.') }}</p>

                    <div class="beloved-principal-card beloved-reveal beloved-delay-3">
                        <div class="beloved-principal-card__avatar">
                            <x-application-logo class="h-full w-full object-cover" />
                        </div>
                        <div>
                            <span>{{ $schoolName }}</span>
                            <small>{{ $tagline }}</small>
                        </div>
                    </div>

                    <a href="{{ route('about') }}" class="beloved-director-cta beloved-reveal beloved-delay-3">
                        <span>
                            <small>From the School</small>
                            <strong>{{ $setting('landing_welcome_profile_button_text', 'Read Full School Profile') }}</strong>
                        </span>
                        <i aria-hidden="true">&rarr;</i>
                        <b></b>
                    </a>
                </div>

                <div class="beloved-welcome__right">
                    <div class="beloved-video-thumb beloved-reveal">
                        @if (! empty($schoolSettings['hero_background_video']))
                            <video autoplay muted loop controls playsinline preload="auto" poster="{{ $mediaAt(0) }}">
                                <source src="{{ asset($schoolSettings['hero_background_video']) }}">
                            </video>
                        @else
                            <div class="beloved-video-thumb__cover" style="{{ $cardImageStyle($mediaAt(0)) }}"></div>
                            <span class="beloved-video-thumb__play" aria-hidden="true"></span>
                        @endif
                        <div class="beloved-video-thumb__label">Campus life at {{ $schoolName }}</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="beloved-section beloved-section--cream" id="programs">
            <div class="beloved-container">
                <div class="beloved-sec-hdr beloved-sec-hdr--center">
                    <span class="beloved-eyebrow">{{ $setting('landing_programs_kicker', 'Our Programs') }}</span>
                    <h2 class="beloved-sec-title beloved-reveal">{{ $setting('landing_programs_title', 'Academic Pathways') }} <em>{{ $setting('landing_programs_emphasis', "at {$schoolName}") }}</em></h2>
                    <span class="beloved-sec-rule beloved-reveal"></span>
                    <p class="beloved-sec-sub beloved-reveal">{{ $setting('landing_programs_text', 'Every class shares the same values, standards, and commitment to excellence.') }}</p>
                </div>

                <div class="beloved-schools-grid">
                    @foreach ($programCards as $index => $program)
                        <a href="{{ $program['link'] }}" class="beloved-school-card {{ $loop->last ? 'beloved-school-card--wide' : '' }} beloved-reveal beloved-delay-{{ $index % 3 }}">
                            <div class="beloved-school-card__img">
                                <div class="beloved-school-card__bg" style="{{ $cardImageStyle($program['image']) }}"></div>
                                <span class="beloved-badge">{{ $program['badge'] }}</span>
                            </div>
                            <div class="beloved-school-card__body">
                                <h3>{{ $program['title'] }}</h3>
                                <p>{{ $program['text'] }}</p>
                                <span>Learn more <span aria-hidden="true">&rarr;</span></span>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="beloved-schools-cta beloved-reveal">
                    <a href="{{ route('admissions') }}" class="beloved-btn beloved-btn--gold beloved-btn--lg">{{ $setting('landing_programs_button_text', 'Register Your Child Now') }}</a>
                </div>
            </div>
        </section>

        <section class="beloved-section beloved-section--cream" id="events">
            <div class="beloved-container">
                <div class="beloved-sec-hdr">
                    <span class="beloved-eyebrow">{{ $setting('landing_events_kicker', 'Upcoming Events') }}</span>
                    <h2 class="beloved-sec-title beloved-reveal">{{ $setting('landing_events_title', "What's Happening") }} <em>{{ $setting('landing_events_emphasis', "at {$schoolName}") }}</em></h2>
                    <span class="beloved-sec-rule beloved-reveal"></span>
                </div>

                <div class="beloved-events-layout">
                    <div class="beloved-events-list">
                        @foreach ($eventItems as $index => $event)
                            <article class="beloved-event-item beloved-reveal beloved-delay-{{ $index % 3 }}">
                                <div class="beloved-event-item__date">
                                    <span>{{ $event['month'] }}</span>
                                    <strong>{{ $event['day'] }}</strong>
                                </div>
                                <div>
                                    <h3>{{ $event['title'] }}</h3>
                                    <p>{{ $event['text'] }}</p>
                                    <div>
                                        @foreach ($event['tags'] as $tag)
                                            <span>{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <aside class="beloved-events-sidebar">
                        <div class="beloved-events-cta-box beloved-reveal beloved-delay-1">
                            <h3>{{ $setting('landing_events_sidebar_title', 'Need admission support?') }}</h3>
                            <p>{{ $setting('landing_events_sidebar_text', 'Get class placement guidance, requirements, and parent support from the school office.') }}</p>
                            <a href="{{ route('contact') }}" class="beloved-btn beloved-btn--gold beloved-btn--sm">{{ $setting('landing_events_sidebar_button_text', 'Contact School') }}</a>
                        </div>
                    </aside>
                </div>
            </div>
        </section>

        <section class="beloved-section" id="gallery">
            <div class="beloved-container">
                <div class="beloved-sec-hdr beloved-sec-hdr--center">
                    <span class="beloved-eyebrow">{{ $setting('landing_gallery_kicker', 'Gallery') }}</span>
                    <h2 class="beloved-sec-title beloved-reveal">{{ $setting('landing_gallery_title', 'Life at') }} <em>{{ $setting('landing_gallery_emphasis', $schoolName) }}</em></h2>
                    <span class="beloved-sec-rule beloved-reveal"></span>
                </div>

                <div class="beloved-gallery-masonry">
                    @foreach ($galleryItems as $index => $item)
                        <div class="beloved-gallery-item {{ $item['class'] }} beloved-reveal beloved-delay-{{ $index % 4 }}">
                            <div class="beloved-gallery-item__bg" style="{{ $cardImageStyle($item['image']) }}"></div>
                            <div class="beloved-gallery-item__overlay">
                                <span>{{ $item['label'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="beloved-gallery-cta beloved-reveal">
                    <a href="{{ route('about') }}" class="beloved-btn beloved-btn--outline beloved-btn--lg">{{ $setting('landing_gallery_button_text', 'View School Profile') }}</a>
                </div>
            </div>
        </section>

        <section class="beloved-section beloved-section--cream" id="testimonials">
            <div class="beloved-container">
                <div class="beloved-sec-hdr beloved-sec-hdr--center">
                    <span class="beloved-eyebrow">{{ $setting('landing_testimonials_kicker', 'Testimonials') }}</span>
                    <h2 class="beloved-sec-title beloved-reveal">{{ $setting('landing_testimonials_title', 'What Our') }} <em>{{ $setting('landing_testimonials_emphasis', 'Parents Say') }}</em></h2>
                    <span class="beloved-sec-rule beloved-reveal"></span>
                </div>

                <div class="beloved-testi-grid">
                    @foreach ($testimonials as $index => $testimonial)
                        <article class="beloved-testi-card beloved-reveal beloved-delay-{{ $index }}">
                            <div class="beloved-testi-card__quote" aria-hidden="true">"</div>
                            <div class="beloved-testi-card__stars" aria-label="5 stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                            <p>{{ $testimonial['text'] }}</p>
                            <div class="beloved-testi-card__author">
                                <span>{{ $testimonial['initials'] }}</span>
                                <div>
                                    <strong>{{ $testimonial['name'] }}</strong>
                                    <small>{{ $testimonial['role'] }}</small>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        @if ($showNewsletter)
            <section class="beloved-newsletter-band">
                <div class="beloved-container beloved-newsletter-band__inner">
                    <div>
                        <h2>{{ $setting('landing_newsletter_title', 'Newsletter') }}</h2>
                        <p>{{ $setting('landing_newsletter_text', "Receive school news, events, admissions updates, and parent notices from {$schoolName}.") }}</p>
                    </div>
                    <form class="beloved-newsletter-band__form" x-data="{ subscribed: false }" @submit.prevent="subscribed = true">
                        <input type="email" placeholder="{{ $setting('landing_newsletter_placeholder', 'Enter your email address') }}" required aria-label="Email address">
                        <button type="submit" x-text="subscribed ? @js($setting('landing_newsletter_subscribed_text', 'Subscribed')) : @js($setting('landing_newsletter_button_text', 'Subscribe'))">{{ $setting('landing_newsletter_button_text', 'Subscribe') }}</button>
                    </form>
                </div>
            </section>
        @endif

        <button type="button" class="beloved-back-to-top" x-show="backVisible" x-transition.opacity @click="scrollTop()" aria-label="Back to top">
            &uarr;
        </button>
    </div>
@endsection
