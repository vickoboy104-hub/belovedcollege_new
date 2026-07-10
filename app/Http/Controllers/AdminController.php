<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\AcademicSession;
use App\Models\Announcement;
use App\Models\ContactMessage;
use App\Models\FeeInvoice;
use App\Models\FeeItem;
use App\Models\Assessment;
use App\Models\SchoolClass;
use App\Models\Setting;
use App\Models\StaffProfile;
use App\Models\Student;
use App\Models\StudentPromotion;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use App\Services\PromotionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function settings(Request $request, ?string $section = null): View
    {
        $sections = collect([
            'website-foundation',
            'theme-colors',
            'landing-builder',
            'homepage-media',
            'workspace-backgrounds',
            'site-backgrounds',
            'welcome-popup',
            'gallery-uploader',
            'homepage-text',
            'box-backgrounds-a',
            'box-backgrounds-b',
            'payment-settings',
            'contact-messages',
        ]);
        $activeSettingsSection = $sections->contains($section) ? $section : 'website-foundation';

        return view('admin.settings', [
            'settings' => Setting::pluck('value', 'key'),
            'messages' => ContactMessage::latest()->take(10)->get(),
            'activeSettingsSection' => $activeSettingsSection,
            'landingBuilderStats' => [
                'students' => Student::count(),
                'staff' => StaffProfile::count(),
                'classes' => SchoolClass::count(),
                'news' => Announcement::where('is_published', true)->count(),
            ],
            'latestLandingAnnouncements' => Announcement::query()
                ->where('is_published', true)
                ->latest('published_at')
                ->take(4)
                ->get(),
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $rules = [
            'group' => ['required', 'string', 'max:100'],
            'settings_section' => ['nullable', 'string', 'max:100'],
            'school_name' => ['nullable', 'string', 'max:255'], 'motto' => ['nullable', 'string', 'max:255'], 'site_tagline' => ['nullable', 'string', 'max:255'], 'site_subtitle' => ['nullable', 'string', 'max:255'],
            'school_email' => ['nullable', 'email', 'max:255'], 'school_phone' => ['nullable', 'string', 'max:255'], 'school_address' => ['nullable', 'string', 'max:500'],
            'whatsapp_number' => ['nullable', 'string', 'max:255'], 'whatsapp_link' => ['nullable', 'string', 'max:500'], 'contact_email_recipient' => ['nullable', 'email', 'max:255'],
            'mail_mailer' => ['nullable', 'string', 'max:50'], 'mail_host' => ['nullable', 'string', 'max:255'], 'mail_port' => ['nullable', 'numeric', 'min:1', 'max:65535'],
            'mail_username' => ['nullable', 'string', 'max:255'], 'mail_password' => ['nullable', 'string', 'max:255'], 'mail_encryption' => ['nullable', 'string', 'max:50'],
            'mail_from_address' => ['nullable', 'email', 'max:255'], 'mail_from_name' => ['nullable', 'string', 'max:255'], 'principal_name' => ['nullable', 'string', 'max:255'],
            'hero_blurb' => ['nullable', 'string', 'max:1000'], 'portal_notice' => ['nullable', 'string', 'max:1000'],
            'theme_preset' => ['nullable', Rule::in(['light-corporate', 'dark-corporate', 'colourful-professional', 'custom'])], 'theme_primary' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'], 'theme_secondary' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'], 'theme_accent' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'], 'theme_highlight' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'], 'theme_text' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'], 'theme_surface' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'], 'theme_soft' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'], 'top_bar_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_page_bg' => ['nullable', 'string', 'max:50'],
            'theme_sidebar_bg' => ['nullable', 'string', 'max:50'],
            'theme_topbar_bg' => ['nullable', 'string', 'max:50'],
            'theme_sidebar_active' => ['nullable', 'string', 'max:50'],
            'theme_card_students' => ['nullable', 'string', 'max:50'],
            'theme_card_staff' => ['nullable', 'string', 'max:50'],
            'theme_card_invoices' => ['nullable', 'string', 'max:50'],
            'theme_card_payments' => ['nullable', 'string', 'max:50'],
            'theme_panel_quick' => ['nullable', 'string', 'max:50'],
            'theme_card_reg_student' => ['nullable', 'string', 'max:50'],
            'theme_card_add_parent' => ['nullable', 'string', 'max:50'],
            'theme_panel_announcements' => ['nullable', 'string', 'max:50'],
            'theme_card_announcement' => ['nullable', 'string', 'max:50'],
            'theme_panel_finance' => ['nullable', 'string', 'max:50'],
            'theme_finance_students' => ['nullable', 'string', 'max:50'],
            'theme_finance_billed' => ['nullable', 'string', 'max:50'],
            'theme_finance_collected' => ['nullable', 'string', 'max:50'],
            'theme_finance_outstanding' => ['nullable', 'string', 'max:50'],
            'theme_button_bg' => ['nullable', 'string', 'max:50'],
            'theme_button_text' => ['nullable', 'string', 'max:50'],
            'theme_icon_bg' => ['nullable', 'string', 'max:50'],
            'theme_icon_fg' => ['nullable', 'string', 'max:50'],
            'theme_text_main' => ['nullable', 'string', 'max:50'],
            'theme_text_heading' => ['nullable', 'string', 'max:50'],
            'theme_text_muted' => ['nullable', 'string', 'max:50'],
            'theme_text_dark_card' => ['nullable', 'string', 'max:50'],
            'theme_text_light_bg' => ['nullable', 'string', 'max:50'],
            'theme_sidebar_text' => ['nullable', 'string', 'max:50'],
            'theme_sidebar_active_text' => ['nullable', 'string', 'max:50'],
            'theme_card_title_text' => ['nullable', 'string', 'max:50'],
            'theme_card_body_text' => ['nullable', 'string', 'max:50'],
            'theme_table_text' => ['nullable', 'string', 'max:50'],
            'theme_form_label' => ['nullable', 'string', 'max:50'],
            'theme_form_placeholder' => ['nullable', 'string', 'max:50'],
            'theme_badge_text' => ['nullable', 'string', 'max:50'],
            'theme_link_text' => ['nullable', 'string', 'max:50'],
            'theme_border' => ['nullable', 'string', 'max:50'],
            'theme_shadow_strength' => ['nullable', 'string', 'max:50'],
            'theme_progress_bar' => ['nullable', 'string', 'max:50'],
            'theme_progress_track' => ['nullable', 'string', 'max:50'],
            'site_background_1_opacity' => ['nullable', 'numeric', 'min:0', 'max:100'], 'site_background_2_opacity' => ['nullable', 'numeric', 'min:0', 'max:100'], 'site_background_3_opacity' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'welcome_popup_enabled' => ['nullable', 'boolean'], 'landing_use_live_stats' => ['nullable', 'boolean'], 'landing_use_latest_announcements' => ['nullable', 'boolean'], 'landing_show_newsletter' => ['nullable', 'boolean'], 'welcome_popup_title' => ['nullable', 'string', 'max:255'], 'welcome_popup_text' => ['nullable', 'string', 'max:1000'], 'welcome_popup_button_text' => ['nullable', 'string', 'max:255'], 'welcome_popup_button_link' => ['nullable', 'string', 'max:500'],
            'paystack_public_key' => ['nullable', 'string', 'max:255'], 'paystack_secret_key' => ['nullable', 'string', 'max:255'], 'paystack_webhook_secret' => ['nullable', 'string', 'max:255'],
            'palmpay_merchant_id' => ['nullable', 'string', 'max:255'], 'palmpay_app_id' => ['nullable', 'string', 'max:255'], 'palmpay_public_key' => ['nullable', 'string', 'max:5000'], 'palmpay_private_key' => ['nullable', 'string', 'max:5000'], 'palmpay_webhook_secret' => ['nullable', 'string', 'max:255'], 'palmpay_checkout_url' => ['nullable', 'url', 'max:500'],
            'payment_instruction' => ['nullable', 'string', 'max:2000'],
            'hero_intro_background_opacity' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
        foreach (range(1, 3) as $i) {
            $rules["bank_name_{$i}"] = ['nullable', 'string', 'max:255'];
            $rules["account_name_{$i}"] = ['nullable', 'string', 'max:255'];
            $rules["account_number_{$i}"] = ['nullable', 'string', 'max:50'];
        }
        foreach ([
            'logo_file','favicon_file','hero_background_video_poster','hero_intro_background_image','welcome_popup_image','hero_slide_1_image','hero_slide_2_image','hero_slide_3_image','hero_slide_4_image','hero_slide_5_image','gallery_image_1','gallery_image_2','gallery_image_3','gallery_image_4','admin_background_image','site_background_1','site_background_2','site_background_3','section_background_1','section_background_2','section_background_3','quick_intro_background_image','academic_section_background_image','founders_background_image','gallery_section_background_image','news_section_background_image'
        ] as $imageKey) $rules[$imageKey] = ['nullable','image','max:51200'];
        foreach (range(1, 5) as $i) {
            $rules["hero_slide_{$i}_title"] = ['nullable', 'string', 'max:255'];
            $rules["hero_slide_{$i}_text"] = ['nullable', 'string', 'max:1000'];
            $rules["hero_slide_{$i}_video"] = ['nullable', 'file', 'mimes:mp4,webm,mov,m4v', 'max:51200'];
        }
        $rules['hero_background_video'] = ['nullable','file','mimes:mp4,webm,mov,m4v','max:51200'];
        foreach (range(1,3) as $i) { $rules["hero_highlight_{$i}_text"]=['nullable','string','max:255']; $rules["hero_highlight_{$i}_background"]=['nullable','image','max:51200']; }
        foreach (range(1,4) as $i) { $rules["homepage_stat_{$i}_label"]=['nullable','string','max:255']; $rules["homepage_stat_{$i}_value"]=['nullable','string','max:255']; $rules["homepage_stat_{$i}_background"]=['nullable','image','max:51200']; $rules["home_feature_{$i}_title"]=['nullable','string','max:255']; $rules["home_feature_{$i}_text"]=['nullable','string','max:1000']; $rules["home_feature_{$i}_background"]=['nullable','image','max:51200']; }
        foreach (range(1,6) as $i) { $rules["academic_card_{$i}_title"]=['nullable','string','max:255']; $rules["academic_card_{$i}_text"]=['nullable','string','max:1000']; $rules["academic_card_{$i}_background"]=['nullable','image','max:51200']; }
        foreach (['quick_intro_kicker','quick_intro_title','quick_intro_text_1','quick_intro_text_2','why_choose_kicker','why_choose_title','why_choose_text','why_choose_button_text','why_choose_button_link','academic_section_kicker','academic_section_title','founders_kicker','founders_title','founders_text_1','founders_text_2','founders_values_text','gallery_section_kicker','gallery_section_title','gallery_section_text','news_section_kicker','news_section_title','news_section_empty_text','cta_kicker','cta_title','cta_text','cta_button_text','cta_button_link','cta_phone_label'] as $textKey) $rules[$textKey] = ['nullable','string','max:2000'];
        foreach (range(1,5) as $i) {
            foreach (['eyebrow','title','emphasis','text','primary','primary_link','secondary','secondary_link','label'] as $field) {
                $rules["landing_slide_{$i}_{$field}"] = ['nullable', 'string', 'max:'.(str_contains($field, 'link') ? '500' : '1000')];
            }
        }
        foreach (range(1,4) as $i) {
            $rules["landing_stat_{$i}_label"] = ['nullable', 'string', 'max:255'];
            $rules["landing_stat_{$i}_value"] = ['nullable', 'string', 'max:255'];
            foreach (['month','day','title','text','tag_1','tag_2'] as $field) {
                $rules["landing_event_{$i}_{$field}"] = ['nullable', 'string', 'max:'.($field === 'text' ? '1000' : '255')];
            }
        }
        foreach (range(1,7) as $i) {
            foreach (['badge','title','text','link'] as $field) {
                $rules["landing_program_{$i}_{$field}"] = ['nullable', 'string', 'max:'.($field === 'text' ? '1000' : '500')];
            }
        }
        foreach (range(1,6) as $i) {
            $rules["landing_gallery_{$i}_label"] = ['nullable', 'string', 'max:255'];
        }
        foreach (range(1,3) as $i) {
            foreach (['initials','name','role','text'] as $field) {
                $rules["landing_testimonial_{$i}_{$field}"] = ['nullable', 'string', 'max:'.($field === 'text' ? '1000' : '255')];
            }
        }
        foreach ([
            'landing_admission_kicker','landing_admission_title','landing_admission_text','landing_admission_primary_text','landing_admission_support_text','landing_admission_whatsapp_text',
            'landing_welcome_kicker','landing_welcome_title','landing_welcome_text_1','landing_welcome_text_2','landing_welcome_profile_button_text',
            'landing_programs_kicker','landing_programs_title','landing_programs_emphasis','landing_programs_text','landing_programs_button_text',
            'landing_events_kicker','landing_events_title','landing_events_emphasis','landing_events_sidebar_title','landing_events_sidebar_text','landing_events_sidebar_button_text',
            'landing_gallery_kicker','landing_gallery_title','landing_gallery_emphasis','landing_gallery_button_text',
            'landing_testimonials_kicker','landing_testimonials_title','landing_testimonials_emphasis',
            'landing_newsletter_title','landing_newsletter_text','landing_newsletter_placeholder','landing_newsletter_button_text','landing_newsletter_subscribed_text',
        ] as $textKey) $rules[$textKey] = ['nullable', 'string', 'max:2000'];
        $validated = $request->validate($rules);
        $group = $validated['group']; unset($validated['group']);
        $settingsSection = $validated['settings_section'] ?? null; unset($validated['settings_section']);

        $booleanSections = [
            'welcome_popup_enabled' => 'welcome-popup',
            'landing_use_live_stats' => 'landing-builder',
            'landing_use_latest_announcements' => 'landing-builder',
            'landing_show_newsletter' => 'landing-builder',
        ];
        foreach ($booleanSections as $key => $section) {
            if ($settingsSection === $section || $request->has($key)) {
                $validated[$key] = $request->boolean($key);
            } else {
                unset($validated[$key]);
            }
        }
        $uploadMap = ['logo_file' => 'logo_path', 'favicon_file' => 'favicon_path'];
        foreach (array_keys($rules) as $key) if (str_contains($key, 'image') || str_contains($key, 'background') || str_contains($key, 'video')) $uploadMap[$key] ??= $key;
        $uploadMap['hero_background_video_poster'] = 'hero_background_video_poster';
        foreach ($uploadMap as $input => $settingKey) { unset($validated[$input]); if ($request->hasFile($input)) $validated[$settingKey] = $this->saveUploadedAsset($request->file($input), $settingKey); }
        Setting::setMany(array_filter($validated, fn ($value) => $value !== null), $group);
        return back()->with('status', 'Settings saved successfully.');
    }

    public function people(Request $request): View
    {
        $studentCount = Student::count();
        $staffCount = StaffProfile::count();
        $parentCount = Student::query()->whereNotNull('parent_user_id')->distinct()->count('parent_user_id');
        $classCount = SchoolClass::count();

        return view('admin.people', compact('studentCount', 'staffCount', 'parentCount', 'classCount'));
    }

    public function parents(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $students = Student::query()
            ->with('user', 'parent', 'schoolClass')
            ->whereNotNull('parent_user_id')
            ->get();

        $parentRows = $students
            ->groupBy('parent_user_id')
            ->map(function ($group) {
                /** @var \App\Models\Student $primaryStudent */
                $primaryStudent = $group->first();
                $parent = $primaryStudent->parent;
                $children = $group
                    ->sortBy(fn (Student $student) => $student->user->fullName())
                    ->values();

                return [
                    'parent' => $parent,
                    'children' => $children,
                    'child_count' => $children->count(),
                    'class_names' => $children
                        ->map(fn (Student $student) => $student->schoolClass->display_name ?? 'No class')
                        ->unique()
                        ->values(),
                ];
            })
            ->filter(fn (array $row) => $row['parent'])
            ->values();

        if ($search !== '') {
            $needle = Str::lower($search);

            $parentRows = $parentRows->filter(function (array $row) use ($needle) {
                $parent = $row['parent'];
                $children = $row['children'];
                $haystack = Str::lower(implode(' ', array_filter([
                    $parent->fullName(),
                    $parent->email,
                    $parent->phone,
                    $children->pluck('user.name')->implode(' '),
                    $children->pluck('admission_no')->implode(' '),
                    $row['class_names']->implode(' '),
                ])));

                foreach (array_filter(explode(' ', $needle)) as $word) {
                    if (! str_contains($haystack, $word)) {
                        return false;
                    }
                }
                return true;
            })->values();
        }

        $summary = [
            'linkedParents' => $parentRows->count(),
            'childrenCovered' => $parentRows->sum('child_count'),
            'multiChildFamilies' => $parentRows->filter(fn (array $row) => $row['child_count'] > 1)->count(),
            'studentsWithoutPortalParent' => Student::query()->whereNull('parent_user_id')->count(),
        ];

        return view('admin.parents', compact('parentRows', 'search', 'summary'));
    }

    public function students(Request $request, ?string $classSlug = null): View
    {
        $classSlug = $classSlug ?: trim((string) $request->string('classSlug'));
        $classSlug = $classSlug !== '' ? $classSlug : null;
        $search = trim((string) $request->string('search'));
        $statusFilter = trim((string) $request->string('status'));
        $billingStatusFilter = trim((string) $request->string('billing_status'));
        $studentViews = collect([
            'directory',
            'new-students',
            'inactive',
            'siblings',
            'debtors',
            'class-bills',
        ]);
        $activeStudentView = $studentViews->contains($request->string('view')->toString())
            ? $request->string('view')->toString()
            : 'directory';
        $classes = SchoolClass::orderBy('name')->orderBy('section')->get();
        $allStudents = Student::with([
            'user',
            'schoolClass',
            'parent',
            'academicSession',
            'feeInvoices.feeItem',
            'feeInvoices.payments',
        ])->get();
        $activeClass = null;

        if ($classSlug && $classSlug !== 'all' && $classSlug !== 'unassigned') {
            $activeClass = $classes->firstWhere('slug', $classSlug);
            abort_unless($activeClass, 404);
        }

        $students = $allStudents
            ->when($activeClass, fn (Collection $rows) => $rows->where('school_class_id', $activeClass->id))
            ->when($classSlug === 'unassigned', fn (Collection $rows) => $rows->whereNull('school_class_id'))
            ->values();

        if ($statusFilter !== '') {
            $students = $students
                ->filter(fn (Student $student) => ($student->status ?? $student->user->status) === $statusFilter)
                ->values();
        }

        if ($billingStatusFilter !== '') {
            $students = $students
                ->filter(function (Student $student) use ($billingStatusFilter) {
                    $balance = (float) $student->feeInvoices->sum('balance');
                    $overpayment = (float) $student->feeInvoices->sum(fn (FeeInvoice $invoice) => max((float) $invoice->amount_paid - (float) $invoice->amount_due, 0));

                    return match ($billingStatusFilter) {
                        'debtors' => $balance > 0,
                        'clear' => $balance <= 0,
                        'overpaid' => $overpayment > 0,
                        default => true,
                    };
                })
                ->values();
        }

        if ($search !== '') {
            $students = $students
                ->filter(fn (Student $student) => $this->matchesStudentSearch($student, $search))
                ->values();
        }

        $studentTotalCount = $students->count();
        $studentPerPage = 10;
        $studentPageCount = max(1, (int) ceil($studentTotalCount / $studentPerPage));
        $studentPage = min(max(1, (int) $request->integer('page', 1)), $studentPageCount);
        $students = $students->forPage($studentPage, $studentPerPage)->values();
        $studentShowingFrom = $studentTotalCount > 0 ? (($studentPage - 1) * $studentPerPage) + 1 : 0;
        $studentShowingTo = min($studentTotalCount, $studentPage * $studentPerPage);

        $studentGroups = $students->groupBy(fn ($s) => $s->schoolClass->display_name ?? 'Unassigned');
        $filterState = array_filter([
            'view' => $activeStudentView,
            'search' => $search,
            'status' => $statusFilter,
            'billing_status' => $billingStatusFilter,
        ], fn ($value) => $value !== null && $value !== '');
        $classDirectory = $classes
            ->map(fn (SchoolClass $class) => [
                'key' => $class->slug,
                'name' => $class->display_name,
                'count' => $allStudents->where('school_class_id', $class->id)->count(),
                'href' => route('admin.students.index', ['classSlug' => $class->slug, 'view' => 'directory']),
            ])
            ->values();
        $unassignedCount = $allStudents->whereNull('school_class_id')->count();

        if ($unassignedCount > 0) {
            $classDirectory->push([
                'key' => 'unassigned',
                'name' => 'Unassigned',
                'count' => $unassignedCount,
                'href' => route('admin.students.index', ['classSlug' => 'unassigned', 'view' => 'directory']),
            ]);
        }

        $classNavItems = collect([
            ['key' => 'all', 'label' => 'All Students', 'href' => route('admin.students.index', $filterState)],
            ...$classes->map(fn (SchoolClass $class) => [
                'key' => $class->slug,
                'label' => $class->display_name,
                'href' => route('admin.students.index', ['classSlug' => $class->slug] + $filterState),
            ])->all(),
        ]);

        if (Student::query()->whereNull('school_class_id')->exists()) {
            $classNavItems->push([
                'key' => 'unassigned',
                'label' => 'Unassigned',
                'href' => route('admin.students.index', ['classSlug' => 'unassigned'] + $filterState),
            ]);
        }

        $studentContext = array_filter([
            'classSlug' => $classSlug,
            'search' => $search,
            'status' => $statusFilter,
            'billing_status' => $billingStatusFilter,
        ], fn ($value) => $value !== null && $value !== '');
        $studentOfficeNavItems = [
            ['key' => 'directory', 'label' => 'Directory', 'href' => route('admin.students.index', $studentContext + ['view' => 'directory'])],
            ['key' => 'new-students', 'label' => 'New Students', 'href' => route('admin.students.index', $studentContext + ['view' => 'new-students'])],
            ['key' => 'inactive', 'label' => 'Inactive', 'href' => route('admin.students.index', $studentContext + ['view' => 'inactive'])],
            ['key' => 'siblings', 'label' => 'Siblings', 'href' => route('admin.students.index', $studentContext + ['view' => 'siblings'])],
            ['key' => 'debtors', 'label' => 'Debtors', 'href' => route('admin.students.index', $studentContext + ['view' => 'debtors'])],
            ['key' => 'class-bills', 'label' => 'Class Bills', 'href' => route('admin.students.index', $studentContext + ['view' => 'class-bills'])],
        ];

        $currentSessionId = AcademicSession::query()->where('is_current', true)->value('id');
        $newStudents = $allStudents
            ->filter(fn (Student $student) => $this->isNewStudent($student, $currentSessionId))
            ->when($search !== '', fn (Collection $rows) => $rows->filter(fn (Student $student) => $this->matchesStudentSearch($student, $search)))
            ->sortByDesc(fn (Student $student) => optional($student->enrolled_at)->timestamp ?? $student->created_at?->timestamp ?? 0)
            ->values();
        $inactiveStudents = $allStudents
            ->filter(fn (Student $student) => ($student->status ?? $student->user->status) === 'inactive')
            ->when($search !== '', fn (Collection $rows) => $rows->filter(fn (Student $student) => $this->matchesStudentSearch($student, $search)))
            ->values();
        $siblingRows = $this->buildSiblingRows($allStudents, $search);
        $studentDebtorRows = $this->buildStudentDebtorRows($allStudents, $search);
        $studentClassBillingRows = $this->buildStudentClassBillingRows($classes, $allStudents);
        $studentWorkspaceStats = [
            'total' => $allStudents->count(),
            'active' => $allStudents->where('status', 'active')->count(),
            'new' => $allStudents->filter(fn (Student $student) => $this->isNewStudent($student, $currentSessionId))->count(),
            'inactive' => $allStudents->where('status', 'inactive')->count(),
            'sibling_families' => $allStudents->whereNotNull('parent_user_id')->groupBy('parent_user_id')->filter(fn (Collection $group) => $group->count() > 1)->count(),
            'debtors' => $this->buildStudentDebtorRows($allStudents)->count(),
        ];

        $activeStudentClassPage = $activeClass?->slug ?? ($classSlug === 'unassigned' ? 'unassigned' : 'all');
        $pageTitle = match ($activeStudentView) {
            'new-students' => 'Newly Registered Students',
            'inactive' => 'Inactive Students',
            'siblings' => 'Sibling Groups',
            'debtors' => 'Student Debtors',
            'class-bills' => 'Student Class Billing',
            default => $activeClass?->display_name ?? ($classSlug === 'unassigned' ? 'Unassigned Students' : 'All Students'),
        };

        return view('admin.students', compact(
            'students',
            'classes',
            'search',
            'statusFilter',
            'billingStatusFilter',
            'studentTotalCount',
            'studentPerPage',
            'studentPage',
            'studentPageCount',
            'studentShowingFrom',
            'studentShowingTo',
            'studentGroups',
            'classDirectory',
            'classNavItems',
            'activeStudentClassPage',
            'pageTitle',
            'activeStudentView',
            'studentOfficeNavItems',
            'studentWorkspaceStats',
            'newStudents',
            'inactiveStudents',
            'siblingRows',
            'studentDebtorRows',
            'studentClassBillingRows',
        ));
    }

    public function staff(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $departmentFilter = trim((string) $request->string('department'));
        $staffViews = collect(['directory', 'payroll', 'class-allocation']);
        $activeStaffView = $staffViews->contains($request->string('view')->toString())
            ? $request->string('view')->toString()
            : 'directory';
        $allStaff = StaffProfile::with('user.managedClasses')->get();
        $staff = $allStaff->when($departmentFilter !== '', fn (Collection $rows) => $rows->where('department', $departmentFilter))->values();
        if ($search !== '') {
            $staff = $staff->filter(fn (StaffProfile $profile) => $this->matchesStaffSearch($profile, $search))->values();
        }
        $staffGroups = $staff->groupBy(fn ($p) => $p->department ?: 'General');
        $departmentDirectory = $staffGroups->map(fn ($group, $name) => ['name' => $name, 'count' => $group->count()])->values();
        $departmentOptions = StaffProfile::query()->whereNotNull('department')->where('department','!=','')->distinct()->orderBy('department')->pluck('department');
        $staffOfficeNavItems = [
            ['key' => 'directory', 'label' => 'Directory', 'href' => route('admin.staff.index', ['department' => $departmentFilter ?: null, 'view' => 'directory'])],
            ['key' => 'payroll', 'label' => 'Payroll', 'href' => route('admin.staff.index', ['department' => $departmentFilter ?: null, 'view' => 'payroll'])],
            ['key' => 'class-allocation', 'label' => 'Class Allocation', 'href' => route('admin.staff.index', ['department' => $departmentFilter ?: null, 'view' => 'class-allocation'])],
        ];
        $payrollRows = $staff
            ->groupBy(fn (StaffProfile $profile) => $profile->department ?: 'General')
            ->map(fn (Collection $group, string $department) => [
                'department' => $department,
                'staff_count' => $group->count(),
                'staff_with_salary' => $group->filter(fn (StaffProfile $profile) => (float) $profile->salary > 0)->count(),
                'monthly_total' => (float) $group->sum(fn (StaffProfile $profile) => (float) $profile->salary),
                'average_salary' => $group->filter(fn (StaffProfile $profile) => (float) $profile->salary > 0)->count() > 0
                    ? round($group->filter(fn (StaffProfile $profile) => (float) $profile->salary > 0)->avg('salary'), 2)
                    : 0,
                'profiles' => $group->sortBy(fn (StaffProfile $profile) => $profile->user->fullName())->values(),
            ])
            ->sortByDesc('monthly_total')
            ->values();
        $classAllocationRows = SchoolClass::with('classTeacher.staffProfile')
            ->orderBy('name')
            ->orderBy('section')
            ->get()
            ->map(fn (SchoolClass $class) => [
                'class' => $class,
                'teacher' => $class->classTeacher,
                'department' => $class->classTeacher?->staffProfile?->department,
                'designation' => $class->classTeacher?->staffProfile?->designation,
            ]);
        $staffWorkspaceStats = [
            'staff_count' => $allStaff->count(),
            'active_count' => $allStaff->where('status', 'active')->count(),
            'salary_count' => $allStaff->filter(fn (StaffProfile $profile) => (float) $profile->salary > 0)->count(),
            'monthly_total' => (float) $allStaff->sum(fn (StaffProfile $profile) => (float) $profile->salary),
            'class_teachers' => $classAllocationRows->filter(fn (array $row) => $row['teacher'] !== null)->count(),
        ];

        return view('admin.staff', compact(
            'staff',
            'search',
            'staffGroups',
            'departmentDirectory',
            'departmentFilter',
            'departmentOptions',
            'activeStaffView',
            'staffOfficeNavItems',
            'payrollRows',
            'classAllocationRows',
            'staffWorkspaceStats',
        ));
    }

    public function showStudent(Request $request, Student $student): View { $student->loadMissing('user', 'parent', 'schoolClass', 'academicSession'); return view('admin.student-profile', ['student' => $student, 'classes' => SchoolClass::orderBy('name')->get(), 'terms' => Term::with('academicSession')->latest('start_date')->get(), 'filters' => $this->studentRedirectParameters($request->all())]); }
    public function showStaff(Request $request, StaffProfile $staffProfile): View { $staffProfile->loadMissing('user.managedClasses'); return view('admin.staff-profile', ['staffProfile' => $staffProfile, 'filters' => $this->staffRedirectParameters($request->all())]); }
    public function storeStudent(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'], 'middle_name' => ['nullable', 'string', 'max:255'], 'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'], 'phone' => ['nullable', 'string', 'max:255'], 'password' => ['nullable', 'string', 'min:8'],
            'school_class_id' => ['nullable', 'exists:school_classes,id'], 'admission_no' => ['nullable', 'string', 'max:255', 'unique:students,admission_no'], 'student_id_no' => ['nullable', 'string', 'max:255', 'unique:students,student_id_no'],
            'parent_name' => ['nullable', 'string', 'max:255'], 'parent_email' => ['nullable', 'email', 'max:255'], 'parent_phone' => ['nullable', 'string', 'max:255'], 'passport_photo' => ['nullable', 'image', 'max:51200'],
        ] + collect(['gender','date_of_birth','place_of_birth','nationality','lga','state_of_origin','religion','guardian_name','guardian_phone','parents_occupation','office_residence_phone','address','previous_school','previous_class','doctor_name','doctor_phone','doctor_address','physical_notes','medical_notes'])->mapWithKeys(fn ($f) => [$f => ['nullable']])->all());

        $parent = $this->syncParentUser($data);
        $name = $this->buildFullName($data['first_name'], $data['middle_name'] ?? null, $data['last_name']);
        $password = $data['password'] ?? $this->generateTemporaryPassword();
        $user = User::create([
            'name' => $name, 'first_name' => $data['first_name'], 'middle_name' => $data['middle_name'] ?? null, 'last_name' => $data['last_name'],
            'email' => $data['email'] ?? null, 'phone' => $data['phone'] ?? null, 'role' => UserRole::Student, 'status' => 'active', 'password' => $password, 'email_verified_at' => now(), 'temp_password_plaintext' => $password, 'temp_password_generated_at' => now(),
            'avatar_url' => $request->hasFile('passport_photo') ? $this->saveUploadedAsset($request->file('passport_photo'), 'student-passport-'.Str::slug($name)) : null,
        ]);
        $student = Student::create(collect($data)->except(['first_name','middle_name','last_name','email','phone','password','parent_name','parent_email','parent_phone','passport_photo'])->merge([
            'user_id' => $user->id, 'parent_user_id' => $parent?->id, 'admission_no' => $data['admission_no'] ?: $this->generateStudentAdmissionNumber(), 'academic_session_id' => AcademicSession::query()->where('is_current', true)->value('id'), 'status' => 'active', 'enrolled_at' => now()->toDateString(),
        ])->all());
        $this->syncMandatoryFeeInvoices($student);

        return redirect()->route('admin.students.index')->with('generated_credentials', ['audience' => 'student', 'name' => $name, 'identifier' => $student->admission_no, 'email' => $user->email ?: 'No email', 'password' => $password]);
    }

    public function storeStaff(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'], 'middle_name' => ['nullable', 'string', 'max:255'], 'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'], 'phone' => ['nullable', 'string', 'max:255'], 'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', Rule::in(['teacher','principal','accountant','admin'])], 'employee_no' => ['nullable', 'string', 'max:255', 'unique:staff_profiles,employee_no'],
            'department' => ['nullable', 'string', 'max:255'], 'designation' => ['nullable', 'string', 'max:255'], 'qualification' => ['nullable', 'string', 'max:255'], 'hire_date' => ['nullable', 'date'], 'salary' => ['nullable', 'numeric', 'min:0'], 'passport_photo' => ['nullable', 'image', 'max:51200'],
        ]);

        $name = $this->buildFullName($data['first_name'], $data['middle_name'] ?? null, $data['last_name']);
        $password = $data['password'] ?? $this->generateTemporaryPassword();
        $user = User::create([
            'name' => $name, 'first_name' => $data['first_name'], 'middle_name' => $data['middle_name'] ?? null, 'last_name' => $data['last_name'],
            'email' => $data['email'], 'phone' => $data['phone'] ?? null, 'role' => $data['role'], 'status' => 'active', 'password' => $password, 'email_verified_at' => now(), 'temp_password_plaintext' => $password, 'temp_password_generated_at' => now(),
            'avatar_url' => $request->hasFile('passport_photo') ? $this->saveUploadedAsset($request->file('passport_photo'), 'staff-passport-'.Str::slug($name)) : null,
        ]);
        $profile = StaffProfile::create(collect($data)->except(['first_name','middle_name','last_name','email','phone','password','role','passport_photo'])->merge(['user_id' => $user->id, 'employee_no' => $data['employee_no'] ?: $this->generateEmployeeNumber(), 'status' => 'active'])->all());

        return redirect()->route('admin.staff.index')->with('generated_credentials', ['audience' => 'staff', 'name' => $name, 'identifier' => $profile->employee_no, 'email' => $user->email, 'password' => $password]);
    }

    public function updateStudent(Request $request, Student $student): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'], 'middle_name' => ['nullable', 'string', 'max:255'], 'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users','email')->ignore($student->user_id)], 'phone' => ['nullable', 'string', 'max:255'],
            'school_class_id' => ['nullable', 'exists:school_classes,id'], 'admission_no' => ['required', 'string', 'max:255', Rule::unique('students','admission_no')->ignore($student->id)], 'student_id_no' => ['nullable', 'string', 'max:255', Rule::unique('students','student_id_no')->ignore($student->id)],
            'parent_name' => ['nullable', 'string', 'max:255'], 'parent_email' => ['nullable', 'email', 'max:255'], 'parent_phone' => ['nullable', 'string', 'max:255'], 'passport_photo' => ['nullable', 'image', 'max:51200'], 'status' => ['nullable', 'string', 'max:255'],
        ] + collect(['gender','date_of_birth','place_of_birth','nationality','lga','state_of_origin','religion','guardian_name','guardian_phone','parents_occupation','office_residence_phone','address','previous_school','previous_class','doctor_name','doctor_phone','doctor_address','physical_notes','medical_notes'])->mapWithKeys(fn ($f) => [$f => ['nullable']])->all());

        $parent = $this->syncParentUser($data, $student->parent);
        $name = $this->buildFullName($data['first_name'], $data['middle_name'] ?? null, $data['last_name']);
        $student->user->update([
            'name' => $name, 'first_name' => $data['first_name'], 'middle_name' => $data['middle_name'] ?? null, 'last_name' => $data['last_name'], 'email' => $data['email'] ?? null, 'phone' => $data['phone'] ?? null, 'status' => $data['status'] ?? 'active',
            'avatar_url' => $request->hasFile('passport_photo') ? $this->saveUploadedAsset($request->file('passport_photo'), 'student-passport-'.Str::slug($name)) : $student->user->avatar_url,
        ]);
        $student->update(collect($data)->except(['first_name','middle_name','last_name','email','phone','parent_name','parent_email','parent_phone','passport_photo'])->merge(['parent_user_id' => $parent?->id])->all());
        $this->syncMandatoryFeeInvoices($student);

        return $this->redirectBackToStudents($request->all(), 'Student record updated successfully.');
    }

    public function updateStaff(Request $request, StaffProfile $staffProfile): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'], 'middle_name' => ['nullable', 'string', 'max:255'], 'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users','email')->ignore($staffProfile->user_id)], 'phone' => ['nullable', 'string', 'max:255'],
            'role' => ['required', Rule::in(['teacher','principal','accountant','admin'])], 'employee_no' => ['required', 'string', 'max:255', Rule::unique('staff_profiles','employee_no')->ignore($staffProfile->id)],
            'department' => ['nullable', 'string', 'max:255'], 'designation' => ['nullable', 'string', 'max:255'], 'qualification' => ['nullable', 'string', 'max:255'], 'hire_date' => ['nullable', 'date'], 'salary' => ['nullable', 'numeric', 'min:0'], 'passport_photo' => ['nullable', 'image', 'max:51200'], 'status' => ['nullable', 'string', 'max:255'],
        ]);

        $name = $this->buildFullName($data['first_name'], $data['middle_name'] ?? null, $data['last_name']);
        $staffProfile->user->update([
            'name' => $name, 'first_name' => $data['first_name'], 'middle_name' => $data['middle_name'] ?? null, 'last_name' => $data['last_name'], 'email' => $data['email'], 'phone' => $data['phone'] ?? null, 'role' => $data['role'], 'status' => $data['status'] ?? 'active',
            'avatar_url' => $request->hasFile('passport_photo') ? $this->saveUploadedAsset($request->file('passport_photo'), 'staff-passport-'.Str::slug($name)) : $staffProfile->user->avatar_url,
        ]);
        $staffProfile->update(collect($data)->except(['first_name','middle_name','last_name','email','phone','role','passport_photo'])->all());

        return $this->redirectBackToStaff($request->all(), 'Staff record updated successfully.');
    }
    public function resetStudentTemporaryPassword(Request $request, Student $student): RedirectResponse
    {
        $password = $this->generateTemporaryPassword();
        $student->user->update(['password' => $password, 'temp_password_plaintext' => $password, 'temp_password_generated_at' => now()]);

        return $this->redirectAfterPasswordReset(
            $request,
            'student',
            $student,
            ['audience' => 'student', 'name' => $student->user->fullName(), 'identifier' => $student->admission_no ?: ($student->student_id_no ?: 'No login ID'), 'email' => $student->user->email ?: 'No email', 'password' => $password],
            'Temporary student password generated successfully.'
        );
    }
    public function resetStaffTemporaryPassword(Request $request, StaffProfile $staffProfile): RedirectResponse
    {
        $password = $this->generateTemporaryPassword();
        $staffProfile->user->update(['password' => $password, 'temp_password_plaintext' => $password, 'temp_password_generated_at' => now()]);

        return $this->redirectAfterPasswordReset(
            $request,
            'staff',
            $staffProfile,
            ['audience' => 'staff', 'name' => $staffProfile->user->fullName(), 'identifier' => $staffProfile->employee_no, 'email' => $staffProfile->user->email, 'password' => $password],
            'Temporary staff password generated successfully.'
        );
    }
    public function deactivateStudent(Request $request, Student $student): RedirectResponse { $student->update(['status' => 'inactive']); $student->user->update(['status' => 'inactive']); return $this->redirectBackToStudents($request->all(), 'Student account deactivated.'); }
    public function deactivateStaff(Request $request, StaffProfile $staffProfile): RedirectResponse { $staffProfile->update(['status' => 'inactive']); $staffProfile->user->update(['status' => 'inactive']); return $this->redirectBackToStaff($request->all(), 'Staff account deactivated.'); }
    public function destroyStudent(Request $request, Student $student): RedirectResponse { $student->user?->delete(); return $this->redirectBackToStudents($request->all(), 'Student record deleted.'); }
    public function destroyStaff(Request $request, StaffProfile $staffProfile): RedirectResponse { $staffProfile->user?->delete(); return $this->redirectBackToStaff($request->all(), 'Staff record deleted.'); }

    public function academics(Request $request, PromotionService $promotionService, ?string $section = null): View
    {
        $sections = collect([
            'session-setup',
            'term-setup',
            'session-rollover',
            'promotion-review',
            'class-setup',
            'subject-setup',
            'announcement',
            'cbt-control',
        ]);
        $activeAcademicSection = $sections->contains($section) ? $section : 'session-setup';

        $sessions = AcademicSession::query()
            ->with('closedByUser')
            ->latest('start_date')
            ->get();
        $currentSession = $sessions->firstWhere('is_current', true);
        $latestClosedSession = $sessions
            ->filter(fn (AcademicSession $session) => $session->closed_at !== null)
            ->sortByDesc('closed_at')
            ->first();
        $promotionSourceSession = $latestClosedSession && $currentSession && $latestClosedSession->id !== $currentSession->id
            ? $latestClosedSession
            : null;
        $promotionPreview = $promotionSourceSession
            ? $promotionService->buildPromotionPreview($promotionSourceSession)
            : collect();
        $promotionPreviewByClass = $promotionPreview->groupBy(fn (array $row) => $row['current_class']?->display_name ?? 'Unassigned');
        $promotionSummary = [
            'students' => $promotionPreview->count(),
            'recommended_promotions' => $promotionPreview->where('recommended_status', 'promote')->count(),
            'recommended_repeats' => $promotionPreview->where('recommended_status', 'repeat')->count(),
        ];

        return view('admin.academics', [
            'sessions' => $sessions,
            'terms' => Term::with('academicSession')->latest('start_date')->get(),
            'classes' => SchoolClass::with('classTeacher')->orderBy('name')->get(),
            'subjects' => Subject::orderBy('name')->get(),
            'announcements' => Announcement::latest()->take(10)->get(),
            'teachers' => User::whereIn('role', [UserRole::Teacher, UserRole::Principal])->orderBy('name')->get(),
            'cbtEnabled' => (string) Setting::getValue('cbt_enabled', '1') === '1',
            'cbtAssessments' => Assessment::query()
                ->with('teacher', 'subject', 'schoolClass')
                ->withCount('cbtQuestions', 'cbtAttempts')
                ->where('is_cbt', true)
                ->latest('cbt_starts_at')
                ->take(20)
                ->get(),
            'currentSession' => $currentSession,
            'promotionSourceSession' => $promotionSourceSession,
            'promotionPreview' => $promotionPreview,
            'promotionPreviewByClass' => $promotionPreviewByClass,
            'promotionSummary' => $promotionSummary,
            'activeAcademicSection' => $activeAcademicSection,
        ]);
    }

    public function storeSession(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:academic_sessions,name'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'promotion_pass_mark' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_current' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('is_current')) {
            AcademicSession::query()->update(['is_current' => false]);
        }

        AcademicSession::create([
            ...$data,
            'promotion_pass_mark' => $data['promotion_pass_mark'] ?? 50,
            'is_current' => $request->boolean('is_current'),
        ]);

        return back()->with('status', 'Academic session created successfully.');
    }

    public function closeSession(Request $request, AcademicSession $session): RedirectResponse
    {
        abort_if($session->closed_at !== null, 422, 'This session has already been closed.');

        $data = $request->validate([
            'promotion_pass_mark' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        DB::transaction(function () use ($request, $session, $data): void {
            $session->update([
                'promotion_pass_mark' => $data['promotion_pass_mark'],
                'closed_at' => now(),
                'closed_by' => $request->user()->id,
                'is_current' => false,
            ]);

            Term::query()
                ->where('academic_session_id', $session->id)
                ->update(['is_current' => false]);
        });

        return back()->with('status', 'Session closed. Create or mark the next session as current, then process promotions.');
    }

    public function processPromotions(Request $request, PromotionService $promotionService): RedirectResponse
    {
        $data = $request->validate([
            'source_session_id' => ['required', 'exists:academic_sessions,id'],
            'target_session_id' => ['required', 'exists:academic_sessions,id', 'different:source_session_id'],
            'decisions' => ['nullable', 'array'],
            'decisions.*' => ['nullable', Rule::in(['promote', 'repeat'])],
            'target_class_ids' => ['nullable', 'array'],
            'target_class_ids.*' => ['nullable', 'exists:school_classes,id'],
            'notes' => ['nullable', 'array'],
            'notes.*' => ['nullable', 'string', 'max:500'],
        ]);

        $sourceSession = AcademicSession::query()->findOrFail($data['source_session_id']);
        $targetSession = AcademicSession::query()->findOrFail($data['target_session_id']);

        if ($sourceSession->closed_at === null) {
            return back()->withErrors([
                'source_session_id' => 'The source session must be closed before promotions can be processed.',
            ]);
        }

        if (! $targetSession->is_current) {
            return back()->withErrors([
                'target_session_id' => 'Select the current active session as the destination session.',
            ]);
        }

        $preview = $promotionService
            ->buildPromotionPreview($sourceSession)
            ->keyBy(fn (array $row) => $row['student']->id);

        if ($preview->isEmpty()) {
            return back()->withErrors([
                'source_session_id' => 'There are no students left in the closed session to promote or repeat.',
            ]);
        }

        $pendingActions = [];
        $errors = [];

        foreach ($preview as $studentId => $row) {
            $student = $row['student'];
            $decision = data_get($data, "decisions.$studentId", $row['recommended_status']);
            $targetClassId = data_get($data, "target_class_ids.$studentId");
            $targetClassId = $decision === 'repeat'
                ? ($targetClassId ?: $student->school_class_id)
                : ($targetClassId ?: $row['recommended_next_class']?->id);

            if (! $targetClassId) {
                $errors[] = $student->user->fullName().' needs a target class before the promotion can be processed.';
                continue;
            }

            $targetClass = SchoolClass::query()->find($targetClassId);

            if (! $targetClass) {
                $errors[] = $student->user->fullName().' has an invalid target class selection.';
                continue;
            }

            $pendingActions[] = [
                'student' => $student,
                'decision' => $decision,
                'target_class' => $targetClass,
                'row' => $row,
                'notes' => data_get($data, "notes.$studentId"),
            ];
        }

        if ($errors !== []) {
            return back()->withErrors([
                'promotions' => implode(' ', $errors),
            ]);
        }

        $counts = [
            'promoted' => 0,
            'repeated' => 0,
        ];

        DB::transaction(function () use ($request, $pendingActions, $sourceSession, $targetSession, &$counts): void {
            foreach ($pendingActions as $action) {
                $student = $action['student']->fresh(['schoolClass', 'user']);

                if (! $student || (int) $student->academic_session_id !== (int) $sourceSession->id) {
                    continue;
                }

                $decision = $action['decision'];
                $targetClass = $action['target_class'];
                $row = $action['row'];

                $student->update([
                    'academic_session_id' => $targetSession->id,
                    'school_class_id' => $targetClass->id,
                    'status' => 'active',
                ]);

                StudentPromotion::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'from_academic_session_id' => $sourceSession->id,
                    ],
                    [
                        'to_academic_session_id' => $targetSession->id,
                        'from_school_class_id' => $row['current_class']?->id,
                        'to_school_class_id' => $targetClass->id,
                        'promotion_status' => $decision,
                        'promotion_threshold' => $row['promotion_threshold'],
                        'overall_percentage' => $row['overall_percentage'],
                        'subject_total_percentage' => $row['subject_total_percentage'],
                        'subject_count' => $row['subject_count'],
                        'approved_by' => $request->user()->id,
                        'approved_at' => now(),
                        'notes' => $action['notes'],
                    ],
                );

                $this->syncMandatoryFeeInvoices($student->fresh());
                $counts[$decision === 'promote' ? 'promoted' : 'repeated']++;
            }
        });

        return back()->with('status', "Promotion processing completed. {$counts['promoted']} promoted and {$counts['repeated']} marked to repeat in {$targetSession->name}.");
    }
    public function storeTerm(Request $request): RedirectResponse { $data = $request->validate(['academic_session_id' => ['required','exists:academic_sessions,id'], 'name' => ['required','string','max:255'], 'start_date' => ['required','date'], 'end_date' => ['required','date','after_or_equal:start_date'], 'is_current' => ['nullable','boolean']]); if ($request->boolean('is_current')) Term::query()->update(['is_current' => false]); Term::create([...$data, 'slug' => Str::slug($data['name'].'-'.Str::random(4)), 'is_current' => $request->boolean('is_current')]); return back()->with('status', 'Term created successfully.'); }
    public function storeClass(Request $request): RedirectResponse { $data = $request->validate(['name' => ['required','string','max:255'], 'section' => ['nullable','string','max:255'], 'class_teacher_id' => ['nullable', Rule::exists('users', 'id')->where(fn ($query) => $query->whereIn('role', [UserRole::Teacher->value, UserRole::Principal->value])), Rule::unique('school_classes', 'class_teacher_id')], 'capacity' => ['nullable','integer','min:1'], 'room' => ['nullable','string','max:255'], 'description' => ['nullable','string','max:2000']]); SchoolClass::create([...$data, 'slug' => Str::slug($data['name'].'-'.($data['section'] ?: Str::random(4))).'-'.Str::lower(Str::random(4))]); return back()->with('status', 'Class created successfully.'); }
    public function updateClass(Request $request, SchoolClass $schoolClass): RedirectResponse { $data = $request->validate(['name' => ['required','string','max:255'], 'section' => ['nullable','string','max:255'], 'class_teacher_id' => ['nullable', Rule::exists('users', 'id')->where(fn ($query) => $query->whereIn('role', [UserRole::Teacher->value, UserRole::Principal->value])), Rule::unique('school_classes', 'class_teacher_id')->ignore($schoolClass->id)], 'capacity' => ['nullable','integer','min:1'], 'room' => ['nullable','string','max:255'], 'description' => ['nullable','string','max:2000']]); $schoolClass->update($data); return back()->with('status', 'Class teacher account and class details updated successfully.'); }
    public function storeSubject(Request $request): RedirectResponse { Subject::create($request->validate(['name' => ['required','string','max:255'], 'code' => ['nullable','string','max:255','unique:subjects,code'], 'description' => ['nullable','string','max:2000']])); return back()->with('status', 'Subject created successfully.'); }
    public function storeAnnouncement(Request $request): RedirectResponse { $data = $request->validate(['title' => ['required','string','max:255'], 'excerpt' => ['nullable','string','max:500'], 'body' => ['required','string','max:10000'], 'category' => ['required','string','max:255'], 'is_published' => ['nullable','boolean']]); $slug = Str::slug($data['title']); if (Announcement::where('slug', $slug)->exists()) $slug .= '-'.Str::lower(Str::random(4)); Announcement::create([...$data, 'slug' => $slug, 'author_id' => $request->user()->id, 'is_published' => $request->boolean('is_published', true), 'published_at' => now()]); return back()->with('status', 'Announcement published successfully.'); }

    protected function syncParentUser(array $data, ?User $existingParent = null): ?User
    {
        $email = $data['parent_email'] ?? null; $name = $data['parent_name'] ?? null; $phone = $data['parent_phone'] ?? null;
        if (! $email && ! $name && ! $phone) return $existingParent;
        if ($email) {
            $parent = User::firstOrNew(['email' => $email]);
            $parent->fill(['name' => $name ?: ($existingParent?->name ?: 'Parent Account'), 'email' => $email, 'phone' => $phone, 'role' => UserRole::Parent, 'status' => 'active']);
            if (! $parent->exists) { $parent->password = $this->generateTemporaryPassword(); $parent->email_verified_at = now(); }
            $parent->save();
            return $parent;
        }
        if ($existingParent) $existingParent->update(['name' => $name ?: $existingParent->name, 'phone' => $phone ?: $existingParent->phone]);
        return $existingParent;
    }

    protected function buildFullName(string $firstName, ?string $middleName, string $lastName): string
    {
        return collect([$firstName, $middleName, $lastName])->filter()->implode(' ');
    }

    protected function isNewStudent(Student $student, ?int $currentSessionId): bool
    {
        if ($currentSessionId !== null && (int) $student->academic_session_id === (int) $currentSessionId) {
            return true;
        }

        $enrolledAt = $student->enrolled_at ?? $student->created_at;

        return $enrolledAt !== null && $enrolledAt->gte(now()->subDays(90));
    }

    protected function matchesStudentSearch(Student $student, string $search): bool
    {
        $needle = Str::lower($search);
        $haystack = Str::lower(implode(' ', array_filter([
            $student->user->fullName(),
            $student->user->email,
            $student->admission_no,
            $student->student_id_no,
            $student->schoolClass->display_name ?? null,
            $student->parent?->fullName() ?? $student->parent?->name ?? null,
            $student->guardian_name,
            $student->guardian_phone,
            $student->parent?->phone,
            $student->user->phone,
        ])));

        foreach (array_filter(explode(' ', $needle)) as $word) {
            if (! str_contains($haystack, $word)) {
                return false;
            }
        }
        return true;
    }

    protected function matchesStaffSearch(StaffProfile $profile, string $search): bool
    {
        $needle = Str::lower($search);
        $haystack = Str::lower(implode(' ', array_filter([
            $profile->user->fullName(),
            $profile->user->email,
            $profile->employee_no,
            $profile->department,
            $profile->designation,
            $profile->user->roleLabel(),
        ])));

        foreach (array_filter(explode(' ', $needle)) as $word) {
            if (! str_contains($haystack, $word)) {
                return false;
            }
        }
        return true;
    }

    protected function buildSiblingRows(Collection $students, string $search = ''): Collection
    {
        $rows = $students
            ->whereNotNull('parent_user_id')
            ->groupBy('parent_user_id')
            ->filter(fn (Collection $group) => $group->count() > 1)
            ->map(function (Collection $group) {
                /** @var Student $primary */
                $primary = $group->first();

                return [
                    'parent' => $primary->parent,
                    'students' => $group->sortBy(fn (Student $student) => $student->user->fullName())->values(),
                    'family_size' => $group->count(),
                    'class_names' => $group->map(fn (Student $student) => $student->schoolClass->display_name ?? 'Unassigned')->unique()->values(),
                ];
            })
            ->values();

        if ($search !== '') {
            $needle = Str::lower($search);
            $rows = $rows->filter(function (array $row) use ($needle) {
                $haystack = Str::lower(implode(' ', array_filter([
                    $row['parent']?->fullName() ?? $row['parent']?->name,
                    $row['parent']?->email,
                    $row['students']->pluck('user.name')->implode(' '),
                    $row['students']->pluck('admission_no')->implode(' '),
                    $row['class_names']->implode(' '),
                ])));

                foreach (array_filter(explode(' ', $needle)) as $word) {
                    if (! str_contains($haystack, $word)) {
                        return false;
                    }
                }
                return true;
            })->values();
        }

        return $rows;
    }

    protected function buildStudentDebtorRows(Collection $students, string $search = ''): Collection
    {
        $rows = $students->map(function (Student $student) {
            $outstandingTotal = (float) $student->feeInvoices->sum('balance');
            $paidTotal = (float) $student->feeInvoices->sum('amount_paid');
            $overpaymentTotal = (float) $student->feeInvoices->sum(fn (FeeInvoice $invoice) => max((float) $invoice->amount_paid - (float) $invoice->amount_due, 0));

            return [
                'student' => $student,
                'invoice_count' => $student->feeInvoices->count(),
                'outstanding_total' => $outstandingTotal,
                'paid_total' => $paidTotal,
                'overpayment_total' => $overpaymentTotal,
                'items' => $student->feeInvoices
                    ->filter(fn (FeeInvoice $invoice) => (float) $invoice->balance > 0)
                    ->sortByDesc('balance')
                    ->values(),
            ];
        })->filter(fn (array $row) => $row['outstanding_total'] > 0);

        if ($search !== '') {
            $rows = $rows->filter(fn (array $row) => $this->matchesStudentSearch($row['student'], $search));
        }

        return $rows->sortByDesc('outstanding_total')->values();
    }

    protected function buildStudentClassBillingRows(Collection $classes, Collection $students): Collection
    {
        return $classes->map(function (SchoolClass $class) use ($students) {
            $classStudents = $students->where('school_class_id', $class->id);
            $classInvoices = $classStudents->flatMap(fn (Student $student) => $student->feeInvoices);
            $expectedTotal = (float) $classInvoices->sum('amount_due');
            $collectedTotal = (float) $classInvoices->sum('amount_paid');
            $outstandingTotal = (float) $classInvoices->sum('balance');

            return [
                'class' => $class,
                'student_count' => $classStudents->count(),
                'invoice_count' => $classInvoices->count(),
                'students_with_debt' => $classInvoices->where('balance', '>', 0)->pluck('student_id')->unique()->count(),
                'expected_total' => $expectedTotal,
                'collected_total' => $collectedTotal,
                'outstanding_total' => $outstandingTotal,
                'collection_rate' => $expectedTotal > 0 ? round(($collectedTotal / $expectedTotal) * 100, 1) : 0,
            ];
        })->sortByDesc('outstanding_total')->values();
    }

    protected function generateTemporaryPassword(): string
    {
        return Str::upper(Str::random(3)).'@'.Str::random(5);
    }

    protected function generateStudentAdmissionNumber(): string
    {
        do { $candidate = 'ADM-'.now()->format('y').'-'.Str::upper(Str::random(6)); } while (Student::where('admission_no', $candidate)->exists());
        return $candidate;
    }

    protected function generateEmployeeNumber(): string
    {
        do { $candidate = 'STF-'.now()->format('y').'-'.Str::upper(Str::random(6)); } while (StaffProfile::where('employee_no', $candidate)->exists());
        return $candidate;
    }

    protected function redirectAfterPasswordReset(Request $request, string $audience, Student|StaffProfile $record, array $credentials, string $status): RedirectResponse
    {
        if ($request->string('redirect_to') === 'profile') {
            $parameters = $audience === 'student'
                ? ['student' => $record] + $this->studentRedirectParameters($request->all())
                : ['staffProfile' => $record] + $this->staffRedirectParameters($request->all());

            $route = $audience === 'student' ? 'admin.students.show' : 'admin.staff.show';

            return redirect()->route($route, $parameters)
                ->with('status', $status)
                ->with('generated_credentials', $credentials);
        }

        return $audience === 'student'
            ? $this->redirectBackToStudents($request->all(), $status)->with('generated_credentials', $credentials)
            : $this->redirectBackToStaff($request->all(), $status)->with('generated_credentials', $credentials);
    }

    protected function redirectBackToPeople(array $inputs, string $status): RedirectResponse { return redirect()->route('admin.people', $this->peopleRedirectParameters($inputs))->with('status', $status); }
    protected function peopleRedirectParameters(array $inputs): array { $parameters = []; foreach (['search','class_id','department'] as $key) { $value = trim((string) ($inputs[$key] ?? $inputs["redirect_{$key}"] ?? '')); if ($value !== '') $parameters[$key] = $value; } return $parameters; }
    protected function studentRedirectParameters(array $inputs): array { $parameters = []; foreach (['search','classSlug','view'] as $key) { $value = trim((string) ($inputs[$key] ?? $inputs["redirect_{$key}"] ?? '')); if ($value !== '') $parameters[$key] = $value; } return $parameters; }
    protected function staffRedirectParameters(array $inputs): array { $parameters = []; foreach (['search','department','view'] as $key) { $value = trim((string) ($inputs[$key] ?? $inputs["redirect_{$key}"] ?? '')); if ($value !== '') $parameters[$key] = $value; } return $parameters; }
    protected function redirectBackToStudents(array $inputs, string $status): RedirectResponse { return redirect()->route('admin.students.index', $this->studentRedirectParameters($inputs))->with('status', $status); }
    protected function redirectBackToStaff(array $inputs, string $status): RedirectResponse { return redirect()->route('admin.staff.index', $this->staffRedirectParameters($inputs))->with('status', $status); }
    protected function saveUploadedAsset($file, string $prefix): string { $directory = public_path('uploads/settings'); if (!File::exists($directory)) File::makeDirectory($directory, 0755, true); $filename = Str::slug($prefix).'-'.time().'-'.Str::lower(Str::random(4)).'.'.$file->getClientOriginalExtension(); $file->move($directory, $filename); return 'uploads/settings/'.$filename; }
    protected function syncMandatoryFeeInvoices(Student $student): void { $sessionId = $student->academic_session_id ?: AcademicSession::query()->where('is_current', true)->value('id'); $feeItems = FeeItem::query()->where('is_mandatory', true)->where(fn ($q) => $q->whereNull('school_class_id')->orWhere('school_class_id', $student->school_class_id))->where(fn ($q) => $q->whereNull('academic_session_id')->orWhere('academic_session_id', $sessionId))->get(); foreach ($feeItems as $feeItem) { if (FeeInvoice::query()->where('student_id', $student->id)->where('fee_item_id', $feeItem->id)->exists()) continue; FeeInvoice::create(['invoice_no' => 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)), 'student_id' => $student->id, 'fee_item_id' => $feeItem->id, 'amount_due' => $feeItem->amount, 'amount_paid' => 0, 'balance' => $feeItem->amount, 'due_date' => $feeItem->due_date, 'status' => 'unpaid', 'issued_at' => now(), 'notes' => 'Auto-generated from mandatory class/session fees.']); } }
}
