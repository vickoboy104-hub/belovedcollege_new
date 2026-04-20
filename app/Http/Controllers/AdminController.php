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
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function settings(Request $request, ?string $section = null): View
    {
        $sections = collect([
            'website-foundation',
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
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $rules = [
            'group' => ['required', 'string', 'max:100'],
            'school_name' => ['nullable', 'string', 'max:255'], 'motto' => ['nullable', 'string', 'max:255'], 'site_tagline' => ['nullable', 'string', 'max:255'], 'site_subtitle' => ['nullable', 'string', 'max:255'],
            'school_email' => ['nullable', 'email', 'max:255'], 'school_phone' => ['nullable', 'string', 'max:255'], 'school_address' => ['nullable', 'string', 'max:500'],
            'whatsapp_number' => ['nullable', 'string', 'max:255'], 'whatsapp_link' => ['nullable', 'string', 'max:500'], 'contact_email_recipient' => ['nullable', 'email', 'max:255'],
            'mail_mailer' => ['nullable', 'string', 'max:50'], 'mail_host' => ['nullable', 'string', 'max:255'], 'mail_port' => ['nullable', 'numeric', 'min:1', 'max:65535'],
            'mail_username' => ['nullable', 'string', 'max:255'], 'mail_password' => ['nullable', 'string', 'max:255'], 'mail_encryption' => ['nullable', 'string', 'max:50'],
            'mail_from_address' => ['nullable', 'email', 'max:255'], 'mail_from_name' => ['nullable', 'string', 'max:255'], 'principal_name' => ['nullable', 'string', 'max:255'],
            'hero_blurb' => ['nullable', 'string', 'max:1000'], 'portal_notice' => ['nullable', 'string', 'max:1000'],
            'theme_preset' => ['nullable', 'string', 'max:100'], 'theme_primary' => ['nullable', 'string', 'max:20'], 'theme_secondary' => ['nullable', 'string', 'max:20'], 'theme_accent' => ['nullable', 'string', 'max:20'], 'theme_highlight' => ['nullable', 'string', 'max:20'], 'theme_text' => ['nullable', 'string', 'max:20'], 'top_bar_color' => ['nullable', 'string', 'max:20'],
            'site_background_1_opacity' => ['nullable', 'numeric', 'min:0', 'max:100'], 'site_background_2_opacity' => ['nullable', 'numeric', 'min:0', 'max:100'], 'site_background_3_opacity' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'welcome_popup_enabled' => ['nullable', 'boolean'], 'welcome_popup_title' => ['nullable', 'string', 'max:255'], 'welcome_popup_text' => ['nullable', 'string', 'max:1000'], 'welcome_popup_button_text' => ['nullable', 'string', 'max:255'], 'welcome_popup_button_link' => ['nullable', 'string', 'max:500'],
            'hero_slide_1_title' => ['nullable', 'string', 'max:255'], 'hero_slide_1_text' => ['nullable', 'string', 'max:1000'], 'hero_slide_2_title' => ['nullable', 'string', 'max:255'], 'hero_slide_2_text' => ['nullable', 'string', 'max:1000'], 'hero_slide_3_title' => ['nullable', 'string', 'max:255'], 'hero_slide_3_text' => ['nullable', 'string', 'max:1000'], 'hero_slide_4_title' => ['nullable', 'string', 'max:255'], 'hero_slide_4_text' => ['nullable', 'string', 'max:1000'],
            'paystack_public_key' => ['nullable', 'string', 'max:255'], 'paystack_secret_key' => ['nullable', 'string', 'max:255'], 'paystack_webhook_secret' => ['nullable', 'string', 'max:255'],
            'palmpay_merchant_id' => ['nullable', 'string', 'max:255'], 'palmpay_app_id' => ['nullable', 'string', 'max:255'], 'palmpay_public_key' => ['nullable', 'string', 'max:5000'], 'palmpay_private_key' => ['nullable', 'string', 'max:5000'], 'palmpay_webhook_secret' => ['nullable', 'string', 'max:255'], 'palmpay_checkout_url' => ['nullable', 'url', 'max:500'],
            'hero_intro_background_opacity' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
        foreach ([
            'logo_file','favicon_file','hero_background_video_poster','hero_intro_background_image','welcome_popup_image','hero_slide_1_image','hero_slide_2_image','hero_slide_3_image','hero_slide_4_image','gallery_image_1','gallery_image_2','gallery_image_3','gallery_image_4','admin_background_image','site_background_1','site_background_2','site_background_3','section_background_1','section_background_2','section_background_3','quick_intro_background_image','academic_section_background_image','founders_background_image','gallery_section_background_image','news_section_background_image'
        ] as $imageKey) $rules[$imageKey] = ['nullable','image','max:51200'];
        $rules['hero_background_video'] = ['nullable','file','mimes:mp4,webm,mov,m4v','max:51200'];
        foreach (range(1,3) as $i) { $rules["hero_highlight_{$i}_text"]=['nullable','string','max:255']; $rules["hero_highlight_{$i}_background"]=['nullable','image','max:51200']; }
        foreach (range(1,4) as $i) { $rules["homepage_stat_{$i}_label"]=['nullable','string','max:255']; $rules["homepage_stat_{$i}_value"]=['nullable','string','max:255']; $rules["homepage_stat_{$i}_background"]=['nullable','image','max:51200']; $rules["home_feature_{$i}_title"]=['nullable','string','max:255']; $rules["home_feature_{$i}_text"]=['nullable','string','max:1000']; $rules["home_feature_{$i}_background"]=['nullable','image','max:51200']; }
        foreach (range(1,6) as $i) { $rules["academic_card_{$i}_title"]=['nullable','string','max:255']; $rules["academic_card_{$i}_text"]=['nullable','string','max:1000']; $rules["academic_card_{$i}_background"]=['nullable','image','max:51200']; }
        foreach (['quick_intro_kicker','quick_intro_title','quick_intro_text_1','quick_intro_text_2','why_choose_kicker','why_choose_title','why_choose_text','why_choose_button_text','why_choose_button_link','academic_section_kicker','academic_section_title','founders_kicker','founders_title','founders_text_1','founders_text_2','founders_values_text','gallery_section_kicker','gallery_section_title','gallery_section_text','news_section_kicker','news_section_title','news_section_empty_text','cta_kicker','cta_title','cta_text','cta_button_text','cta_button_link','cta_phone_label'] as $textKey) $rules[$textKey] = ['nullable','string','max:2000'];
        $validated = $request->validate($rules);
        $group = $validated['group']; unset($validated['group']); $validated['welcome_popup_enabled'] = $request->boolean('welcome_popup_enabled');
        $uploadMap = ['logo_file' => 'logo_path', 'favicon_file' => 'favicon_path'];
        foreach (array_keys($rules) as $key) if (str_contains($key, 'image') || str_contains($key, 'background') || $key === 'hero_background_video') $uploadMap[$key] ??= $key;
        $uploadMap['hero_background_video_poster'] = 'hero_background_video_poster';
        foreach ($uploadMap as $input => $settingKey) { unset($validated[$input]); if ($request->hasFile($input)) $validated[$settingKey] = $this->saveUploadedAsset($request->file($input), $settingKey); }
        Setting::setMany(array_filter($validated, fn ($value) => $value !== null), $group);
        return back()->with('status', 'Settings saved successfully.');
    }

    public function people(Request $request): View
    {
        return view('admin.people');
    }

    public function students(Request $request, ?string $classSlug = null): View
    {
        $search = trim((string) $request->string('search'));
        $classes = SchoolClass::orderBy('name')->orderBy('section')->get();
        $activeClass = null;

        if ($classSlug && $classSlug !== 'all' && $classSlug !== 'unassigned') {
            $activeClass = $classes->firstWhere('slug', $classSlug);
            abort_unless($activeClass, 404);
        }

        $students = Student::with('user', 'schoolClass', 'parent')
            ->when($activeClass, fn ($q) => $q->where('school_class_id', $activeClass->id))
            ->when($classSlug === 'unassigned', fn ($q) => $q->whereNull('school_class_id'))
            ->get();

        if ($search !== '') {
            $students = $students->filter(fn ($s) => str_contains(strtolower(($s->user->fullName().' '.$s->user->email.' '.$s->admission_no.' '.$s->student_id_no)), strtolower($search)));
        }
        $studentGroups = $students->groupBy(fn ($s) => $s->schoolClass->display_name ?? 'Unassigned');
        $classDirectory = $studentGroups->map(fn ($group, $name) => ['name' => $name, 'count' => $group->count()])->values();
        $classNavItems = collect([
            ['key' => 'all', 'label' => 'All Students', 'href' => route('admin.students.index')],
            ...$classes->map(fn (SchoolClass $class) => [
                'key' => $class->slug,
                'label' => $class->display_name,
                'href' => route('admin.students.index', ['classSlug' => $class->slug]),
            ])->all(),
        ]);

        if (Student::query()->whereNull('school_class_id')->exists()) {
            $classNavItems->push([
                'key' => 'unassigned',
                'label' => 'Unassigned',
                'href' => route('admin.students.index', ['classSlug' => 'unassigned']),
            ]);
        }

        $activeStudentClassPage = $activeClass?->slug ?? ($classSlug === 'unassigned' ? 'unassigned' : 'all');
        $pageTitle = $activeClass?->display_name ?? ($classSlug === 'unassigned' ? 'Unassigned Students' : 'All Students');

        return view('admin.students', compact('students', 'classes', 'search', 'studentGroups', 'classDirectory', 'classNavItems', 'activeStudentClassPage', 'pageTitle'));
    }

    public function staff(Request $request): View
    {
        $search = trim((string) $request->string('search')); $departmentFilter = trim((string) $request->string('department'));
        $staff = StaffProfile::with('user.managedClasses')->when($departmentFilter !== '', fn ($q) => $q->where('department', $departmentFilter))->get();
        if ($search !== '') {
            $staff = $staff->filter(fn ($p) => str_contains(strtolower(($p->user->fullName().' '.$p->user->email.' '.$p->employee_no.' '.$p->department)), strtolower($search)));
        }
        $staffGroups = $staff->groupBy(fn ($p) => $p->department ?: 'General');
        $departmentDirectory = $staffGroups->map(fn ($group, $name) => ['name' => $name, 'count' => $group->count()])->values();
        $departmentOptions = StaffProfile::query()->whereNotNull('department')->where('department','!=','')->distinct()->orderBy('department')->pluck('department');

        return view('admin.staff', compact('staff', 'search', 'staffGroups', 'departmentDirectory', 'departmentFilter', 'departmentOptions'));
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
            'department' => ['nullable', 'string', 'max:255'], 'designation' => ['nullable', 'string', 'max:255'], 'qualification' => ['nullable', 'string', 'max:255'], 'hire_date' => ['nullable', 'date'], 'passport_photo' => ['nullable', 'image', 'max:51200'],
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
            'department' => ['nullable', 'string', 'max:255'], 'designation' => ['nullable', 'string', 'max:255'], 'qualification' => ['nullable', 'string', 'max:255'], 'hire_date' => ['nullable', 'date'], 'passport_photo' => ['nullable', 'image', 'max:51200'], 'status' => ['nullable', 'string', 'max:255'],
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
    protected function studentRedirectParameters(array $inputs): array { $parameters = []; foreach (['search','classSlug'] as $key) { $value = trim((string) ($inputs[$key] ?? $inputs["redirect_{$key}"] ?? '')); if ($value !== '') $parameters[$key] = $value; } return $parameters; }
    protected function staffRedirectParameters(array $inputs): array { $parameters = []; foreach (['search','department'] as $key) { $value = trim((string) ($inputs[$key] ?? $inputs["redirect_{$key}"] ?? '')); if ($value !== '') $parameters[$key] = $value; } return $parameters; }
    protected function redirectBackToStudents(array $inputs, string $status): RedirectResponse { return redirect()->route('admin.students.index', $this->studentRedirectParameters($inputs))->with('status', $status); }
    protected function redirectBackToStaff(array $inputs, string $status): RedirectResponse { return redirect()->route('admin.staff.index', $this->staffRedirectParameters($inputs))->with('status', $status); }
    protected function saveUploadedAsset($file, string $prefix): string { $directory = public_path('uploads/settings'); if (!File::exists($directory)) File::makeDirectory($directory, 0755, true); $filename = Str::slug($prefix).'-'.time().'-'.Str::lower(Str::random(4)).'.'.$file->getClientOriginalExtension(); $file->move($directory, $filename); return 'uploads/settings/'.$filename; }
    protected function syncMandatoryFeeInvoices(Student $student): void { $sessionId = $student->academic_session_id ?: AcademicSession::query()->where('is_current', true)->value('id'); $feeItems = FeeItem::query()->where('is_mandatory', true)->where(fn ($q) => $q->whereNull('school_class_id')->orWhere('school_class_id', $student->school_class_id))->where(fn ($q) => $q->whereNull('academic_session_id')->orWhere('academic_session_id', $sessionId))->get(); foreach ($feeItems as $feeItem) { if (FeeInvoice::query()->where('student_id', $student->id)->where('fee_item_id', $feeItem->id)->exists()) continue; FeeInvoice::create(['invoice_no' => 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)), 'student_id' => $student->id, 'fee_item_id' => $feeItem->id, 'amount_due' => $feeItem->amount, 'amount_paid' => 0, 'balance' => $feeItem->amount, 'due_date' => $feeItem->due_date, 'status' => 'unpaid', 'issued_at' => now(), 'notes' => 'Auto-generated from mandatory class/session fees.']); } }
}
