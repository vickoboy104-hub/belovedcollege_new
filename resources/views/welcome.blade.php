@extends('layouts.public')

@section('content')
    @php
        $heroSlides = collect(range(1, 4))->map(function (int $index) use ($schoolSettings) {
            return [
                'title' => $schoolSettings["hero_slide_{$index}_title"] ?? null,
                'text' => $schoolSettings["hero_slide_{$index}_text"] ?? null,
                'image' => ! empty($schoolSettings["hero_slide_{$index}_image"]) ? asset($schoolSettings["hero_slide_{$index}_image"]) : null,
            ];
        })->filter(fn (array $slide) => $slide['title'] || $slide['text'] || $slide['image'])->values();

        if ($heroSlides->isEmpty()) {
            $heroSlides = collect([
                ['title' => 'Welcome to BELOVED SCHOOLS', 'text' => 'Building Minds, Shaping Character', 'image' => null],
                ['title' => 'Excellence in Education', 'text' => 'Empowering Students for a Brighter Future', 'image' => null],
                ['title' => 'Godly Values, Strong Discipline', 'text' => 'Raising Responsible Leaders', 'image' => null],
                ['title' => 'Knowledge for All Nation', 'text' => 'Education Without Limits', 'image' => null],
            ]);
        }

        $galleryImages = collect(range(1, 4))
            ->map(fn (int $index) => ! empty($schoolSettings["gallery_image_{$index}"]) ? asset($schoolSettings["gallery_image_{$index}"]) : null)
            ->filter()
            ->values();

        $heroVideo = ! empty($schoolSettings['hero_background_video']) ? asset($schoolSettings['hero_background_video']) : null;
        $heroVideoPoster = ! empty($schoolSettings['hero_background_video_poster']) ? asset($schoolSettings['hero_background_video_poster']) : null;
        $heroIntroBackground = ! empty($schoolSettings['hero_intro_background_image']) ? asset($schoolSettings['hero_intro_background_image']) : null;
        $heroIntroOverlayPercent = max(0, min(100, (int) ($schoolSettings['hero_intro_background_opacity'] ?? 12)));
        $heroIntroOverlayBase = round($heroIntroOverlayPercent / 100, 2);
        $heroIntroOverlayStrong = round(min(0.92, $heroIntroOverlayBase + 0.24), 2);
        $heroIntroOverlaySoft = round(max(0.08, min(0.78, $heroIntroOverlayBase * 0.55)), 2);
        $heroHighlights = collect([
            1 => 'Founded in 2006',
            2 => 'Located in Ore, Ondo State',
            3 => 'Knowledge, Discipline, and Godly Values',
        ])->map(function (string $default, int $index) use ($schoolSettings) {
            return [
                'text' => $schoolSettings["hero_highlight_{$index}_text"] ?? $default,
                'image' => ! empty($schoolSettings["hero_highlight_{$index}_background"]) ? asset($schoolSettings["hero_highlight_{$index}_background"]) : null,
            ];
        })->values();
        $statsCards = collect([
            1 => ['label' => 'Established', 'value' => '2006'],
            2 => ['label' => 'Location', 'value' => 'Ore'],
            3 => ['label' => 'Academic Levels', 'value' => 'JSS-SS3'],
            4 => ['label' => 'Students', 'value' => (string) $stats['students']],
        ])->map(function (array $default, int $index) use ($schoolSettings) {
            return [
                'label' => $schoolSettings["homepage_stat_{$index}_label"] ?? $default['label'],
                'value' => $schoolSettings["homepage_stat_{$index}_value"] ?? $default['value'],
                'image' => ! empty($schoolSettings["homepage_stat_{$index}_background"]) ? asset($schoolSettings["homepage_stat_{$index}_background"]) : null,
            ];
        })->values();
        $quickIntro = [
            'kicker' => $schoolSettings['quick_intro_kicker'] ?? 'Quick Intro',
            'title' => $schoolSettings['quick_intro_title'] ?? 'A real school environment for growth and purpose.',
            'text_1' => $schoolSettings['quick_intro_text_1'] ?? 'Founded in 2006 and located in Ore, Ondo State, BELOVED SCHOOLS is committed to raising educated, disciplined, and God-fearing students.',
            'text_2' => $schoolSettings['quick_intro_text_2'] ?? 'With professional teachers, modern learning facilities, and a strong moral foundation, we prepare students for success in both academics and life.',
            'image' => ! empty($schoolSettings['quick_intro_background_image']) ? asset($schoolSettings['quick_intro_background_image']) : null,
        ];
        $featureCards = collect([
            1 => ['title' => 'Experienced and Professional Teachers', 'text' => 'Students learn from committed teachers who combine subject mastery with close guidance.'],
            2 => ['title' => 'Strong Academic Performance', 'text' => 'Academic excellence remains central to classroom instruction and student development.'],
            3 => ['title' => 'Godly and Moral Training', 'text' => 'The school builds character through strong moral discipline and spiritual values.'],
            4 => ['title' => 'Conducive Learning Environment', 'text' => 'Students grow in a focused atmosphere with well-equipped facilities and a student-focused teaching approach.'],
        ])->map(function (array $default, int $index) use ($schoolSettings) {
            return [
                'title' => $schoolSettings["home_feature_{$index}_title"] ?? $default['title'],
                'text' => $schoolSettings["home_feature_{$index}_text"] ?? $default['text'],
                'image' => ! empty($schoolSettings["home_feature_{$index}_background"]) ? asset($schoolSettings["home_feature_{$index}_background"]) : null,
            ];
        })->values();
        $whyChoose = [
            'kicker' => $schoolSettings['why_choose_kicker'] ?? 'Why Parents Choose BELOVED SCHOOLS',
            'title' => $schoolSettings['why_choose_title'] ?? 'A disciplined school environment built for excellence.',
            'text' => $schoolSettings['why_choose_text'] ?? 'BELOVED SCHOOLS combines academic excellence with moral discipline to help students become responsible and purposeful leaders.',
            'button_text' => $schoolSettings['why_choose_button_text'] ?? 'Read More',
            'button_link' => $schoolSettings['why_choose_button_link'] ?? route('about'),
        ];
        $academicSection = [
            'kicker' => $schoolSettings['academic_section_kicker'] ?? 'Our Academic Program',
            'title' => $schoolSettings['academic_section_title'] ?? 'A full secondary school structure with clear pathways.',
            'image' => ! empty($schoolSettings['academic_section_background_image']) ? asset($schoolSettings['academic_section_background_image']) : null,
        ];
        $academicCards = collect([
            1 => ['title' => 'Junior Secondary School', 'text' => 'JSS 1 - JSS 3'],
            2 => ['title' => 'Senior Secondary School', 'text' => 'SS 1 - SS 3'],
            3 => ['title' => 'Science Department', 'text' => 'Preparing students for science and technical careers.'],
            4 => ['title' => 'Commercial Department', 'text' => 'Supporting students interested in business and commerce.'],
            5 => ['title' => 'Art Department', 'text' => 'Guiding students in humanities, communication, and the arts.'],
            6 => ['title' => 'Student-Focused Teaching', 'text' => 'Each department is designed to prepare students for higher education and future careers.'],
        ])->map(function (array $default, int $index) use ($schoolSettings) {
            return [
                'title' => $schoolSettings["academic_card_{$index}_title"] ?? $default['title'],
                'text' => $schoolSettings["academic_card_{$index}_text"] ?? $default['text'],
                'image' => ! empty($schoolSettings["academic_card_{$index}_background"]) ? asset($schoolSettings["academic_card_{$index}_background"]) : null,
            ];
        })->values();
        $foundersSection = [
            'kicker' => $schoolSettings['founders_kicker'] ?? 'Meet the Founders',
            'title' => $schoolSettings['founders_title'] ?? 'A school vision built on education and youth development.',
            'text_1' => $schoolSettings['founders_text_1'] ?? 'BELOVED SCHOOLS was founded by Mr. Zebilon K. S. alongside his wife Mrs. Grace Zebilon, whose passion for education and youth development led to the establishment of the school.',
            'text_2' => $schoolSettings['founders_text_2'] ?? 'Their vision was to create a learning environment where students can grow academically while being rooted in strong moral and spiritual values.',
            'values' => $schoolSettings['founders_values_text'] ?? 'Core Values: Discipline, Excellence, Integrity, Godliness, Responsibility',
            'image' => ! empty($schoolSettings['founders_background_image']) ? asset($schoolSettings['founders_background_image']) : null,
        ];
        $gallerySection = [
            'kicker' => $schoolSettings['gallery_section_kicker'] ?? 'Life at BELOVED SCHOOLS',
            'title' => $schoolSettings['gallery_section_title'] ?? 'Life at BELOVED SCHOOLS',
            'text' => $schoolSettings['gallery_section_text'] ?? 'Explore moments from our classrooms, events, and student activities that reflect our commitment to excellence and holistic development.',
            'image' => ! empty($schoolSettings['gallery_section_background_image']) ? asset($schoolSettings['gallery_section_background_image']) : null,
        ];
        $newsSection = [
            'kicker' => $schoolSettings['news_section_kicker'] ?? 'Latest news',
            'title' => $schoolSettings['news_section_title'] ?? 'Announcements and updates',
            'empty_text' => $schoolSettings['news_section_empty_text'] ?? 'School announcements will appear here as BELOVED SCHOOLS shares updates with parents and students.',
            'image' => ! empty($schoolSettings['news_section_background_image']) ? asset($schoolSettings['news_section_background_image']) : null,
        ];
        $whatsappNumber = preg_replace('/\D+/', '', $schoolSettings['whatsapp_number'] ?? '08165587119');
        $whatsappDigits = str_starts_with($whatsappNumber, '0') ? '234'.substr($whatsappNumber, 1) : $whatsappNumber;
        $whatsappLink = $schoolSettings['whatsapp_link'] ?? "https://wa.me/{$whatsappDigits}";
        $ctaSection = [
            'kicker' => $schoolSettings['cta_kicker'] ?? 'Call to action',
            'title' => $schoolSettings['cta_title'] ?? 'Give your child the foundation for a successful future.',
            'text' => $schoolSettings['cta_text'] ?? 'BELOVED SCHOOLS combines knowledge, discipline, integrity, responsibility, and Godliness to prepare students for meaningful impact.',
            'button_text' => $schoolSettings['cta_button_text'] ?? 'Enroll Today',
            'button_link' => $schoolSettings['cta_button_link'] ?? route('admissions'),
            'phone_label' => $schoolSettings['cta_phone_label'] ?? 'Call Us',
        ];
        $contentCardStyle = function (?string $image): string {
            return $image
                ? "--content-card-bg: url('{$image}');"
                : '';
        };
    @endphp

    <div
        x-data="{
            showPopup: false,
            popupEnabled: {{ ! empty($schoolSettings['welcome_popup_enabled']) ? 'true' : 'false' }},
            init() {
                if (this.popupEnabled) {
                    setTimeout(() => {
                        this.showPopup = true;
                    }, 900);
                }
            }
        }"
    >
        @if (! empty($schoolSettings['welcome_popup_enabled']))
            <div x-show="showPopup" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 px-4 py-8" style="display: none;">
                <div class="relative w-full max-w-2xl overflow-hidden rounded-[2rem] bg-white shadow-2xl shadow-slate-950/25">
                    <button @click="showPopup = false" class="absolute right-4 top-4 rounded-full bg-white/90 px-3 py-1 text-sm font-semibold text-slate-700 shadow">Close</button>
                    <div class="grid md:grid-cols-[0.9fr,1.1fr]">
                        <div class="brand-gradient min-h-64">
                            @if (! empty($schoolSettings['welcome_popup_image']))
                                <img src="{{ asset($schoolSettings['welcome_popup_image']) }}" alt="Welcome popup" class="h-full w-full object-cover">
                            @endif
                        </div>
                        <div class="px-6 py-8 md:px-8">
                            <div class="section-kicker text-xs font-semibold uppercase tracking-[0.32em]">Welcome</div>
                            <h2 class="display-font mt-3 text-3xl font-bold text-slate-950">{{ $schoolSettings['welcome_popup_title'] ?? 'Apply to BELOVED SCHOOLS' }}</h2>
                            <p class="mt-4 text-sm leading-7 text-slate-600">{{ $schoolSettings['welcome_popup_text'] ?? 'Give your child the foundation for a successful future with disciplined learning, academic excellence, and Godly values.' }}</p>
                            <div class="mt-6 flex flex-wrap gap-3">
                                <a href="{{ $schoolSettings['welcome_popup_button_link'] ?? route('admissions') }}" class="theme-button">
                                    {{ $schoolSettings['welcome_popup_button_text'] ?? 'Apply Now' }}
                                </a>
                                <button @click="showPopup = false" class="theme-button-secondary">Dismiss</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <section
            x-data="heroExperience(@js([
                'hasVideo' => (bool) $heroVideo,
                'hasSlides' => $heroSlides->isNotEmpty(),
                'slideCount' => $heroSlides->count(),
                'slideDuration' => 5500,
            ]))"
            x-init="init()"
            class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 lg:py-16"
        >
            <div class="fade-in hero-media-stage">
                <div class="hero-stage-indicator" x-text="activeMode === 'video' ? 'Campus film' : 'School highlights'"></div>

                @if ($heroVideo)
                    <div x-show="activeMode === 'video'" x-transition.opacity.duration.700ms class="hero-media-layer" style="display: none;">
                        <video
                            x-ref="heroVideo"
                            autoplay
                            muted
                            playsinline
                            preload="metadata"
                            x-on:ended="handleVideoEnded"
                            x-on:error="startSlides()"
                            poster="{{ $heroVideoPoster }}"
                        >
                            <source src="{{ $heroVideo }}">
                        </video>
                        <div class="hero-media-shade"></div>
                        <div class="hero-media-copy">
                            <div class="hero-media-pill">Welcome</div>
                            <h2 class="display-font mt-4 max-w-2xl text-3xl font-bold sm:text-4xl">Welcome to {{ $schoolSettings['school_name'] ?? 'BELOVED SCHOOLS' }}</h2>
                            <p class="mt-4 max-w-xl text-sm leading-7 text-white/85">Raising future leaders through knowledge, discipline, and Godly values.</p>
                        </div>
                    </div>
                @endif

                @foreach ($heroSlides as $index => $slide)
                    <div
                        x-show="activeMode === 'slides' && slide === {{ $index }}"
                        x-transition.opacity.duration.700ms
                        class="hero-media-layer hero-slide"
                        style="display: none; {{ $slide['image'] ? "background-image: url('{$slide['image']}')" : 'background: linear-gradient(135deg, var(--theme-primary), var(--theme-secondary) 55%, var(--theme-accent));' }}"
                    >
                        <div class="hero-media-shade"></div>
                        <div class="hero-media-copy">
                            <div class="hero-media-pill">Banner</div>
                            <h2 class="display-font mt-4 max-w-2xl text-3xl font-bold sm:text-4xl">{{ $slide['title'] }}</h2>
                            <p class="mt-4 max-w-xl text-sm leading-7 text-white/85">{{ $slide['text'] }}</p>
                        </div>
                    </div>
                @endforeach

                @if ($heroSlides->count() > 1)
                    <div class="hero-stage-dots">
                        @foreach ($heroSlides as $index => $slide)
                            <button
                                type="button"
                                @click="startSlides(); slide = {{ $index }}"
                                class="hero-stage-dot"
                                :class="{ 'is-active': activeMode === 'slides' && slide === {{ $index }} }"
                                aria-label="Show slide {{ $index + 1 }}"
                            ></button>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="reveal-up space-y-6">
                <div class="notice-badge inline-flex rounded-full px-4 py-2 text-sm font-semibold shadow-sm">
                    {{ $schoolSettings['site_tagline'] ?? 'Building Minds, Shaping Character' }}
                </div>

                <div
                    class="mesh-card hero-intro-card px-7 py-7 sm:px-8 sm:py-9 {{ $heroIntroBackground ? 'hero-intro-card-has-image' : '' }}"
                    @if ($heroIntroBackground)
                        style="--hero-intro-bg-image: url('{{ $heroIntroBackground }}'); --hero-intro-overlay-strong: {{ $heroIntroOverlayStrong }}; --hero-intro-overlay-base: {{ $heroIntroOverlayBase }}; --hero-intro-overlay-soft: {{ $heroIntroOverlaySoft }};"
                    @endif
                >
                    <h1 class="display-font max-w-3xl text-5xl font-bold leading-tight text-slate-950 sm:text-6xl">
                        {{ $schoolSettings['site_subtitle'] ?? 'Raising Future Leaders Through Knowledge and Godly Values' }}
                    </h1>
                    <p class="brand-text mt-3 text-2xl font-bold">{{ $schoolSettings['school_name'] ?? 'BELOVED SCHOOLS' }}</p>
                    <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-600">
                        {{ $schoolSettings['hero_blurb'] ?? 'At BELOVED SCHOOLS, we combine academic excellence with strong moral discipline to prepare students for a successful and purposeful life.' }}
                    </p>
                    <div class="mt-8 flex flex-wrap gap-4">
                        <a href="{{ route('admissions') }}" class="theme-button">Apply Now</a>
                        <a href="{{ route('contact') }}" class="theme-button-secondary">Contact Us</a>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    @foreach ($heroHighlights as $item)
                        <div class="rounded-[1.75rem] border border-slate-200 bg-white px-4 py-4 text-sm font-semibold text-slate-700 shadow-sm {{ $item['image'] ? 'content-media-card' : '' }}" style="{{ $contentCardStyle($item['image']) }}">{{ $item['text'] }}</div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 md:grid-cols-4">
                @foreach ($statsCards as $item)
                    <div class="stat-tile reveal-up {{ $item['image'] ? 'content-media-card' : '' }}" style="{{ $contentCardStyle($item['image']) }}">
                        <div class="text-sm uppercase tracking-[0.24em] text-slate-500">{{ $item['label'] }}</div>
                        <div class="display-font mt-3 text-4xl font-bold text-slate-950">{{ $item['value'] }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="mx-auto mt-16 max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-[0.95fr,1.05fr]">
                <div class="section-card brand-surface brand-outline reveal-up {{ $quickIntro['image'] ? 'content-media-card' : '' }}" style="{{ $contentCardStyle($quickIntro['image']) }}">
                    <div class="section-kicker text-xs font-semibold uppercase tracking-[0.32em]">{{ $quickIntro['kicker'] }}</div>
                    <h2 class="display-font mt-4 text-3xl font-bold text-slate-950">{{ $quickIntro['title'] }}</h2>
                    <div class="mt-5 space-y-4 text-sm leading-7 text-slate-600">
                        <p>{{ $quickIntro['text_1'] }}</p>
                        <p>{{ $quickIntro['text_2'] }}</p>
                    </div>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    @foreach ($featureCards as $card)
                        <div class="section-card reveal-up {{ $card['image'] ? 'content-media-card' : '' }}" style="{{ $contentCardStyle($card['image']) }}">
                            <h3 class="display-font text-2xl font-bold text-slate-950">{{ $card['title'] }}</h3>
                            <p class="mt-3 text-sm leading-7 text-slate-600">{{ $card['text'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="mx-auto mt-16 max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="immersive-band reveal-up" style="{{ ! empty($schoolSettings['section_background_1']) ? "background-image: url('".asset($schoolSettings['section_background_1'])."')" : 'background-image: linear-gradient(135deg, var(--theme-primary), var(--theme-secondary) 58%, var(--theme-highlight));' }}">
                <div class="immersive-band-content">
                    <div class="immersive-band-card">
                        <div class="section-kicker text-xs font-semibold uppercase tracking-[0.32em]" style="color: rgba(255, 255, 255, 0.84) !important; text-shadow: 0 3px 14px rgba(2, 6, 23, 0.42);">{{ $whyChoose['kicker'] }}</div>
                        <h2 class="display-font mt-4 text-3xl font-bold sm:text-4xl">{{ $whyChoose['title'] }}</h2>
                        <p class="immersive-band-copy">{{ $whyChoose['text'] }}</p>
                        <a href="{{ $whyChoose['button_link'] }}" class="theme-button mt-6 inline-flex">{{ $whyChoose['button_text'] }}</a>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto mt-16 max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-[1.05fr,0.95fr]">
                <div class="section-card reveal-up {{ $academicSection['image'] ? 'content-media-card' : '' }}" style="{{ $contentCardStyle($academicSection['image']) }}">
                    <div class="section-kicker text-xs font-semibold uppercase tracking-[0.32em]">{{ $academicSection['kicker'] }}</div>
                    <h2 class="display-font mt-3 text-3xl font-bold text-slate-950">{{ $academicSection['title'] }}</h2>
                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        @foreach ($academicCards as $program)
                            <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5 {{ $program['image'] ? 'content-media-card' : '' }}" style="{{ $contentCardStyle($program['image']) }}">
                                <h3 class="display-font text-xl font-bold text-slate-900">{{ $program['title'] }}</h3>
                                <p class="mt-3 text-sm leading-7 text-slate-600">{{ $program['text'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="section-card reveal-up {{ $foundersSection['image'] ? 'content-media-card' : '' }}" style="{{ $contentCardStyle($foundersSection['image']) }}">
                    <div class="section-kicker text-xs font-semibold uppercase tracking-[0.32em]">{{ $foundersSection['kicker'] }}</div>
                    <h2 class="display-font mt-3 text-3xl font-bold text-slate-950">{{ $foundersSection['title'] }}</h2>
                    <p class="mt-4 text-sm leading-7 text-slate-600">{{ $foundersSection['text_1'] }}</p>
                    <p class="mt-4 text-sm leading-7 text-slate-600">{{ $foundersSection['text_2'] }}</p>
                    <div class="mt-6 space-y-3 text-sm text-slate-600">
                        <div>{!! nl2br(e($foundersSection['values'])) !!}</div>
                        <div><span class="font-semibold text-slate-900">Motto:</span> {{ $schoolSettings['motto'] ?? 'Knowledge for All Nation' }}</div>
                        <div><span class="font-semibold text-slate-900">Location:</span> {{ $schoolSettings['school_address'] ?? 'Ayeteju Street, Ore, Ondo State' }}</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto mt-16 max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-[1.05fr,0.95fr]">
                <div class="section-card reveal-up {{ $gallerySection['image'] ? 'content-media-card' : '' }}" style="{{ $contentCardStyle($gallerySection['image']) }}">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <div class="section-kicker text-xs font-semibold uppercase tracking-[0.32em]">{{ $gallerySection['kicker'] }}</div>
                            <h2 class="display-font mt-3 text-3xl font-bold text-slate-950">{{ $gallerySection['title'] }}</h2>
                        </div>
                        <p class="text-sm text-slate-500">{{ $gallerySection['text'] }}</p>
                    </div>
                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        @forelse ($galleryImages as $image)
                            <div class="gallery-frame h-52">
                                <img src="{{ $image }}" alt="BELOVED SCHOOLS gallery image">
                            </div>
                        @empty
                            @foreach ([
                                'Classroom life',
                                'Student activities',
                                'School events',
                                'Academic excellence',
                            ] as $placeholder)
                                <div class="soft-grid flex h-52 items-end rounded-[2rem] border border-slate-200 bg-[color:var(--theme-surface)] p-5">
                                    <div>
                                        <div class="section-kicker text-xs font-semibold uppercase tracking-[0.32em]">Gallery slot</div>
                                        <div class="display-font mt-2 text-2xl font-bold text-slate-900">{{ $placeholder }}</div>
                                    </div>
                                </div>
                            @endforeach
                        @endforelse
                    </div>
                </div>

                <div class="section-card reveal-up {{ $newsSection['image'] ? 'content-media-card' : '' }}" style="{{ $contentCardStyle($newsSection['image']) }}">
                    <div class="section-kicker text-xs font-semibold uppercase tracking-[0.32em]">{{ $newsSection['kicker'] }}</div>
                    <h2 class="display-font mt-3 text-3xl font-bold text-slate-950">{{ $newsSection['title'] }}</h2>
                    <div class="mt-6 space-y-4">
                        @forelse ($announcements as $announcement)
                            <article class="rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5">
                                <div class="text-xs uppercase tracking-[0.24em] text-slate-500">{{ $announcement->category }}</div>
                                <h3 class="mt-3 display-font text-xl font-bold text-slate-900">{{ $announcement->title }}</h3>
                                <p class="mt-3 text-sm leading-7 text-slate-600">{{ $announcement->excerpt ?: \Illuminate\Support\Str::limit($announcement->body, 155) }}</p>
                            </article>
                        @empty
                            <div class="rounded-[1.75rem] border border-dashed border-slate-300 px-6 py-8 text-sm text-slate-500">
                                {{ $newsSection['empty_text'] }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto mt-16 max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="immersive-band reveal-up" style="{{ ! empty($schoolSettings['section_background_3']) ? "background-image: url('".asset($schoolSettings['section_background_3'])."')" : 'background-image: linear-gradient(135deg, color-mix(in srgb, var(--theme-primary) 70%, #020617), color-mix(in srgb, var(--theme-highlight) 40%, #020617));' }}">
                <div class="immersive-band-content">
                    <div class="immersive-band-card">
                        <div class="section-kicker text-xs font-semibold uppercase tracking-[0.32em]" style="color: rgba(255, 255, 255, 0.84) !important; text-shadow: 0 3px 14px rgba(2, 6, 23, 0.42);">{{ $ctaSection['kicker'] }}</div>
                        <h2 class="display-font mt-4 text-3xl font-bold sm:text-4xl">{{ $ctaSection['title'] }}</h2>
                        <p class="immersive-band-copy">{{ $ctaSection['text'] }}</p>
                        <div class="mt-6 flex flex-wrap gap-3">
                            <a href="{{ $ctaSection['button_link'] }}" class="theme-button inline-flex">{{ $ctaSection['button_text'] }}</a>
                            <a href="tel:{{ $schoolSettings['school_phone'] ?? '08067046701' }}" class="theme-button-secondary inline-flex">{{ $ctaSection['phone_label'] }}: {{ $schoolSettings['school_phone'] ?? '08067046701' }}</a>
                            <a href="{{ $whatsappLink }}" target="_blank" rel="noopener noreferrer" class="theme-button-secondary inline-flex">WhatsApp Us</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
