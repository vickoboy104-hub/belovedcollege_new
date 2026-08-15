<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\ContactMessage;
use App\Models\SchoolClass;
use App\Models\Setting;
use App\Models\StaffProfile;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class WebsiteController extends Controller
{
    public function home(): View
    {
        $announcements = collect();
        $stats = [
            'students' => 0,
            'staff' => 0,
            'classes' => 0,
            'news' => 0,
        ];

        if (Schema::hasTable('announcements')) {
            $announcements = Announcement::query()
                ->where('is_published', true)
                ->latest('published_at')
                ->take(3)
                ->get();
        }

        if (Schema::hasTable('students')) {
            $stats['students'] = Student::count();
        }

        if (Schema::hasTable('staff_profiles')) {
            $stats['staff'] = StaffProfile::count();
        }

        if (Schema::hasTable('school_classes')) {
            $stats['classes'] = SchoolClass::count();
        }

        if (Schema::hasTable('announcements')) {
            $stats['news'] = Announcement::where('is_published', true)->count();
        }

        return view('welcome', compact('announcements', 'stats'));
    }

    public function about(): View
    {
        return view('pages.about');
    }

    public function admissions(): View
    {
        $classes = SchoolClass::query()->orderBy('name')->get();

        return view('pages.admissions', compact('classes'));
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    public function storeContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        ContactMessage::create($validated);

        $recipient = Setting::getValue('contact_email_recipient', 'vickoboy104@gmail.com');
        $activeMailer = (string) config('mail.default', 'log');

        if (in_array($activeMailer, ['log', 'array'], true)) {
            return back()->with('status', 'Thank you. Your message has been received by the school.');
        }

        try {
            Mail::send('emails.contact-message', ['payload' => $validated], function ($message) use ($validated, $recipient): void {
                $message->to($recipient)
                    ->replyTo($validated['email'], $validated['name'])
                    ->subject('Website contact: '.$validated['subject']);
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->withErrors([
                'mail' => 'Your message was received, but email delivery is temporarily unavailable. The school can still view it in the portal.',
            ]);
        }

        return back()->with('status', 'Your message has been received and sent to the school email successfully.');
    }
}
