<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CbtController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PrivateMediaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StudentManagementController;
use App\Http\Controllers\StudentPortalController;
use App\Http\Controllers\TeacherAccessController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\WebsiteController;
use App\Http\Middleware\EnsureCbtSubmissionIsOpen;
use Illuminate\Support\Facades\Route;

Route::get('/', [WebsiteController::class, 'home'])->name('home');
Route::get('/about', [WebsiteController::class, 'about'])->name('about');
Route::get('/admissions', [WebsiteController::class, 'admissions'])->name('admissions');
Route::get('/contact', [WebsiteController::class, 'contact'])->name('contact');
Route::post('/contact', [WebsiteController::class, 'storeContact'])
    ->middleware('throttle:5,1')
    ->name('contact.store');
Route::get('/result-checker', [ReportController::class, 'checker'])->name('reports.checker');
Route::post('/result-checker', [ReportController::class, 'checkerLookup'])
    ->middleware('throttle:8,1')
    ->name('reports.checker.lookup');

Route::get('/payments/callback/{provider}', [PaymentController::class, 'callback'])
    ->where('provider', 'paystack|palmpay')
    ->middleware('throttle:30,1')
    ->name('payments.callback');
Route::post('/webhooks/paystack', [WebhookController::class, 'paystack'])
    ->middleware('throttle:120,1')
    ->name('webhooks.paystack');
