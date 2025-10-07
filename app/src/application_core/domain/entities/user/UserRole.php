<?php

namespace toubilib\core\domain\entities\user;

class UserRole
{
    public const ADMIN = 0;
    public const USER = 1;

    public static function isValid(int $role): bool
    {
        return in_array($role, [self::ADMIN, self::USER], true);
    }

    public static function toString(int $role): string
    {
        return match($role) {
            self::ADMIN => 'admin',
            self::USER => 'user',
            default => 'unknown'
        };
    }
}