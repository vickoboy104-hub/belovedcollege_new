@php
    $user = auth()->user();
    $roleLabel = $user?->roleLabel() ?? 'Portal';
    
    // Check if we have active student & term context in the request to build report workspace sub-links
    $currentStudentId = request()->route('student') ?? request()->query('student');
    if ($currentStudentId && is_object($currentStudentId)) {
        $currentStudentId = $currentStudentId->id;
    }
    $currentTermId = request()->query('term_id');
    $currentClassSlug = request()->query('classSlug');
    $currentSearch = request()->query('search');

    $reportChildren = [];
    if ($currentStudentId) {
        $reportState = array_filter([
            'student' => $currentStudentId,
            'term_id' => $currentTermId,
            'classSlug' => $currentClassSlug,
            'search' => $currentSearch,
        ], fn ($value) => $value !== null && $value !== '');

        $reportChildren = [
            ['label' => 'Reports Directory', 'route' => 'admin.reports.index', 'params' => array_filter(['classSlug' => $currentClassSlug, 'term_id' => $currentTermId, 'search' => $currentSearch])],
            ['label' => 'Overview', 'section' => 'overview', 'route' => 'admin.reports.show', 'params' => array_merge(['section' => 'overview'], $reportState)],
            ['label' => 'Compiled Scores', 'section' => 'scores', 'route' => 'admin.reports.show', 'params' => array_merge(['section' => 'scores'], $reportState)],
            ['label' => 'Remarks & Ratings', 'section' => 'remarks', 'route' => 'admin.reports.show', 'params' => array_merge(['section' => 'remarks'], $reportState)],
            ['label' => 'Publication Settings', 'section' => 'publication', 'route' => 'admin.reports.show', 'params' => array_merge(['section' => 'publication'], $reportState)],
        ];
    } else {
        $reportChildren = [
            ['label' => 'Reports Directory', 'route' => 'admin.reports.index'],
        ];
    }

    $portalStudentId = request()->query('student');
    if ($portalStudentId && is_object($portalStudentId)) {
        $portalStudentId = $portalStudentId->id;
    }
    $portalParams = $portalStudentId ? ['student' => $portalStudentId] : [];

    $portalChildren = [
        ['label' => 'Overview', 'section' => 'overview', 'route' => 'portal.index', 'params' => array_merge(['section' => 'overview'], $portalParams)],
        ['label' => 'Term Reports', 'section' => 'reports', 'route' => 'portal.index', 'params' => array_merge(['section' => 'reports'], $portalParams)],
        ['label' => 'Lesson Notes', 'section' => 'lessons', 'route' => 'portal.index', 'params' => array_merge(['section' => 'lessons'], $portalParams)],
        ['label' => 'Assignments', 'section' => 'assignments', 'route' => 'portal.index', 'params' => array_merge(['section' => 'assignments'], $portalParams)],
        ['label' => 'Test Grades', 'section' => 'results', 'route' => 'portal.index', 'params' => array_merge(['section' => 'results'], $portalParams)],
        ['label' => 'CBT Exams', 'section' => 'cbt', 'route' => 'portal.index', 'params' => array_merge(['section' => 'cbt'], $portalParams)],
        ['label' => 'Billing & Fees', 'section' => 'billing', 'route' => 'portal.index', 'params' => array_merge(['section' => 'billing'], $portalParams)],
        ['label' => 'Attendance Log', 'section' => 'attendance', 'route' => 'portal.index', 'params' => array_merge(['section' => 'attendance'], $portalParams)],
    ];

    $academicsChildren = [
        ['label' => 'Session Setup', 'section' => 'session-setup', 'route' => 'admin.academics', 'params' => ['section' => 'session-setup'], 'keywords' => 'academic year, school calendar, session management'],
        ['label' => 'Term Setup', 'section' => 'term-setup', 'route' => 'admin.academics', 'params' => ['section' => 'term-setup'], 'keywords' => 'semester, term dates, academic period'],
        ['label' => 'Rollover & Closure', 'section' => 'session-rollover', 'route' => 'admin.academics', 'params' => ['section' => 'session-rollover'], 'keywords' => 'end of year, session close, move students'],
        ['label' => 'Promotions Review', 'section' => 'promotion-review', 'route' => 'admin.academics', 'params' => ['section' => 'promotion-review'], 'keywords' => 'promote students, grade levels, academic progression'],
        ['label' => 'Classes Setup', 'section' => 'class-setup', 'route' => 'admin.academics', 'params' => ['section' => 'class-setup'], 'keywords' => 'classroom, grade, category, level'],
        ['label' => 'Subjects Setup', 'section' => 'subject-setup', 'route' => 'admin.academics', 'params' => ['section' => 'subject-setup'], 'keywords' => 'topics, syllabus, courses, curriculum, subjects'],
        ['label' => 'Announcements', 'section' => 'announcement', 'route' => 'admin.academics', 'params' => ['section' => 'announcement'], 'keywords' => 'news, notifications, school board, updates'],
        ['label' => 'CBT Control Room', 'section' => 'cbt-control', 'route' => 'admin.academics', 'params' => ['section' => 'cbt-control'], 'keywords' => 'computer based test, exams, online assessments, quiz'],
    ];

    $peopleChildren = [
        ['label' => 'People Overview', 'route' => 'admin.people', 'keywords' => 'users, directory, counts, statistics'],
        ['label' => 'Students', 'route' => 'admin.students.index', 'keywords' => 'enrolled students, children, pupils'],
        ['label' => 'Parents', 'route' => 'admin.parents.index', 'keywords' => 'guardians, families, next of kin'],
        ['label' => 'Staff', 'route' => 'admin.staff.index', 'keywords' => 'teachers, employees, workers, payroll, administration'],
    ];

    $studentChildren = [
        ['label' => 'Directory', 'route' => 'admin.students.index', 'params' => ['view' => 'directory'], 'view' => 'directory', 'keywords' => 'student list, search students, enrollment'],
        ['label' => 'New Students', 'route' => 'admin.students.index', 'params' => ['view' => 'new-students'], 'view' => 'new-students', 'keywords' => 'fresh students, recently added, admissions'],
        ['label' => 'Inactive', 'route' => 'admin.students.index', 'params' => ['view' => 'inactive'], 'view' => 'inactive', 'keywords' => 'withdrawn, graduated, left school'],
        ['label' => 'Siblings', 'route' => 'admin.students.index', 'params' => ['view' => 'siblings'], 'view' => 'siblings', 'keywords' => 'family links, brothers, sisters'],
        ['label' => 'Debtors', 'route' => 'admin.students.index', 'params' => ['view' => 'debtors'], 'view' => 'debtors', 'keywords' => 'unpaid fees, balance due, financial status'],
        ['label' => 'Class Bills', 'route' => 'admin.students.index', 'params' => ['view' => 'class-bills'], 'view' => 'class-bills', 'keywords' => 'student invoices, term fees, billing'],
    ];

    $parentChildren = [
        ['label' => 'Parent Directory', 'route' => 'admin.parents.index', 'keywords' => 'guardian list, search parents'],
        ['label' => 'Student Links', 'route' => 'admin.students.index', 'params' => ['view' => 'siblings'], 'view' => 'siblings', 'keywords' => 'connected children, families'],
        ['label' => 'Debtor Families', 'route' => 'admin.students.index', 'params' => ['view' => 'debtors'], 'view' => 'debtors', 'keywords' => 'unpaid accounts, parent balances'],
    ];

    $staffChildren = [
        ['label' => 'Directory', 'route' => 'admin.staff.index', 'params' => ['view' => 'directory'], 'view' => 'directory', 'keywords' => 'teacher list, employee records, staff search'],
        ['label' => 'Payroll', 'route' => 'admin.staff.index', 'params' => ['view' => 'payroll'], 'view' => 'payroll', 'keywords' => 'salaries, payments, staff finance, wages'],
        ['label' => 'Class Allocation', 'route' => 'admin.staff.index', 'params' => ['view' => 'class-allocation'], 'view' => 'class-allocation', 'keywords' => 'assign teachers, class master, subject teachers'],
    ];

    $settingsChildren = [
        ['label' => 'Foundation', 'section' => 'website-foundation', 'route' => 'admin.settings', 'params' => ['section' => 'website-foundation'], 'keywords' => 'school name, email, phone, logo, favicon, address, motto, details, headers'],
        ['label' => 'Theme', 'section' => 'theme-colors', 'route' => 'admin.settings', 'params' => ['section' => 'theme-colors'], 'keywords' => 'colors, styling, appearance, brand, custom theme'],
        ['label' => 'Landing Builder', 'section' => 'landing-builder', 'route' => 'admin.settings', 'params' => ['section' => 'landing-builder'], 'keywords' => 'homepage builder, admissions page, slides, testimonials, topics, headers'],
        ['label' => 'Homepage Media', 'section' => 'homepage-media', 'route' => 'admin.settings', 'params' => ['section' => 'homepage-media'], 'keywords' => 'videos, banners, gallery images, hero media'],
        ['label' => 'Workspace BG', 'section' => 'workspace-backgrounds', 'route' => 'admin.settings', 'params' => ['section' => 'workspace-backgrounds'], 'keywords' => 'dashboard wallpaper, portal background'],
        ['label' => 'Site BG', 'section' => 'site-backgrounds', 'route' => 'admin.settings', 'params' => ['section' => 'site-backgrounds'], 'keywords' => 'parallax backgrounds, website sections'],
        ['label' => 'Popup', 'section' => 'welcome-popup', 'route' => 'admin.settings', 'params' => ['section' => 'welcome-popup'], 'keywords' => 'greeting banner, notice, announcements'],
        ['label' => 'Gallery', 'section' => 'gallery-uploader', 'route' => 'admin.settings', 'params' => ['section' => 'gallery-uploader'], 'keywords' => 'photos, images, quick upload'],
        ['label' => 'Homepage Text', 'section' => 'homepage-text', 'route' => 'admin.settings', 'params' => ['section' => 'homepage-text'], 'keywords' => 'copywriting, titles, descriptions, blurbs'],
        ['label' => 'Box BG A', 'section' => 'box-backgrounds-a', 'route' => 'admin.settings', 'params' => ['section' => 'box-backgrounds-a'], 'keywords' => 'highlight backgrounds, stat box wallpapers'],
        ['label' => 'Box BG B', 'section' => 'box-backgrounds-b', 'route' => 'admin.settings', 'params' => ['section' => 'box-backgrounds-b'], 'keywords' => 'academic backgrounds, founders box backgrounds'],
        ['label' => 'Payments', 'section' => 'payment-settings', 'route' => 'admin.settings', 'params' => ['section' => 'payment-settings'], 'keywords' => 'paystack, palmpay, bank accounts, checkout settings'],
        ['label' => 'Messages', 'section' => 'contact-messages', 'route' => 'admin.settings', 'params' => ['section' => 'contact-messages'], 'keywords' => 'contact form, inquiries, visitor messages'],
    ];

    $financeDeskChildren = [
        ['label' => 'Fee Items', 'section' => 'create-fee-item', 'route' => 'admin.finance', 'params' => ['section' => 'create-fee-item'], 'keywords' => 'billing items, costs, pricing'],
        ['label' => 'Invoices', 'section' => 'generate-invoice', 'route' => 'admin.finance', 'params' => ['section' => 'generate-invoice'], 'keywords' => 'bills, student fees, payment requests'],
        ['label' => 'Record Payment', 'section' => 'record-payment', 'route' => 'admin.finance', 'params' => ['section' => 'record-payment'], 'keywords' => 'cash, transfer, receipts'],
        ['label' => 'Overview', 'section' => 'finance-overview', 'route' => 'admin.finance', 'params' => ['section' => 'finance-overview'], 'keywords' => 'financial stats, revenue, summaries'],
        ['label' => 'Recent Invoices', 'section' => 'recent-invoices', 'route' => 'admin.finance', 'params' => ['section' => 'recent-invoices'], 'keywords' => 'latest bills, pending payments'],
    ];

    $financeRecordsChildren = [
        ['label' => 'Printable List', 'section' => 'printable-fee-list', 'route' => 'admin.finance.records', 'params' => ['section' => 'printable-fee-list'], 'keywords' => 'pdf reports, fee lists, export'],
        ['label' => 'Fee Catalog', 'section' => 'created-fee-items', 'route' => 'admin.finance.records', 'params' => ['section' => 'created-fee-items'], 'keywords' => 'all fees, price list'],
        ['label' => 'Student Balances', 'section' => 'student-balances', 'route' => 'admin.finance.records', 'params' => ['section' => 'student-balances'], 'keywords' => 'debtors, overpayments, account status'],
        ['label' => 'Class Bills', 'section' => 'class-bills', 'route' => 'admin.finance.records', 'params' => ['section' => 'class-bills'], 'keywords' => 'class summaries, bulk billing'],
        ['label' => 'Payment Summary', 'section' => 'payment-summary', 'route' => 'admin.finance.records', 'params' => ['section' => 'payment-summary'], 'keywords' => 'financial reports, daily collections'],
        ['label' => 'Overpayments', 'section' => 'overpayment-tracker', 'route' => 'admin.finance.records', 'params' => ['section' => 'overpayment-tracker'], 'keywords' => 'credit balances, advance payments'],
        ['label' => 'Progression', 'section' => 'payment-progression', 'route' => 'admin.finance.records', 'params' => ['section' => 'payment-progression'], 'keywords' => 'collection trends, time analysis'],
        ['label' => 'Recent Payments', 'section' => 'recent-payments', 'route' => 'admin.finance.records', 'params' => ['section' => 'recent-payments'], 'keywords' => 'history, logs, latest transactions'],
    ];

    $teachingChildren = [
        ['label' => 'Lessons', 'section' => 'publish-lesson', 'route' => 'teacher.learning', 'params' => ['section' => 'publish-lesson'], 'keywords' => 'topics, syllabus, learning material, notes'],
        ['label' => 'Assignments', 'section' => 'create-assignment', 'route' => 'teacher.learning', 'params' => ['section' => 'create-assignment'], 'keywords' => 'homework, projects, tasks'],
        ['label' => 'Assessments', 'section' => 'assessment', 'route' => 'teacher.learning', 'params' => ['section' => 'assessment'], 'keywords' => 'tests, quiz, grading, exams'],
        ['label' => 'Results', 'section' => 'record-result', 'route' => 'teacher.learning', 'params' => ['section' => 'record-result'], 'keywords' => 'grades, marks, scores, reports'],
        ['label' => 'Attendance', 'section' => 'attendance', 'route' => 'teacher.learning', 'params' => ['section' => 'attendance'], 'keywords' => 'presence, rolls, absence'],
        ['label' => 'Create CBT', 'section' => 'cbt-create', 'route' => 'teacher.learning', 'params' => ['section' => 'cbt-create'], 'keywords' => 'exam builder, question bank'],
        ['label' => 'CBT Library', 'section' => 'cbt-list', 'route' => 'teacher.learning', 'params' => ['section' => 'cbt-list'], 'keywords' => 'all exams, past tests'],
        ['label' => 'Latest Content', 'section' => 'latest-content', 'route' => 'teacher.learning', 'params' => ['section' => 'latest-content'], 'keywords' => 'recent uploads, news'],
        ['label' => 'Submissions', 'section' => 'submissions', 'route' => 'teacher.learning', 'params' => ['section' => 'submissions'], 'keywords' => 'marking, homework review'],
        ['label' => 'CBT Reviews', 'section' => 'cbt-attempts', 'route' => 'teacher.learning', 'params' => ['section' => 'cbt-attempts'], 'keywords' => 'student performance, exam review'],
    ];

    $navGroups = [
        [
            'label' => 'Workspace',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => 'dashboard', 'icon' => 'dashboard'],
            ],
        ],
    ];

    if ($user?->hasAnyRole(['admin', 'principal'])) {
        $navGroups[] = [
            'label' => 'Administration',
            'items' => [
                [
                    'label' => 'People Hub',
                    'route' => 'admin.people',
                    'active' => 'admin.people|admin.students.*|admin.parents.*|admin.staff.*',
                    'icon' => 'people',
                    'id' => 'people_hub',
                    'children' => $peopleChildren,
                    'keywords' => 'staff, students, parents, directory, users, profiles, management',
                ],
                [
                    'label' => 'School Management', 
                    'route' => 'admin.academics', 
                    'active' => 'admin.academics*', 
                    'icon' => 'school',
                    'id' => 'school_mgmt',
                    'children' => $academicsChildren,
                    'keywords' => 'sessions, terms, rollover, promotions, classes, subjects, announcements, CBT, academics, topics, headers',
                ],
                [
                    'label' => 'Student Management',
                    'route' => 'admin.students.index',
                    'active' => 'admin.students.*',
                    'icon' => 'student',
                    'id' => 'student_mgmt',
                    'children' => $studentChildren,
                    'keywords' => 'students, directory, debtors, bills',
                ],
                [
                    'label' => 'Parents Management',
                    'route' => 'admin.parents.index',
                    'active' => 'admin.parents.*|admin.students.index',
                    'icon' => 'parents',
                    'id' => 'parents_mgmt',
                    'children' => $parentChildren,
                    'keywords' => 'parents, families, directory',
                ],
                [
                    'label' => 'Staff Management',
                    'route' => 'admin.staff.index',
                    'active' => 'admin.staff.*',
                    'icon' => 'staff',
                    'id' => 'staff_mgmt',
                    'children' => $staffChildren,
                    'keywords' => 'teachers, payroll, directory, employees',
                ],
                [
                    'label' => 'Reports', 
                    'route' => 'admin.reports.index', 
                    'active' => 'admin.reports*', 
                    'icon' => 'reports',
                    'id' => 'reports_mgmt',
                    'children' => $reportChildren,
                    'keywords' => 'academic reports, results, scores, remarks, publication',
                ],
                [
                    'label' => 'Settings',
                    'route' => 'admin.settings',
                    'active' => 'admin.settings*',
                    'icon' => 'settings',
                    'id' => 'settings_mgmt',
                    'children' => $settingsChildren,
                    'keywords' => 'school info, website, theme, payments, details, headers, topics',
                ],
            ],
        ];
    }

    if ($user?->hasAnyRole(['admin', 'principal', 'accountant'])) {
        $navGroups[] = [
            'label' => 'Finance',
            'items' => [
                [
                    'label' => 'Bills & Payment',
                    'route' => 'admin.finance',
                    'active' => 'admin.finance',
                    'icon' => 'bills',
                    'id' => 'finance_desk',
                    'children' => $financeDeskChildren,
                    'keywords' => 'money, fees, invoices, payments, revenue, billing',
                ],
                [
                    'label' => 'Finance Records',
                    'route' => 'admin.finance.records',
                    'active' => 'admin.finance.records|admin.finance.printable-fee-list',
                    'icon' => 'finance-records',
                    'id' => 'finance_records',
                    'children' => $financeRecordsChildren,
                    'keywords' => 'financial reports, transactions, balances, collection history, records',
                ],
            ],
        ];
    }

    if ($user?->hasAnyRole(['admin', 'principal', 'teacher'])) {
        $navGroups[] = [
            'label' => 'Teaching',
            'items' => [
                [
                    'label' => 'Learning Workspace',
                    'route' => 'teacher.learning',
                    'active' => 'teacher.learning|teacher.cbt.*',
                    'icon' => 'learning',
                    'id' => 'teacher_workspace',
                    'children' => $teachingChildren,
                    'keywords' => 'lessons, assignments, assessments, results, attendance, CBT, classroom, topics',
                ],
            ],
        ];
    }

    if ($user?->hasAnyRole(['student', 'parent'])) {
        $navGroups[] = [
            'label' => 'Student',
            'items' => [
                [
                    'label' => 'Student Portal', 
                    'route' => 'portal.index', 
                    'active' => 'portal.index|portal.cbt.*|portal.results.*|portal.record', 
                    'icon' => 'portal',
                    'id' => 'student_portal',
                    'children' => $portalChildren
                ],
            ],
        ];
    }

    $accountLinks = [
        ['label' => 'Profile', 'route' => 'profile.edit', 'active' => 'profile.*', 'icon' => 'profile'],
    ];

    $userInitials = collect(explode(' ', trim($user?->name ?? 'User')))
        ->filter()
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->take(2)
        ->join('');

    $mobileCoreItems = collect($navGroups)
        ->flatMap(fn ($group) => $group['items'])
        ->filter(fn ($item) => isset($item['route']))
        ->reject(fn ($item) => ($item['route'] ?? null) === 'dashboard')
        ->take(3)
        ->values()
        ->all();

    $mobileNavItems = array_merge(
        [['label' => 'Home', 'route' => 'dashboard', 'active' => 'dashboard', 'icon' => 'dashboard']],
        $mobileCoreItems,
        [['label' => 'Profile', 'route' => 'profile.edit', 'active' => 'profile.*', 'icon' => 'profile']]
    );

    $defaultOpenGroup = match (true) {
        request()->routeIs('portal.*') => 'student_portal',
        request()->routeIs('admin.academics*') => 'school_mgmt',
        request()->routeIs('admin.reports*') => 'reports_mgmt',
        request()->routeIs('admin.settings*') => 'settings_mgmt',
        request()->routeIs('admin.finance.records') || request()->routeIs('admin.finance.printable-fee-list') => 'finance_records',
        request()->routeIs('admin.finance') => 'finance_desk',
        request()->routeIs('teacher.*') => 'teacher_workspace',
        request()->routeIs('admin.staff.*') => 'staff_mgmt',
        request()->routeIs('admin.parents.*') => 'parents_mgmt',
        request()->routeIs('admin.students.*') => 'student_mgmt',
        request()->routeIs('admin.people') => 'people_hub',
        default => '',
    };

    $sidebarSearchItems = [];
    $addSidebarSearchEntry = function (array $entry, array $trail = [], int $depth = 0) use (&$addSidebarSearchEntry, &$sidebarSearchItems) {
        $label = $entry['label'] ?? null;

        if (! $label) {
            return;
        }

        $children = $entry['children'] ?? [];
        $href = isset($entry['route']) ? route($entry['route'], $entry['params'] ?? []) : null;
        $fullTrail = array_values(array_filter([...$trail, $label]));

        if ($href) {
            $sidebarSearchItems[] = [
                'label' => $label,
                'context' => implode(' / ', $trail),
                'trail' => implode(' / ', $fullTrail),
                'href' => $href,
                'depth' => $depth,
                'type' => $depth === 0 ? (count($children) ? 'Menu' : 'Page') : 'Sub menu',
                'keywords' => implode(' ', array_filter([
                    $entry['id'] ?? '',
                    $entry['active'] ?? '',
                    $entry['section'] ?? '',
                    $entry['view'] ?? '',
                    $entry['route'] ?? '',
                    $entry['keywords'] ?? '',
                ])),
            ];
        }

        foreach ($children as $child) {
            $addSidebarSearchEntry($child, $fullTrail, $depth + 1);
        }
    };

    foreach ($navGroups as $group) {
        foreach ($group['items'] as $item) {
            $addSidebarSearchEntry($item, [$group['label']], 0);
        }
    }

    foreach ($accountLinks as $item) {
        $addSidebarSearchEntry($item, ['Account'], 0);
    }

    $sidebarSearchItems = collect($sidebarSearchItems)
        ->unique(fn ($item) => $item['href'].'|'.$item['trail'])
        ->values()
        ->all();

    $navSearchEntryBaseText = function (array $entry, array $trail = []): string {
        return collect([
            ...$trail,
            $entry['label'] ?? '',
            $entry['id'] ?? '',
            $entry['active'] ?? '',
            $entry['section'] ?? '',
            $entry['view'] ?? '',
            $entry['route'] ?? '',
            $entry['keywords'] ?? '',
            isset($entry['params']) ? implode(' ', array_filter($entry['params'])) : '',
        ])
            ->filter(fn ($value) => filled($value))
            ->implode(' ');
    };

    $navSearchEntryText = function (array $entry, array $trail = []) use (&$navSearchEntryText, $navSearchEntryBaseText): string {
        $children = collect($entry['children'] ?? [])
            ->map(fn ($child) => $navSearchEntryText($child, [...$trail, $entry['label'] ?? '']))
            ->implode(' ');

        return trim($navSearchEntryBaseText($entry, $trail).' '.$children);
    };

    $navSearchGroupText = function (array $group) use ($navSearchEntryText): string {
        return trim(collect([
            $group['label'] ?? '',
            collect($group['items'] ?? [])
                ->map(fn ($item) => $navSearchEntryText($item, [$group['label'] ?? '']))
                ->implode(' '),
        ])->filter()->implode(' '));
    };