Route::post('/webhooks/palmpay', [WebhookController::class, 'palmpay'])
    ->middleware('throttle:120,1')
    ->name('webhooks.palmpay');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/private-media/users/{user}/avatar', [PrivateMediaController::class, 'avatar'])
        ->middleware('throttle:120,1')
        ->name('private-media.avatar');
    Route::post('/payments/{invoice}/checkout/{provider}', [PaymentController::class, 'checkout'])->name('payments.checkout');
    Route::post('/payments/checkout/{provider}', [PaymentController::class, 'checkoutSelection'])->name('payments.selection.checkout');
    Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');

    Route::middleware('role:admin,principal')->group(function () {
        Route::get('/admin/settings/{section?}', [AdminController::class, 'settings'])
            ->where('section', 'website-foundation|theme-colors|landing-builder|homepage-media|workspace-backgrounds|site-backgrounds|welcome-popup|gallery-uploader|homepage-text|box-backgrounds-a|box-backgrounds-b|payment-settings|contact-messages')
            ->name('admin.settings');
        Route::post('/admin/settings', [AdminController::class, 'updateSettings'])->name('admin.settings.update');
        Route::get('/admin/people', [AdminController::class, 'people'])->name('admin.people');
        Route::get('/admin/people/students/{classSlug?}', [StudentManagementController::class, 'index'])->name('admin.students.index');
        Route::get('/admin/people/parents', [AdminController::class, 'parents'])->name('admin.parents.index');
        Route::get('/admin/people/staff', [AdminController::class, 'staff'])->name('admin.staff.index');
        Route::get('/admin/students/{student}', [AdminController::class, 'showStudent'])->name('admin.students.show');
        Route::get('/admin/staff/{staffProfile}', [AdminController::class, 'showStaff'])->name('admin.staff.show');
        Route::post('/admin/students', [AdminController::class, 'storeStudent'])->name('admin.students.store');
        Route::post('/admin/staff', [AdminController::class, 'storeStaff'])->name('admin.staff.store');
        Route::post('/admin/students/{student}/temporary-password', [AdminController::class, 'resetStudentTemporaryPassword'])->name('admin.students.password.reset');
        Route::post('/admin/staff/{staffProfile}/temporary-password', [AdminController::class, 'resetStaffTemporaryPassword'])->name('admin.staff.password.reset');
        Route::patch('/admin/students/{student}', [AdminController::class, 'updateStudent'])->name('admin.students.update');
        Route::patch('/admin/staff/{staffProfile}', [AdminController::class, 'updateStaff'])->name('admin.staff.update');
        Route::patch('/admin/students/{student}/deactivate', [AdminController::class, 'deactivateStudent'])->name('admin.students.deactivate');
        Route::patch('/admin/staff/{staffProfile}/deactivate', [AdminController::class, 'deactivateStaff'])->name('admin.staff.deactivate');
        Route::delete('/admin/students/{student}', [AdminController::class, 'destroyStudent'])->name('admin.students.destroy');
        Route::delete('/admin/staff/{staffProfile}', [AdminController::class, 'destroyStaff'])->name('admin.staff.destroy');
        Route::get('/admin/teacher-access', [TeacherAccessController::class, 'index'])->name('admin.teacher-access.index');
        Route::post('/admin/teacher-access', [TeacherAccessController::class, 'store'])->name('admin.teacher-access.store');
        Route::patch('/admin/teacher-access/{assignment}/revoke', [TeacherAccessController::class, 'revoke'])->name('admin.teacher-access.revoke');
        Route::patch('/admin/teacher-access/{assignment}/restore', [TeacherAccessController::class, 'restore'])->name('admin.teacher-access.restore');
        Route::get('/admin/academics/{section?}', [AdminController::class, 'academics'])
            ->where('section', 'session-setup|term-setup|session-rollover|promotion-review|class-setup|subject-setup|announcement|cbt-control')
            ->name('admin.academics');
        Route::post('/admin/sessions', [AdminController::class, 'storeSession'])->name('admin.sessions.store');
        Route::patch('/admin/sessions/{session}/close', [AdminController::class, 'closeSession'])->name('admin.sessions.close');
        Route::post('/admin/sessions/promotions', [AdminController::class, 'processPromotions'])->name('admin.sessions.promotions.process');
        Route::post('/admin/terms', [AdminController::class, 'storeTerm'])->name('admin.terms.store');
        Route::post('/admin/classes', [AdminController::class, 'storeClass'])->name('admin.classes.store');
        Route::patch('/admin/classes/{schoolClass}', [AdminController::class, 'updateClass'])->name('admin.classes.update');
        Route::post('/admin/subjects', [AdminController::class, 'storeSubject'])->name('admin.subjects.store');
        Route::post('/admin/announcements', [AdminController::class, 'storeAnnouncement'])->name('admin.announcements.store');
        Route::post('/admin/cbt/toggle', [CbtController::class, 'toggleGlobal'])->name('admin.cbt.toggle');
        Route::patch('/admin/cbt-assessments/{assessment}/toggle', [CbtController::class, 'toggleAssessment'])->name('admin.cbt.assessments.toggle');
        Route::get('/admin/reports/student/{student}/{section?}', [ReportController::class, 'adminShow'])
            ->where('section', 'overview|scores|remarks|publication')
            ->name('admin.reports.show');
        Route::get('/admin/reports/{classSlug?}', [ReportController::class, 'adminIndex'])
            ->name('admin.reports.index');
        Route::post('/admin/students/{student}/reports/{term}', [ReportController::class, 'update'])->name('admin.reports.update');
        Route::post('/admin/students/{student}/reports/{term}/publish', [ReportController::class, 'publish'])->name('admin.reports.publish');
        Route::get('/admin/students/{student}/reports/{term}/print', [ReportController::class, 'adminPrint'])->name('admin.reports.print');
        Route::get('/admin/students/{student}/record', [ReportController::class, 'adminRecord'])->name('admin.students.record');
    });

    Route::middleware('role:admin,principal,accountant')->group(function () {
        Route::get('/admin/finance/records/{section?}', [FinanceController::class, 'records'])
            ->where('section', 'printable-fee-list|created-fee-items|student-balances|class-bills|payment-summary|recent-payments|overpayment-tracker|payment-progression')
            ->name('admin.finance.records');
        Route::get('/admin/finance/{section?}', [FinanceController::class, 'index'])
            ->where('section', 'create-fee-item|generate-invoice|record-payment|finance-overview|recent-invoices')
            ->name('admin.finance');
        Route::get('/admin/finance/printable-fee-list', [FinanceController::class, 'printableFeeList'])->name('admin.finance.printable-fee-list');
        Route::post('/admin/fee-items', [FinanceController::class, 'storeFeeItem'])->name('admin.fee-items.store');
        Route::delete('/admin/fee-items/{feeItem}', [FinanceController::class, 'destroyFeeItem'])->name('admin.fee-items.destroy');
        Route::post('/admin/invoices', [FinanceController::class, 'storeInvoice'])->name('admin.invoices.store');
        Route::post('/admin/manual-payments', [FinanceController::class, 'storeManualPayment'])->name('admin.manual-payments.store');
        Route::get('/admin/payments/{payment}/receipt', [FinanceController::class, 'receipt'])->name('admin.payments.receipt');
    });

    Route::middleware('role:admin,principal,teacher')->group(function () {
        Route::get('/teacher/learning/{section?}', [TeacherController::class, 'learning'])
            ->where('section', 'publish-lesson|create-assignment|assessment|record-result|attendance|cbt-create|cbt-list|latest-content|submissions|cbt-attempts')
            ->name('teacher.learning');
        Route::post('/teacher/lessons', [TeacherController::class, 'storeLesson'])->name('teacher.lessons.store');
        Route::post('/teacher/assignments', [TeacherController::class, 'storeAssignment'])->name('teacher.assignments.store');
        Route::post('/teacher/assessments', [TeacherController::class, 'storeAssessment'])->name('teacher.assessments.store');
        Route::post('/teacher/cbt-assessments', [CbtController::class, 'storeAssessment'])->name('teacher.cbt.assessments.store');
        Route::get('/teacher/cbt-assessments/{assessment}', [CbtController::class, 'showAssessment'])->name('teacher.cbt.assessments.show');
        Route::post('/teacher/cbt-assessments/{assessment}/questions', [CbtController::class, 'storeQuestion'])->name('teacher.cbt.questions.store');
        Route::patch('/teacher/cbt-questions/{question}', [CbtController::class, 'updateQuestion'])->name('teacher.cbt.questions.update');
        Route::delete('/teacher/cbt-questions/{question}', [CbtController::class, 'destroyQuestion'])->name('teacher.cbt.questions.destroy');
        Route::get('/teacher/cbt-attempts/{attempt}', [CbtController::class, 'showAttemptReview'])->name('teacher.cbt.attempts.show');
        Route::post('/teacher/cbt-answers/{answer}/grade', [CbtController::class, 'gradeAnswer'])->name('teacher.cbt.answers.grade');
        Route::post('/teacher/results', [TeacherController::class, 'storeResult'])->name('teacher.results.store');
        Route::post('/teacher/attendance', [TeacherController::class, 'storeAttendance'])->name('teacher.attendance.store');
        Route::post('/teacher/submissions/{submission}/grade', [TeacherController::class, 'gradeSubmission'])->name('teacher.submissions.grade');
    });

    Route::middleware('role:student,parent')->group(function () {
        Route::get('/portal', [StudentPortalController::class, 'index'])->name('portal.index');
        Route::post('/portal/assignments/{assignment}/submit', [StudentPortalController::class, 'submitAssignment'])->name('portal.assignments.submit');
        Route::get('/portal/cbt/{assessment}', [CbtController::class, 'takeAssessment'])->name('portal.cbt.show');
        Route::post('/portal/cbt/{assessment}/submit', [CbtController::class, 'submitAssessment'])
            ->middleware(EnsureCbtSubmissionIsOpen::class)
            ->name('portal.cbt.submit');
        Route::get('/portal/results/{term}/print', [ReportController::class, 'portalPrint'])->name('portal.results.print');
        Route::get('/portal/student-record', [ReportController::class, 'portalRecord'])->name('portal.record');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
