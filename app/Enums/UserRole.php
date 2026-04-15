<?php

namespace App\Enums;

enum UserRole: string
{
    case Complainant = 'complainant';
    case Officer = 'officer';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Complainant => 'Complainant',
            self::Officer => 'Officer',
            self::Admin => 'Admin',
        };
    }
}
