<x-portal-layout>
    <x-slot name="header">
        <x-page-header
            title="School Settings"
            eyebrow="Administration"
            description="Manage your school information, website portal, theme customizer, landing builder, media sliders, payment gateways, and view user contact messages."
        />
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
        $themePresetOptions = [
            'light-corporate'       => 'Light Corporate (Clean & Professional)',
            'dark-corporate'        => 'Dark Corporate (Dark Mode)',
            'colourful-professional' => 'Colourful Professional (Vibrant Dashboard)',
            'custom'                => 'Custom Theme (Manual colors)',
        ];
        $legacyPresetMap = [
            'classic-blue'   => 'light-corporate',
            'midnight-blue'  => 'light-corporate',
            'stylish-green'  => 'light-corporate',
            'ruby-red'       => 'light-corporate',
            'royal-purple'   => 'light-corporate',
            'sunset-orange'  => 'light-corporate',
            'midnight-cyber' => 'dark-corporate',
            'clean-corporate'=> 'light-corporate',
            'emerald-gold'   => 'light-corporate',
            'ruby-gold'      => 'light-corporate',
            'royal-violet'   => 'light-corporate',
        ];
        $currentThemePreset = old('theme_preset', $settings['theme_preset'] ?? 'light-corporate');
        $currentThemePreset = $legacyPresetMap[$currentThemePreset] ?? $currentThemePreset;
        $currentThemePreset = array_key_exists($currentThemePreset, $themePresetOptions) ? $currentThemePreset : 'light-corporate';
        $themeSwatches = [
            'light-corporate'        => ['#2563EB', '#0B1F3A', '#FBBF24'],
            'dark-corporate'         => ['#60A5FA', '#020617', '#FACC15'],
            'colourful-professional' => ['#2563EB', '#111827', '#7C3AED'],
            'custom' => [
                $settings['theme_primary'] ?? '#2563EB',
                $settings['theme_secondary'] ?? '#0F172A',
                $settings['theme_accent'] ?? '#FBBF24',
            ],
        ];
        $builderSchoolName = $settings['school_name'] ?? 'BELOVED SCHOOLS';
        $landingSlideDefaults = [
            1 => ['eyebrow' => "Welcome to {$builderSchoolName} - Est. 2006", 'title' => 'Where Knowledge Shapes', 'emphasis' => 'Future Leaders', 'text' => $settings['hero_blurb'] ?? 'A disciplined school environment where academic excellence, character, and Godly values work together.', 'primary' => 'Apply Now', 'primary_link' => route('admissions'), 'secondary' => 'Explore Our School', 'secondary_link' => route('about'), 'label' => 'Welcome'],
            2 => ['eyebrow' => 'Academic Excellence', 'title' => 'JSS to SS3 With', 'emphasis' => 'Clear Pathways', 'text' => 'Junior and senior secondary students learn through structured classes, focused departments, and close teacher guidance.', 'primary' => 'Our Program', 'primary_link' => route('admissions'), 'secondary' => 'Contact Office', 'secondary_link' => route('contact'), 'label' => 'Academics'],
            3 => ['eyebrow' => 'Character and Discipline', 'title' => 'Strong Values for', 'emphasis' => 'Purposeful Living', 'text' => 'Students are trained to combine knowledge with discipline, integrity, responsibility, and spiritual growth.', 'primary' => 'Why Parents Choose Us', 'primary_link' => route('about'), 'secondary' => 'Student Login', 'secondary_link' => route('student.login'), 'label' => 'Values'],
            4 => ['eyebrow' => 'Admissions Open', 'title' => 'Give Your Child', 'emphasis' => 'A Strong Foundation', 'text' => 'Speak with the school office about available classes, requirements, and the next admission step.', 'primary' => 'Start Admission', 'primary_link' => route('admissions'), 'secondary' => 'Call School', 'secondary_link' => 'tel:'.($settings['school_phone'] ?? '08067046701'), 'label' => 'Admissions'],
            5 => ['eyebrow' => 'Digital School Portal', 'title' => 'A Connected Campus', 'emphasis' => 'For Staff and Students', 'text' => 'The portal supports assignments, CBT, reports, finance records, and day-to-day school administration.', 'primary' => 'Staff Login', 'primary_link' => route('staff.login'), 'secondary' => 'Student Login', 'secondary_link' => route('student.login'), 'label' => 'Portal'],
        ];
        $landingLinkOptions = [
            ['label' => 'Admissions page', 'url' => route('admissions')],
            ['label' => 'Contact page', 'url' => route('contact')],
            ['label' => 'Student login', 'url' => route('student.login')],
            ['label' => 'Staff login', 'url' => route('staff.login')],
            ['label' => 'About page', 'url' => route('about')],
        ];
        $landingLinkPreset = function (?string $value) use ($landingLinkOptions): string {
            foreach ($landingLinkOptions as $option) {
                if ($value === $option['url']) {
                    return $option['url'];
                }
            }

            return 'custom';
        };
        $landingStatDefaults = [
            1 => ['label' => 'Students', 'value' => (string) ($landingBuilderStats['students'] ?? 0)],
            2 => ['label' => 'Staff', 'value' => (string) ($landingBuilderStats['staff'] ?? 0)],
            3 => ['label' => 'Classes', 'value' => (string) ($landingBuilderStats['classes'] ?? 0)],
            4 => ['label' => 'Published Updates', 'value' => (string) ($landingBuilderStats['news'] ?? 0)],
        ];
        $landingProgramDefaults = [
            1 => ['badge' => 'Program', 'title' => 'Junior Secondary School', 'text' => 'JSS 1 to JSS 3 students build a strong academic foundation with disciplined learning routines.', 'link' => route('admissions')],
            2 => ['badge' => 'Program', 'title' => 'Senior Secondary School', 'text' => 'SS 1 to SS 3 students prepare for examinations, leadership, and higher education.', 'link' => route('admissions')],
            3 => ['badge' => 'Department', 'title' => 'Science Department', 'text' => 'Focused preparation for science, medicine, engineering, technology, and research pathways.', 'link' => route('admissions')],
            4 => ['badge' => 'Department', 'title' => 'Commercial Department', 'text' => 'Business, finance, commerce, and enterprise subjects taught with practical direction.', 'link' => route('admissions')],
            5 => ['badge' => 'Department', 'title' => 'Art Department', 'text' => 'Humanities, communication, law, social sciences, and creative pathways are supported.', 'link' => route('admissions')],
            6 => ['badge' => 'Portal', 'title' => 'Digital Learning Workspace', 'text' => 'CBT, assignments, results, records, and staff workflows are managed from one platform.', 'link' => route('staff.login')],
            7 => ['badge' => 'Values', 'title' => 'Moral and Godly Training', 'text' => 'Students are shaped through discipline, integrity, responsibility, and spiritual guidance.', 'link' => route('about')],
        ];
        $landingEventDefaults = [
            1 => ['month' => 'Now', 'day' => '01', 'title' => 'Admission Enquiries', 'text' => 'Parents can contact the school office for admission guidance, requirements, and available classes.', 'tag_1' => 'Admissions', 'tag_2' => 'School Office'],
            2 => ['month' => 'Term', 'day' => '02', 'title' => 'Academic Records Update', 'text' => 'Teachers continue to upload assignments, tests, CBT activities, and reports through the school portal.', 'tag_1' => 'Portal', 'tag_2' => 'Academics'],
            3 => ['month' => 'Week', 'day' => '03', 'title' => 'Student Development', 'text' => 'Classroom instruction, discipline, and moral training continue across all departments.', 'tag_1' => 'Students', 'tag_2' => 'Values'],
            4 => ['month' => 'Open', 'day' => '04', 'title' => 'Parent Support Desk', 'text' => 'Parents can reach the school for finance records, student updates, and general support.', 'tag_1' => 'Parents', 'tag_2' => 'Support'],
        ];
        $landingGalleryDefaults = [
            1 => 'Classroom life',
            2 => 'Student activities',
            3 => 'Academic focus',
            4 => 'School community',
            5 => 'Moral development',
            6 => 'Leadership and service',
        ];
        $landingTestimonialDefaults = [
            1 => ['initials' => 'PA', 'name' => 'Parent Testimonial', 'role' => 'Junior Secondary Parent', 'text' => 'The school gives our children structure, discipline, and academic attention. We are grateful for the care and consistency.'],
            2 => ['initials' => 'ST', 'name' => 'Student Voice', 'role' => 'Senior Secondary Student', 'text' => 'Teachers explain clearly, assignments are organized, and the portal helps me follow my academic work.'],
            3 => ['initials' => 'AL', 'name' => 'Alumni Reflection', 'role' => 'Former Student', 'text' => 'The values learned here continue to guide me. BELOVED SCHOOLS helped me grow in confidence and responsibility.'],
        ];

        $settingsNavItems = [
            ['key' => 'website-foundation', 'label' => 'Foundation', 'href' => route('admin.settings', ['section' => 'website-foundation'])],
            ['key' => 'theme-colors', 'label' => 'Theme', 'href' => route('admin.settings', ['section' => 'theme-colors'])],
            ['key' => 'landing-builder', 'label' => 'Landing Builder', 'href' => route('admin.settings', ['section' => 'landing-builder'])],
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
        <!-- 1. Foundation settings -->
        <div x-show="activeSection === 'website-foundation'" class="space-y-6">
            <x-form-card
                action="{{ route('admin.settings.update') }}"
                method="POST"
                enctype="multipart/form-data"
                title="Website Foundation"
                description="Configure essential school parameters, administrative contacts, outgoing SMTP email servers, and brand logos."
            >
                <input type="hidden" name="group" value="school">
                <input type="hidden" name="settings_section" value="website-foundation">

                <!-- Foundation Grid -->
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">School Name</label>
                        <input name="school_name" value="{{ old('school_name', $settings['school_name'] ?? '') }}" placeholder="School name" class="theme-input w-full" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">School Motto</label>
                        <input name="motto" value="{{ old('motto', $settings['motto'] ?? '') }}" placeholder="School motto" class="theme-input w-full" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Website Tagline</label>
                        <input name="site_tagline" value="{{ old('site_tagline', $settings['site_tagline'] ?? '') }}" placeholder="Website tagline" class="theme-input w-full" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Homepage Subtitle</label>
                        <input name="site_subtitle" value="{{ old('site_subtitle', $settings['site_subtitle'] ?? '') }}" placeholder="Homepage subtitle" class="theme-input w-full" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">School Email</label>
                        <input name="school_email" value="{{ old('school_email', $settings['school_email'] ?? '') }}" placeholder="School email" class="theme-input w-full" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">School Phone</label>
                        <div class="phone-field">
                            <input id="school-phone-settings" name="school_phone" type="tel" inputmode="tel" autocomplete="tel-national" value="{{ old('school_phone', $settings['school_phone'] ?? '') }}" placeholder="School phone" class="theme-input w-full" />
                            <button type="button" class="contact-picker-button" x-data="contactField({ target: 'school-phone-settings' })" x-show="supported" x-cloak @click="pick()">Pick</button>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">WhatsApp Number</label>
                        <input name="whatsapp_number" type="tel" inputmode="tel" autocomplete="tel-national" value="{{ old('whatsapp_number', $settings['whatsapp_number'] ?? '08165587119') }}" placeholder="WhatsApp number" class="theme-input w-full" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">WhatsApp Link Override</label>
                        <input name="whatsapp_link" value="{{ old('whatsapp_link', $settings['whatsapp_link'] ?? '') }}" placeholder="WhatsApp link override (optional)" class="theme-input w-full" />
                    </div>
                    <div class="space-y-1 md:col-span-2">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Contact Form Recipient Email</label>
                        <input name="contact_email_recipient" value="{{ old('contact_email_recipient', $settings['contact_email_recipient'] ?? 'vickoboy104@gmail.com') }}" placeholder="Contact form recipient email" class="theme-input w-full" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Principal Name</label>
                        <input name="principal_name" value="{{ old('principal_name', $settings['principal_name'] ?? '') }}" placeholder="Principal name" class="theme-input w-full" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">School Address</label>
                        <input name="school_address" value="{{ old('school_address', $settings['school_address'] ?? '') }}" placeholder="School address" class="theme-input w-full" />
                    </div>
                    <div class="space-y-1 md:col-span-2">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Hero Intro Text</label>
                        <textarea name="hero_blurb" rows="3" placeholder="Hero intro text" class="theme-input w-full">{{ old('hero_blurb', $settings['hero_blurb'] ?? '') }}</textarea>
                    </div>
                    <div class="space-y-1 md:col-span-2">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Portal Login Screen Notice</label>
                        <textarea name="portal_notice" rows="3" placeholder="Portal notice shown on login pages" class="theme-input w-full">{{ old('portal_notice', $settings['portal_notice'] ?? '') }}</textarea>
                    </div>
                </div>

                <!-- SMTP Section -->
                <div class="border-t border-slate-100 pt-6 mt-6">
                    <h4 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-2">Outgoing SMTP Mail Server</h4>
                    <p class="text-xs text-slate-500 mb-4">Integrate real SMTP settings to dispatch dynamic web contacts straight to your official email account.</p>
                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Mailer Driver</label>
                            <select name="mail_mailer" class="theme-input w-full">
                                @foreach (['log' => 'Log only', 'smtp' => 'SMTP'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('mail_mailer', $settings['mail_mailer'] ?? 'log') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">SMTP Host</label>
                            <input name="mail_host" value="{{ old('mail_host', $settings['mail_host'] ?? '') }}" placeholder="e.g. smtp.mailgun.org" class="theme-input w-full" />
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">SMTP Port</label>
                            <input name="mail_port" value="{{ old('mail_port', $settings['mail_port'] ?? '587') }}" placeholder="SMTP port" class="theme-input w-full" />
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Encryption Protocol</label>
                            <input name="mail_encryption" value="{{ old('mail_encryption', $settings['mail_encryption'] ?? 'tls') }}" placeholder="tls / ssl" class="theme-input w-full" />
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">SMTP Username</label>
                            <input name="mail_username" value="{{ old('mail_username', $settings['mail_username'] ?? '') }}" placeholder="SMTP username" class="theme-input w-full" />
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">SMTP Password</label>
                            <input name="mail_password" type="password" value="{{ old('mail_password', $settings['mail_password'] ?? '') }}" placeholder="SMTP password" class="theme-input w-full" />
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">From Sender Address</label>
                            <input name="mail_from_address" value="{{ old('mail_from_address', $settings['mail_from_address'] ?? $settings['school_email'] ?? '') }}" placeholder="From email address" class="theme-input w-full" />
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">From Sender Name</label>
                            <input name="mail_from_name" value="{{ old('mail_from_name', $settings['mail_from_name'] ?? $settings['school_name'] ?? '') }}" placeholder="From name" class="theme-input w-full" />
                        </div>
                    </div>
                </div>

                <!-- Logo Section -->
                <div class="border-t border-slate-100 pt-6 mt-6">
                    <h4 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Official Logo & Favicon</h4>
                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Upload Brand Logo</label>
                                <span class="text-xs text-slate-400">PNG/JPG Format</span>
                            </div>
                            <input type="file" name="logo_file" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#071833] file:text-white hover:file:bg-[#0b1f3a]">
                            <div class="pt-3 border-t border-slate-200/50 flex items-center gap-4">
                                <div class="w-16 h-16 rounded-xl border border-slate-200 bg-white flex items-center justify-center p-2 shrink-0 shadow-sm">
                                    <x-application-logo class="h-12 w-auto object-contain" />
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-slate-900">Current Portal Logo</div>
                                    <p class="text-[10px] text-slate-500 mt-1">Rendered on sidebar menus, receipt headers, and login screens.</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Upload Browser Favicon</label>
                                <span class="text-xs text-slate-400">ICO/PNG Format</span>
                            </div>
                            <input type="file" name="favicon_file" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#071833] file:text-white hover:file:bg-[#0b1f3a]">
                            <div class="pt-3 border-t border-slate-200/50 flex items-center gap-4">
                                <div class="w-16 h-16 rounded-xl border border-slate-200 bg-white flex items-center justify-center p-2 shrink-0 shadow-sm">
                                    @if (! empty($settings['favicon_path']))
                                        <img src="{{ asset($settings['favicon_path']) }}" alt="Favicon" class="h-10 w-10 object-contain rounded">
                                    @else
                                        <div class="text-[10px] text-slate-400">None</div>
                                    @endif
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-slate-900">Current Favicon</div>
                                    <p class="text-[10px] text-slate-500 mt-1">Displayed inside the browser tab outline bar.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <x-slot name="actions">
                    <x-action-button type="submit" variant="success" icon="check">
                        Save Foundation Settings
                    </x-action-button>
                </x-slot>
            </x-form-card>
        </div>

        <!-- 2. Theme Colors -->
        <div x-show="activeSection === 'theme-colors'" x-data="{ preset: @js($currentThemePreset) }">
            <x-form-card
                action="{{ route('admin.settings.update') }}"
                method="POST"
                title="Website Theme Colors"
                description="Select a style scheme for the public website, admin portal, staff portal, student portal, buttons, navigation accents, and page surfaces."
            >
                <input type="hidden" name="group" value="school">
                <input type="hidden" name="settings_section" value="theme-colors">

                <div class="grid gap-6 md:grid-cols-3">
                    @foreach ($themeSwatches as $value => $colors)
                        <label class="cursor-pointer rounded-2xl border p-5 transition flex flex-col justify-between hover:border-[#fbbf24] hover:shadow-md {{ $currentThemePreset === $value ? 'border-[#071833] bg-white ring-2 ring-[#fbbf24]/50 shadow-md' : 'border-slate-200 bg-slate-50' }}">
                            <div class="flex items-center justify-between gap-3 mb-4">
                                <span class="font-bold text-sm text-slate-900">{{ $themePresetOptions[$value] }}</span>
                                <input type="radio" name="theme_preset" value="{{ $value }}" x-model="preset" @checked($currentThemePreset === $value) class="accent-[#071833] h-4 w-4">
                            </div>
                            <div class="flex gap-2" aria-hidden="true">
                                @foreach ($colors as $color)
                                    <span class="h-8 flex-1 rounded-lg border border-white/20 shadow-sm" style="background-color: {{ $color }}"></span>
                                @endforeach
                            </div>
                        </label>
                    @endforeach
                </div>

                {{-- ═══════════════════════════════════════════════════════════ --}}
                {{-- MANUAL COLOR CONTROLS                                          --}}
                {{-- ═══════════════════════════════════════════════════════════ --}}

                <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-sm">
                    <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-slate-200 flex items-center gap-3">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-blue-600 text-white text-xs font-bold">A</span>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">Manual Color Controls</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Fine-tune the selected theme preset with custom colors.</p>
                        </div>
                    </div>
                    <div class="p-6 grid gap-5 sm:grid-cols-2 md:grid-cols-4">
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-600 uppercase tracking-wider block">Primary Color</label>
                            <input type="color" name="theme_primary" value="{{ old('theme_primary', $settings['theme_primary'] ?? '#2563EB') }}" @input="preset = 'custom'" class="h-12 w-full rounded-xl border-2 border-slate-200 bg-white p-1 cursor-pointer">
                            <p class="text-[9px] text-slate-400 leading-tight">Brand buttons &amp; links</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-600 uppercase tracking-wider block">Secondary / Sidebar Color</label>
                            <input type="color" name="theme_secondary" value="{{ old('theme_secondary', $settings['theme_secondary'] ?? '#0F172A') }}" @input="preset = 'custom'" class="h-12 w-full rounded-xl border-2 border-slate-200 bg-white p-1 cursor-pointer">
                            <p class="text-[9px] text-slate-400 leading-tight">Sidebar &amp; dark panels</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-600 uppercase tracking-wider block">Accent / Yellow Color</label>
                            <input type="color" name="theme_accent" value="{{ old('theme_accent', $settings['theme_accent'] ?? '#FBBF24') }}" @input="preset = 'custom'" class="h-12 w-full rounded-xl border-2 border-slate-200 bg-white p-1 cursor-pointer">
                            <p class="text-[9px] text-slate-400 leading-tight">Highlights &amp; badges</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-600 uppercase tracking-wider block">Soft Surface Color</label>
                            <input type="color" name="theme_soft" value="{{ old('theme_soft', $settings['theme_soft'] ?? '#F1F5F9') }}" @input="preset = 'custom'" class="h-12 w-full rounded-xl border-2 border-slate-200 bg-white p-1 cursor-pointer">
                            <p class="text-[9px] text-slate-400 leading-tight">Muted surface shading for cards and sections</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-600 uppercase tracking-wider block">Page Background Color</label>
                            <input type="color" name="theme_page_bg" value="{{ old('theme_page_bg', $settings['theme_page_bg'] ?? '#F8FAFC') }}" @input="preset = 'custom'" class="h-12 w-full rounded-xl border-2 border-slate-200 bg-white p-1 cursor-pointer">
                            <p class="text-[9px] text-slate-400 leading-tight">Admin canvas colour</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-600 uppercase tracking-wider block">Card / Surface Color</label>
                            <input type="color" name="theme_surface" value="{{ old('theme_surface', $settings['theme_surface'] ?? '#FFFFFF') }}" @input="preset = 'custom'" class="h-12 w-full rounded-xl border-2 border-slate-200 bg-white p-1 cursor-pointer">
                            <p class="text-[9px] text-slate-400 leading-tight">Card backgrounds</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-600 uppercase tracking-wider block">Top Bar / Header Color</label>
                            <input type="color" name="top_bar_color" value="{{ old('top_bar_color', $settings['top_bar_color'] ?? '#FFFFFF') }}" @input="preset = 'custom'" class="h-12 w-full rounded-xl border-2 border-slate-200 bg-white p-1 cursor-pointer">
                            <p class="text-[9px] text-slate-400 leading-tight">Header and top navigation background</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-600 uppercase tracking-wider block">Text Color</label>
                            <input type="color" name="theme_text" value="{{ old('theme_text', $settings['theme_text'] ?? '#0F172A') }}" @input="preset = 'custom'" class="h-12 w-full rounded-xl border-2 border-slate-200 bg-white p-1 cursor-pointer">
                            <p class="text-[9px] text-slate-400 leading-tight">Headings &amp; body text</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-600 uppercase tracking-wider block">Muted Text Color</label>
                            <input type="color" name="theme_text_muted" value="{{ old('theme_text_muted', $settings['theme_text_muted'] ?? '#64748B') }}" @input="preset = 'custom'" class="h-12 w-full rounded-xl border-2 border-slate-200 bg-white p-1 cursor-pointer">
                            <p class="text-[9px] text-slate-400 leading-tight">Subtitles &amp; captions</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-600 uppercase tracking-wider block">Border Color</label>
                            <input type="color" name="theme_border" value="{{ old('theme_border', $settings['theme_border'] ?? '#CBD5E1') }}" @input="preset = 'custom'" class="h-12 w-full rounded-xl border-2 border-slate-200 bg-white p-1 cursor-pointer">
                            <p class="text-[9px] text-slate-400 leading-tight">Card &amp; input borders</p>
                        </div>
                    </div>
                </div>





                <x-slot name="actions">
                    <x-action-button type="submit" variant="success" icon="check">
                        Save Theme Configurations
                    </x-action-button>
                </x-slot>
            </x-form-card>
        </div>

        <!-- 3. Landing Builder -->
        <div x-show="activeSection === 'landing-builder'">
            <x-form-card
                action="{{ route('admin.settings.update') }}"
                method="POST"
                enctype="multipart/form-data"
                title="Public Landing Builder"
                description="Manage and structure slide outlines, parent testimonials, metrics, and core headings of the frontend admissions page."
            >
                <input type="hidden" name="group" value="school">
                <input type="hidden" name="settings_section" value="landing-builder">

                <!-- Switches Grid -->
                <div class="grid gap-6 md:grid-cols-3 mb-6">
                    <label class="flex items-start gap-4 p-5 rounded-2xl border border-slate-200 bg-slate-50 cursor-pointer hover:bg-slate-100/50 transition">
                        <input type="checkbox" name="landing_use_live_stats" value="1" class="mt-1 rounded border-slate-300 text-[#071833] focus:ring-[#071833] h-4 w-4" @checked(old('landing_use_live_stats', $settings['landing_use_live_stats'] ?? '1'))>
                        <div>
                            <span class="block font-bold text-slate-900 text-xs uppercase tracking-wider">Sync Live Statistics</span>
                            <span class="block text-[10px] text-slate-500 mt-1">Automatically pull real numbers of enrolled students, active teachers, and classes directly from database tables.</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-4 p-5 rounded-2xl border border-slate-200 bg-slate-50 cursor-pointer hover:bg-slate-100/50 transition">
                        <input type="checkbox" name="landing_use_latest_announcements" value="1" class="mt-1 rounded border-slate-300 text-[#071833] focus:ring-[#071833] h-4 w-4" @checked(old('landing_use_latest_announcements', $settings['landing_use_latest_announcements'] ?? '1'))>
                        <div>
                            <span class="block font-bold text-slate-900 text-xs uppercase tracking-wider">Auto-Sync Announcements</span>
                            <span class="block text-[10px] text-slate-500 mt-1">Pull published school board updates and events dynamically into the front page without manual entry.</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-4 p-5 rounded-2xl border border-slate-200 bg-slate-50 cursor-pointer hover:bg-slate-100/50 transition">
                        <input type="checkbox" name="landing_show_newsletter" value="1" class="mt-1 rounded border-slate-300 text-[#071833] focus:ring-[#071833] h-4 w-4" @checked(old('landing_show_newsletter', $settings['landing_show_newsletter'] ?? '1'))>
                        <div>
                            <span class="block font-bold text-slate-900 text-xs uppercase tracking-wider">Display Newsletter Strip</span>
                            <span class="block text-[10px] text-slate-500 mt-1">Toggle the display of the email subscription section situated at the footer of the public homepage.</span>
                        </div>
                    </label>
                </div>

                <!-- Slide details accordion -->
                <div class="landing-slide-editor space-y-4" x-data="{ openSlide: 1 }">
                    <div class="section-header border-b border-slate-100 pb-3">
                        <div>
                            <h4 class="section-title">Hero Slideshow Text Templates</h4>
                            <p class="section-description">Open one slide at a time, edit the copy, choose button destinations, and review the first-screen message.</p>
                        </div>
                        <a href="{{ route('home') }}" target="_blank" class="btn btn-secondary">Preview Homepage</a>
                    </div>

                    <div class="landing-slide-accordion">
                        @foreach ($landingSlideDefaults as $index => $slide)
                            @php
                                $primaryLinkValue = old("landing_slide_{$index}_primary_link", $settings["landing_slide_{$index}_primary_link"] ?? $slide['primary_link']);
                                $secondaryLinkValue = old("landing_slide_{$index}_secondary_link", $settings["landing_slide_{$index}_secondary_link"] ?? $slide['secondary_link']);
                                $primaryDestination = $landingLinkPreset($primaryLinkValue);
                                $secondaryDestination = $landingLinkPreset($secondaryLinkValue);
                                $currentSlideImage = $settings["hero_slide_{$index}_image"] ?? null;
                            @endphp
                            <article
                                class="landing-slide-card"
                                x-data="{
                                    primaryDestination: @js($primaryDestination),
                                    secondaryDestination: @js($secondaryDestination),
                                    primaryCustom: @js($primaryLinkValue),
                                    secondaryCustom: @js($secondaryLinkValue)
                                }"
                            >
                                <button type="button" class="landing-slide-summary" x-on:click="openSlide = openSlide === {{ $index }} ? null : {{ $index }}">
                                    <span class="landing-slide-summary-main">
                                        <span class="landing-slide-number">Slide {{ $index }}</span>
                                        <strong>{{ old("landing_slide_{$index}_label", $settings["landing_slide_{$index}_label"] ?? $slide['label']) }}</strong>
                                    </span>
                                    <span class="landing-slide-summary-title">{{ old("landing_slide_{$index}_title", $settings["landing_slide_{$index}_title"] ?? $slide['title']) }}</span>
                                    <span class="landing-slide-chevron" x-text="openSlide === {{ $index }} ? '-' : '+'"></span>
                                </button>

                                <div class="landing-slide-body" x-show="openSlide === {{ $index }}" x-transition>
                                    <div class="landing-slide-fields">
                                        <label>
                                            <span>Slide Label</span>
                                            <input name="landing_slide_{{ $index }}_label" value="{{ old("landing_slide_{$index}_label", $settings["landing_slide_{$index}_label"] ?? $slide['label']) }}" placeholder="Welcome" class="theme-input w-full" />
                                        </label>
                                        <label>
                                            <span>Eyebrow / Kicker</span>
                                            <input name="landing_slide_{{ $index }}_eyebrow" value="{{ old("landing_slide_{$index}_eyebrow", $settings["landing_slide_{$index}_eyebrow"] ?? $slide['eyebrow']) }}" placeholder="Welcome to Beloved Schools" class="theme-input w-full" />
                                        </label>
                                        <label>
                                            <span>Main Heading</span>
                                            <input name="landing_slide_{{ $index }}_title" value="{{ old("landing_slide_{$index}_title", $settings["landing_slide_{$index}_title"] ?? $slide['title']) }}" placeholder="Main heading" class="theme-input w-full" />
                                        </label>
                                        <label>
                                            <span>Subheading</span>
                                            <input name="landing_slide_{{ $index }}_emphasis" value="{{ old("landing_slide_{$index}_emphasis", $settings["landing_slide_{$index}_emphasis"] ?? $slide['emphasis']) }}" placeholder="Highlighted heading text" class="theme-input w-full" />
                                        </label>
                                        <label class="landing-slide-wide">
                                            <span>Description</span>
                                            <textarea name="landing_slide_{{ $index }}_text" rows="3" placeholder="Short homepage slide description" class="theme-input w-full">{{ old("landing_slide_{$index}_text", $settings["landing_slide_{$index}_text"] ?? $slide['text']) }}</textarea>
                                        </label>
                                        <label>
                                            <span>Primary Button Text</span>
                                            <input name="landing_slide_{{ $index }}_primary" value="{{ old("landing_slide_{$index}_primary", $settings["landing_slide_{$index}_primary"] ?? $slide['primary']) }}" placeholder="Apply Now" class="theme-input w-full" />
                                        </label>
                                        <label>
                                            <span>Primary Button Destination</span>
                                            <input type="hidden" name="landing_slide_{{ $index }}_primary_link" x-bind:value="primaryDestination === 'custom' ? primaryCustom : primaryDestination">
                                            <select x-model="primaryDestination" class="theme-input w-full">
                                                @foreach ($landingLinkOptions as $option)
                                                    <option value="{{ $option['url'] }}">{{ $option['label'] }}</option>
                                                @endforeach
                                                <option value="custom">Custom link</option>
                                            </select>
                                        </label>
                                        <label x-show="primaryDestination === 'custom'" x-transition>
                                            <span>Custom Primary Link</span>
                                            <input x-model="primaryCustom" placeholder="https://example.com/page" class="theme-input w-full" />
                                        </label>
                                        <label>
                                            <span>Secondary Button Text</span>
                                            <input name="landing_slide_{{ $index }}_secondary" value="{{ old("landing_slide_{$index}_secondary", $settings["landing_slide_{$index}_secondary"] ?? $slide['secondary']) }}" placeholder="Explore Our School" class="theme-input w-full" />
                                        </label>
                                        <label>
                                            <span>Secondary Button Destination</span>
                                            <input type="hidden" name="landing_slide_{{ $index }}_secondary_link" x-bind:value="secondaryDestination === 'custom' ? secondaryCustom : secondaryDestination">
                                            <select x-model="secondaryDestination" class="theme-input w-full">
                                                @foreach ($landingLinkOptions as $option)
                                                    <option value="{{ $option['url'] }}">{{ $option['label'] }}</option>
                                                @endforeach
                                                <option value="custom">Custom link</option>
                                            </select>
                                        </label>
                                        <label x-show="secondaryDestination === 'custom'" x-transition>
                                            <span>Custom Secondary Link</span>
                                            <input x-model="secondaryCustom" placeholder="https://example.com/page" class="theme-input w-full" />
                                        </label>
                                        <label class="landing-slide-wide">
                                            <span>Background Image</span>
                                            <input type="file" name="hero_slide_{{ $index }}_image" accept="image/*" class="block w-full text-xs" />
                                        </label>
                                        <label class="landing-slide-wide">
                                            <span>Background Video (optional, overrides image)</span>
                                            <input type="file" name="hero_slide_{{ $index }}_video" accept=".mp4,.webm,.mov,.m4v" class="block w-full text-xs" />
                                            @if (! empty($settings["hero_slide_{$index}_video"]))
                                                <div class="mt-2 text-[10px] text-slate-500 flex items-center gap-2">
                                                    <span class="text-emerald-600 font-semibold">✓ Active Video:</span>
                                                    <a href="{{ asset($settings["hero_slide_{$index}_video"]) }}" target="_blank" class="underline hover:text-slate-700">View Video</a>
                                                </div>
                                            @endif
                                        </label>
                                    </div>

                                    <div class="landing-slide-preview" style="--preview-img: url('{{ $currentSlideImage ? asset($currentSlideImage) : '' }}');">
                                        <div>
                                            <span>{{ old("landing_slide_{$index}_eyebrow", $settings["landing_slide_{$index}_eyebrow"] ?? $slide['eyebrow']) }}</span>
                                            <strong>{{ old("landing_slide_{$index}_title", $settings["landing_slide_{$index}_title"] ?? $slide['title']) }}</strong>
                                            <p>{{ old("landing_slide_{$index}_text", $settings["landing_slide_{$index}_text"] ?? $slide['text']) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>

                <!-- Metrics and Admissions headings -->
                <div class="grid gap-6 lg:grid-cols-2 mt-6">
                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">Manual Stats Fallback</h4>
                        <p class="text-[10px] text-slate-500">Provide fallback metrics displayed when live statistics are switched off.</p>
                        <div class="grid gap-4 md:grid-cols-2">
                            @foreach ($landingStatDefaults as $index => $stat)
                                <div class="space-y-2 p-3 bg-white border border-slate-200 rounded-xl">
                                    <div class="text-[10px] font-bold text-slate-400">Stat Card {{ $index }}</div>
                                    <input name="landing_stat_{{ $index }}_label" value="{{ old("landing_stat_{$index}_label", $settings["landing_stat_{$index}_label"] ?? $stat['label']) }}" placeholder="Label" class="theme-input w-full text-xs" />
                                    <input name="landing_stat_{{ $index }}_value" value="{{ old("landing_stat_{$index}_value", $settings["landing_stat_{$index}_value"] ?? $stat['value']) }}" placeholder="Value" class="theme-input w-full text-xs" />
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">Admission Promotion Panel</h4>
                        <div class="space-y-3">
                            <input name="landing_admission_kicker" value="{{ old('landing_admission_kicker', $settings['landing_admission_kicker'] ?? 'Admissions in Progress') }}" placeholder="Kicker text" class="theme-input w-full text-xs" />
                            <input name="landing_admission_title" value="{{ old('landing_admission_title', $settings['landing_admission_title'] ?? 'Apply for the Current Academic Session') }}" placeholder="Main Title" class="theme-input w-full text-xs" />
                            <textarea name="landing_admission_text" rows="3" placeholder="Blurb text" class="theme-input w-full text-xs">{{ old('landing_admission_text', $settings['landing_admission_text'] ?? "Admission into {$builderSchoolName} is open. Speak with the school office for entrance guidance and available classes.") }}</textarea>
                            <div class="grid gap-3 sm:grid-cols-3">
                                <input name="landing_admission_primary_text" value="{{ old('landing_admission_primary_text', $settings['landing_admission_primary_text'] ?? 'Admission Details') }}" placeholder="Primary button" class="theme-input w-full text-xs" />
                                <input name="landing_admission_support_text" value="{{ old('landing_admission_support_text', $settings['landing_admission_support_text'] ?? 'Request Support') }}" placeholder="Support button" class="theme-input w-full text-xs" />
                                <input name="landing_admission_whatsapp_text" value="{{ old('landing_admission_whatsapp_text', $settings['landing_admission_whatsapp_text'] ?? 'Chat on WhatsApp') }}" placeholder="WhatsApp button" class="theme-input w-full text-xs" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Academic Program settings -->
                <div class="mt-6 p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">Academics & Departmental Information Widgets</h4>
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        @foreach ($landingProgramDefaults as $index => $program)
                            <div class="p-4 bg-white border border-slate-200 rounded-xl space-y-3">
                                <div class="text-[10px] font-bold text-[#071833] border-b border-slate-100 pb-1">Card {{ $index }}</div>
                                <input name="landing_program_{{ $index }}_badge" value="{{ old("landing_program_{$index}_badge", $settings["landing_program_{$index}_badge"] ?? $program['badge']) }}" placeholder="Badge label" class="theme-input w-full text-xs" />
                                <input name="landing_program_{{ $index }}_title" value="{{ old("landing_program_{$index}_title", $settings["landing_program_{$index}_title"] ?? $program['title']) }}" placeholder="Headline" class="theme-input w-full text-xs" />
                                <textarea name="landing_program_{{ $index }}_text" rows="3" placeholder="Widget details text" class="theme-input w-full text-xs">{{ old("landing_program_{$index}_text", $settings["landing_program_{$index}_text"] ?? $program['text']) }}</textarea>
                                <input name="landing_program_{{ $index }}_link" value="{{ old("landing_program_{$index}_link", $settings["landing_program_{$index}_link"] ?? $program['link']) }}" placeholder="Redirect link" class="theme-input w-full text-xs" />
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Heading Customizers and Events -->
                <div class="grid gap-6 lg:grid-cols-2 mt-6">
                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">Public Headings Templates</h4>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <input name="landing_programs_kicker" value="{{ old('landing_programs_kicker', $settings['landing_programs_kicker'] ?? 'Our Programs') }}" placeholder="Programs kicker" class="theme-input w-full text-xs" />
                            <input name="landing_programs_title" value="{{ old('landing_programs_title', $settings['landing_programs_title'] ?? 'Academic Pathways') }}" placeholder="Programs title" class="theme-input w-full text-xs" />
                            <input name="landing_programs_emphasis" value="{{ old('landing_programs_emphasis', $settings['landing_programs_emphasis'] ?? "at {$builderSchoolName}") }}" placeholder="Programs emphasis" class="theme-input w-full text-xs" />
                            <input name="landing_programs_button_text" value="{{ old('landing_programs_button_text', $settings['landing_programs_button_text'] ?? 'Register Your Child Now') }}" placeholder="Programs button" class="theme-input w-full text-xs" />
                            <textarea name="landing_programs_text" rows="2" placeholder="Programs subtitle" class="theme-input w-full text-xs sm:col-span-2">{{ old('landing_programs_text', $settings['landing_programs_text'] ?? 'Every class shares the same values, standards, and commitment to excellence.') }}</textarea>
                            
                            <input name="landing_gallery_kicker" value="{{ old('landing_gallery_kicker', $settings['landing_gallery_kicker'] ?? 'Gallery') }}" placeholder="Gallery kicker" class="theme-input w-full text-xs" />
                            <input name="landing_gallery_title" value="{{ old('landing_gallery_title', $settings['landing_gallery_title'] ?? 'Life at') }}" placeholder="Gallery title" class="theme-input w-full text-xs" />
                            <input name="landing_gallery_emphasis" value="{{ old('landing_gallery_emphasis', $settings['landing_gallery_emphasis'] ?? $builderSchoolName) }}" placeholder="Gallery emphasis" class="theme-input w-full text-xs" />
                            <input name="landing_gallery_button_text" value="{{ old('landing_gallery_button_text', $settings['landing_gallery_button_text'] ?? 'View School Profile') }}" placeholder="Gallery button" class="theme-input w-full text-xs" />

                            <input name="landing_testimonials_kicker" value="{{ old('landing_testimonials_kicker', $settings['landing_testimonials_kicker'] ?? 'Testimonials') }}" placeholder="Testimonials kicker" class="theme-input w-full text-xs" />
                            <input name="landing_testimonials_title" value="{{ old('landing_testimonials_title', $settings['landing_testimonials_title'] ?? 'What Our') }}" placeholder="Testimonials title" class="theme-input w-full text-xs" />
                            <input name="landing_testimonials_emphasis" value="{{ old('landing_testimonials_emphasis', $settings['landing_testimonials_emphasis'] ?? 'Parents Say') }}" placeholder="Testimonials emphasis" class="theme-input w-full text-xs sm:col-span-2" />
                        </div>
                    </div>

                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">Welcome Address & News Sidebar</h4>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <input name="landing_welcome_kicker" value="{{ old('landing_welcome_kicker', $settings['landing_welcome_kicker'] ?? 'Welcome Address') }}" placeholder="Welcome kicker" class="theme-input w-full text-xs" />
                            <input name="landing_welcome_title" value="{{ old('landing_welcome_title', $settings['landing_welcome_title'] ?? ($settings['site_subtitle'] ?? 'Raising Future Leaders Through Knowledge and Godly Values')) }}" placeholder="Welcome title" class="theme-input w-full text-xs" />
                            <textarea name="landing_welcome_text_1" rows="2" placeholder="Welcome address par 1" class="theme-input w-full text-xs sm:col-span-2">{{ old('landing_welcome_text_1', $settings['landing_welcome_text_1'] ?? "Founded in 2006 and located in Ore, Ondo State, {$builderSchoolName} is committed to raising educated, disciplined, and God-fearing students.") }}</textarea>
                            <textarea name="landing_welcome_text_2" rows="2" placeholder="Welcome address par 2" class="theme-input w-full text-xs sm:col-span-2">{{ old('landing_welcome_text_2', $settings['landing_welcome_text_2'] ?? 'With professional teachers, focused learning, and strong moral foundations, the school prepares students for academic success and responsible living.') }}</textarea>
                            <input name="landing_welcome_profile_button_text" value="{{ old('landing_welcome_profile_button_text', $settings['landing_welcome_profile_button_text'] ?? 'Read Full School Profile') }}" placeholder="Full profile button" class="theme-input w-full text-xs sm:col-span-2" />

                            <input name="landing_events_kicker" value="{{ old('landing_events_kicker', $settings['landing_events_kicker'] ?? 'Upcoming Events') }}" placeholder="Events kicker" class="theme-input w-full text-xs" />
                            <input name="landing_events_title" value="{{ old('landing_events_title', $settings['landing_events_title'] ?? "What's Happening") }}" placeholder="Events title" class="theme-input w-full text-xs" />
                            <input name="landing_events_emphasis" value="{{ old('landing_events_emphasis', $settings['landing_events_emphasis'] ?? "at {$builderSchoolName}") }}" placeholder="Events emphasis" class="theme-input w-full text-xs sm:col-span-2" />
                        </div>
                    </div>
                </div>

                <!-- Event Cards & Testimonials -->
                <div class="grid gap-6 lg:grid-cols-3 mt-6">
                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4 lg:col-span-2">
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">Manual Event Fallback Cards</h4>
                        <div class="grid gap-4 sm:grid-cols-2">
                            @foreach ($landingEventDefaults as $index => $event)
                                <div class="p-3 bg-white border border-slate-200 rounded-xl space-y-2">
                                    <div class="text-[10px] font-bold text-slate-400">Event card {{ $index }}</div>
                                    <div class="grid gap-2 grid-cols-2">
                                        <input name="landing_event_{{ $index }}_month" value="{{ old("landing_event_{$index}_month", $settings["landing_event_{$index}_month"] ?? $event['month']) }}" placeholder="Month" class="theme-input w-full text-xs" />
                                        <input name="landing_event_{{ $index }}_day" value="{{ old("landing_event_{$index}_day", $settings["landing_event_{$index}_day"] ?? $event['day']) }}" placeholder="Day" class="theme-input w-full text-xs" />
                                    </div>
                                    <input name="landing_event_{{ $index }}_title" value="{{ old("landing_event_{$index}_title", $settings["landing_event_{$index}_title"] ?? $event['title']) }}" placeholder="Title" class="theme-input w-full text-xs" />
                                    <textarea name="landing_event_{{ $index }}_text" rows="2" placeholder="Brief details" class="theme-input w-full text-xs">{{ old("landing_event_{$index}_text", $settings["landing_event_{$index}_text"] ?? $event['text']) }}</textarea>
                                    <div class="grid gap-2 grid-cols-2">
                                        <input name="landing_event_{{ $index }}_tag_1" value="{{ old("landing_event_{$index}_tag_1", $settings["landing_event_{$index}_tag_1"] ?? $event['tag_1']) }}" placeholder="Tag 1" class="theme-input w-full text-xs" />
                                        <input name="landing_event_{{ $index }}_tag_2" value="{{ old("landing_event_{$index}_tag_2", $settings["landing_event_{$index}_tag_2"] ?? $event['tag_2']) }}" placeholder="Tag 2" class="theme-input w-full text-xs" />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">Support Sidebar Panel</h4>
                        <div class="space-y-3">
                            <input name="landing_events_sidebar_title" value="{{ old('landing_events_sidebar_title', $settings['landing_events_sidebar_title'] ?? 'Need admission support?') }}" placeholder="Support card title" class="theme-input w-full text-xs" />
                            <textarea name="landing_events_sidebar_text" rows="3" placeholder="Instructions/helpline text" class="theme-input w-full text-xs">{{ old('landing_events_sidebar_text', $settings['landing_events_sidebar_text'] ?? 'Get class placement guidance, requirements, and parent support from the school office.') }}</textarea>
                            <input name="landing_events_sidebar_button_text" value="{{ old('landing_events_sidebar_button_text', $settings['landing_events_sidebar_button_text'] ?? 'Contact School') }}" placeholder="Redirect text" class="theme-input w-full text-xs" />
                        </div>
                    </div>
                </div>

                <!-- Testimonials & Newsletter -->
                <div class="grid gap-6 lg:grid-cols-3 mt-6">
                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4 lg:col-span-2">
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">Parent & Alumni Testimonials</h4>
                        <div class="grid gap-4 sm:grid-cols-3">
                            @foreach ($landingTestimonialDefaults as $index => $testimonial)
                                <div class="p-3 bg-white border border-slate-200 rounded-xl space-y-2">
                                    <div class="text-[10px] font-bold text-[#071833]">Quote {{ $index }}</div>
                                    <input name="landing_testimonial_{{ $index }}_initials" value="{{ old("landing_testimonial_{$index}_initials", $settings["landing_testimonial_{$index}_initials"] ?? $testimonial['initials']) }}" placeholder="Initials" class="theme-input w-full text-xs" />
                                    <input name="landing_testimonial_{{ $index }}_name" value="{{ old("landing_testimonial_{$index}_name", $settings["landing_testimonial_{$index}_name"] ?? $testimonial['name']) }}" placeholder="Full Name" class="theme-input w-full text-xs" />
                                    <input name="landing_testimonial_{{ $index }}_role" value="{{ old("landing_testimonial_{$index}_role", $settings["landing_testimonial_{$index}_role"] ?? $testimonial['role']) }}" placeholder="Sub-label (Parent / Alumni)" class="theme-input w-full text-xs" />
                                    <textarea name="landing_testimonial_{{ $index }}_text" rows="3" placeholder="Quote contents" class="theme-input w-full text-xs">{{ old("landing_testimonial_{$index}_text", $settings["landing_testimonial_{$index}_text"] ?? $testimonial['text']) }}</textarea>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">Newsletter Subscription Footer</h4>
                        <div class="space-y-3">
                            <input name="landing_newsletter_title" value="{{ old('landing_newsletter_title', $settings['landing_newsletter_title'] ?? 'Newsletter') }}" placeholder="Title" class="theme-input w-full text-xs" />
                            <textarea name="landing_newsletter_text" rows="2" placeholder="Description text" class="theme-input w-full text-xs">{{ old('landing_newsletter_text', $settings['landing_newsletter_text'] ?? "Receive school news, events, admissions updates, and parent notices from {$builderSchoolName}.") }}</textarea>
                            <input name="landing_newsletter_placeholder" value="{{ old('landing_newsletter_placeholder', $settings['landing_newsletter_placeholder'] ?? 'Enter your email address') }}" placeholder="Input placeholder" class="theme-input w-full text-xs" />
                            <div class="grid gap-2 grid-cols-2">
                                <input name="landing_newsletter_button_text" value="{{ old('landing_newsletter_button_text', $settings['landing_newsletter_button_text'] ?? 'Subscribe') }}" placeholder="Submit button label" class="theme-input w-full text-xs" />
                                <input name="landing_newsletter_subscribed_text" value="{{ old('landing_newsletter_subscribed_text', $settings['landing_newsletter_subscribed_text'] ?? 'Subscribed') }}" placeholder="Success banner label" class="theme-input w-full text-xs" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gallery Labels -->
                <div class="mt-6 p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">Gallery Category Labels</h4>
                    <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6">
                        @foreach ($landingGalleryDefaults as $index => $label)
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-slate-400 block">Category {{ $index }}</label>
                                <input name="landing_gallery_{{ $index }}_label" value="{{ old("landing_gallery_{$index}_label", $settings["landing_gallery_{$index}_label"] ?? $label) }}" placeholder="Gallery label {{ $index }}" class="theme-input w-full text-xs" />
                            </div>
                        @endforeach
                    </div>
                </div>

                <x-slot name="actions">
                    <x-action-button type="submit" variant="success" icon="check">
                        Save Landing Builder
                    </x-action-button>
                </x-slot>
            </x-form-card>
        </div>

        <!-- 4. Homepage Media -->
        <div x-show="activeSection === 'homepage-media'">
            <x-form-card
                action="{{ route('admin.settings.update') }}"
                method="POST"
                enctype="multipart/form-data"
                title="Homepage Media Resources"
                description="Upload banner videos, sliders background layers, and landing gallery items directly to the portal."
            >
                <input type="hidden" name="group" value="school">
                <input type="hidden" name="settings_section" value="homepage-media">

                <!-- Video Panel -->
                <div class="grid gap-6 lg:grid-cols-[1.2fr,0.8fr]">
                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">Hero Video Loop Uploader</h4>
                        <p class="text-[10px] text-slate-500 leading-normal">Select a concise MP4/WebM highlight reel. The landing page plays the video first, smoothly loops into slideshow items, and resets seamlessly.</p>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-700 uppercase tracking-wider block">Video Asset</label>
                                <input type="file" name="hero_background_video" accept=".mp4,.webm,.mov,.m4v,video/mp4,video/webm,video/quicktime" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#071833] file:text-white hover:file:bg-[#0b1f3a]">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-700 uppercase tracking-wider block">Video Poster Image</label>
                                <input type="file" name="hero_background_video_poster" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#071833] file:text-white hover:file:bg-[#0b1f3a]">
                            </div>
                        </div>
                    </div>

                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4 flex flex-col justify-between">
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">Active Video Loop</h4>
                        <div class="shrink-0 flex items-center justify-center p-2 rounded-xl bg-white border border-slate-200 shadow-sm relative overflow-hidden h-36">
                            @if (! empty($settings['hero_background_video']))
                                <video controls playsinline muted preload="metadata" poster="{{ ! empty($settings['hero_background_video_poster']) ? asset($settings['hero_background_video_poster']) : '' }}" class="h-full w-full object-cover rounded-lg">
                                    <source src="{{ asset($settings['hero_background_video']) }}">
                                </video>
                            @else
                                <div class="text-[10px] text-slate-400">No Hero Video Uploaded</div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Intro Box background slider -->
                <div class="grid gap-6 lg:grid-cols-[1.2fr,0.8fr] mt-6">
                    <div x-data="{ heroIntroOpacity: {{ (int) old('hero_intro_background_opacity', $settings['hero_intro_background_opacity'] ?? 12) }} }" class="p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">Hero Headline Box Backdrop</h4>
                        <input type="file" name="hero_intro_background_image" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#071833] file:text-white hover:file:bg-[#0b1f3a]">
                        <p class="text-[10px] text-slate-400">Renders behind the central header overlay card.</p>
                        
                        <div class="p-4 bg-white border border-slate-200 rounded-xl">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-bold text-slate-700 uppercase tracking-wider">Dark Overlay Opacity</span>
                                <span class="text-xs font-bold text-[#071833] bg-slate-100 border border-slate-200 px-2 py-0.5 rounded shadow-sm" x-text="heroIntroOpacity + '%'"></span>
                            </div>
                            <input type="range" name="hero_intro_background_opacity" min="0" max="100" step="1" x-model="heroIntroOpacity" class="h-2 w-full cursor-pointer accent-[#fbbf24] bg-slate-200 rounded-lg appearance-none">
                            <p class="text-[9px] text-slate-400 mt-2">Adjust slides transparency overlay ratios to amplify headline text contrast.</p>
                        </div>
                    </div>

                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4 flex flex-col justify-between">
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">Active Headline Backdrop</h4>
                        <div class="shrink-0 flex items-center justify-center p-2 rounded-xl bg-white border border-slate-200 shadow-sm overflow-hidden h-36">
                            @if (! empty($settings['hero_intro_background_image']))
                                <img src="{{ asset($settings['hero_intro_background_image']) }}" alt="Headline backdrop" class="h-full w-full object-cover rounded-lg">
                            @else
                                <div class="text-[10px] text-slate-400">Fallback Slate Gradient</div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Slide & Gallery Images -->
                <div class="mt-6 p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">Slide Image Backdrops</h4>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                        @foreach ([1, 2, 3, 4, 5] as $index)
                            <div class="p-4 bg-white border border-slate-200 rounded-xl space-y-3">
                                <div class="text-[10px] font-bold text-slate-400 border-b border-slate-100 pb-1">Slide {{ $index }} Backdrop</div>
                                <input name="hero_slide_{{ $index }}_title" value="{{ old("hero_slide_{$index}_title", $settings["hero_slide_{$index}_title"] ?? '') }}" placeholder="Title" class="theme-input w-full text-xs" />
                                <textarea name="hero_slide_{{ $index }}_text" rows="2" placeholder="Description" class="theme-input w-full text-xs">{{ old("hero_slide_{$index}_text", $settings["hero_slide_{$index}_text"] ?? '') }}</textarea>
                                <input type="file" name="hero_slide_{{ $index }}_image" accept="image/*" class="block w-full text-[10px]">
                                @if (! empty($settings["hero_slide_{$index}_image"]))
                                    <div class="h-24 w-full rounded-lg overflow-hidden border border-slate-100 shadow-inner">
                                        <img src="{{ asset($settings["hero_slide_{$index}_image"]) }}" alt="Slide {{ $index }}" class="h-full w-full object-cover">
                                    </div>
                                @endif
                                 <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block pt-1">Background Video <span class="text-slate-300 font-normal normal-case">(overrides image)</span></label>
                                 <input type="file" name="hero_slide_{{ $index }}_video" accept=".mp4,.webm,.mov,.m4v" class="block w-full text-[10px]">
                                 @if (! empty($settings["hero_slide_{$index}_video"]))
 
                                     <div class="h-20 w-full rounded-lg overflow-hidden border border-emerald-200 shadow-inner bg-black">
                                         <video muted loop autoplay playsinline class="h-full w-full object-cover">
                                             <source src="{{ asset($settings["hero_slide_{$index}_video"]) }}">
                                         </video>
                                     </div>
                                     <p class="text-[9px] text-emerald-600 font-semibold">✓ Video active for this slide</p>
                                 @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6 p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">Homepage Grid Photo Album</h4>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ([1, 2, 3, 4] as $index)
                            <div class="p-4 bg-white border border-slate-200 rounded-xl space-y-3">
                                <div class="text-[10px] font-bold text-slate-400 border-b border-slate-100 pb-1">Gallery Image {{ $index }}</div>
                                <input type="file" name="gallery_image_{{ $index }}" accept="image/*" class="block w-full text-[10px]">
                                @if (! empty($settings["gallery_image_{$index}"]))
                                    <div class="h-24 w-full rounded-lg overflow-hidden border border-slate-100 shadow-inner">
                                        <img src="{{ asset($settings["gallery_image_{$index}"]) }}" alt="Gallery image {{ $index }}" class="h-full w-full object-cover">
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <x-slot name="actions">
                    <x-action-button type="submit" variant="success" icon="check">
                        Save Homepage Media
                    </x-action-button>
                </x-slot>
            </x-form-card>
        </div>

        <!-- 5. Workspace Backgrounds -->
        <div x-show="activeSection === 'workspace-backgrounds'">
            <x-form-card
                action="{{ route('admin.settings.update') }}"
                method="POST"
                enctype="multipart/form-data"
                title="Portal Interface Background"
                description="Upload a bespoke, high-resolution visual backdrop that renders behind administrative dashboards, academic outlines, and settings panels."
            >
                <input type="hidden" name="group" value="school">
                <input type="hidden" name="settings_section" value="workspace-backgrounds">

                <div class="grid gap-6 lg:grid-cols-[1.3fr,0.7fr]">
                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider block border-b border-slate-200 pb-2">Dedicated Workspace Wallpaper</h4>
                        <input type="file" name="admin_background_image" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#071833] file:text-white hover:file:bg-[#0b1f3a]">
                        <p class="text-[10px] text-slate-500 leading-relaxed">Provide a highly muted, high-resolution panorama or structural render. Recommended resolution: 1920x1080px. A soft radial gradient fallback acts as the default wrapper.</p>
                    </div>
                    
                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 flex flex-col justify-between">
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2 block">Active Wallpaper</h4>
                        <div class="shrink-0 flex items-center justify-center p-2 rounded-xl bg-white border border-slate-200 shadow-sm overflow-hidden h-36">
                            @if (! empty($settings['admin_background_image']))
                                <img src="{{ asset($settings['admin_background_image']) }}" alt="Portal wallpaper" class="h-full w-full object-cover rounded-lg">
                            @else
                                <div class="text-[10px] text-slate-400">Default Radial Gradient Mesh</div>
                            @endif
                        </div>
                    </div>
                </div>

                <x-slot name="actions">
                    <x-action-button type="submit" variant="success" icon="check">
                        Save Wallpaper Settings
                    </x-action-button>
                </x-slot>
            </x-form-card>
        </div>

        <!-- 6. Site Backgrounds -->
        <div x-show="activeSection === 'site-backgrounds'">
            <x-form-card
                action="{{ route('admin.settings.update') }}"
                method="POST"
                enctype="multipart/form-data"
                title="Admissions Site Backgrounds"
                description="Upload wide-view parallax background layers and scrolling sections designed to decorate the frontend public admissions template."
            >
                <input type="hidden" name="group" value="school">
                <input type="hidden" name="settings_section" value="site-backgrounds">

                <!-- Site Backgrounds -->
                <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">Admissions Parallax Layers</h4>
                    <div class="grid gap-4 lg:grid-cols-3">
                        @foreach ([1, 2, 3] as $index)
                            <div x-data="{ siteBgOpacity: {{ (int) old("site_background_{$index}_opacity", $settings["site_background_{$index}_opacity"] ?? 78) }} }" class="p-4 bg-white border border-slate-200 rounded-xl space-y-3">
                                <div class="text-[10px] font-bold text-slate-400 border-b border-slate-100 pb-1">Parallax Layer {{ $index }}</div>
                                <input type="file" name="site_background_{{ $index }}" accept="image/*" class="block w-full text-[10px]">
                                
                                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-[9px] font-bold text-slate-700 uppercase tracking-wider">Overlay Opacity</span>
                                        <span class="text-[10px] font-bold text-[#071833]" x-text="siteBgOpacity + '%'"></span>
                                    </div>
                                    <input type="range" name="site_background_{{ $index }}_opacity" min="0" max="100" step="1" x-model="siteBgOpacity" class="h-1.5 w-full cursor-pointer accent-[#fbbf24] bg-slate-200 rounded-lg appearance-none">
                                </div>

                                @if (! empty($settings["site_background_{$index}"]))
                                    <div class="h-24 w-full rounded-lg overflow-hidden border border-slate-100 shadow-inner">
                                        <img src="{{ asset($settings["site_background_{$index}"]) }}" alt="Parallax layer {{ $index }}" class="h-full w-full object-cover">
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Section Backgrounds -->
                <div class="mt-6 p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">Scrolling Sub-Section Backdrops</h4>
                    <div class="grid gap-4 lg:grid-cols-3">
                        @foreach ([1, 2, 3] as $index)
                            <div class="p-4 bg-white border border-slate-200 rounded-xl space-y-3">
                                <div class="text-[10px] font-bold text-slate-400 border-b border-slate-100 pb-1">Scrolling Backdrop {{ $index }}</div>
                                <input type="file" name="section_background_{{ $index }}" accept="image/*" class="block w-full text-[10px]">
                                
                                @if (! empty($settings["section_background_{$index}"]))
                                    <div class="h-24 w-full rounded-lg overflow-hidden border border-slate-100 shadow-inner">
                                        <img src="{{ asset($settings["section_background_{$index}"]) }}" alt="Scrolling backdrop {{ $index }}" class="h-full w-full object-cover">
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <x-slot name="actions">
                    <x-action-button type="submit" variant="success" icon="check">
                        Save Site Backgrounds
                    </x-action-button>
                </x-slot>
            </x-form-card>
        </div>

        <!-- 7. Welcome Popup -->
        <div x-show="activeSection === 'welcome-popup'">
            <x-form-card
                action="{{ route('admin.settings.update') }}"
                method="POST"
                enctype="multipart/form-data"
                title="Website Welcome Pop-up"
                description="Toggle, edit, and custom-theme the interactive greeting banner presented to new public homepage visitors."
            >
                <input type="hidden" name="group" value="school">
                <input type="hidden" name="settings_section" value="welcome-popup">

                <div class="grid gap-6 md:grid-cols-2">
                    <label class="flex items-start gap-4 p-5 rounded-2xl border border-slate-200 bg-slate-50 cursor-pointer hover:bg-slate-100/50 transition md:col-span-2">
                        <input type="checkbox" name="welcome_popup_enabled" value="1" class="mt-1 rounded border-slate-300 text-[#071833] focus:ring-[#071833] h-4 w-4" @checked(old('welcome_popup_enabled', $settings['welcome_popup_enabled'] ?? false))>
                        <div>
                            <span class="block font-bold text-slate-900 text-xs uppercase tracking-wider">Enable Welcome Pop-up Display</span>
                            <span class="block text-[10px] text-slate-500 mt-1">If enabled, a customized overlay block containing registration links, event announcements, or admissions schedules automatically fades in.</span>
                        </div>
                    </label>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Pop-up Header Title</label>
                        <input name="welcome_popup_title" value="{{ old('welcome_popup_title', $settings['welcome_popup_title'] ?? '') }}" placeholder="Popup title" class="theme-input w-full" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Button CTA Label</label>
                        <input name="welcome_popup_button_text" value="{{ old('welcome_popup_button_text', $settings['welcome_popup_button_text'] ?? '') }}" placeholder="Button text" class="theme-input w-full" />
                    </div>
                    <div class="space-y-1 md:col-span-2">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Button CTA Redirect Link</label>
                        <input name="welcome_popup_button_link" value="{{ old('welcome_popup_button_link', $settings['welcome_popup_button_link'] ?? route('admissions')) }}" placeholder="Button link" class="theme-input w-full" />
                    </div>
                    <div class="space-y-1 md:col-span-2">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Pop-up Announcement Description</label>
                        <textarea name="welcome_popup_text" rows="3" placeholder="Popup text" class="theme-input w-full">{{ old('welcome_popup_text', $settings['welcome_popup_text'] ?? '') }}</textarea>
                    </div>

                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4 md:col-span-2">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider block">Uploader Greeting Illustration</label>
                        <input type="file" name="welcome_popup_image" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#071833] file:text-white hover:file:bg-[#0b1f3a]">
                        @if (! empty($settings['welcome_popup_image']))
                            <div class="mt-3 h-32 rounded-xl overflow-hidden border border-slate-200 bg-white max-w-sm">
                                <img src="{{ asset($settings['welcome_popup_image']) }}" alt="Welcome illustration" class="h-full w-full object-cover">
                            </div>
                        @endif
                    </div>
                </div>

                <x-slot name="actions">
                    <x-action-button type="submit" variant="success" icon="check">
                        Save Pop-up Settings
                    </x-action-button>
                </x-slot>
            </x-form-card>
        </div>

        <!-- 8. Gallery Quick Uploader -->
        <div x-show="activeSection === 'gallery-uploader'">
            <x-form-card
                action="{{ route('admin.settings.update') }}"
                method="POST"
                enctype="multipart/form-data"
                title="Gallery Asset Quick Uploader"
                description="Upload custom graphics directly to the gallery folders in case the large website settings form timeouts."
            >
                <input type="hidden" name="group" value="school">
                <input type="hidden" name="settings_section" value="gallery-uploader">

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ([1, 2, 3, 4] as $index)
                        <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                            <label class="text-xs font-bold text-slate-900 uppercase tracking-wider block border-b border-slate-200 pb-1">Gallery slot {{ $index }}</label>
                            <input type="file" name="gallery_image_{{ $index }}" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-semibold file:bg-[#071833] file:text-white hover:file:bg-[#0b1f3a]">
                            <div class="shrink-0 flex items-center justify-center p-2 rounded-xl bg-white border border-slate-200 shadow-sm overflow-hidden h-36">
                                @if (! empty($settings["gallery_image_{$index}"]))
                                    <img src="{{ asset($settings["gallery_image_{$index}"]) }}" alt="Gallery image {{ $index }}" class="h-full w-full object-cover rounded-lg">
                                @else
                                    <div class="text-[10px] text-slate-400">Empty Slot</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <x-slot name="actions">
                    <x-action-button type="submit" variant="success" icon="check">
                        Save Gallery Images
                    </x-action-button>
                </x-slot>
            </x-form-card>
        </div>

        <!-- 9. Homepage Text -->
        <div x-show="activeSection === 'homepage-text'">
            <x-form-card
                action="{{ route('admin.settings.update') }}"
                method="POST"
                title="Homepage Copy & Typography"
                description="Refine text templates, highlight blurbs, key academic outlines, and founder address templates displayed on public directories."
            >
                <input type="hidden" name="group" value="school">
                <input type="hidden" name="settings_section" value="homepage-text">

                <!-- Highlights & Stats -->
                <div class="grid gap-6 md:grid-cols-2 mt-4">
                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">Hero Feature Highlights</h4>
                        <div class="space-y-3">
                            @foreach ($heroHighlightDefaults as $index => $default)
                                <div class="space-y-1">
                                    <label class="text-[10px] font-bold text-slate-400">Highlight {{ $index }}</label>
                                    <input name="hero_highlight_{{ $index }}_text" value="{{ old("hero_highlight_{$index}_text", $settings["hero_highlight_{$index}_text"] ?? $default) }}" placeholder="Highlight {{ $index }} text" class="theme-input w-full text-xs" />
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">Official Stat Badges</h4>
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach ($homepageStatDefaults as $index => $default)
                                <div class="p-3 bg-white border border-slate-200 rounded-xl space-y-2">
                                    <div class="text-[10px] font-bold text-slate-400">Stat Card {{ $index }}</div>
                                    <input name="homepage_stat_{{ $index }}_label" value="{{ old("homepage_stat_{$index}_label", $settings["homepage_stat_{$index}_label"] ?? $default['label']) }}" placeholder="Label" class="theme-input w-full text-xs" />
                                    <input name="homepage_stat_{{ $index }}_value" value="{{ old("homepage_stat_{$index}_value", $settings["homepage_stat_{$index}_value"] ?? $default['value']) }}" placeholder="Value" class="theme-input w-full text-xs" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Intro & Why choose us -->
                <div class="grid gap-6 md:grid-cols-2 mt-6">
                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">Quick Overview Block</h4>
                        <div class="space-y-3">
                            <input name="quick_intro_kicker" value="{{ old('quick_intro_kicker', $settings['quick_intro_kicker'] ?? 'Quick Intro') }}" placeholder="Kicker text" class="theme-input w-full text-xs" />
                            <input name="quick_intro_title" value="{{ old('quick_intro_title', $settings['quick_intro_title'] ?? 'A real school environment for growth and purpose.') }}" placeholder="Headline text" class="theme-input w-full text-xs" />
                            <textarea name="quick_intro_text_1" rows="3" class="theme-input w-full text-xs" placeholder="Paragraph 1">{{ old('quick_intro_text_1', $settings['quick_intro_text_1'] ?? 'Founded in 2006 and located in Ore, Ondo State, BELOVED SCHOOLS is committed to raising educated, disciplined, and God-fearing students.') }}</textarea>
                            <textarea name="quick_intro_text_2" rows="3" class="theme-input w-full text-xs" placeholder="Paragraph 2">{{ old('quick_intro_text_2', $settings['quick_intro_text_2'] ?? 'With professional teachers, modern learning facilities, and a strong moral foundation, we prepare students for success in both academics and life.') }}</textarea>
                        </div>
                    </div>

                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">Why Parents Choose Us Strip</h4>
                        <div class="space-y-3">
                            <input name="why_choose_kicker" value="{{ old('why_choose_kicker', $settings['why_choose_kicker'] ?? 'Why Parents Choose BELOVED SCHOOLS') }}" placeholder="Kicker text" class="theme-input w-full text-xs" />
                            <input name="why_choose_title" value="{{ old('why_choose_title', $settings['why_choose_title'] ?? 'A disciplined school environment built for excellence.') }}" placeholder="Headline text" class="theme-input w-full text-xs" />
                            <textarea name="why_choose_text" rows="4" class="theme-input w-full text-xs" placeholder="Description text">{{ old('why_choose_text', $settings['why_choose_text'] ?? 'BELOVED SCHOOLS combines academic excellence with moral discipline to help students become responsible and purposeful leaders.') }}</textarea>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <input name="why_choose_button_text" value="{{ old('why_choose_button_text', $settings['why_choose_button_text'] ?? 'Read More') }}" placeholder="Button text" class="theme-input w-full text-xs" />
                                <input name="why_choose_button_link" value="{{ old('why_choose_button_link', $settings['why_choose_button_link'] ?? route('about')) }}" placeholder="Button link" class="theme-input w-full text-xs" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Feature Cards -->
                <div class="mt-6 p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">Home Feature Cards</h4>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($featureDefaults as $index => $default)
                            <div class="p-4 bg-white border border-slate-200 rounded-xl space-y-3">
                                <div class="text-[10px] font-bold text-slate-400 border-b border-slate-100 pb-1">Feature card {{ $index }}</div>
                                <input name="home_feature_{{ $index }}_title" value="{{ old("home_feature_{$index}_title", $settings["home_feature_{$index}_title"] ?? $default['title']) }}" placeholder="Card Title" class="theme-input w-full text-xs" />
                                <textarea name="home_feature_{{ $index }}_text" rows="3" class="theme-input w-full text-xs" placeholder="Blurb detail">{{ old("home_feature_{$index}_text", $settings["home_feature_{$index}_text"] ?? $default['text']) }}</textarea>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Academic Section headings -->
                <div class="mt-6 p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">Academic Section Outline</h4>
                    <div class="grid gap-4 sm:grid-cols-2 mb-4">
                        <input name="academic_section_kicker" value="{{ old('academic_section_kicker', $settings['academic_section_kicker'] ?? 'Our Academic Program') }}" placeholder="Academic section kicker" class="theme-input w-full text-xs" />
                        <input name="academic_section_title" value="{{ old('academic_section_title', $settings['academic_section_title'] ?? 'A full secondary school structure with clear pathways.') }}" placeholder="Academic section title" class="theme-input w-full text-xs" />
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($academicCardDefaults as $index => $default)
                            <div class="p-4 bg-white border border-slate-200 rounded-xl space-y-3">
                                <div class="text-[10px] font-bold text-slate-400 border-b border-slate-100 pb-1">Department widget {{ $index }}</div>
                                <input name="academic_card_{{ $index }}_title" value="{{ old("academic_card_{$index}_title", $settings["academic_card_{$index}_title"] ?? $default['title']) }}" placeholder="Widget title" class="theme-input w-full text-xs" />
                                <textarea name="academic_card_{{ $index }}_text" rows="3" class="theme-input w-full text-xs" placeholder="Details info">{{ old("academic_card_{$index}_text", $settings["academic_card_{$index}_text"] ?? $default['text']) }}</textarea>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Founders & Announcements headers -->
                <div class="grid gap-6 lg:grid-cols-2 mt-6">
                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">Founders Brief Block</h4>
                        <div class="space-y-3">
                            <input name="founders_kicker" value="{{ old('founders_kicker', $settings['founders_kicker'] ?? 'Meet the Founders') }}" placeholder="Founders kicker" class="theme-input w-full text-xs" />
                            <input name="founders_title" value="{{ old('founders_title', $settings['founders_title'] ?? 'A school vision built on education and youth development.') }}" placeholder="Founders title" class="theme-input w-full text-xs" />
                            <textarea name="founders_text_1" rows="3" class="theme-input w-full text-xs" placeholder="Founders address par 1">{{ old('founders_text_1', $settings['founders_text_1'] ?? 'BELOVED SCHOOLS was founded by Mr. Zebilon K. S. alongside his wife Mrs. Grace Zebilon, whose passion for education and youth development led to the establishment of the school.') }}</textarea>
                            <textarea name="founders_text_2" rows="3" class="theme-input w-full text-xs" placeholder="Founders address par 2">{{ old('founders_text_2', $settings['founders_text_2'] ?? 'Their vision was to create a learning environment where students can grow academically while being rooted in strong moral and spiritual values.') }}</textarea>
                            <textarea name="founders_values_text" rows="3" class="theme-input w-full text-xs" placeholder="Founders core values statement">{{ old('founders_values_text', $settings['founders_values_text'] ?? 'Core Values: Discipline, Excellence, Integrity, Godliness, Responsibility') }}</textarea>
                        </div>
                    </div>

                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4 flex flex-col justify-between">
                        <div class="space-y-4">
                            <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">News and Gallery Headlines</h4>
                            <div class="space-y-3">
                                <input name="gallery_section_kicker" value="{{ old('gallery_section_kicker', $settings['gallery_section_kicker'] ?? 'Life at BELOVED SCHOOLS') }}" placeholder="Gallery kicker" class="theme-input w-full text-xs" />
                                <input name="gallery_section_title" value="{{ old('gallery_section_title', $settings['gallery_section_title'] ?? 'Life at BELOVED SCHOOLS') }}" placeholder="Gallery title" class="theme-input w-full text-xs" />
                                <textarea name="gallery_section_text" rows="3" class="theme-input w-full text-xs" placeholder="Gallery details text">{{ old('gallery_section_text', $settings['gallery_section_text'] ?? 'Explore moments from our classrooms, events, and student activities that reflect our commitment to excellence and holistic development.') }}</textarea>
                                
                                <input name="news_section_kicker" value="{{ old('news_section_kicker', $settings['news_section_kicker'] ?? 'Latest news') }}" placeholder="News kicker" class="theme-input w-full text-xs" />
                                <input name="news_section_title" value="{{ old('news_section_title', $settings['news_section_title'] ?? 'Announcements and updates') }}" placeholder="News title" class="theme-input w-full text-xs" />
                                <textarea name="news_section_empty_text" rows="3" class="theme-input w-full text-xs" placeholder="News empty state text">{{ old('news_section_empty_text', $settings['news_section_empty_text'] ?? 'School announcements will appear here as BELOVED SCHOOLS shares updates with parents and students.') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Call to Action footer widget -->
                <div class="mt-6 p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">Public Landing CTA Band</h4>
                    <div class="space-y-3">
                        <input name="cta_kicker" value="{{ old('cta_kicker', $settings['cta_kicker'] ?? 'Call to action') }}" placeholder="Kicker text" class="theme-input w-full text-xs" />
                        <input name="cta_title" value="{{ old('cta_title', $settings['cta_title'] ?? 'Give your child the foundation for a successful future.') }}" placeholder="Main Title" class="theme-input w-full text-xs" />
                        <textarea name="cta_text" rows="3" class="theme-input w-full text-xs" placeholder="Description content details">{{ old('cta_text', $settings['cta_text'] ?? 'BELOVED SCHOOLS combines knowledge, discipline, integrity, responsibility, and Godliness to prepare students for meaningful impact.') }}</textarea>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <input name="cta_button_text" value="{{ old('cta_button_text', $settings['cta_button_text'] ?? 'Enroll Today') }}" placeholder="CTA primary button text" class="theme-input w-full text-xs" />
                            <input name="cta_button_link" value="{{ old('cta_button_link', $settings['cta_button_link'] ?? route('admissions')) }}" placeholder="CTA primary button link" class="theme-input w-full text-xs" />
                            <input name="cta_phone_label" value="{{ old('cta_phone_label', $settings['cta_phone_label'] ?? 'Call Us') }}" placeholder="CTA contact label" class="theme-input w-full text-xs" />
                        </div>
                    </div>
                </div>

                <x-slot name="actions">
                    <x-action-button type="submit" variant="success" icon="check">
                        Save Homepage Copy
                    </x-action-button>
                </x-slot>
            </x-form-card>
        </div>

        <!-- 10. Box Backgrounds A -->
        <div x-show="activeSection === 'box-backgrounds-a'">
            <x-form-card
                action="{{ route('admin.settings.update') }}"
                method="POST"
                enctype="multipart/form-data"
                title="Hero and Highlight Wallpapers (Group A)"
                description="Upload custom graphic backdrop layers designed to enhance individual highlights, metrics tiles, and homepage overview widgets."
            >
                <input type="hidden" name="group" value="school">
                <input type="hidden" name="settings_section" value="box-backgrounds-a">

                <!-- Highlight Backgrounds -->
                <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">Hero Feature Highlights Wallpapers</h4>
                    <div class="grid gap-4 sm:grid-cols-3">
                        @foreach ($heroHighlightDefaults as $index => $default)
                            <div class="p-4 bg-white border border-slate-200 rounded-xl space-y-3">
                                <div class="text-[10px] font-bold text-slate-400 border-b border-slate-100 pb-1">Highlight {{ $index }} Backdrop</div>
                                <input type="file" name="hero_highlight_{{ $index }}_background" accept="image/*" class="block w-full text-[10px]">
                                @if (! empty($settings["hero_highlight_{$index}_background"]))
                                    <div class="h-20 w-full rounded-lg overflow-hidden border border-slate-100">
                                        <img src="{{ asset($settings["hero_highlight_{$index}_background"]) }}" alt="Highlight {{ $index }} wallpaper" class="h-full w-full object-cover">
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Stats Backgrounds -->
                <div class="mt-6 p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">homepage Stats Box Backdrops</h4>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($homepageStatDefaults as $index => $default)
                            <div class="p-4 bg-white border border-slate-200 rounded-xl space-y-3">
                                <div class="text-[10px] font-bold text-slate-400 border-b border-slate-100 pb-1">Stat {{ $index }} Box Backdrop</div>
                                <input type="file" name="homepage_stat_{{ $index }}_background" accept="image/*" class="block w-full text-[10px]">
                                @if (! empty($settings["homepage_stat_{$index}_background"]))
                                    <div class="h-20 w-full rounded-lg overflow-hidden border border-slate-100">
                                        <img src="{{ asset($settings["homepage_stat_{$index}_background"]) }}" alt="Stat {{ $index }} wallpaper" class="h-full w-full object-cover">
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Feature & Intro backgrounds -->
                <div class="grid gap-6 lg:grid-cols-[0.8fr,1.2fr] mt-6">
                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">Quick Intro Backdrop</h4>
                        <input type="file" name="quick_intro_background_image" accept="image/*" class="block w-full text-[10px]">
                        @if (! empty($settings['quick_intro_background_image']))
                            <div class="h-28 w-full rounded-lg overflow-hidden border border-slate-200 bg-white p-1">
                                <img src="{{ asset($settings['quick_intro_background_image']) }}" alt="Intro wallpaper" class="h-full w-full object-cover rounded">
                            </div>
                        @endif
                    </div>

                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">Home Feature Cards Backdrops</h4>
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach ($featureDefaults as $index => $default)
                                <div class="p-3 bg-white border border-slate-200 rounded-xl space-y-2">
                                    <div class="text-[10px] font-bold text-slate-400">Card {{ $index }} Backdrop</div>
                                    <input type="file" name="home_feature_{{ $index }}_background" accept="image/*" class="block w-full text-[10px]">
                                    @if (! empty($settings["home_feature_{$index}_background"]))
                                        <div class="h-16 w-full rounded-lg overflow-hidden border border-slate-100">
                                            <img src="{{ asset($settings["home_feature_{$index}_background"]) }}" alt="Feature {{ $index }} wallpaper" class="h-full w-full object-cover">
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <x-slot name="actions">
                    <x-action-button type="submit" variant="success" icon="check">
                        Save Wallpapers Group A
                    </x-action-button>
                </x-slot>
            </x-form-card>
        </div>

        <!-- 11. Box Backgrounds B -->
        <div x-show="activeSection === 'box-backgrounds-b'">
            <x-form-card
                action="{{ route('admin.settings.update') }}"
                method="POST"
                enctype="multipart/form-data"
                title="Academic, Founders and Sections Wallpapers (Group B)"
                description="Upload custom graphic backdrop layers designed to enhance individual academic class widgets, founder profiles, and news blocks."
            >
                <input type="hidden" name="group" value="school">
                <input type="hidden" name="settings_section" value="box-backgrounds-b">

                <!-- Academic Section Backgrounds -->
                <div class="grid gap-6 lg:grid-cols-[0.8fr,1.2fr]">
                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">Academic Section Main Backdrop</h4>
                        <input type="file" name="academic_section_background_image" accept="image/*" class="block w-full text-[10px]">
                        @if (! empty($settings['academic_section_background_image']))
                            <div class="h-28 w-full rounded-lg overflow-hidden border border-slate-200 bg-white p-1">
                                <img src="{{ asset($settings['academic_section_background_image']) }}" alt="Academic overview wallpaper" class="h-full w-full object-cover rounded">
                            </div>
                        @endif
                    </div>

                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">Individual Class/widget Backdrops</h4>
                        <div class="grid gap-3 sm:grid-cols-2 md:grid-cols-3">
                            @foreach ($academicCardDefaults as $index => $default)
                                <div class="p-3 bg-white border border-slate-200 rounded-xl space-y-2">
                                    <div class="text-[10px] font-bold text-slate-400">widget {{ $index }} Backdrop</div>
                                    <input type="file" name="academic_card_{{ $index }}_background" accept="image/*" class="block w-full text-[10px]">
                                    @if (! empty($settings["academic_card_{$index}_background"]))
                                        <div class="h-16 w-full rounded-lg overflow-hidden border border-slate-100">
                                            <img src="{{ asset($settings["academic_card_{$index}_background"]) }}" alt="Academic card {{ $index }} wallpaper" class="h-full w-full object-cover">
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Key Blocks Wallpapers -->
                <div class="mt-6 p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">Founders, Gallery, and News Card Wallpapers</h4>
                    <div class="grid gap-4 sm:grid-cols-3">
                        @foreach ([
                            'founders_background_image' => 'Founders box background',
                            'gallery_section_background_image' => 'Gallery box background',
                            'news_section_background_image' => 'Latest news box background',
                        ] as $field => $label)
                            <div class="p-4 bg-white border border-slate-200 rounded-xl space-y-3">
                                <div class="text-[10px] font-bold text-slate-400 border-b border-slate-100 pb-1">{{ $label }}</div>
                                <input type="file" name="{{ $field }}" accept="image/*" class="block w-full text-[10px]">
                                @if (! empty($settings[$field]))
                                    <div class="h-24 w-full rounded-lg overflow-hidden border border-slate-100">
                                        <img src="{{ asset($settings[$field]) }}" alt="{{ $label }} wallpaper" class="h-full w-full object-cover">
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <x-slot name="actions">
                    <x-action-button type="submit" variant="success" icon="check">
                        Save Wallpapers Group B
                    </x-action-button>
                </x-slot>
            </x-form-card>
        </div>

        <!-- 12. Payment Settings -->
        <div x-show="activeSection === 'payment-settings'">
            <x-form-card
                action="{{ route('admin.settings.update') }}"
                method="POST"
                title="Payment Gateways Integration"
                description="Configure integration parameters for secure automated Paystack, PalmPay, and manual bank transfers."
            >
                <input type="hidden" name="group" value="payments">
                <input type="hidden" name="settings_section" value="payment-settings">

                <div class="grid gap-6 md:grid-cols-2">
                    <!-- Paystack integration -->
                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                        <div class="flex items-center gap-3 border-b border-slate-200 pb-2">
                            <div class="w-8 h-8 rounded-lg bg-teal-50 flex items-center justify-center text-teal-600 font-bold shrink-0">P</div>
                            <h4 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Paystack Credentials</h4>
                        </div>
                        <div class="space-y-3">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-slate-700 uppercase tracking-wider block">Public Key</label>
                                <input name="paystack_public_key" value="{{ old('paystack_public_key', $settings['paystack_public_key'] ?? '') }}" placeholder="pk_live_..." class="theme-input w-full text-xs" />
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-slate-700 uppercase tracking-wider block">Secret Key</label>
                                <input name="paystack_secret_key" value="{{ old('paystack_secret_key', $settings['paystack_secret_key'] ?? '') }}" placeholder="sk_live_..." class="theme-input w-full text-xs" />
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-slate-700 uppercase tracking-wider block">Webhook Secret Signature</label>
                                <input name="paystack_webhook_secret" value="{{ old('paystack_webhook_secret', $settings['paystack_webhook_secret'] ?? '') }}" placeholder="whsec_..." class="theme-input w-full text-xs" />
                            </div>
                        </div>
                    </div>

                    <!-- PalmPay integration -->
                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                        <div class="flex items-center gap-3 border-b border-slate-200 pb-2">
                            <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center text-purple-600 font-bold shrink-0">PP</div>
                            <h4 class="text-sm font-bold text-slate-900 uppercase tracking-wider">PalmPay Merchant Settings</h4>
                        </div>
                        <div class="space-y-3">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-slate-700 uppercase tracking-wider block">Merchant ID</label>
                                <input name="palmpay_merchant_id" value="{{ old('palmpay_merchant_id', $settings['palmpay_merchant_id'] ?? '') }}" placeholder="PalmPay Merchant ID" class="theme-input w-full text-xs" />
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-slate-700 uppercase tracking-wider block">App ID</label>
                                <input name="palmpay_app_id" value="{{ old('palmpay_app_id', $settings['palmpay_app_id'] ?? '') }}" placeholder="PalmPay App ID" class="theme-input w-full text-xs" />
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-slate-700 uppercase tracking-wider block">Checkout API URL</label>
                                <input name="palmpay_checkout_url" value="{{ old('palmpay_checkout_url', $settings['palmpay_checkout_url'] ?? '') }}" placeholder="https://api.palmpay.com/..." class="theme-input w-full text-xs" />
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-slate-700 uppercase tracking-wider block">PalmPay Public Key</label>
                                <textarea name="palmpay_public_key" rows="2" placeholder="Public key certificate" class="theme-input w-full text-xs">{{ old('palmpay_public_key', $settings['palmpay_public_key'] ?? '') }}</textarea>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-slate-700 uppercase tracking-wider block">PalmPay Private Key</label>
                                <textarea name="palmpay_private_key" rows="2" placeholder="Private key certificate" class="theme-input w-full text-xs">{{ old('palmpay_private_key', $settings['palmpay_private_key'] ?? '') }}</textarea>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-slate-700 uppercase tracking-wider block">PalmPay Webhook Secret</label>
                                <input name="palmpay_webhook_secret" value="{{ old('palmpay_webhook_secret', $settings['palmpay_webhook_secret'] ?? '') }}" placeholder="PalmPay webhook verification key" class="theme-input w-full text-xs" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bank Accounts Grid -->
                <div class="mt-6 p-5 rounded-2xl border border-slate-200 bg-slate-50 space-y-4">
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">Manual Bank Transfers (Portal Display)</h4>
                    <p class="text-[10px] text-slate-500">Provide official accounts shown to parents and staff under manual checkout settings.</p>
                    <div class="grid gap-4 sm:grid-cols-3">
                        @foreach (range(1, 3) as $index)
                            <div class="p-4 bg-white border border-slate-200 rounded-xl space-y-3">
                                <div class="text-[10px] font-bold text-slate-400 border-b border-slate-100 pb-1 uppercase tracking-wider">Account {{ $index }}</div>
                                <input name="bank_name_{{ $index }}" value="{{ old("bank_name_{$index}", $settings["bank_name_{$index}"] ?? '') }}" placeholder="Bank name" class="theme-input w-full text-xs" />
                                <input name="account_name_{{ $index }}" value="{{ old("account_name_{$index}", $settings["account_name_{$index}"] ?? '') }}" placeholder="Account name" class="theme-input w-full text-xs" />
                                <input name="account_number_{{ $index }}" value="{{ old("account_number_{$index}", $settings["account_number_{$index}"] ?? '') }}" placeholder="Account number" class="theme-input w-full text-xs" />
                            </div>
                        @endforeach
                    </div>

                    <div class="space-y-1 mt-4">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Checkout Instructions</label>
                        <textarea name="payment_instruction" rows="3" placeholder="Optional payment instruction shown in portal and finance pages" class="theme-input w-full text-xs">{{ old('payment_instruction', $settings['payment_instruction'] ?? '') }}</textarea>
                    </div>
                </div>

                <x-slot name="actions">
                    <x-action-button type="submit" variant="success" icon="check">
                        Save Payment Credentials
                    </x-action-button>
                </x-slot>
            </x-form-card>
        </div>

        <!-- 13. Contact Messages -->
        <div x-show="activeSection === 'contact-messages'">
            <x-dashboard-card
                title="Recent Public Contact Messages"
                subtitle="View the latest queries submitted by visitors and prospective parents."
                icon="message-square"
                accent="blue"
            >
                <div class="space-y-6">
                    @forelse ($messages as $message)
                        <article class="p-5 rounded-2xl border border-slate-200 bg-slate-50 transition hover:border-[#fbbf24] hover:bg-white shadow-sm flex flex-col justify-between">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-200/60 pb-2 mb-3">
                                <div>
                                    <span class="font-extrabold text-sm text-slate-900 block sm:inline">{{ $message->name }}</span>
                                    <span class="text-[10px] text-slate-500 sm:ml-2">({{ $message->email }} @if($message->phone) | {{ $message->phone }} @endif)</span>
                                </div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest sm:shrink-0 mt-1 sm:mt-0">{{ $message->created_at->format('d M Y, h:i A') }}</span>
                            </div>
                            <div class="text-xs font-bold text-[#071833] uppercase tracking-wider mb-2">Subject: {{ $message->subject }}</div>
                            <p class="text-xs text-slate-600 leading-relaxed bg-white border border-slate-100 p-4 rounded-xl shadow-inner whitespace-pre-line">{{ $message->message }}</p>
                        </article>
                    @empty
                        <x-empty-state
                            title="No Incoming Messages"
                            description="Public contact form submissions will appear here once prospective visitors submit them."
                            icon="mail"
                        />
                    @endforelse
                </div>
            </x-dashboard-card>
        </div>
    </div>
</x-portal-layout>
