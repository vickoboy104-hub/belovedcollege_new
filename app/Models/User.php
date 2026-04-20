<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'first_name', 'middle_name', 'last_name', 'email', 'password', 'role', 'phone', 'status', 'avatar_url', 'email_verified_at', 'temp_password_plaintext', 'temp_password_generated_at'])]
#[Hidden(['password', 'remember_token', 'temp_password_plaintext'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'temp_password_plaintext' => 'encrypted',
            'temp_password_generated_at' => 'datetime',
        ];
    }

    public function studentProfile(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function staffProfile(): HasOne
    {
        return $this->hasOne(StaffProfile::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'teacher_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'teacher_id');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'teacher_id');
    }

    public function managedClasses(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'class_teacher_id');
    }

    public function gradedCbtAttempts(): HasMany
    {
        return $this->hasMany(CbtAttempt::class, 'graded_by');
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'author_id');
    }

    public function hasAnyRole(array|string|UserRole ...$roles): bool
    {
        $flattened = collect($roles)->flatten()->map(
            fn (mixed $role) => $role instanceof UserRole ? $role->value : $role
        );

        return $flattened->contains($this->role?->value ?? $this->role);
    }

    public function roleLabel(): string
    {
        return $this->role instanceof UserRole
            ? $this->role->label()
            : str((string) $this->role)->headline()->toString();
    }

    public function fullName(): string
    {
        $segments = array_filter([$this->first_name, $this->middle_name, $this->last_name]);

        return count($segments) > 0 ? implode(' ', $segments) : $this->name;
    }

    public function isClassTeacher(): bool
    {
        return $this->managedClasses()->exists();
    }
}
