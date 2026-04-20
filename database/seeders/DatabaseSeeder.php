<?php

namespace Database\Seeders;

use App\Enums\AssessmentType;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\AcademicSession;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Assessment;
use App\Models\AssessmentResult;
use App\Models\AttendanceRecord;
use App\Models\FeeInvoice;
use App\Models\FeeItem;
use App\Models\Lesson;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Setting;
use App\Models\StaffProfile;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    protected string $schoolDomain = 'belovedschool.test';

    protected string $defaultPassword = 'password';

    protected array $houses = ['Emerald', 'Sapphire', 'Ruby', 'Gold'];

    protected array $states = ['Ondo', 'Lagos', 'Oyo', 'Ogun', 'Ekiti'];

    protected array $towns = ['Ore', 'Akure', 'Ibadan', 'Abeokuta', 'Ile-Ife'];

    protected array $firstNames = [
        'Daniel', 'Esther', 'Joshua', 'Mercy', 'David', 'Grace', 'Samuel', 'Deborah', 'Elijah', 'Ruth',
        'Timothy', 'Joy', 'Nathaniel', 'Favour', 'Michael', 'Peace', 'Caleb', 'Princess', 'Emmanuel', 'Testimony',
        'Faith', 'Victor', 'Lydia', 'Naomi', 'Joseph', 'Sarah', 'Paul', 'Hannah', 'John', 'Patience',
    ];

    protected array $lastNames = [
        'Adeyemi', 'Ojo', 'Balogun', 'Adebayo', 'Okonkwo', 'Nwosu', 'Yusuf', 'Afolabi', 'Ogunleye', 'Eze',
        'Ajayi', 'Ibrahim', 'Olawale', 'Okechukwu', 'Akinsola', 'Onyeka', 'Bakare', 'Adewale', 'Umeh', 'Okafor',
    ];

    public function run(): void
    {
        $adminEmail = env('DEFAULT_ADMIN_EMAIL', 'admin@belovedschool.test');
        $adminPassword = env('DEFAULT_ADMIN_PASSWORD', $this->defaultPassword);
        $adminName = env('DEFAULT_ADMIN_NAME', 'Platform Administrator');

        $this->seedSchoolSettings();

        $session = AcademicSession::create([
            'name' => '2025/2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-07-31',
            'is_current' => true,
        ]);

        $terms = collect([
            'first' => Term::create([
                'academic_session_id' => $session->id,
                'name' => 'First Term',
                'slug' => 'first-term',
                'start_date' => '2025-09-01',
                'end_date' => '2025-12-19',
                'is_current' => false,
            ]),
            'second' => Term::create([
                'academic_session_id' => $session->id,
                'name' => 'Second Term',
                'slug' => 'second-term',
                'start_date' => '2026-01-12',
                'end_date' => '2026-04-10',
                'is_current' => true,
            ]),
            'third' => Term::create([
                'academic_session_id' => $session->id,
                'name' => 'Third Term',
                'slug' => 'third-term',
                'start_date' => '2026-04-27',
                'end_date' => '2026-07-24',
                'is_current' => false,
            ]),
        ]);

        $subjects = $this->seedSubjects();

        $staffDefinitions = $this->staffDefinitions($adminEmail, $adminPassword, $adminName);
        $staffUsers = $this->registerStaff($staffDefinitions);

        $classes = $this->seedClasses($staffUsers);
        $classFeeItems = $this->seedFeeItems($classes, $session, $terms->get('second'));
        $studentsByClass = $this->seedStudents($classes, $session, $classFeeItems, $staffUsers->get('accountant'));

        $this->seedAcademicContent($classes, $studentsByClass, $subjects, $terms->get('second'), $staffUsers);
        $this->seedAnnouncements($staffUsers);
        $this->exportLoginSheets($staffUsers);
    }

    protected function seedSchoolSettings(): void
    {
        Setting::setMany([
            'school_name' => 'BELOVED SCHOOLS',
            'motto' => 'Knowledge for All Nations',
            'site_tagline' => 'Building Minds, Shaping Character',
            'site_subtitle' => 'Raising Future Leaders Through Knowledge and Godly Values',
            'school_email' => 'info@belovedschool.test',
            'school_phone' => '08067046701',
            'school_address' => 'Ayeteju Street, Ore, Ondo State',
            'principal_name' => 'Dr. Ifeoma Okeke',
            'hero_blurb' => 'At BELOVED SCHOOLS, we combine academic excellence with strong moral discipline to prepare students for a successful and purposeful life.',
            'portal_notice' => 'For admission enquiries, contact BELOVED SCHOOLS through the school office or the contact page.',
            'theme_preset' => 'classic-blue',
            'top_bar_color' => '#0b2a66',
            'hero_slide_1_title' => 'Welcome to BELOVED SCHOOLS',
            'hero_slide_1_text' => 'Building Minds, Shaping Character',
            'hero_slide_2_title' => 'Excellence in Education',
            'hero_slide_2_text' => 'Empowering Students for a Brighter Future',
            'hero_slide_3_title' => 'Godly Values, Strong Discipline',
            'hero_slide_3_text' => 'Raising Responsible Leaders',
            'hero_slide_4_title' => 'Knowledge for All Nations',
            'hero_slide_4_text' => 'Education Without Limits',
            'quick_intro_title' => 'A real school environment for growth and purpose.',
            'quick_intro_text_1' => 'Founded in 2006 and located in Ore, Ondo State, BELOVED SCHOOLS is committed to raising educated, disciplined, and God-fearing students.',
            'quick_intro_text_2' => 'With professional teachers, modern learning facilities, and a strong moral foundation, we prepare students for success in both academics and life.',
            'why_choose_kicker' => 'Why Parents Choose BELOVED SCHOOLS',
            'why_choose_title' => 'A disciplined school environment built for excellence.',
            'why_choose_text' => 'BELOVED SCHOOLS combines academic excellence with moral discipline to help students become responsible and purposeful leaders.',
            'founders_kicker' => 'Meet the Founders',
            'founders_title' => 'A school vision built on education and youth development.',
            'founders_text_1' => 'BELOVED SCHOOLS was founded by Mr. Zebilon K. S. alongside his wife Mrs. Grace Zebilon, whose passion for education and youth development led to the establishment of the school.',
            'founders_text_2' => 'Their vision was to create a learning environment where students can grow academically while being rooted in strong moral and spiritual values.',
            'gallery_section_kicker' => 'Life at BELOVED SCHOOLS',
            'gallery_section_title' => 'Life at BELOVED SCHOOLS',
            'gallery_section_text' => 'Explore moments from our classrooms, events, and student activities that reflect our commitment to excellence and holistic development.',
            'news_section_title' => 'Announcements and updates',
            'news_section_empty_text' => 'School announcements will appear here as BELOVED SCHOOLS shares updates with parents and students.',
            'cta_title' => 'Give your child the foundation for a successful future.',
            'cta_text' => 'BELOVED SCHOOLS combines knowledge, discipline, integrity, responsibility, and Godliness to prepare students for meaningful impact.',
            'welcome_popup_enabled' => '1',
            'welcome_popup_title' => 'Apply to BELOVED SCHOOLS',
            'welcome_popup_text' => 'Give your child the foundation for a successful future with disciplined learning, academic excellence, and Godly values.',
            'welcome_popup_button_text' => 'Apply Now',
            'welcome_popup_button_link' => '/admissions',
        ], 'school');

        Setting::setMany([
            'paystack_public_key' => 'pk_test_demo',
            'paystack_secret_key' => '',
            'paystack_webhook_secret' => '',
            'palmpay_merchant_id' => '',
            'palmpay_app_id' => '',
            'palmpay_public_key' => '',
            'palmpay_private_key' => '',
            'palmpay_webhook_secret' => '',
            'palmpay_checkout_url' => '',
        ], 'payments');
    }

    protected function seedSubjects(): Collection
    {
        $definitions = [
            ['name' => 'Mathematics', 'code' => 'MTH101'],
            ['name' => 'English Language', 'code' => 'ENG101'],
            ['name' => 'Basic Science', 'code' => 'BST101'],
            ['name' => 'Social Studies', 'code' => 'SOS101'],
            ['name' => 'Civic Education', 'code' => 'CVE101'],
            ['name' => 'Physics', 'code' => 'PHY201'],
            ['name' => 'Chemistry', 'code' => 'CHM201'],
            ['name' => 'Biology', 'code' => 'BIO201'],
            ['name' => 'Economics', 'code' => 'ECO201'],
            ['name' => 'Commerce', 'code' => 'COM201'],
            ['name' => 'Financial Accounting', 'code' => 'ACC201'],
            ['name' => 'Literature in English', 'code' => 'LIT201'],
            ['name' => 'Government', 'code' => 'GOV201'],
            ['name' => 'Christian Religious Studies', 'code' => 'CRS201'],
        ];

        return collect($definitions)->mapWithKeys(fn (array $subject) => [
            $subject['code'] => Subject::create([
                'name' => $subject['name'],
                'code' => $subject['code'],
                'description' => $subject['name'].' curriculum module',
            ]),
        ]);
    }

    protected function staffDefinitions(string $adminEmail, string $adminPassword, string $adminName): Collection
    {
        return collect([
            [
                'key' => 'platform_admin',
                'name' => $adminName,
                'first_name' => 'Platform',
                'last_name' => 'Administrator',
                'email' => $adminEmail,
                'role' => UserRole::Admin,
                'phone' => '+2348030001001',
                'password' => $adminPassword,
                'employee_no' => 'BVS-ADM-001',
                'department' => 'Operations',
                'designation' => 'Platform Administrator',
                'qualification' => 'B.Sc. Information Systems',
                'hire_date' => '2024-01-15',
                'salary' => 260000,
                'responsibilities' => ['System administration', 'User access control'],
            ],
            [
                'key' => 'principal',
                'name' => 'Dr. Ifeoma Okeke',
                'first_name' => 'Ifeoma',
                'last_name' => 'Okeke',
                'email' => 'principal@belovedschool.test',
                'role' => UserRole::Principal,
                'phone' => '+2348030001002',
                'password' => $this->defaultPassword,
                'employee_no' => 'BVS-PRN-001',
                'department' => 'Leadership',
                'designation' => 'Principal',
                'qualification' => 'PhD Educational Management',
                'hire_date' => '2020-01-06',
                'salary' => 420000,
                'responsibilities' => ['Academic leadership', 'Whole-school supervision'],
            ],
            [
                'key' => 'accountant',
                'name' => 'Mrs. Kemi Balogun',
                'first_name' => 'Kemi',
                'last_name' => 'Balogun',
                'email' => 'accountant@belovedschool.test',
                'role' => UserRole::Accountant,
                'phone' => '+2348030001003',
                'password' => $this->defaultPassword,
                'employee_no' => 'BVS-ACC-001',
                'department' => 'Accounts',
                'designation' => 'Bursar / School Accountant',
                'qualification' => 'B.Sc. Accounting',
                'hire_date' => '2021-04-12',
                'salary' => 280000,
                'responsibilities' => ['Fee management', 'Finance records'],
            ],
            [
                'key' => 'registrar',
                'name' => 'Mrs. Omowunmi Sanya',
                'first_name' => 'Omowunmi',
                'last_name' => 'Sanya',
                'email' => 'registrar@belovedschool.test',
                'role' => UserRole::Admin,
                'phone' => '+2348030001004',
                'password' => $this->defaultPassword,
                'employee_no' => 'BVS-ADM-002',
                'department' => 'Registry',
                'designation' => 'Registrar & Admissions Officer',
                'qualification' => 'B.A. English',
                'hire_date' => '2022-02-01',
                'salary' => 215000,
                'responsibilities' => ['Admissions', 'Student records'],
            ],
            [
                'key' => 'ict_officer',
                'name' => 'Mr. Seun Alabi',
                'first_name' => 'Seun',
                'last_name' => 'Alabi',
                'email' => 'ict@belovedschool.test',
                'role' => UserRole::Admin,
                'phone' => '+2348030001005',
                'password' => $this->defaultPassword,
                'employee_no' => 'BVS-ADM-003',
                'department' => 'ICT',
                'designation' => 'ICT / MIS Officer',
                'qualification' => 'B.Tech Computer Science',
                'hire_date' => '2023-01-10',
                'salary' => 230000,
                'responsibilities' => ['ICT support', 'Portal support'],
            ],
            [
                'key' => 'vp_academics',
                'name' => 'Mr. Ayodele Akinwale',
                'first_name' => 'Ayodele',
                'last_name' => 'Akinwale',
                'email' => 'vp.academics@belovedschool.test',
                'role' => UserRole::Teacher,
                'phone' => '+2348030001006',
                'password' => $this->defaultPassword,
                'employee_no' => 'BVS-TCH-001',
                'department' => 'Academics',
                'designation' => 'Vice Principal Academics',
                'qualification' => 'M.Ed Curriculum Studies',
                'hire_date' => '2021-09-01',
                'salary' => 320000,
                'responsibilities' => ['Academic coordination', 'Timetable supervision'],
            ],
            [
                'key' => 'exam_officer',
                'name' => 'Mrs. Sandra Umeh',
                'first_name' => 'Sandra',
                'last_name' => 'Umeh',
                'email' => 'exam.officer@belovedschool.test',
                'role' => UserRole::Teacher,
                'phone' => '+2348030001007',
                'password' => $this->defaultPassword,
                'employee_no' => 'BVS-TCH-002',
                'department' => 'Academics',
                'designation' => 'Examinations Officer',
                'qualification' => 'B.Ed Measurement and Evaluation',
                'hire_date' => '2022-01-15',
                'salary' => 235000,
                'responsibilities' => ['Exam records', 'Result collation'],
            ],
            [
                'key' => 'guidance_counsellor',
                'name' => 'Mrs. Folake Ojo',
                'first_name' => 'Folake',
                'last_name' => 'Ojo',
                'email' => 'counsellor@belovedschool.test',
                'role' => UserRole::Teacher,
                'phone' => '+2348030001008',
                'password' => $this->defaultPassword,
                'employee_no' => 'BVS-TCH-003',
                'department' => 'Student Affairs',
                'designation' => 'Guidance Counsellor',
                'qualification' => 'M.Ed Guidance and Counselling',
                'hire_date' => '2022-03-10',
                'salary' => 220000,
                'responsibilities' => ['Student welfare', 'Career guidance'],
            ],
            [
                'key' => 'librarian',
                'name' => 'Mr. Chinedu Okafor',
                'first_name' => 'Chinedu',
                'last_name' => 'Okafor',
                'email' => 'librarian@belovedschool.test',
                'role' => UserRole::Teacher,
                'phone' => '+2348030001009',
                'password' => $this->defaultPassword,
                'employee_no' => 'BVS-TCH-004',
                'department' => 'Library Services',
                'designation' => 'School Librarian',
                'qualification' => 'B.LIS Library and Information Science',
                'hire_date' => '2023-01-12',
                'salary' => 205000,
                'responsibilities' => ['Library supervision', 'Reading culture'],
            ],
            [
                'key' => 'math_teacher',
                'name' => 'Mr. Daniel Adeyemi',
                'first_name' => 'Daniel',
                'last_name' => 'Adeyemi',
                'email' => 'mathematics@belovedschool.test',
                'role' => UserRole::Teacher,
                'phone' => '+2348030001010',
                'password' => $this->defaultPassword,
                'employee_no' => 'BVS-TCH-101',
                'department' => 'Mathematics',
                'designation' => 'Mathematics Teacher',
                'qualification' => 'B.Sc. Mathematics Education',
                'hire_date' => '2022-09-01',
                'salary' => 210000,
                'subject_codes' => ['MTH101'],
            ],
            [
                'key' => 'english_teacher',
                'name' => 'Mrs. Bimpe Afolabi',
                'first_name' => 'Bimpe',
                'last_name' => 'Afolabi',
                'email' => 'english@belovedschool.test',
                'role' => UserRole::Teacher,
                'phone' => '+2348030001011',
                'password' => $this->defaultPassword,
                'employee_no' => 'BVS-TCH-102',
                'department' => 'Languages',
                'designation' => 'English Language Teacher',
                'qualification' => 'B.A. English / PGDE',
                'hire_date' => '2022-09-05',
                'salary' => 210000,
                'subject_codes' => ['ENG101'],
            ],
            [
                'key' => 'basic_science_teacher',
                'name' => 'Mrs. Funke Adebayo',
                'first_name' => 'Funke',
                'last_name' => 'Adebayo',
                'email' => 'basicscience@belovedschool.test',
                'role' => UserRole::Teacher,
                'phone' => '+2348030001012',
                'password' => $this->defaultPassword,
                'employee_no' => 'BVS-TCH-103',
                'department' => 'Junior Science',
                'designation' => 'Basic Science Teacher',
                'qualification' => 'B.Sc. Integrated Science',
                'hire_date' => '2022-10-01',
                'salary' => 208000,
                'subject_codes' => ['BST101'],
            ],
            [
                'key' => 'social_studies_teacher',
                'name' => 'Mr. Kolade Bakare',
                'first_name' => 'Kolade',
                'last_name' => 'Bakare',
                'email' => 'socialstudies@belovedschool.test',
                'role' => UserRole::Teacher,
                'phone' => '+2348030001013',
                'password' => $this->defaultPassword,
                'employee_no' => 'BVS-TCH-104',
                'department' => 'Humanities',
                'designation' => 'Social Studies Teacher',
                'qualification' => 'B.Sc. Social Studies',
                'hire_date' => '2022-10-10',
                'salary' => 208000,
                'subject_codes' => ['SOS101'],
            ],
            [
                'key' => 'civic_teacher',
                'name' => 'Mrs. Bose Ibrahim',
                'first_name' => 'Bose',
                'last_name' => 'Ibrahim',
                'email' => 'civicedu@belovedschool.test',
                'role' => UserRole::Teacher,
                'phone' => '+2348030001014',
                'password' => $this->defaultPassword,
                'employee_no' => 'BVS-TCH-105',
                'department' => 'Humanities',
                'designation' => 'Civic Education Teacher',
                'qualification' => 'B.A. Political Science',
                'hire_date' => '2022-11-01',
                'salary' => 207000,
                'subject_codes' => ['CVE101'],
            ],
            [
                'key' => 'physics_teacher',
                'name' => 'Mr. Stephen Ojo',
                'first_name' => 'Stephen',
                'last_name' => 'Ojo',
                'email' => 'physics@belovedschool.test',
                'role' => UserRole::Teacher,
                'phone' => '+2348030001015',
                'password' => $this->defaultPassword,
                'employee_no' => 'BVS-TCH-106',
                'department' => 'Science',
                'designation' => 'Physics Teacher',
                'qualification' => 'B.Sc. Physics Education',
                'hire_date' => '2021-09-01',
                'salary' => 225000,
                'subject_codes' => ['PHY201'],
            ],
            [
                'key' => 'chemistry_teacher',
                'name' => 'Mrs. Modupe Ogunleye',
                'first_name' => 'Modupe',
                'last_name' => 'Ogunleye',
                'email' => 'chemistry@belovedschool.test',
                'role' => UserRole::Teacher,
                'phone' => '+2348030001016',
                'password' => $this->defaultPassword,
                'employee_no' => 'BVS-TCH-107',
                'department' => 'Science',
                'designation' => 'Chemistry Teacher',
                'qualification' => 'B.Sc. Chemistry',
                'hire_date' => '2021-10-10',
                'salary' => 225000,
                'subject_codes' => ['CHM201'],
            ],
            [
                'key' => 'biology_teacher',
                'name' => 'Mr. Emmanuel Umeh',
                'first_name' => 'Emmanuel',
                'last_name' => 'Umeh',
                'email' => 'biology@belovedschool.test',
                'role' => UserRole::Teacher,
                'phone' => '+2348030001017',
                'password' => $this->defaultPassword,
                'employee_no' => 'BVS-TCH-108',
                'department' => 'Science',
                'designation' => 'Biology Teacher',
                'qualification' => 'B.Sc. Biology',
                'hire_date' => '2021-11-01',
                'salary' => 225000,
                'subject_codes' => ['BIO201'],
            ],
            [
                'key' => 'economics_teacher',
                'name' => 'Mr. Tunde Ajayi',
                'first_name' => 'Tunde',
                'last_name' => 'Ajayi',
                'email' => 'economics@belovedschool.test',
                'role' => UserRole::Teacher,
                'phone' => '+2348030001018',
                'password' => $this->defaultPassword,
                'employee_no' => 'BVS-TCH-109',
                'department' => 'Commercial',
                'designation' => 'Economics Teacher',
                'qualification' => 'B.Sc. Economics',
                'hire_date' => '2021-11-15',
                'salary' => 223000,
                'subject_codes' => ['ECO201'],
            ],
            [
                'key' => 'commerce_teacher',
                'name' => 'Mrs. Adaeze Okonkwo',
                'first_name' => 'Adaeze',
                'last_name' => 'Okonkwo',
                'email' => 'commerce@belovedschool.test',
                'role' => UserRole::Teacher,
                'phone' => '+2348030001019',
                'password' => $this->defaultPassword,
                'employee_no' => 'BVS-TCH-110',
                'department' => 'Commercial',
                'designation' => 'Commerce Teacher',
                'qualification' => 'B.Sc. Business Education',
                'hire_date' => '2021-12-01',
                'salary' => 223000,
                'subject_codes' => ['COM201'],
            ],
            [
                'key' => 'accounting_teacher',
                'name' => 'Mr. Nnamdi Eze',
                'first_name' => 'Nnamdi',
                'last_name' => 'Eze',
                'email' => 'accounting@belovedschool.test',
                'role' => UserRole::Teacher,
                'phone' => '+2348030001020',
                'password' => $this->defaultPassword,
                'employee_no' => 'BVS-TCH-111',
                'department' => 'Commercial',
                'designation' => 'Financial Accounting Teacher',
                'qualification' => 'B.Sc. Accounting Education',
                'hire_date' => '2022-01-01',
                'salary' => 223000,
                'subject_codes' => ['ACC201'],
            ],
            [
                'key' => 'literature_teacher',
                'name' => 'Mrs. Blessing Nwosu',
                'first_name' => 'Blessing',
                'last_name' => 'Nwosu',
                'email' => 'literature@belovedschool.test',
                'role' => UserRole::Teacher,
                'phone' => '+2348030001021',
                'password' => $this->defaultPassword,
                'employee_no' => 'BVS-TCH-112',
                'department' => 'Arts',
                'designation' => 'Literature Teacher',
                'qualification' => 'B.A. Literature',
                'hire_date' => '2022-01-10',
                'salary' => 221000,
                'subject_codes' => ['LIT201'],
            ],
            [
                'key' => 'government_teacher',
                'name' => 'Mr. Victor Adewale',
                'first_name' => 'Victor',
                'last_name' => 'Adewale',
                'email' => 'government@belovedschool.test',
                'role' => UserRole::Teacher,
                'phone' => '+2348030001022',
                'password' => $this->defaultPassword,
                'employee_no' => 'BVS-TCH-113',
                'department' => 'Arts',
                'designation' => 'Government Teacher',
                'qualification' => 'B.Sc. Political Science',
                'hire_date' => '2022-02-01',
                'salary' => 221000,
                'subject_codes' => ['GOV201'],
            ],
            [
                'key' => 'crs_teacher',
                'name' => 'Mrs. Lydia Onyeka',
                'first_name' => 'Lydia',
                'last_name' => 'Onyeka',
                'email' => 'crs@belovedschool.test',
                'role' => UserRole::Teacher,
                'phone' => '+2348030001023',
                'password' => $this->defaultPassword,
                'employee_no' => 'BVS-TCH-114',
                'department' => 'Arts',
                'designation' => 'CRS Teacher',
                'qualification' => 'B.A. Religious Studies',
                'hire_date' => '2022-02-15',
                'salary' => 219000,
                'subject_codes' => ['CRS201'],
            ],
        ]);
    }

    protected function registerStaff(Collection $definitions): Collection
    {
        return $definitions->mapWithKeys(function (array $staff) {
            $user = $this->createUser([
                'name' => $staff['name'],
                'first_name' => $staff['first_name'],
                'last_name' => $staff['last_name'],
                'email' => $staff['email'],
                'role' => $staff['role'],
                'phone' => $staff['phone'],
                'password' => $staff['password'],
                'temp_password_plaintext' => $staff['password'],
                'temp_password_generated_at' => now(),
            ]);

            StaffProfile::create([
                'user_id' => $user->id,
                'employee_no' => $staff['employee_no'],
                'department' => $staff['department'],
                'designation' => $staff['designation'],
                'qualification' => $staff['qualification'],
                'hire_date' => $staff['hire_date'],
                'salary' => $staff['salary'],
                'status' => 'active',
            ]);

            return [$staff['key'] => $user];
        });
    }

    protected function seedClasses(Collection $staffUsers): Collection
    {
        $definitions = collect([
            ['name' => 'JSS 1', 'section' => 'General', 'room' => 'Block A1', 'description' => 'Junior secondary foundational class.', 'class_teacher_key' => 'english_teacher'],
            ['name' => 'JSS 2', 'section' => 'General', 'room' => 'Block A2', 'description' => 'Junior secondary development class.', 'class_teacher_key' => 'math_teacher'],
            ['name' => 'JSS 3', 'section' => 'General', 'room' => 'Block A3', 'description' => 'Junior secondary transition class.', 'class_teacher_key' => 'social_studies_teacher'],
            ['name' => 'SS 1', 'section' => 'Science', 'room' => 'Block S1', 'description' => 'Senior secondary science stream.', 'class_teacher_key' => 'physics_teacher'],
            ['name' => 'SS 1', 'section' => 'Commercial', 'room' => 'Block C1', 'description' => 'Senior secondary commercial stream.', 'class_teacher_key' => 'economics_teacher'],
            ['name' => 'SS 1', 'section' => 'Art', 'room' => 'Block H1', 'description' => 'Senior secondary arts stream.', 'class_teacher_key' => 'literature_teacher'],
            ['name' => 'SS 2', 'section' => 'Science', 'room' => 'Block S2', 'description' => 'Senior secondary science stream.', 'class_teacher_key' => 'chemistry_teacher'],
            ['name' => 'SS 2', 'section' => 'Commercial', 'room' => 'Block C2', 'description' => 'Senior secondary commercial stream.', 'class_teacher_key' => 'accounting_teacher'],
            ['name' => 'SS 2', 'section' => 'Art', 'room' => 'Block H2', 'description' => 'Senior secondary arts stream.', 'class_teacher_key' => 'government_teacher'],
            ['name' => 'SS 3', 'section' => 'Science', 'room' => 'Block S3', 'description' => 'Senior secondary science graduation class.', 'class_teacher_key' => 'biology_teacher'],
            ['name' => 'SS 3', 'section' => 'Commercial', 'room' => 'Block C3', 'description' => 'Senior secondary commercial graduation class.', 'class_teacher_key' => 'commerce_teacher'],
            ['name' => 'SS 3', 'section' => 'Art', 'room' => 'Block H3', 'description' => 'Senior secondary arts graduation class.', 'class_teacher_key' => 'crs_teacher'],
        ]);

        return $definitions->map(function (array $class) use ($staffUsers) {
            $teacher = $staffUsers->get($class['class_teacher_key']);

            return SchoolClass::create([
                'name' => $class['name'],
                'slug' => Str::slug($class['name'].'-'.$class['section']),
                'section' => $class['section'],
                'class_teacher_id' => $teacher?->id,
                'capacity' => 40,
                'room' => $class['room'],
                'description' => $class['description'],
            ]);
        })->values();
    }

    protected function seedFeeItems(Collection $classes, AcademicSession $session, Term $term): Collection
    {
        return $classes->mapWithKeys(function (SchoolClass $class, int $index) use ($session, $term) {
            $baseAmount = match ($class->name) {
                'JSS 1' => 95000,
                'JSS 2' => 105000,
                'JSS 3' => 115000,
                'SS 1' => 145000,
                'SS 2' => 155000,
                default => 165000,
            };

            $departmentAdjustment = match ($class->section) {
                'Science' => 25000,
                'Commercial' => 15000,
                'Art' => 12000,
                default => 0,
            };

            $tuition = FeeItem::create([
                'name' => sprintf('%s %s Tuition Fee', $class->name, $class->section),
                'academic_session_id' => $session->id,
                'term_id' => $term->id,
                'school_class_id' => $class->id,
                'amount' => $baseAmount + $departmentAdjustment,
                'due_date' => $term->start_date,
                'description' => 'Core tuition fee for '.$class->name.' '.$class->section,
                'is_mandatory' => true,
            ]);

            $development = FeeItem::create([
                'name' => sprintf('%s %s Development Levy', $class->name, $class->section),
                'academic_session_id' => $session->id,
                'term_id' => $term->id,
                'school_class_id' => $class->id,
                'amount' => 18000 + ($index * 500),
                'due_date' => now()->addWeeks(2)->toDateString(),
                'description' => 'Infrastructure and co-curricular support levy.',
                'is_mandatory' => true,
            ]);

            $exam = FeeItem::create([
                'name' => sprintf('%s %s Exam & Materials Fee', $class->name, $class->section),
                'academic_session_id' => $session->id,
                'term_id' => $term->id,
                'school_class_id' => $class->id,
                'amount' => 12000 + ($index * 250),
                'due_date' => now()->addWeeks(4)->toDateString(),
                'description' => 'Assessment materials, scripts, and continuous assessment support.',
                'is_mandatory' => true,
            ]);

            return [$class->id => collect([$tuition, $development, $exam])];
        });
    }

    protected function seedStudents(Collection $classes, AcademicSession $session, Collection $classFeeItems, User $accountant): Collection
    {
        $studentsByClass = collect();
        $studentSerial = 1;

        foreach ($classes as $classIndex => $class) {
            $classStudents = collect();

            for ($position = 1; $position <= 5; $position++) {
                $studentContext = $this->buildStudentContext($class, $classIndex, $position, $studentSerial);

                $parent = $this->createUser([
                    'name' => $studentContext['parent_name'],
                    'first_name' => $studentContext['parent_first_name'],
                    'last_name' => $studentContext['last_name'],
                    'email' => $studentContext['parent_email'],
                    'role' => UserRole::Parent,
                    'phone' => $studentContext['parent_phone'],
                    'password' => $this->defaultPassword,
                    'temp_password_plaintext' => $this->defaultPassword,
                    'temp_password_generated_at' => now(),
                ]);

                $studentUser = $this->createUser([
                    'name' => $studentContext['student_name'],
                    'first_name' => $studentContext['first_name'],
                    'last_name' => $studentContext['last_name'],
                    'email' => $studentContext['student_email'],
                    'role' => UserRole::Student,
                    'phone' => $studentContext['student_phone'],
                    'password' => $this->defaultPassword,
                    'temp_password_plaintext' => $this->defaultPassword,
                    'temp_password_generated_at' => now(),
                ]);

                $student = Student::create([
                    'user_id' => $studentUser->id,
                    'parent_user_id' => $parent->id,
                    'admission_no' => $studentContext['admission_no'],
                    'student_id_no' => $studentContext['student_id_no'],
                    'school_class_id' => $class->id,
                    'academic_session_id' => $session->id,
                    'boarding_status' => $position % 2 === 0 ? 'Day' : 'Boarding',
                    'house' => $this->houses[($studentSerial - 1) % count($this->houses)],
                    'gender' => $position % 2 === 0 ? 'Female' : 'Male',
                    'date_of_birth' => now()->subYears(11 + $this->classAgeOffset($class->name))->subMonths($position)->toDateString(),
                    'place_of_birth' => $this->towns[$classIndex % count($this->towns)],
                    'nationality' => 'Nigerian',
                    'lga' => 'Odigbo',
                    'blood_group' => ['A+', 'B+', 'O+', 'AB+'][($studentSerial - 1) % 4],
                    'state_of_origin' => $this->states[($studentSerial - 1) % count($this->states)],
                    'religion' => 'Christianity',
                    'guardian_name' => $parent->name,
                    'guardian_phone' => $parent->phone,
                    'parents_occupation' => $position % 2 === 0 ? 'Civil Servant' : 'Entrepreneur',
                    'office_residence_phone' => '+234809500'.str_pad((string) $studentSerial, 4, '0', STR_PAD_LEFT),
                    'address' => 'Ayeteju Street, Ore, Ondo State',
                    'previous_school' => $class->name === 'JSS 1' ? 'Beloved Nursery & Primary School' : 'BELOVED SCHOOLS',
                    'previous_class' => $this->previousClassName($class->name),
                    'medical_notes' => 'Fit for school activities.',
                    'physical_notes' => 'No physical limitations recorded.',
                    'doctor_name' => 'Dr. Moses Alade',
                    'doctor_address' => 'Ore Central Clinic',
                    'doctor_phone' => '+234807400'.str_pad((string) $studentSerial, 4, '0', STR_PAD_LEFT),
                    'enrolled_at' => '2025-09-01',
                    'status' => 'active',
                ]);

                $this->seedAttendance($student, $class);
                $this->seedInvoicesAndPayments($student, $classFeeItems->get($class->id, collect()), $accountant, $position);

                $classStudents->push($student);
                $studentSerial++;
            }

            $studentsByClass->put($class->id, $classStudents);
        }

        return $studentsByClass;
    }

    protected function seedAttendance(Student $student, SchoolClass $class): void
    {
        $dates = collect(range(0, 9))->map(fn (int $offset) => now()->subDays(14 - $offset)->toDateString());

        foreach ($dates as $offset => $date) {
            AttendanceRecord::create([
                'school_class_id' => $class->id,
                'student_id' => $student->id,
                'taken_by' => $class->class_teacher_id,
                'attendance_date' => $date,
                'status' => $offset === 4 && $student->id % 7 === 0 ? 'late' : 'present',
                'note' => $offset === 4 && $student->id % 7 === 0 ? 'Arrived after assembly.' : 'Recorded during morning attendance.',
            ]);
        }
    }

    protected function seedInvoicesAndPayments(Student $student, Collection $feeItems, User $accountant, int $studentIndex): void
    {
        foreach ($feeItems as $itemIndex => $feeItem) {
            $paymentFactor = match ($itemIndex) {
                0 => 0.45 + (($studentIndex % 3) * 0.15),
                1 => $studentIndex % 2 === 0 ? 0.60 : 0.00,
                default => 0.00,
            };

            $paidAmount = round((float) $feeItem->amount * $paymentFactor, 2);

            $invoice = FeeInvoice::create([
                'invoice_no' => sprintf('INV-%s-%04d-%02d', now()->format('Y'), $student->id, $itemIndex + 1),
                'student_id' => $student->id,
                'fee_item_id' => $feeItem->id,
                'amount_due' => $feeItem->amount,
                'amount_paid' => $paidAmount,
                'balance' => max((float) $feeItem->amount - $paidAmount, 0),
                'due_date' => $feeItem->due_date,
                'status' => $paidAmount <= 0 ? 'unpaid' : ($paidAmount >= (float) $feeItem->amount ? 'paid' : 'part-paid'),
                'issued_at' => now()->subDays(18),
                'notes' => 'Auto-generated finance record for seeded academic session.',
            ]);

            if ($paidAmount <= 0) {
                continue;
            }

            Payment::create([
                'fee_invoice_id' => $invoice->id,
                'student_id' => $student->id,
                'provider' => $studentIndex % 2 === 0 ? PaymentProvider::Paystack : PaymentProvider::PalmPay,
                'reference' => 'PAY-'.Str::upper(Str::random(10)),
                'receipt_no' => 'RCP-'.Str::upper(Str::random(8)),
                'amount' => $paidAmount,
                'currency' => 'NGN',
                'status' => PaymentStatus::Paid,
                'channel' => $studentIndex % 2 === 0 ? 'online' : 'bank-transfer',
                'paid_at' => now()->subDays(10),
                'recorded_by' => $accountant->id,
                'note' => 'Seeded installment payment.',
                'payload' => ['seeded' => true, 'invoice_no' => $invoice->invoice_no],
            ]);
        }
    }

    protected function seedAcademicContent(Collection $classes, Collection $studentsByClass, Collection $subjects, Term $term, Collection $staffUsers): void
    {
        foreach ($classes as $classIndex => $class) {
            $students = $studentsByClass->get($class->id, collect());
            $classSubjects = $this->subjectsForClass($class, $subjects);

            foreach ($classSubjects as $subjectIndex => $subject) {
                $teacher = $this->teacherForSubject($subject->code, $staffUsers);

                $lesson = Lesson::create([
                    'teacher_id' => $teacher->id,
                    'subject_id' => $subject->id,
                    'school_class_id' => $class->id,
                    'title' => sprintf('%s for %s %s', $subject->name, $class->name, $class->section),
                    'summary' => 'Guided classroom instruction for '.$subject->name.'.',
                    'body' => 'Students are introduced to core concepts, worked examples, classroom exercises, and note-based revision activities for '.$subject->name.'.',
                    'resource_link' => 'https://example.com/resources/'.Str::slug($subject->name),
                    'published_at' => now()->subDays(20 - $subjectIndex),
                ]);

                $assignment = Assignment::create([
                    'teacher_id' => $teacher->id,
                    'subject_id' => $subject->id,
                    'school_class_id' => $class->id,
                    'title' => sprintf('%s Assignment - %s %s', $subject->name, $class->name, $class->section),
                    'instructions' => 'Complete the take-home questions based on the lesson "'.$lesson->title.'". Show all working and submit neatly.',
                    'due_date' => now()->subDays(4 - ($subjectIndex % 3)),
                    'total_score' => 20,
                    'status' => 'published',
                ]);

                foreach ($students as $studentPosition => $student) {
                    AssignmentSubmission::create([
                        'assignment_id' => $assignment->id,
                        'student_id' => $student->id,
                        'content' => 'Auto-seeded response for '.$subject->name.' covering examples, notes, and worked exercises.',
                        'submitted_at' => now()->subDays(3 - ($studentPosition % 2)),
                        'score' => 11 + (($classIndex + $subjectIndex + $studentPosition) % 10),
                        'feedback' => 'Good effort. Improve presentation and revise your corrections.',
                        'graded_by' => $teacher->id,
                    ]);
                }

                foreach ([AssessmentType::Test, AssessmentType::Exam] as $assessmentIndex => $type) {
                    $totalScore = $type === AssessmentType::Exam ? 100 : 40;
                    $assessment = Assessment::create([
                        'teacher_id' => $teacher->id,
                        'term_id' => $term->id,
                        'subject_id' => $subject->id,
                        'school_class_id' => $class->id,
                        'title' => sprintf('%s %s %s', $class->name, $subject->name, $type->label()),
                        'type' => $type,
                        'total_score' => $totalScore,
                        'scheduled_at' => now()->subDays(12 - ($assessmentIndex + $subjectIndex)),
                        'notes' => $type === AssessmentType::Exam ? 'Term examination record.' : 'Continuous assessment record.',
                    ]);

                    foreach ($students as $studentPosition => $student) {
                        $score = $this->generateScore($classIndex, $subjectIndex, $studentPosition, $totalScore, $type === AssessmentType::Exam);
                        ['grade' => $grade, 'remark' => $remark] = $this->gradeData($score, $totalScore);

                        AssessmentResult::create([
                            'assessment_id' => $assessment->id,
                            'student_id' => $student->id,
                            'score' => $score,
                            'grade' => $grade,
                            'remark' => $remark,
                        ]);
                    }
                }
            }
        }
    }

    protected function seedAnnouncements(Collection $staffUsers): void
    {
        Announcement::create([
            'title' => 'Resumption update for all students',
            'slug' => 'resumption-update-for-all-students',
            'excerpt' => 'All classes and departments now have complete digital records for the current session.',
            'body' => 'BELOVED SCHOOLS has fully updated attendance, academic records, fee schedules, staffing structure, and class activity data for the current session.',
            'category' => 'news',
            'is_published' => true,
            'published_at' => now()->subDays(7),
            'author_id' => $staffUsers->get('principal')->id,
        ]);

        Announcement::create([
            'title' => 'Second term assessment schedule released',
            'slug' => 'second-term-assessment-schedule-released',
            'excerpt' => 'Teachers have uploaded tests, assignments, and examination records across all classes.',
            'body' => 'Students in JSS and SS classes should review their lesson notes, assignments, and portal notices ahead of the next assessment cycle.',
            'category' => 'event',
            'is_published' => true,
            'published_at' => now()->subDays(3),
            'author_id' => $staffUsers->get('exam_officer')->id,
        ]);
    }

    protected function exportLoginSheets(Collection $staffUsers): void
    {
        $exportDir = storage_path('exports');
        File::ensureDirectoryExists($exportDir);

        $studentFile = fopen($exportDir.DIRECTORY_SEPARATOR.'student-logins.csv', 'w');
        fputcsv($studentFile, ['Name', 'Email', 'Password', 'Admission No', 'Student ID', 'Class', 'Section', 'Parent', 'Parent Email', 'Phone']);

        Student::query()
            ->with('user', 'parent', 'schoolClass')
            ->get()
            ->sortBy(fn (Student $student) => sprintf('%s-%s', $student->schoolClass?->name ?? '', $student->admission_no))
            ->each(function (Student $student) use ($studentFile): void {
                fputcsv($studentFile, [
                    $student->user?->fullName(),
                    $student->user?->email,
                    $student->user?->temp_password_plaintext ?: $this->defaultPassword,
                    $student->admission_no,
                    $student->student_id_no,
                    $student->schoolClass?->name,
                    $student->schoolClass?->section,
                    $student->parent?->fullName(),
                    $student->parent?->email,
                    $student->user?->phone,
                ]);
            });
        fclose($studentFile);

        $staffFile = fopen($exportDir.DIRECTORY_SEPARATOR.'staff-logins.csv', 'w');
        fputcsv($staffFile, ['Name', 'Email', 'Password', 'Role', 'Department', 'Designation', 'Employee No', 'Phone']);

        $staffUsers
            ->values()
            ->each(fn (User $user) => $user->load('staffProfile'))
            ->sortBy(fn (User $user) => $user->staffProfile?->employee_no ?? $user->email)
            ->each(function (User $user) use ($staffFile): void {
                fputcsv($staffFile, [
                    $user->fullName(),
                    $user->email,
                    $user->temp_password_plaintext ?: $this->defaultPassword,
                    $user->role?->value ?? $user->role,
                    $user->staffProfile?->department,
                    $user->staffProfile?->designation,
                    $user->staffProfile?->employee_no,
                    $user->phone,
                ]);
            });
        fclose($staffFile);

        $summary = [
            '# BELOVED SCHOOLS Demo Data Export',
            '',
            'Student export: `storage/exports/student-logins.csv`',
            'Staff export: `storage/exports/staff-logins.csv`',
            '',
            'All seeded demo accounts use the password `password` unless overridden by `.env` for the default admin.',
        ];

        file_put_contents($exportDir.DIRECTORY_SEPARATOR.'README.md', implode(PHP_EOL, $summary));
    }

    protected function teacherForSubject(string $subjectCode, Collection $staffUsers): User
    {
        return match ($subjectCode) {
            'MTH101' => $staffUsers->get('math_teacher'),
            'ENG101' => $staffUsers->get('english_teacher'),
            'BST101' => $staffUsers->get('basic_science_teacher'),
            'SOS101' => $staffUsers->get('social_studies_teacher'),
            'CVE101' => $staffUsers->get('civic_teacher'),
            'PHY201' => $staffUsers->get('physics_teacher'),
            'CHM201' => $staffUsers->get('chemistry_teacher'),
            'BIO201' => $staffUsers->get('biology_teacher'),
            'ECO201' => $staffUsers->get('economics_teacher'),
            'COM201' => $staffUsers->get('commerce_teacher'),
            'ACC201' => $staffUsers->get('accounting_teacher'),
            'LIT201' => $staffUsers->get('literature_teacher'),
            'GOV201' => $staffUsers->get('government_teacher'),
            'CRS201' => $staffUsers->get('crs_teacher'),
            default => $staffUsers->get('vp_academics'),
        };
    }

    protected function subjectsForClass(SchoolClass $class, Collection $subjects): Collection
    {
        $codes = match (true) {
            str_starts_with($class->name, 'JSS') => ['MTH101', 'ENG101', 'BST101', 'SOS101', 'CVE101'],
            $class->section === 'Science' => ['ENG101', 'MTH101', 'PHY201', 'CHM201', 'BIO201'],
            $class->section === 'Commercial' => ['ENG101', 'MTH101', 'ECO201', 'COM201', 'ACC201'],
            default => ['ENG101', 'MTH101', 'LIT201', 'GOV201', 'CRS201'],
        };

        return collect($codes)->map(fn (string $code) => $subjects->get($code))->filter()->values();
    }

    protected function buildStudentContext(SchoolClass $class, int $classIndex, int $position, int $serial): array
    {
        $firstName = $this->firstNames[($serial - 1) % count($this->firstNames)];
        $lastName = $this->lastNames[($classIndex + $position - 1) % count($this->lastNames)];
        $studentName = $firstName.' '.$lastName;
        $slug = Str::slug($class->name.'-'.$class->section);
        $sectionCode = strtoupper(substr($class->section, 0, 3));

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'student_name' => $studentName,
            'student_email' => 'student.'.strtolower($slug).'.'.$position.'@'.$this->schoolDomain,
            'student_phone' => '+234811200'.str_pad((string) $serial, 4, '0', STR_PAD_LEFT),
            'parent_first_name' => 'Parent'.$position,
            'parent_name' => 'Mr./Mrs. '.$lastName,
            'parent_email' => 'parent.'.strtolower($slug).'.'.$position.'@'.$this->schoolDomain,
            'parent_phone' => '+234812300'.str_pad((string) $serial, 4, '0', STR_PAD_LEFT),
            'admission_no' => sprintf('BVS-%s-%s-%03d', Str::upper(str_replace(' ', '', $class->name)), $sectionCode, $position),
            'student_id_no' => sprintf('STD-%04d', $serial),
        ];
    }

    protected function previousClassName(string $className): string
    {
        return match ($className) {
            'JSS 1' => 'Primary 6',
            'JSS 2' => 'JSS 1',
            'JSS 3' => 'JSS 2',
            'SS 1' => 'JSS 3',
            'SS 2' => 'SS 1',
            default => 'SS 2',
        };
    }

    protected function classAgeOffset(string $className): int
    {
        return match ($className) {
            'JSS 1' => 0,
            'JSS 2' => 1,
            'JSS 3' => 2,
            'SS 1' => 3,
            'SS 2' => 4,
            default => 5,
        };
    }

    protected function generateScore(int $classIndex, int $subjectIndex, int $studentPosition, float $totalScore, bool $exam = false): float
    {
        $basePercent = 56 + (($classIndex * 4 + $subjectIndex * 7 + $studentPosition * 5) % 31);

        if ($exam) {
            $basePercent += 4;
        }

        return round(($basePercent / 100) * $totalScore, 2);
    }

    protected function gradeData(float $score, float $totalScore): array
    {
        $percentage = $totalScore > 0 ? ($score / $totalScore) * 100 : 0;

        return match (true) {
            $percentage >= 80 => ['grade' => 'A1', 'remark' => 'Excellent'],
            $percentage >= 70 => ['grade' => 'B2', 'remark' => 'Very Good'],
            $percentage >= 60 => ['grade' => 'B3', 'remark' => 'Good'],
            $percentage >= 50 => ['grade' => 'C4', 'remark' => 'Credit'],
            $percentage >= 45 => ['grade' => 'C5', 'remark' => 'Fair'],
            $percentage >= 40 => ['grade' => 'C6', 'remark' => 'Pass'],
            default => ['grade' => 'F9', 'remark' => 'Needs Improvement'],
        };
    }

    protected function createUser(array $attributes): User
    {
        return User::create([
            'name' => $attributes['name'],
            'first_name' => $attributes['first_name'] ?? null,
            'middle_name' => $attributes['middle_name'] ?? null,
            'last_name' => $attributes['last_name'] ?? null,
            'email' => $attributes['email'],
            'email_verified_at' => now(),
            'password' => Hash::make($attributes['password'] ?? $this->defaultPassword),
            'role' => $attributes['role'],
            'phone' => $attributes['phone'] ?? null,
            'status' => $attributes['status'] ?? 'active',
            'avatar_url' => $attributes['avatar_url'] ?? null,
            'temp_password_plaintext' => $attributes['temp_password_plaintext'] ?? null,
            'temp_password_generated_at' => $attributes['temp_password_generated_at'] ?? null,
        ]);
    }
}
