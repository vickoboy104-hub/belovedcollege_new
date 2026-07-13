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
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['name', 'first_name', 'middle_name', 'last_name', 'email', 'password', 'role', 'phone', 'status', 'avatar_url', 'avatar_path', 'email_verified_at', 'must_change_password', 'temp_password_plaintext', 'temp_password_generated_at'])]
#[Hidden(['password', 'remember_token', 'temp_password_plaintext', 'avatar_path'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            $temporaryPasswordWasSupplied = $user->isDirty('temp_password_plaintext')
                && filled($user->getAttribute('temp_password_plaintext'));
            $temporaryPasswordWasGenerated = $user->isDirty('temp_password_generated_at')
                && filled($user->getAttribute('temp_password_generated_at'));

            if ($temporaryPasswordWasSupplied || $temporaryPasswordWasGenerated) {
                $user->must_change_password = true;
            }

            // A generated password may be shown once through the session response,
            // but it is never retained in the database.
            $user->temp_password_plaintext = null;
            $user->temp_password_generated_at = null;

            $role = $user->role instanceof UserRole ? $user->role->value : (string) $user->role;
            if (! $user->exists && $role === UserRole::Parent->value && $user->isDirty('password')) {
                $user->must_change_password = true;
            }

            static::movePassportUploadToPrivateStorage($user);
        });

        static::created(function (User $user): void {
            if ($user->avatar_path && blank($user->avatar_url)) {
                $user->updateQuietly([
                    'avatar_url' => '/private-media/users/'.$user->getKey().'/avatar',
                ]);
            }
        });
    }

    protected static function movePassportUploadToPrivateStorage(User $user): void
    {
        if (! $user->isDirty('avatar_url')) {
            return;
        }

        $publicPath = ltrim((string) $user->getAttribute('avatar_url'), '/');
        if (! preg_match('#^uploads/settings/(student|staff)-passport-#', $publicPath)) {
            return;
        }

        $source = public_path($publicPath);
        if (! File::isFile($source)) {
            return;
        }

        $extension = strtolower((string) pathinfo($source, PATHINFO_EXTENSION));
        $extension = preg_match('/^[a-z0-9]{2,5}$/', $extension) ? $extension : 'jpg';
        $privatePath = 'avatars/'.Str::uuid().'.'.$extension;
        $stream = fopen($source, 'rb');

        if ($stream === false) {
            return;
        }

        try {
            $stored = Storage::disk('local')->put($privatePath, $stream);
        } finally {
            fclose($stream);
        }

        if (! $stored) {
            return;
        }

        File::delete($source);
        $user->avatar_path = $privatePath;
        $user->avatar_url = $user->exists
            ? '/private-media/users/'.$user->getKey().'/avatar'
            : null;
    }

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
            'must_change_password' => 'boolean',
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
