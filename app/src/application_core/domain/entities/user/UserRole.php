<?php

namespace toubilib\core\domain\entities\user;

class UserRole
{
    public const ADMIN = 0;
    public const PATIENT = 1;
    public const USER = self::PATIENT; // alias historique
    public const PRATICIEN = 10;

    private const LABELS = [
        self::ADMIN => 'admin',
        self::PATIENT => 'patient',
        self::PRATICIEN => 'praticien',
    ];

    public static function isValid(int $role): bool
    {
        return array_key_exists($role, self::LABELS);
    }

    public static function toString(int $role): string
    {
        return self::LABELS[$role] ?? 'inconnu';
    }

    public static function fromString(string $role): ?int
    {
        $map = array_flip(self::LABELS);
        return $map[$role] ?? null;
    }
}
