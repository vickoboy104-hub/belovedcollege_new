<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Administration</p>
            <h1 class="display-font mt-2 text-3xl font-bold text-slate-950">Website, branding, contact, and payment settings</h1>
        </div>
    </x-slot>

    @php
        $heroHighlightDefaults = [
            1 => 'Founded in 2006',
            2 => 'Located in Ore, Ondo State',
            3 => 'Knowledge, Discipline, and Godly Values',
        ];
        $homepageStatDefaults = [
            1 => ['label' => 'Established', 'value' => '2006'],
            2 => ['label' => 'Location', 'value' => 'Ore'],
            3 => ['label' => 'Academic Levels', 'value' => 'JSS-SS3'],
            4 => ['label' => 'Students', 'value' => 'Live student count'],
        ];
        $featureDefaults = [
            1 => ['title' => 'Experienced and Professional Teachers', 'text' => 'Students learn from committed teachers who combine subject mastery with close guidance.'],
            2 => ['title' => 'Strong Academic Performance', 'text' => 'Academic excellence remains central to classroom instruction and student development.'],
            3 => ['title' => 'Godly and Moral Training', 'text' => 'The school builds character through strong moral discipline and spiritual values.'],
            4 => ['title' => 'Conducive Learning Environment', 'text' => 'Students grow in a focused atmosphere with well-equipped facilities and a student-focused teaching approach.'],
        ];
        $academicCardDefaults = [
            1 => ['title' => 'Junior Secondary School', 'text' => 'JSS 1 - JSS 3'],
            2 => ['title' => 'Senior Secondary School', 'text' => 'SS 1 - SS 3'],
            3 => ['title' => 'Science Department', 'text' => 'Preparing students for science and technical careers.'],
            4 => ['title' => 'Commercial Department', 'text' => 'Supporting students interested in business and commerce.'],
            5 => ['title' => 'Art Department', 'text' => 'Guiding students in humanities, communication, and the arts.'],
            6 => ['title' => 'Student-Focused Teaching', 'text' => 'Each department is designed to prepare students for higher education and future careers.'],
        ];
    @endphp

    @php
        $settingsNavItems = [
            ['key' => 'website-foundation', 'label' => 'Foundation', 'href' => route('admin.settings', ['section' => 'website-foundation'])],
            ['key' => 'homepage-media', 'label' => 'Homepage Media', 'href' => route('admin.settings', ['section' => 'homepage-media'])],
            ['key' => 'workspace-backgrounds', 'label' => 'Workspace BG', 'href' => route('admin.settings', ['section' => 'workspace-backgrounds'])],
            ['key' => 'site-backgrounds', 'label' => 'Site BG', 'href' => route('admin.settings', ['section' => 'site-backgrounds'])],
            ['key' => 'welcome-popup', 'label' => 'Popup', 'href' => route('admin.settings', ['section' => 'welcome-popup'])],
            ['key' => 'gallery-uploader', 'label' => 'Gallery', 'href' => route('admin.settings', ['section' => 'gallery-uploader'])],
            ['key' => 'homepage-text', 'label' => 'Homepage Text', 'href' => route('admin.settings', ['section' => 'homepage-text'])],
            ['key' => 'box-backgrounds-a', 'label' => 'Box BG A', 'href' => route('admin.settings', ['section' => 'box-backgrounds-a'])],
            ['key' => 'box-backgrounds-b', 'label' => 'Box BG B', 'href' => route('admin.settings', ['section' => 'box-backgrounds-b'])],
            ['key' => 'payment-settings', 'label' => 'Payments', 'href' => route('admin.settings', ['section' => 'payment-settings'])],
            ['key' => 'contact-messages', 'label' => 'Messages', 'href' => route('admin.settings', ['section' => 'contact-messages'])],
        ];
    @endphp

    <div x-data="{ activeSection: @js($activeSettingsSection) }" class="grid gap-8">
        <x-section-nav :items="$settingsNavItems" :active="$activeSettingsSection" />

        <section class="section-card" x-show="['website-foundation', 'homepage-media', 'workspace-backgrounds', 'site-backgrounds', 'welcome-popup'].includes(activeSection)">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="display-font text-2xl font-bold text-slate-950">Branding, contact, theme, and homepage content</h2>
                    <p class="mt-2 text-sm text-slate-500">Everything here updates the public website without touching code.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="mt-6 space-y-8">
                @csrf
                <input type="hidden" name="group" value="school">

                <div x-show="activeSection === 'website-foundation'" class="grid gap-4 md:grid-cols-2">
                    <input name="school_name" value="{{ old('school_name', $settings['school_name'] ?? '') }}" placeholder="School name" class="theme-input" />
                    <input name="motto" value="{{ old('motto', $settings['motto'] ?? '') }}" placeholder="School motto" class="theme-input" />
                    <input name="site_tagline" value="{{ old('site_tagline', $settings['site_tagline'] ?? '') }}" placeholder="Website tagline" class="theme-input" />
                    <input name="site_subtitle" value="{{ old('site_subtitle', $settings['site_subtitle'] ?? '') }}" placeholder="Homepage subtitle" class="theme-input" />
                    <input name="school_email" value="{{ old('school_email', $settings['school_email'] ?? '') }}" placeholder="School email" class="theme-input" />
                    <div class="phone-field">
                        <input id="school-phone-settings" name="school_phone" type="tel" inputmode="tel" autocomplete="tel-national" value="{{ old('school_phone', $settings['school_phone'] ?? '') }}" placeholder="School phone" class="theme-input" />
                        <button type="button" class="contact-picker-button" x-data="contactField({ target: 'school-phone-settings' })" x-show="supported" x-cloak @click="pick()">Pick</button>
                    </div>
                    <input name="whatsapp_number" type="tel" inputmode="tel" autocomplete="tel-national" value="{{ old('whatsapp_number', $settings['whatsapp_number'] ?? '08165587119') }}" placeholder="WhatsApp number" class="theme-input" />
                    <input name="whatsapp_link" value="{{ old('whatsapp_link', $settings['whatsapp_link'] ?? '') }}" placeholder="WhatsApp link override (optional)" class="theme-input" />
                    <input name="contact_email_recipient" value="{{ old('contact_email_recipient', $settings['contact_email_recipient'] ?? 'vickoboy104@gmail.com') }}" placeholder="Contact form recipient email" class="theme-input md:col-span-2" />
                    <input name="principal_name" value="{{ old('principal_name', $settings['principal_name'] ?? '') }}" placeholder="Principal name" class="theme-input" />
                    <input name="school_address" value="{{ old('school_address', $settings['school_address'] ?? '') }}" placeholder="School address" class="theme-input" />
                </div>

                <div x-show="activeSection === 'website-foundation'" class="grid gap-4">
                    <textarea name="hero_blurb" rows="3" placeholder="Hero intro text" class="theme-input">{{ old('hero_blurb', $settings['hero_blurb'] ?? '') }}</textarea>
                    <textarea name="portal_notice" rows="3" placeholder="Portal notice shown on login pages" class="theme-input">{{ old('portal_notice', $settings['portal_notice'] ?? '') }}</textarea>
                </div>

                <div x-show="activeSection === 'website-foundation'">
                    <div class="section-kicker text-xs font-semibold uppercase tracking-[0.32em]">Theme controls</div>
                    <div class="mt-4 grid gap-4 md:grid-cols-3">
                        <select name="theme_preset" class="theme-input">
                            @foreach ([
                                'classic-blue' => 'Classic Blue',
                                'navy-gold' => 'Navy Gold',
                                'royal-cyan' => 'Royal Cyan',
                            ] as $value => $label)
                                <option value="{{ $value }}" @selected(old('theme_preset', $settings['theme_preset'] ?? 'classic-blue') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <label class="rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-600">
                            <span class="mb-2 block font-semibold text-slate-900">Primary</span>
                            <input type="color" name="theme_primary" value="{{ old('theme_primary', $settings['theme_primary'] ?? '#174ea6') }}" class="h-11 w-full rounded-xl border-0 bg-transparent p-0">
                        </label>
                        <label class="rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-600">
                            <span class="mb-2 block font-semibold text-slate-900">Secondary</span>
                            <input type="color" name="theme_secondary" value="{{ old('theme_secondary', $settings['theme_secondary'] ?? '#0f3d91') }}" class="h-11 w-full rounded-xl border-0 bg-transparent p-0">
                        </label>
                        <label class="rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-600">
                            <span class="mb-2 block font-semibold text-slate-900">Accent</span>
                            <input type="color" name="theme_accent" value="{{ old('theme_accent', $settings['theme_accent'] ?? '#0f766e') }}" class="h-11 w-full rounded-xl border-0 bg-transparent p-0">
                        </label>
                        <label class="rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-600">
                            <span class="mb-2 block font-semibold text-slate-900">Highlight</span>
                            <input type="color" name="theme_highlight" value="{{ old('theme_highlight', $settings['theme_highlight'] ?? '#f59e0b') }}" class="h-11 w-full rounded-xl border-0 bg-transparent p-0">
                        </label>
                        <label class="rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-600">
                            <span class="mb-2 block font-semibold text-slate-900">Text</span>
                            <input type="color" name="theme_text" value="{{ old('theme_text', $settings['theme_text'] ?? '#0f172a') }}" class="h-11 w-full rounded-xl border-0 bg-transparent p-0">
                        </label>
                        <label class="rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-600">
                            <span class="mb-2 block font-semibold text-slate-900">Top bar</span>
                            <input type="color" name="top_bar_color" value="{{ old('top_bar_color', $settings['top_bar_color'] ?? '#0b2a66') }}" class="h-11 w-full rounded-xl border-0 bg-transparent p-0">
                        </label>
                    </div>
                </div>

                <div x-show="activeSection === 'website-foundation'">
                    <div class="section-kicker text-xs font-semibold uppercase tracking-[0.32em]">Outgoing email delivery</div>
                    <p class="mt-2 text-sm text-slate-500">Set SMTP details here if you want the contact form to send messages to a real email address instead of only saving them in the database.</p>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <select name="mail_mailer" class="theme-input">
                            @foreach (['log' => 'Log only', 'smtp' => 'SMTP'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('mail_mailer', $settings['mail_mailer'] ?? 'log') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <input name="mail_host" value="{{ old('mail_host', $settings['mail_host'] ?? '') }}" placeholder="SMTP host" class="theme-input" />
                        <input name="mail_port" value="{{ old('mail_port', $settings['mail_port'] ?? '587') }}" placeholder="SMTP port" class="theme-input" />
                        <input name="mail_encryption" value="{{ old('mail_encryption', $settings['mail_encryption'] ?? 'tls') }}" placeholder="SMTP encryption (e.g. tls)" class="theme-input" />
                        <input name="mail_username" value="{{ old('mail_username', $settings['mail_username'] ?? '') }}" placeholder="SMTP username" class="theme-input" />
                        <input name="mail_password" type="password" value="{{ old('mail_password', $settings['mail_password'] ?? '') }}" placeholder="SMTP password or app password" class="theme-input" />
                        <input name="mail_from_address" value="{{ old('mail_from_address', $settings['mail_from_address'] ?? $settings['school_email'] ?? '') }}" placeholder="From email address" class="theme-input" />
                        <input name="mail_from_name" value="{{ old('mail_from_name', $settings['mail_from_name'] ?? $settings['school_name'] ?? '') }}" placeholder="From name" class="theme-input" />
                    </div>
                </div>

                <div x-show="activeSection === 'website-foundation'">
                    <div class="section-kicker text-xs font-semibold uppercase tracking-[0.32em]">Logo and icons</div>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <label class="rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-600">
                            <span class="mb-2 block font-semibold text-slate-900">Upload logo</span>
                            <input type="file" name="logo_file" accept="image/*" class="block w-full text-sm">
                        </label>
                        <label class="rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-600">
                            <span class="mb-2 block font-semibold text-slate-900">Upload favicon</span>
                            <input type="file" name="favicon_file" accept="image/*" class="block w-full text-sm">
                        </label>
                    </div>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-sm font-semibold text-slate-900">Current logo</div>
                            <div class="mt-3">
                                <x-application-logo class="h-16 w-16" />
                            </div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-sm font-semibold text-slate-900">Current favicon</div>
                            @if (! empty($settings['favicon_path']))
                                <img src="{{ asset($settings['favicon_path']) }}" alt="Favicon" class="mt-3 h-16 w-16 rounded-2xl border border-slate-200 object-cover">
                            @else
                                <div class="mt-3 text-sm text-slate-500">No favicon uploaded yet.</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div x-show="activeSection === 'homepage-media'">
                    <div class="section-kicker text-xs font-semibold uppercase tracking-[0.32em]">Homepage media stage</div>
                    <div class="mt-4 grid gap-4 lg:grid-cols-[1.15fr,0.85fr]">
                        <div class="rounded-[2rem] border border-slate-200 p-5">
                            <div class="font-semibold text-slate-900">Hero background video loop</div>
                            <p class="mt-2 text-sm leading-7 text-slate-500">Upload one short video. The homepage will play the video first, switch to the slideshow as soon as the video ends, then return to the video and keep repeating.</p>
                            <div class="mt-4 grid gap-4 md:grid-cols-2">
                                <label class="rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-600">
                                    <span class="mb-2 block font-semibold text-slate-900">Hero video</span>
                                    <input type="file" name="hero_background_video" accept=".mp4,.webm,.mov,.m4v,video/mp4,video/webm,video/quicktime" class="block w-full text-sm">
                                </label>
                                <label class="rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-600">
                                    <span class="mb-2 block font-semibold text-slate-900">Video poster image</span>
                                    <input type="file" name="hero_background_video_poster" accept="image/*" class="block w-full text-sm">
                                </label>
                            </div>
                        </div>

                        <div class="rounded-[2rem] border border-slate-200 bg-slate-50 p-5">
                            <div class="font-semibold text-slate-900">Current hero media</div>
                            <div class="mt-4 space-y-4">
                                @if (! empty($settings['hero_background_video']))
                                    <video controls playsinline muted preload="metadata" poster="{{ ! empty($settings['hero_background_video_poster']) ? asset($settings['hero_background_video_poster']) : '' }}" class="h-52 w-full rounded-3xl object-cover">
                                        <source src="{{ asset($settings['hero_background_video']) }}">
                                    </video>
                                @else
                                    <div class="rounded-3xl border border-dashed border-slate-300 px-4 py-10 text-sm text-slate-500">No hero video uploaded yet.</div>
                                @endif

                                @if (! empty($settings['hero_background_video_poster']))
                                    <img src="{{ asset($settings['hero_background_video_poster']) }}" alt="Hero poster" class="h-32 w-full rounded-3xl object-cover">
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 lg:grid-cols-[0.9fr,1.1fr]">
                        <div
                            x-data="{ heroIntroOpacity: {{ (int) old('hero_intro_background_opacity', $settings['hero_intro_background_opacity'] ?? 12) }} }"
                            class="rounded-[2rem] border border-slate-200 p-5 text-sm text-slate-600"
                        >
                            <div class="mb-2 font-semibold text-slate-900">Hero intro box background</div>
                            <input type="file" name="hero_intro_background_image" accept="image/*" class="block w-full text-sm">
                            <p class="mt-3 text-xs text-slate-500">This controls the background of the first homepage card that contains the main headline, school name, intro text, and the Apply Now / Contact Us buttons.</p>

                            <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="font-semibold text-slate-900">Overlay opacity</span>
                                    <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700 shadow-sm" x-text="heroIntroOpacity + '%'"></span>
                                </div>
                                <input
                                    type="range"
                                    name="hero_intro_background_opacity"
                                    min="0"
                                    max="100"
                                    step="1"
                                    x-model="heroIntroOpacity"
                                    class="mt-4 h-2 w-full cursor-pointer accent-[var(--theme-primary)]"
                                >
                                <p class="mt-3 text-xs text-slate-500">Lower values show more of the image. Higher values add a darker overlay to improve text contrast.</p>
                            </div>
                        </div>

                        <div class="rounded-[2rem] border border-slate-200 bg-slate-50 p-5">
                            <div class="font-semibold text-slate-900">Current hero intro box background</div>
                            @if (! empty($settings['hero_intro_background_image']))
                                <img src="{{ asset($settings['hero_intro_background_image']) }}" alt="Hero intro background" class="mt-4 h-48 w-full rounded-3xl object-cover">
                            @else
                                <div class="mt-4 rounded-3xl border border-dashed border-slate-300 px-4 py-10 text-sm text-slate-500">No custom hero intro background uploaded yet.</div>
                            @endif
                            <div class="mt-4 text-xs text-slate-500">
                                Current overlay opacity:
                                <span class="font-semibold text-slate-700">{{ (int) ($settings['hero_intro_background_opacity'] ?? 12) }}%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div x-show="activeSection === 'homepage-media'">
                    <div class="section-kicker text-xs font-semibold uppercase tracking-[0.32em]">Homepage slides and gallery</div>
                    <div class="mt-4 grid gap-6 lg:grid-cols-3">
                        @foreach ([1, 2, 3, 4] as $index)
                            <div class="rounded-[2rem] border border-slate-200 p-5">
                                <div class="font-semibold text-slate-900">Hero slide {{ $index }}</div>
                                <div class="mt-4 space-y-4">
                                    <input name="hero_slide_{{ $index }}_title" value="{{ old("hero_slide_{$index}_title", $settings["hero_slide_{$index}_title"] ?? '') }}" placeholder="Slide title" class="theme-input w-full" />
                                    <textarea name="hero_slide_{{ $index }}_text" rows="3" placeholder="Slide text" class="theme-input w-full">{{ old("hero_slide_{$index}_text", $settings["hero_slide_{$index}_text"] ?? '') }}</textarea>
                                    <input type="file" name="hero_slide_{{ $index }}_image" accept="image/*" class="block w-full text-sm">
                                    @if (! empty($settings["hero_slide_{$index}_image"]))
                                        <img src="{{ asset($settings["hero_slide_{$index}_image"]) }}" alt="Slide {{ $index }}" class="h-40 w-full rounded-2xl object-cover">
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-4">
                        @foreach ([1, 2, 3, 4] as $index)
                            <label class="rounded-[2rem] border border-slate-200 p-4 text-sm text-slate-600">
                                <span class="mb-2 block font-semibold text-slate-900">Gallery image {{ $index }}</span>
                                <input type="file" name="gallery_image_{{ $index }}" accept="image/*" class="block w-full text-sm">
                                @if (! empty($settings["gallery_image_{$index}"]))
                                    <img src="{{ asset($settings["gallery_image_{$index}"]) }}" alt="Gallery {{ $index }}" class="mt-3 h-36 w-full rounded-2xl object-cover">
                                @endif
                            </label>
                        @endforeach
                    </div>
                </div>

                <div x-show="activeSection === 'workspace-backgrounds'">
                    <div class="section-kicker text-xs font-semibold uppercase tracking-[0.32em]">Admin workspace background</div>
                    <p class="mt-2 text-sm text-slate-500">Upload one shared background image for the dashboard, settings, people hub, student pages, staff pages, and internal profile screens.</p>
                    <div class="mt-4 grid gap-4 lg:grid-cols-[0.9fr,1.1fr]">
                        <label class="rounded-[2rem] border border-slate-200 p-4 text-sm text-slate-600">
                            <span class="mb-2 block font-semibold text-slate-900">Admin background image</span>
                            <input type="file" name="admin_background_image" accept="image/*" class="block w-full text-sm">
                            <p class="mt-3 text-xs text-slate-500">Best used with a wide campus image, building photo, or a calm branded backdrop for the full internal workspace.</p>
                        </label>
                        <div class="rounded-[2rem] border border-slate-200 p-4 text-sm text-slate-600">
                            <span class="mb-2 block font-semibold text-slate-900">Current admin background</span>
                            @if (! empty($settings['admin_background_image']))
                                <img src="{{ asset($settings['admin_background_image']) }}" alt="Admin background" class="mt-3 h-48 w-full rounded-2xl object-cover">
                            @else
                                <div class="mt-3 rounded-2xl border border-dashed border-slate-300 px-4 py-10 text-xs text-slate-500">No dedicated admin background uploaded yet. The admin area currently falls back to a clean soft gradient.</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div x-show="activeSection === 'site-backgrounds'">
                    <div class="section-kicker text-xs font-semibold uppercase tracking-[0.32em]">Main website background</div>
                    <p class="mt-2 text-sm text-slate-500">Upload up to three large background images. The site will show the first one near the top, switch as visitors scroll down, then continue with the third image lower on the page.</p>
                    <div class="mt-4 grid gap-4 lg:grid-cols-3">
                        @foreach ([1, 2, 3] as $index)
                            <label class="rounded-[2rem] border border-slate-200 p-4 text-sm text-slate-600">
                                <span class="mb-2 block font-semibold text-slate-900">Site background {{ $index }}</span>
                                <input type="file" name="site_background_{{ $index }}" accept="image/*" class="block w-full text-sm">
                                <div class="mt-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="font-semibold text-slate-900">Image overlay opacity</span>
                                        <span class="text-xs font-semibold text-slate-500">{{ (int) old("site_background_{$index}_opacity", $settings["site_background_{$index}_opacity"] ?? 78) }}%</span>
                                    </div>
                                    <input type="range" name="site_background_{{ $index }}_opacity" min="0" max="100" step="1" value="{{ (int) old("site_background_{$index}_opacity", $settings["site_background_{$index}_opacity"] ?? 78) }}" class="mt-3 h-2 w-full cursor-pointer accent-[var(--theme-primary)]">
                                </div>
                                @if (! empty($settings["site_background_{$index}"]))
                                    <img src="{{ asset($settings["site_background_{$index}"]) }}" alt="Site background {{ $index }}" class="mt-3 h-40 w-full rounded-2xl object-cover">
                                @else
                                    <div class="mt-3 rounded-2xl border border-dashed border-slate-300 px-4 py-8 text-xs text-slate-500">Upload a large campus or lifestyle image with enough open space for the page content to sit clearly on top.</div>
                                @endif
                            </label>
                        @endforeach
                    </div>
                </div>

                <div x-show="activeSection === 'site-backgrounds'">
                    <div class="section-kicker text-xs font-semibold uppercase tracking-[0.32em]">Scrolling background sections</div>
                    <p class="mt-2 text-sm text-slate-500">These images power the full-width background bands lower on the homepage.</p>
                    <div class="mt-4 grid gap-4 lg:grid-cols-3">
                        @foreach ([1, 2, 3] as $index)
                            <label class="rounded-[2rem] border border-slate-200 p-4 text-sm text-slate-600">
                                <span class="mb-2 block font-semibold text-slate-900">Section background {{ $index }}</span>
                                <input type="file" name="section_background_{{ $index }}" accept="image/*" class="block w-full text-sm">
                                @if (! empty($settings["section_background_{$index}"]))
                                    <img src="{{ asset($settings["section_background_{$index}"]) }}" alt="Section background {{ $index }}" class="mt-3 h-40 w-full rounded-2xl object-cover">
                                @else
                                    <div class="mt-3 rounded-2xl border border-dashed border-slate-300 px-4 py-8 text-xs text-slate-500">Upload a wide lifestyle, campus, event, or classroom image.</div>
                                @endif
                            </label>
                        @endforeach
                    </div>
                </div>

                <div x-show="activeSection === 'welcome-popup'">
                    <div class="section-kicker text-xs font-semibold uppercase tracking-[0.32em]">Welcome popup</div>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <label class="flex items-center gap-3 rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-600 md:col-span-2">
                            <input type="checkbox" name="welcome_popup_enabled" value="1" class="rounded border-slate-300" @checked(old('welcome_popup_enabled', $settings['welcome_popup_enabled'] ?? false))>
                            Enable welcome popup on homepage
                        </label>
                        <input name="welcome_popup_title" value="{{ old('welcome_popup_title', $settings['welcome_popup_title'] ?? '') }}" placeholder="Popup title" class="theme-input" />
                        <input name="welcome_popup_button_text" value="{{ old('welcome_popup_button_text', $settings['welcome_popup_button_text'] ?? '') }}" placeholder="Popup button text" class="theme-input" />
                        <input name="welcome_popup_button_link" value="{{ old('welcome_popup_button_link', $settings['welcome_popup_button_link'] ?? route('admissions')) }}" placeholder="Popup button link" class="theme-input md:col-span-2" />
                        <textarea name="welcome_popup_text" rows="3" placeholder="Popup text" class="theme-input md:col-span-2">{{ old('welcome_popup_text', $settings['welcome_popup_text'] ?? '') }}</textarea>
                        <label class="rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-600 md:col-span-2">
                            <span class="mb-2 block font-semibold text-slate-900">Popup image</span>
                            <input type="file" name="welcome_popup_image" accept="image/*" class="block w-full text-sm">
                            @if (! empty($settings['welcome_popup_image']))
                                <img src="{{ asset($settings['welcome_popup_image']) }}" alt="Popup image" class="mt-3 h-44 rounded-2xl object-cover">
                            @endif
                        </label>
                    </div>
                </div>

                <button
                    x-show="['website-foundation', 'homepage-media', 'workspace-backgrounds', 'site-backgrounds', 'welcome-popup'].includes(activeSection)"
                    type="submit"
                    class="theme-button"
                    style="position: fixed; right: 1.5rem; bottom: 1.5rem; z-index: 80; box-shadow: 0 22px 48px rgba(15, 42, 102, 0.28);"
                >
                    Save website settings
                </button>
            </form>
        </section>

        <section class="section-card" x-show="activeSection === 'gallery-uploader'">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="display-font text-2xl font-bold text-slate-950">Gallery quick uploader</h2>
                    <p class="mt-2 text-sm text-slate-500">Use this smaller uploader if gallery images fail inside the large website settings form.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
                @csrf
                <input type="hidden" name="group" value="school">

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    @foreach ([1, 2, 3, 4] as $index)
                        <label class="rounded-[2rem] border border-slate-200 p-4 text-sm text-slate-600">
                            <span class="mb-2 block font-semibold text-slate-900">Gallery image {{ $index }}</span>
                            <input type="file" name="gallery_image_{{ $index }}" accept="image/*" class="block w-full text-sm">
                            @if (! empty($settings["gallery_image_{$index}"]))
                                <img src="{{ asset($settings["gallery_image_{$index}"]) }}" alt="Gallery {{ $index }}" class="mt-3 h-36 w-full rounded-2xl object-cover">
                            @else
                                <div class="mt-3 rounded-2xl border border-dashed border-slate-300 px-4 py-8 text-xs text-slate-500">No gallery image uploaded yet.</div>
                            @endif
                        </label>
                    @endforeach
                </div>

                <button type="submit" class="theme-button">Save gallery images</button>
            </form>
        </section>

        <section class="section-card" x-show="activeSection === 'homepage-text'">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="display-font text-2xl font-bold text-slate-950">Homepage text control</h2>
                    <p class="mt-2 text-sm text-slate-500">This controls the copy inside the homepage boxes and major sections.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.settings.update') }}" class="mt-6 space-y-8">
                @csrf
                <input type="hidden" name="group" value="school">

                <div class="rounded-[2rem] border border-slate-200 p-5">
                    <div class="font-semibold text-slate-900">Hero highlight boxes</div>
                    <div class="mt-4 grid gap-4 md:grid-cols-3">
                        @foreach ($heroHighlightDefaults as $index => $default)
                            <input name="hero_highlight_{{ $index }}_text" value="{{ old("hero_highlight_{$index}_text", $settings["hero_highlight_{$index}_text"] ?? $default) }}" placeholder="Highlight {{ $index }} text" class="theme-input" />
                        @endforeach
                    </div>
                </div>

                <div class="rounded-[2rem] border border-slate-200 p-5">
                    <div class="font-semibold text-slate-900">Homepage stat boxes</div>
                    <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        @foreach ($homepageStatDefaults as $index => $default)
                            <div class="space-y-3 rounded-[1.5rem] border border-slate-200 p-4">
                                <input name="homepage_stat_{{ $index }}_label" value="{{ old("homepage_stat_{$index}_label", $settings["homepage_stat_{$index}_label"] ?? $default['label']) }}" placeholder="Stat label" class="theme-input w-full" />
                                <input name="homepage_stat_{{ $index }}_value" value="{{ old("homepage_stat_{$index}_value", $settings["homepage_stat_{$index}_value"] ?? $default['value']) }}" placeholder="Stat value" class="theme-input w-full" />
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-[2rem] border border-slate-200 p-5">
                    <div class="font-semibold text-slate-900">Quick intro</div>
                    <div class="mt-4 grid gap-4">
                        <input name="quick_intro_kicker" value="{{ old('quick_intro_kicker', $settings['quick_intro_kicker'] ?? 'Quick Intro') }}" placeholder="Quick intro kicker" class="theme-input" />
                        <input name="quick_intro_title" value="{{ old('quick_intro_title', $settings['quick_intro_title'] ?? 'A real school environment for growth and purpose.') }}" placeholder="Quick intro title" class="theme-input" />
                        <textarea name="quick_intro_text_1" rows="3" class="theme-input" placeholder="Quick intro paragraph 1">{{ old('quick_intro_text_1', $settings['quick_intro_text_1'] ?? 'Founded in 2006 and located in Ore, Ondo State, BELOVED SCHOOLS is committed to raising educated, disciplined, and God-fearing students.') }}</textarea>
                        <textarea name="quick_intro_text_2" rows="3" class="theme-input" placeholder="Quick intro paragraph 2">{{ old('quick_intro_text_2', $settings['quick_intro_text_2'] ?? 'With professional teachers, modern learning facilities, and a strong moral foundation, we prepare students for success in both academics and life.') }}</textarea>
                    </div>
                </div>

                <div class="rounded-[2rem] border border-slate-200 p-5">
                    <div class="font-semibold text-slate-900">Why parents choose us band</div>
                    <div class="mt-4 grid gap-4">
                        <input name="why_choose_kicker" value="{{ old('why_choose_kicker', $settings['why_choose_kicker'] ?? 'Why Parents Choose BELOVED SCHOOLS') }}" placeholder="Band kicker" class="theme-input" />
                        <input name="why_choose_title" value="{{ old('why_choose_title', $settings['why_choose_title'] ?? 'A disciplined school environment built for excellence.') }}" placeholder="Band title" class="theme-input" />
                        <textarea name="why_choose_text" rows="3" class="theme-input" placeholder="Band text">{{ old('why_choose_text', $settings['why_choose_text'] ?? 'BELOVED SCHOOLS combines academic excellence with moral discipline to help students become responsible and purposeful leaders.') }}</textarea>
                        <div class="grid gap-4 md:grid-cols-2">
                            <input name="why_choose_button_text" value="{{ old('why_choose_button_text', $settings['why_choose_button_text'] ?? 'Read More') }}" placeholder="Button text" class="theme-input" />
                            <input name="why_choose_button_link" value="{{ old('why_choose_button_link', $settings['why_choose_button_link'] ?? route('about')) }}" placeholder="Button link" class="theme-input" />
                        </div>
                    </div>
                </div>

                <div class="rounded-[2rem] border border-slate-200 p-5">
                    <div class="font-semibold text-slate-900">Feature cards</div>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        @foreach ($featureDefaults as $index => $default)
                            <div class="space-y-3 rounded-[1.5rem] border border-slate-200 p-4">
                                <input name="home_feature_{{ $index }}_title" value="{{ old("home_feature_{$index}_title", $settings["home_feature_{$index}_title"] ?? $default['title']) }}" placeholder="Feature title" class="theme-input w-full" />
                                <textarea name="home_feature_{{ $index }}_text" rows="3" class="theme-input w-full" placeholder="Feature text">{{ old("home_feature_{$index}_text", $settings["home_feature_{$index}_text"] ?? $default['text']) }}</textarea>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-[2rem] border border-slate-200 p-5">
                    <div class="font-semibold text-slate-900">Academic section</div>
                    <div class="mt-4 space-y-4">
                        <input name="academic_section_kicker" value="{{ old('academic_section_kicker', $settings['academic_section_kicker'] ?? 'Our Academic Program') }}" placeholder="Academic section kicker" class="theme-input" />
                        <input name="academic_section_title" value="{{ old('academic_section_title', $settings['academic_section_title'] ?? 'A full secondary school structure with clear pathways.') }}" placeholder="Academic section title" class="theme-input" />
                        <div class="grid gap-4 md:grid-cols-2">
                            @foreach ($academicCardDefaults as $index => $default)
                                <div class="space-y-3 rounded-[1.5rem] border border-slate-200 p-4">
                                    <input name="academic_card_{{ $index }}_title" value="{{ old("academic_card_{$index}_title", $settings["academic_card_{$index}_title"] ?? $default['title']) }}" placeholder="Academic card title" class="theme-input w-full" />
                                    <textarea name="academic_card_{{ $index }}_text" rows="3" class="theme-input w-full" placeholder="Academic card text">{{ old("academic_card_{$index}_text", $settings["academic_card_{$index}_text"] ?? $default['text']) }}</textarea>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 xl:grid-cols-2">
                    <div class="rounded-[2rem] border border-slate-200 p-5">
                        <div class="font-semibold text-slate-900">Founders box</div>
                        <div class="mt-4 space-y-4">
                            <input name="founders_kicker" value="{{ old('founders_kicker', $settings['founders_kicker'] ?? 'Meet the Founders') }}" placeholder="Founders kicker" class="theme-input" />
                            <input name="founders_title" value="{{ old('founders_title', $settings['founders_title'] ?? 'A school vision built on education and youth development.') }}" placeholder="Founders title" class="theme-input" />
                            <textarea name="founders_text_1" rows="3" class="theme-input" placeholder="Founders paragraph 1">{{ old('founders_text_1', $settings['founders_text_1'] ?? 'BELOVED SCHOOLS was founded by Mr. Zebilon K. S. alongside his wife Mrs. Grace Zebilon, whose passion for education and youth development led to the establishment of the school.') }}</textarea>
                            <textarea name="founders_text_2" rows="3" class="theme-input" placeholder="Founders paragraph 2">{{ old('founders_text_2', $settings['founders_text_2'] ?? 'Their vision was to create a learning environment where students can grow academically while being rooted in strong moral and spiritual values.') }}</textarea>
                            <textarea name="founders_values_text" rows="3" class="theme-input" placeholder="Founders details / values">{{ old('founders_values_text', $settings['founders_values_text'] ?? 'Core Values: Discipline, Excellence, Integrity, Godliness, Responsibility') }}</textarea>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-slate-200 p-5">
                        <div class="font-semibold text-slate-900">Gallery and news boxes</div>
                        <div class="mt-4 space-y-4">
                            <input name="gallery_section_kicker" value="{{ old('gallery_section_kicker', $settings['gallery_section_kicker'] ?? 'Life at BELOVED SCHOOLS') }}" placeholder="Gallery kicker" class="theme-input" />
                            <input name="gallery_section_title" value="{{ old('gallery_section_title', $settings['gallery_section_title'] ?? 'Life at BELOVED SCHOOLS') }}" placeholder="Gallery title" class="theme-input" />
                            <textarea name="gallery_section_text" rows="3" class="theme-input" placeholder="Gallery text">{{ old('gallery_section_text', $settings['gallery_section_text'] ?? 'Explore moments from our classrooms, events, and student activities that reflect our commitment to excellence and holistic development.') }}</textarea>
                            <input name="news_section_kicker" value="{{ old('news_section_kicker', $settings['news_section_kicker'] ?? 'Latest news') }}" placeholder="News kicker" class="theme-input" />
                            <input name="news_section_title" value="{{ old('news_section_title', $settings['news_section_title'] ?? 'Announcements and updates') }}" placeholder="News title" class="theme-input" />
                            <textarea name="news_section_empty_text" rows="3" class="theme-input" placeholder="News empty state text">{{ old('news_section_empty_text', $settings['news_section_empty_text'] ?? 'School announcements will appear here as BELOVED SCHOOLS shares updates with parents and students.') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="rounded-[2rem] border border-slate-200 p-5">
                    <div class="font-semibold text-slate-900">Bottom call to action band</div>
                    <div class="mt-4 space-y-4">
                        <input name="cta_kicker" value="{{ old('cta_kicker', $settings['cta_kicker'] ?? 'Call to action') }}" placeholder="CTA kicker" class="theme-input" />
                        <input name="cta_title" value="{{ old('cta_title', $settings['cta_title'] ?? 'Give your child the foundation for a successful future.') }}" placeholder="CTA title" class="theme-input" />
                        <textarea name="cta_text" rows="3" class="theme-input" placeholder="CTA text">{{ old('cta_text', $settings['cta_text'] ?? 'BELOVED SCHOOLS combines knowledge, discipline, integrity, responsibility, and Godliness to prepare students for meaningful impact.') }}</textarea>
                        <div class="grid gap-4 md:grid-cols-3">
                            <input name="cta_button_text" value="{{ old('cta_button_text', $settings['cta_button_text'] ?? 'Enroll Today') }}" placeholder="Primary button text" class="theme-input" />
                            <input name="cta_button_link" value="{{ old('cta_button_link', $settings['cta_button_link'] ?? route('admissions')) }}" placeholder="Primary button link" class="theme-input" />
                            <input name="cta_phone_label" value="{{ old('cta_phone_label', $settings['cta_phone_label'] ?? 'Call Us') }}" placeholder="Phone button label" class="theme-input" />
                        </div>
                        <p class="text-xs text-slate-500">Use the existing Section background 1 and Section background 3 upload slots above for the “Why Parents Choose” band and bottom CTA band backgrounds.</p>
                    </div>
                </div>

                <button type="submit" class="theme-button">Save homepage text</button>
            </form>
        </section>

        <section class="section-card" x-show="activeSection === 'box-backgrounds-a'">
            <div>
                <h2 class="display-font text-2xl font-bold text-slate-950">Homepage box backgrounds A</h2>
                <p class="mt-2 text-sm text-slate-500">This form covers the top hero boxes, stat tiles, quick intro, and feature cards.</p>
            </div>

            <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="mt-6 space-y-8">
                @csrf
                <input type="hidden" name="group" value="school">

                <div class="grid gap-4 md:grid-cols-3">
                    @foreach ($heroHighlightDefaults as $index => $default)
                        <label class="rounded-[2rem] border border-slate-200 p-4 text-sm text-slate-600">
                            <span class="mb-2 block font-semibold text-slate-900">Hero highlight {{ $index }} background</span>
                            <input type="file" name="hero_highlight_{{ $index }}_background" accept="image/*" class="block w-full text-sm">
                            @if (! empty($settings["hero_highlight_{$index}_background"]))
                                <img src="{{ asset($settings["hero_highlight_{$index}_background"]) }}" alt="Hero highlight {{ $index }} background" class="mt-3 h-32 w-full rounded-2xl object-cover">
                            @endif
                        </label>
                    @endforeach
                </div>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    @foreach ($homepageStatDefaults as $index => $default)
                        <label class="rounded-[2rem] border border-slate-200 p-4 text-sm text-slate-600">
                            <span class="mb-2 block font-semibold text-slate-900">Stat box {{ $index }} background</span>
                            <input type="file" name="homepage_stat_{{ $index }}_background" accept="image/*" class="block w-full text-sm">
                            @if (! empty($settings["homepage_stat_{$index}_background"]))
                                <img src="{{ asset($settings["homepage_stat_{$index}_background"]) }}" alt="Stat box {{ $index }} background" class="mt-3 h-32 w-full rounded-2xl object-cover">
                            @endif
                        </label>
                    @endforeach
                </div>

                <div class="grid gap-4 lg:grid-cols-[0.7fr,1.3fr]">
                    <label class="rounded-[2rem] border border-slate-200 p-4 text-sm text-slate-600">
                        <span class="mb-2 block font-semibold text-slate-900">Quick intro box background</span>
                        <input type="file" name="quick_intro_background_image" accept="image/*" class="block w-full text-sm">
                        @if (! empty($settings['quick_intro_background_image']))
                            <img src="{{ asset($settings['quick_intro_background_image']) }}" alt="Quick intro background" class="mt-3 h-40 w-full rounded-2xl object-cover">
                        @endif
                    </label>

                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach ($featureDefaults as $index => $default)
                            <label class="rounded-[2rem] border border-slate-200 p-4 text-sm text-slate-600">
                                <span class="mb-2 block font-semibold text-slate-900">Feature card {{ $index }} background</span>
                                <input type="file" name="home_feature_{{ $index }}_background" accept="image/*" class="block w-full text-sm">
                                @if (! empty($settings["home_feature_{$index}_background"]))
                                    <img src="{{ asset($settings["home_feature_{$index}_background"]) }}" alt="Feature card {{ $index }} background" class="mt-3 h-32 w-full rounded-2xl object-cover">
                                @endif
                            </label>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="theme-button">Save box backgrounds A</button>
            </form>
        </section>

        <section class="section-card" x-show="activeSection === 'box-backgrounds-b'">
            <div>
                <h2 class="display-font text-2xl font-bold text-slate-950">Homepage box backgrounds B</h2>
                <p class="mt-2 text-sm text-slate-500">This form covers the academic area, founders box, gallery box, and latest news box.</p>
            </div>

            <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="mt-6 space-y-8">
                @csrf
                <input type="hidden" name="group" value="school">

                <div class="grid gap-4 lg:grid-cols-[0.7fr,1.3fr]">
                    <label class="rounded-[2rem] border border-slate-200 p-4 text-sm text-slate-600">
                        <span class="mb-2 block font-semibold text-slate-900">Academic section main box background</span>
                        <input type="file" name="academic_section_background_image" accept="image/*" class="block w-full text-sm">
                        @if (! empty($settings['academic_section_background_image']))
                            <img src="{{ asset($settings['academic_section_background_image']) }}" alt="Academic section background" class="mt-3 h-40 w-full rounded-2xl object-cover">
                        @endif
                    </label>

                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($academicCardDefaults as $index => $default)
                            <label class="rounded-[2rem] border border-slate-200 p-4 text-sm text-slate-600">
                                <span class="mb-2 block font-semibold text-slate-900">Academic card {{ $index }} background</span>
                                <input type="file" name="academic_card_{{ $index }}_background" accept="image/*" class="block w-full text-sm">
                                @if (! empty($settings["academic_card_{$index}_background"]))
                                    <img src="{{ asset($settings["academic_card_{$index}_background"]) }}" alt="Academic card {{ $index }} background" class="mt-3 h-32 w-full rounded-2xl object-cover">
                                @endif
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    @foreach ([
                        'founders_background_image' => 'Founders box background',
                        'gallery_section_background_image' => 'Gallery box background',
                        'news_section_background_image' => 'Latest news box background',
                    ] as $field => $label)
                        <label class="rounded-[2rem] border border-slate-200 p-4 text-sm text-slate-600">
                            <span class="mb-2 block font-semibold text-slate-900">{{ $label }}</span>
                            <input type="file" name="{{ $field }}" accept="image/*" class="block w-full text-sm">
                            @if (! empty($settings[$field]))
                                <img src="{{ asset($settings[$field]) }}" alt="{{ $label }}" class="mt-3 h-40 w-full rounded-2xl object-cover">
                            @endif
                        </label>
                    @endforeach
                </div>

                <button type="submit" class="theme-button">Save box backgrounds B</button>
            </form>
        </section>

        <div class="grid gap-8 xl:grid-cols-[1fr,0.9fr]" x-show="['payment-settings', 'contact-messages'].includes(activeSection)">
            <section class="section-card" x-show="activeSection === 'payment-settings'">
                <h2 class="display-font text-2xl font-bold text-slate-950">Payment gateway setup</h2>
                <p class="mt-2 text-sm text-slate-500">Add or update your Paystack and PalmPay credentials here. The student portal and finance desk will use these settings.</p>

                <form method="POST" action="{{ route('admin.settings.update') }}" class="mt-6 space-y-6">
                    @csrf
                    <input type="hidden" name="group" value="payments">

                    <div class="rounded-[2rem] border border-slate-200 p-5">
                        <div class="font-semibold text-slate-900">Paystack</div>
                        <div class="mt-4 grid gap-4">
                            <input name="paystack_public_key" value="{{ old('paystack_public_key', $settings['paystack_public_key'] ?? '') }}" placeholder="Public key" class="theme-input" />
                            <input name="paystack_secret_key" value="{{ old('paystack_secret_key', $settings['paystack_secret_key'] ?? '') }}" placeholder="Secret key" class="theme-input" />
                            <input name="paystack_webhook_secret" value="{{ old('paystack_webhook_secret', $settings['paystack_webhook_secret'] ?? '') }}" placeholder="Webhook secret" class="theme-input" />
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-slate-200 p-5">
                        <div class="font-semibold text-slate-900">PalmPay</div>
                        <div class="mt-4 grid gap-4">
                            <input name="palmpay_merchant_id" value="{{ old('palmpay_merchant_id', $settings['palmpay_merchant_id'] ?? '') }}" placeholder="Merchant ID" class="theme-input" />
                            <input name="palmpay_app_id" value="{{ old('palmpay_app_id', $settings['palmpay_app_id'] ?? '') }}" placeholder="App ID" class="theme-input" />
                            <input name="palmpay_checkout_url" value="{{ old('palmpay_checkout_url', $settings['palmpay_checkout_url'] ?? '') }}" placeholder="Checkout URL or virtual account endpoint" class="theme-input" />
                            <textarea name="palmpay_public_key" rows="3" placeholder="Public key" class="theme-input">{{ old('palmpay_public_key', $settings['palmpay_public_key'] ?? '') }}</textarea>
                            <textarea name="palmpay_private_key" rows="3" placeholder="Private key" class="theme-input">{{ old('palmpay_private_key', $settings['palmpay_private_key'] ?? '') }}</textarea>
                            <input name="palmpay_webhook_secret" value="{{ old('palmpay_webhook_secret', $settings['palmpay_webhook_secret'] ?? '') }}" placeholder="Webhook secret" class="theme-input" />
                        </div>
                    </div>

                    <button type="submit" class="theme-button">Save payment settings</button>
                </form>
            </section>

            <section class="section-card" x-show="activeSection === 'contact-messages'">
                <h2 class="display-font text-2xl font-bold text-slate-950">Recent contact messages</h2>
                <div class="mt-5 space-y-4">
                    @forelse ($messages as $message)
                        <article class="rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div class="font-semibold text-slate-900">{{ $message->name }}</div>
                                <div class="text-xs uppercase tracking-[0.22em] text-slate-500">{{ $message->created_at->format('d M Y, h:i A') }}</div>
                            </div>
                            <div class="mt-2 text-sm text-slate-500">{{ $message->email }} @if($message->phone) | {{ $message->phone }} @endif</div>
                            <div class="mt-3 font-medium text-slate-900">{{ $message->subject }}</div>
                            <p class="mt-2 text-sm leading-7 text-slate-600">{{ $message->message }}</p>
                        </article>
                    @empty
                        <div class="rounded-[1.75rem] border border-dashed border-slate-300 px-6 py-8 text-sm text-slate-500">
                            No contact messages yet.
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
