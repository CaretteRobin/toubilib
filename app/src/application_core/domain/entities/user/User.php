<?php

namespace toubilib\core\domain\entities\user;

class User
{
    public string $id;
    public string $email;
    public string $passwordHash;
    public int $role;

    public function __construct(
        string $id,
        string $email,
        string $passwordHash,
        int $role
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->role = $role;
    }

    /**
     * Vérifie si le mot de passe fourni correspond au hash stocké
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->passwordHash);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    public function isPatient(): bool
    {
        return $this->role === UserRole::PATIENT;
    }

    public function isPraticien(): bool
    {
        return $this->role === UserRole::PRATICIEN;
    }
}
