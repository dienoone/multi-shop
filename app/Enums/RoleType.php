<?php

namespace App\Enums;

enum RoleType: string
{
    case SuperAdmin  = 'super_admin';
    case Store  = 'store';
    case Customer    = 'customer';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Store => 'Store',
            self::Customer   => 'Customer',
        };
    }
}
