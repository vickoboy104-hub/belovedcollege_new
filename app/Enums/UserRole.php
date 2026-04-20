<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Principal = 'principal';
    case Teacher = 'teacher';
    case Accountant = 'accountant';
    case Parent = 'parent';
    case Student = 'student';

    public function label(): string
    {
        return str($this->value)->headline()->toString();
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $role) => [$role->value => $role->label()])
            ->all();
    }
}
