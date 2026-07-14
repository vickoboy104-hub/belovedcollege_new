<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\TeacherSubjectAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TeacherAccessService
{
    /** @var array<int, Collection<int, TeacherSubjectAssignment>> */
    protected array $assignmentCache = [];

    public function isPrivileged(User $user): bool
    {
        return $user->hasAnyRole(UserRole::Admin, UserRole::Principal);
    }

    public function activeAssignments(User $user): Collection
    {
        if ($this->isPrivileged($user)) {
            return collect();
        }

        return $this->assignmentCache[$user->id] ??= TeacherSubjectAssignment::query()
            ->with(['schoolClass', 'subject'])
            ->where('teacher_id', $user->id)
            ->where('is_active', true)
            ->orderBy('school_class_id')
            ->orderBy('subject_id')
            ->get();
    }

    public function refresh(User $user): void
    {
        unset($this->assignmentCache[$user->id]);
    }

    public function classIds(User $user): ?Collection
    {
        if ($this->isPrivileged($user)) {
            return null;
        }

        return $this->activeAssignments($user)
            ->pluck('school_class_id')
            ->unique()
            ->values();
    }

    public function subjectIds(User $user): ?Collection
    {
        if ($this->isPrivileged($user)) {
            return null;
        }

        return $this->activeAssignments($user)
            ->pluck('subject_id')
            ->unique()
            ->values();
    }

    public function canTeach(User $user, int $schoolClassId, int $subjectId): bool
    {
        if ($this->isPrivileged($user)) {
            return true;
        }

        return $this->activeAssignments($user)->contains(
            fn (TeacherSubjectAssignment $assignment): bool =>
                (int) $assignment->school_class_id === $schoolClassId
                && (int) $assignment->subject_id === $subjectId
        );
    }

    public function canManageClass(User $user, int $schoolClassId): bool
    {
        if ($this->isPrivileged($user)) {
            return true;
        }

        return $this->activeAssignments($user)->contains(
            fn (TeacherSubjectAssignment $assignment): bool =>
                (int) $assignment->school_class_id === $schoolClassId
        );
    }

    public function authorizePair(User $user, int $schoolClassId, int $subjectId): void
    {
        abort_unless($this->canTeach($user, $schoolClassId, $subjectId), 403, 'You are not assigned to teach this subject in the selected class.');
    }

    public function authorizeClass(User $user, int $schoolClassId): void
    {
        abort_unless($this->canManageClass($user, $schoolClassId), 403, 'You do not have teaching access to this class.');
    }

    public function scopePairs(Builder $query, User $user, string $classColumn = 'school_class_id', string $subjectColumn = 'subject_id'): Builder
    {
        if ($this->isPrivileged($user)) {
            return $query;
        }

        $assignments = $this->activeAssignments($user);

        if ($assignments->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $pairQuery) use ($assignments, $classColumn, $subjectColumn): void {
            foreach ($assignments as $assignment) {
                $pairQuery->orWhere(function (Builder $assignmentQuery) use ($assignment, $classColumn, $subjectColumn): void {
                    $assignmentQuery
                        ->where($classColumn, $assignment->school_class_id)
                        ->where($subjectColumn, $assignment->subject_id);
                });
            }
        });
    }

    public function classSubjectMap(User $user): array
    {
        if ($this->isPrivileged($user)) {
            return [];
        }

        return $this->activeAssignments($user)
            ->groupBy('school_class_id')
            ->map(fn (Collection $assignments) => $assignments->pluck('subject_id')->unique()->values()->all())
            ->all();
    }
}
