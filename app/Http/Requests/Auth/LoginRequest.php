<?php

namespace App\Http\Requests\Auth;

use App\Enums\UserRole;
use App\Models\StaffProfile;
use App\Models\Student;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'audience' => ['nullable', 'in:generic,student,staff'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('email') && ! $this->filled('login')) {
            $this->merge(['login' => $this->input('email')]);
        }

        $this->merge([
            'audience' => $this->input('audience', 'generic'),
        ]);
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $user = $this->resolveUser();

        if (! $user || ! Auth::attempt(['email' => $user->email, 'password' => $this->string('password')], $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('login')).'|'.$this->ip().'|'.$this->string('audience'));
    }

    protected function resolveUser(): ?User
    {
        $identifier = trim((string) $this->string('login'));
        $audience = (string) $this->string('audience', 'generic');

        $user = User::query()
            ->where('email', $identifier)
            ->orWhere('name', $identifier)
            ->first();

        if (! $user && in_array($audience, ['student', 'generic'], true)) {
            $student = Student::query()
                ->where('admission_no', $identifier)
                ->orWhere('student_id_no', $identifier)
                ->with('user')
                ->first();

            $user = $student?->user;
        }

        if (! $user && in_array($audience, ['staff', 'generic'], true)) {
            $profile = StaffProfile::query()
                ->where('employee_no', $identifier)
                ->with('user')
                ->first();

            $user = $profile?->user;
        }

        if (! $user || strtolower((string) $user->status) !== 'active') {
            return null;
        }

        if ($audience === 'student' && ! $user->hasAnyRole(UserRole::Student, UserRole::Parent)) {
            return null;
        }

        if ($audience === 'staff' && ! $user->hasAnyRole(UserRole::Admin, UserRole::Principal, UserRole::Teacher, UserRole::Accountant)) {
            return null;
        }

        return $user;
    }
}