@endphp

<div
    x-data="portalNavigation({
        defaultOpenGroup: @js($defaultOpenGroup),
        activeSection: @js(request()->route('section') ?? 'overview'),
    })"
    x-effect="document.body.classList.toggle('overflow-hidden', open)"
    x-on:keydown.escape.window="open = false"
    x-on:portal-search-opened.window="open = false"
    x-on:section-change.window="activeSection = $event.detail"
>
    <nav class="app-topbar fixed inset-x-0 top-0 z-50 border-b">
        <div class="app-topbar-row flex items-center justify-between gap-3 px-3 sm:px-6 lg:px-8">
            <div class="app-topbar-left flex min-w-0 flex-1 items-center gap-3">
                <button
                    type="button"
                    @click="open = !open"
                    :class="{ 'is-open': open }"
                    :aria-expanded="open.toString()"
                    class="hamburger-button rounded-2xl border border-white/25 text-white shadow-sm lg:hidden"
                    aria-label="Toggle sidebar navigation"
                >
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                </button>

                <a href="{{ route('dashboard') }}" class="app-topbar-brand flex min-w-0 items-center gap-3">
                    <x-application-logo class="app-topbar-logo h-10 w-10 shrink-0 sm:h-11 sm:w-11" />
                    <div class="nav-brand-copy">
                        <div class="nav-brand-title display-font text-sm font-bold">{{ $schoolSettings['school_name'] ?? 'BELOVED SCHOOLS' }}</div>
                        <div class="nav-brand-subtitle text-xs uppercase">{{ $roleLabel }}</div>
                    </div>
                </a>
            </div>

            <div class="app-topbar-center hidden min-w-0 flex-1 items-center justify-center md:flex">
                <button type="button" class="app-topbar-search cursor-pointer" x-on:click="$dispatch('open-command-palette')" aria-label="Open command palette">
                    <x-app-icon name="search" class="h-4 w-4" />
                    <span>Search workspace...</span>
                    <span class="ml-auto flex flex-none items-center gap-1 text-[0.65rem] font-bold uppercase tracking-wider opacity-50">
                        <kbd class="rounded border border-white/20 px-1 py-0.5 font-sans">⌘</kbd>
                        <kbd class="rounded border border-white/20 px-1 py-0.5 font-sans">K</kbd>
                    </span>
                </button>
            </div>

            <div class="app-topbar-actions">
                <button
                    type="button"
                    class="flex h-9 w-9 items-center justify-center rounded-full border border-white/20 bg-white/10 text-white shadow-sm md:hidden"
                    x-on:click="$dispatch('open-command-palette')"
                    aria-label="Open workspace search"
                >
                    <x-app-icon name="search" class="h-4 w-4" />
                </button>
                <div class="app-topbar-date hidden text-right text-xs font-medium lg:block">
                    {{ now()->format('M j, Y') }}
                </div>
                <a
                    href="{{ route('profile.edit') }}"
                    class="app-topbar-user"
                >
                    <span class="app-topbar-avatar">{{ $userInitials ?: 'U' }}</span>
                    <span class="hidden min-w-0 text-left sm:block">
                        <span class="block truncate font-bold">{{ $user?->name ?? 'User' }}</span>
                        <span class="block truncate text-[0.68rem] font-semibold text-white/65">{{ $roleLabel }}</span>
                    </span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="app-topbar-logout"
                        aria-label="Log out"
                    >
                        <x-app-icon name="logout" class="h-4 w-4" />
                        <span class="hidden xl:inline">Log Out</span>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <aside class="app-sidebar fixed left-0 z-40 hidden border-r lg:flex lg:flex-col">
        <div class="app-sidebar-scroll flex-1 overflow-y-auto px-3 py-4">
            @foreach ($navGroups as $group)
                @php
                    $groupSearchText = $navSearchGroupText($group);
                @endphp
                <div class="app-sidebar-group" x-show="matchesNav(@js($groupSearchText))">
                    <div class="app-sidebar-label">{{ $group['label'] }}</div>
                    <div class="mt-2 space-y-1">
                        @foreach ($group['items'] as $link)
                            @php
                                $hasChildren = isset($link['children']);
                                $linkSearchBaseText = $navSearchEntryBaseText($link, [$group['label']]);
                                $linkSearchText = $navSearchEntryText($link, [$group['label']]);
                            @endphp
                            @if ($hasChildren)
                                @php
                                    $isActive = collect(explode('|', $link['active']))->contains(fn ($pattern) => request()->routeIs($pattern));
                                @endphp
                                <div
                                    x-data="{ groupActive: {{ $isActive ? 'true' : 'false' }} }"
                                    x-show="matchesNav(@js($linkSearchText), @js($group['label']))"
                                >
                                    <button
                                        type="button"
                                        @click="toggleOpenGroup('{{ $link['id'] }}')"
                                        class="app-sidebar-link w-full text-left flex items-center justify-between select-none cursor-pointer"
                                        :class="groupActive ? 'is-active' : (navGroupExpanded('{{ $link['id'] }}', @js($linkSearchText)) ? 'bg-white/5 text-white' : '')"
                                    >
                                        <div class="flex items-center gap-3">
                                            <span class="app-sidebar-link-icon" :class="groupActive ? 'bg-amber-400/20 text-amber-400 border border-amber-400/30' : ''">
                                                <x-app-icon :name="$link['icon'] ?? 'circle'" class="h-4 w-4" />
                                            </span>
                                            <span class="app-sidebar-link-text">{{ $link['label'] }}</span>
                                        </div>
                                        <span class="transition-transform duration-200 mr-1" :class="navGroupExpanded('{{ $link['id'] }}', @js($linkSearchText)) ? 'rotate-90' : ''">
                                            <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                                        </span>
                                    </button>

                                    <div 
                                        x-show="navGroupExpanded('{{ $link['id'] }}', @js($linkSearchText))" 
                                        x-collapse 
                                        x-cloak
                                        class="pl-3 mt-1.5 mb-2 space-y-1 border-l border-white/10 ml-6"
                                    >
                                        @foreach ($link['children'] as $child)
                                            @php
                                                $childSearchText = $navSearchEntryBaseText($child, [$group['label'], $link['label']]);
                                                $isChildRouteActive = collect(explode('|', $child['active'] ?? $link['active']))->contains(fn ($pattern) => request()->routeIs($pattern));
                                                $currentSectionValue = request()->route('section') ?? request()->query('section', 'overview');
                                                $currentViewValue = request()->query('view', 'directory');
                                                $isChildSectionActive = $isChildRouteActive
                                                    && (
                                                        (isset($child['section']) && $currentSectionValue === $child['section'])
                                                        || (isset($child['view']) && $currentViewValue === $child['view'])
                                                        || (! isset($child['section']) && ! isset($child['view']) && request()->routeIs($child['route']))
                                                    );
                                                $childBaseClass = 'app-sidebar-child-link';
                                                $childActiveClass = $isChildSectionActive ? 'is-active' : '';
                                                $childSpanClass = $isChildSectionActive ? 'is-active' : '';
                                                $isPortal = ($link['id'] === 'student_portal');
                                                $isPortalOnPage = ($isPortal && request()->routeIs('portal.index'));
                                                $childHref = route($child['route'], $child['params'] ?? []);
                                                $childSection = $child['section'] ?? '';
                                            @endphp
                                            @if ($isPortalOnPage)
                                                <a
                                                    href="{{ $childHref }}"
                                                    x-show="matchesNav(@js($childSearchText))"
                                                    x-on:click.prevent="$dispatch('section-change', '{{ $childSection }}'); activeSection = '{{ $childSection }}'; window.history.pushState(null, '', $el.href)"
                                                    class="{{ $childBaseClass }}"
                                                    :class="activeSection === '{{ $childSection }}' ? 'is-active' : ''"
                                                >
                                                    <span class="app-sidebar-child-dot"
                                                          :class="activeSection === '{{ $childSection }}' ? 'is-active' : ''"
                                                    ></span>
                                                    <span>{{ $child['label'] }}</span>
                                                </a>
                                            @elseif ($isPortal)
                                                <a
                                                    href="{{ $childHref }}"
                                                    x-show="matchesNav(@js($childSearchText))"
                                                    x-on:click="localStorage.setItem('sms_nav_group', 'student_portal')"
                                                    class="{{ $childBaseClass }}"
                                                    :class="activeSection === '{{ $childSection }}' ? 'is-active' : ''"
                                                >
                                                    <span class="app-sidebar-child-dot"
                                                          :class="activeSection === '{{ $childSection }}' ? 'is-active' : ''"
                                                    ></span>
                                                    <span>{{ $child['label'] }}</span>
                                                </a>
                                            @else
                                                <a
                                                    href="{{ $childHref }}"
                                                    x-show="matchesNav(@js($childSearchText))"
                                                    class="{{ $childBaseClass }} {{ $childActiveClass }}"
                                                >
                                                    <span class="app-sidebar-child-dot {{ $childSpanClass }}"></span>
                                                    <span>{{ $child['label'] }}</span>
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                @php
                                    $isActive = collect(explode('|', $link['active']))->contains(fn ($pattern) => request()->routeIs($pattern));
                                    $ariaCurrent = $isActive ? 'page' : false;
                                @endphp
                                <a
                                    href="{{ route($link['route'], $link['params'] ?? []) }}"
                                    x-show="matchesNav(@js($linkSearchText), @js($group['label']))"
                                    class="app-sidebar-link {{ $isActive ? 'is-active' : '' }}"
                                    {{ $isActive ? 'aria-current="page"' : '' }}
                                >
                                    <span class="app-sidebar-link-icon">
                                        <x-app-icon :name="$link['icon'] ?? 'circle'" class="h-4 w-4" />
                                    </span>
                                    <span class="app-sidebar-link-text">{{ $link['label'] }}</span>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </aside>

    <div x-cloak x-show="open" x-transition.opacity class="fixed inset-0 z-[60] lg:hidden">
        <button type="button" class="absolute inset-0 bg-slate-950/55" @click="open = false" aria-label="Close sidebar navigation"></button>
        <aside
            x-show="open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="mobile-sidebar-panel relative flex flex-col h-full border-r border-slate-200 shadow-2xl"
        >
            <div class="mobile-sidebar-header">
                <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3" @click="open = false">
                    <x-application-logo class="h-10 w-10 shrink-0" />
                    <div class="min-w-0">
                        <div class="truncate text-sm font-bold text-white">{{ $schoolSettings['school_name'] ?? 'BELOVED SCHOOLS' }}</div>
                        <div class="text-xs font-medium text-white/65">{{ $roleLabel }}</div>
                    </div>
                </a>
                <button @click="open = false" class="hamburger-button is-open rounded-xl border border-white/15 text-white" aria-label="Close sidebar navigation">
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-3 py-4">
                <div class="mb-6 lg:hidden">
                    <x-sidebar-search id="mobile-portal-sidebar-search" />
                </div>
                @foreach ($navGroups as $group)
                    @php
                        $groupSearchText = $navSearchGroupText($group);
                    @endphp
                    <div class="app-sidebar-group" x-show="matchesNav(@js($groupSearchText))">
                        <div class="app-sidebar-label">{{ $group['label'] }}</div>
                        <div class="mt-2 space-y-1">
                            @foreach ($group['items'] as $link)
                                @php
                                    $hasChildren = isset($link['children']);
                                    $linkSearchBaseText = $navSearchEntryBaseText($link, [$group['label']]);
                                    $linkSearchText = $navSearchEntryText($link, [$group['label']]);
                                @endphp
                                @if ($hasChildren)
                                    @php
                                        $isActive = collect(explode('|', $link['active']))->contains(fn ($pattern) => request()->routeIs($pattern));
                                    @endphp
                                    <div
                                        x-data="{ groupActive: {{ $isActive ? 'true' : 'false' }} }"
                                        x-show="matchesNav(@js($linkSearchText), @js($group['label']))"
                                    >
                                        <button
                                            type="button"
                                            @click="toggleOpenGroup('{{ $link['id'] }}')"
                                            class="app-sidebar-link w-full text-left flex items-center justify-between select-none cursor-pointer"
                                            :class="groupActive ? 'is-active' : (navGroupExpanded('{{ $link['id'] }}', @js($linkSearchText)) ? 'bg-white/5 text-white' : '')"
                                        >
                                            <div class="flex items-center gap-3">
                                                <span class="app-sidebar-link-icon" :class="groupActive ? 'bg-amber-400/20 text-amber-400 border border-amber-400/30' : ''">
                                                    <x-app-icon :name="$link['icon'] ?? 'circle'" class="h-4 w-4" />
                                                </span>
                                                <span class="app-sidebar-link-text">{{ $link['label'] }}</span>
                                            </div>
                                            <span class="transition-transform duration-200 mr-1" :class="navGroupExpanded('{{ $link['id'] }}', @js($linkSearchText)) ? 'rotate-90' : ''">
                                                <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                                            </span>
                                        </button>

                                        <div 
                                            x-show="navGroupExpanded('{{ $link['id'] }}', @js($linkSearchText))" 
                                            x-collapse 
                                            x-cloak
                                            class="pl-3 mt-1.5 mb-2 space-y-1 border-l border-white/10 ml-6"
                                        >
                                            @foreach ($link['children'] as $child)
                                                @php
                                                    $childSearchText = $navSearchEntryBaseText($child, [$group['label'], $link['label']]);
                                                    $isChildRouteActive = collect(explode('|', $child['active'] ?? $link['active']))->contains(fn ($pattern) => request()->routeIs($pattern));
                                                    $currentSectionValue = request()->route('section') ?? request()->query('section', 'overview');
                                                    $currentViewValue = request()->query('view', 'directory');
                                                    $isChildSectionActive = $isChildRouteActive
                                                        && (
                                                            (isset($child['section']) && $currentSectionValue === $child['section'])
                                                            || (isset($child['view']) && $currentViewValue === $child['view'])
                                                            || (! isset($child['section']) && ! isset($child['view']) && request()->routeIs($child['route']))
                                                        );

                                                    $isPortal = ($link['id'] === 'student_portal');
                                                    $isPortalOnPage = ($isPortal && request()->routeIs('portal.index'));
                                                    $childHref = route($child['route'], $child['params'] ?? []);
                                                    $childSection = $child['section'] ?? '';
                                                    $childActiveClass = $isChildSectionActive ? 'is-active' : '';
                                                    $childSpanClass = $isChildSectionActive ? 'is-active' : '';
                                                @endphp
                                                @if ($isPortalOnPage)
                                                    <a
                                                        href="{{ $childHref }}"
                                                        x-show="matchesNav(@js($childSearchText))"
                                                        x-on:click.prevent="$dispatch('section-change', '{{ $childSection }}'); activeSection = '{{ $childSection }}'; window.history.pushState(null, '', $el.href); open = false"
                                                        class="app-sidebar-child-link"
                                                        :class="activeSection === '{{ $childSection }}' ? 'is-active' : ''"
                                                    >
                                                        <span class="app-sidebar-child-dot"
                                                              :class="activeSection === '{{ $childSection }}' ? 'is-active' : ''"
                                                        ></span>
                                                        <span>{{ $child['label'] }}</span>
                                                    </a>
                                                @elseif ($isPortal)
                                                    <a
                                                        href="{{ $childHref }}"
                                                        x-show="matchesNav(@js($childSearchText))"
                                                        x-on:click="localStorage.setItem('sms_nav_group', 'student_portal'); open = false"
                                                        class="app-sidebar-child-link"
                                                        :class="activeSection === '{{ $childSection }}' ? 'is-active' : ''"
                                                    >
                                                        <span class="app-sidebar-child-dot"
                                                              :class="activeSection === '{{ $childSection }}' ? 'is-active' : ''"
                                                        ></span>
                                                        <span>{{ $child['label'] }}</span>
                                                    </a>
                                                @else
                                                    <a
                                                        href="{{ $childHref }}"
                                                        x-show="matchesNav(@js($childSearchText))"
                                                        x-on:click="open = false"
                                                        class="app-sidebar-child-link {{ $childActiveClass }}"
                                                    >
                                                        <span class="app-sidebar-child-dot {{ $childSpanClass }}"></span>
                                                        <span>{{ $child['label'] }}</span>
                                                    </a>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    @php
                                        $isActive = collect(explode('|', $link['active']))->contains(fn ($pattern) => request()->routeIs($pattern));
                                    @endphp
                                    <a
                                        href="{{ route($link['route'], $link['params'] ?? []) }}"
                                        x-on:click="open = false"
                                        x-show="matchesNav(@js($linkSearchText), @js($group['label']))"
                                        class="app-sidebar-link {{ $isActive ? 'is-active' : '' }}"
                                        {{ $isActive ? 'aria-current="page"' : '' }}
                                    >
                                        <span class="app-sidebar-link-icon">
                                            <x-app-icon :name="$link['icon'] ?? 'circle'" class="h-4 w-4" />
                                        </span>
                                        <span class="app-sidebar-link-text">{{ $link['label'] }}</span>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach

                @php
                    $accountGroupSearchText = trim('Account '.collect($accountLinks)
                        ->map(fn ($item) => $navSearchEntryText($item, ['Account']))
                        ->implode(' '));
                @endphp
                <div class="app-sidebar-group" x-show="matchesNav(@js($accountGroupSearchText))">
                    <div class="app-sidebar-label">Account</div>
                    <div class="mt-2 space-y-1">
                        @foreach ($accountLinks as $link)
                            @php
                                $isActive = collect(explode('|', $link['active']))->contains(fn ($pattern) => request()->routeIs($pattern));
                                $linkSearchText = $navSearchEntryText($link, ['Account']);
                            @endphp
                            <a
                                href="{{ route($link['route'], $link['params'] ?? []) }}"
                                x-on:click="open = false"
                                x-show="matchesNav(@js($linkSearchText), 'Account')"
                                class="app-sidebar-link {{ $isActive ? 'is-active' : '' }}"
                                {{ $isActive ? 'aria-current="page"' : '' }}
                            >
                                <span class="app-sidebar-link-icon">
                                    <x-app-icon :name="$link['icon'] ?? 'circle'" class="h-4 w-4" />
                                </span>
                                <span class="app-sidebar-link-text">{{ $link['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

            </div>

            <div class="mobile-sidebar-footer shrink-0">
                @php
                    $profileLink = collect($accountLinks)->first();
                @endphp
                <div class="flex items-center justify-between gap-3">
                    @if($profileLink)
                        <a href="{{ route($profileLink['route']) }}" @click="open = false" class="flex items-center gap-2 text-white/90 hover:text-white">
                            <span class="app-topbar-avatar !h-8 !w-8 !text-xs !bg-amber-400 !text-slate-900">{{ $userInitials ?: 'U' }}</span>
                            <span class="text-xs font-bold truncate max-w-[120px]">{{ $user?->name ?? 'User' }}</span>
                        </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="app-sidebar-logout !mt-0 !py-1.5 !px-3 !text-xs flex items-center gap-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold transition">
                            <x-app-icon name="logout" class="h-3.5 w-3.5" />
                            <span>Log Out</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>
    </div>

    <nav class="mobile-bottom-nav lg:hidden" aria-label="Primary mobile navigation">
        @foreach ($mobileNavItems as $item)
            @php
                $isActive = collect(explode('|', $item['active'] ?? $item['route']))->contains(fn ($pattern) => request()->routeIs($pattern));
            @endphp
            <a
                href="{{ route($item['route'], $item['params'] ?? []) }}"
                class="mobile-bottom-nav-link {{ $isActive ? 'is-active' : '' }}"
                aria-label="{{ $item['label'] }}"
                title="{{ $item['label'] }}"
                {{ $isActive ? 'aria-current="page"' : '' }}
            >
                <span class="mobile-bottom-nav-icon">
                    <x-app-icon :name="$item['icon'] ?? 'circle'" class="h-5 w-5" />
                </span>
                <span class="mobile-bottom-nav-text">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>
</div>

    <!-- Command Palette Modal -->
    <div
        x-data="globalSearch(@js($sidebarSearchItems))"
        x-show="open"
        x-cloak
        x-on:open-command-palette.window="toggle()"
        class="fixed inset-0 z-[100] overflow-y-auto p-4 sm:p-6 md:p-20"
        role="dialog"
        aria-modal="true"
    >
        <!-- Backdrop -->
        <div
            x-show="open"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
            @click="close()"
            aria-hidden="true"
        ></div>

        <!-- Modal Panel -->
        <div
            x-show="open"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="mx-auto max-w-2xl transform divide-y divide-slate-100/10 overflow-hidden rounded-2xl bg-slate-900/90 shadow-2xl ring-1 ring-white/10 backdrop-blur-md transition-all"
            @click.stop
        >
            <div class="relative">
                <x-app-icon name="search" class="pointer-events-none absolute left-4 top-3.5 h-5 w-5 text-white/40" />
                <input
                    x-ref="searchInput"
                    x-model="query"
                    type="text"
                    class="h-12 w-full border-0 bg-transparent pl-11 pr-4 text-white placeholder:text-white/40 focus:ring-0 sm:text-sm outline-none"
                    placeholder="Search workspace..."
                    autocomplete="off"
                >
            </div>

            <!-- Results -->
            <ul x-show="filteredItems.length > 0" class="max-h-96 scroll-py-3 overflow-y-auto p-3">
                <template x-for="(item, index) in filteredItems" :key="item.trail">
                    <li
                        class="group flex cursor-default select-none rounded-xl p-3"
                        x-bind:class="selectedIndex === index ? 'bg-amber-400/20 text-white' : 'text-white/70 hover:bg-white/5 hover:text-white'"
                        @click="window.location.href = item.href"
                        @mousemove="selectedIndex = index"
                    >
                        <div class="flex h-10 w-10 flex-none items-center justify-center rounded-lg border" x-bind:class="selectedIndex === index ? 'border-amber-400/30 bg-amber-400/10' : 'border-white/10 bg-white/5'">
                            <x-app-icon name="portal" class="h-5 w-5" x-bind:class="selectedIndex === index ? 'text-amber-400' : 'text-white/50'" />
                        </div>
                        <div class="ml-4 flex-auto">
                            <p class="text-sm font-semibold" x-bind:class="selectedIndex === index ? 'text-white' : 'text-white'" x-text="item.label"></p>
                            <p class="text-xs" x-bind:class="selectedIndex === index ? 'text-amber-400/70' : 'text-white/50'" x-text="item.context"></p>
                        </div>
                    </li>
                </template>
            </ul>

            <!-- Empty state -->
            <div x-show="query !== '' && filteredItems.length === 0" class="px-6 py-14 text-center text-sm sm:px-14">
                <x-app-icon name="search" class="mx-auto h-6 w-6 text-white/40" />
                <p class="mt-4 font-semibold text-white">No results found</p>
                <p class="mt-2 text-white/50">We couldn't find anything matching your search. Please try again.</p>
            </div>
        </div>
    </div>
