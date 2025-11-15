<?php

namespace App\Enums;

enum RoleEnum: string
{
    case ADMIN = 'admin';
    case CUSTOMER = 'customer';
    case SUPER_ADMIN = 'super_admin';

    public function type(): string
    {
        return match($this) {
            self::ADMIN => 'admin',
            self::CUSTOMER  => 'customer',
            self::SUPER_ADMIN  => 'admin',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::ADMIN => "Administrator with admin dashboard access",
            self::CUSTOMER  => "Customer with access to basic customer features",
            self::SUPER_ADMIN  => "Super Administrator with full access to all features",
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::CUSTOMER => 'Customer',
            self::SUPER_ADMIN => 'Super Admin',
        };
    }
}