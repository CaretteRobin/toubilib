<?php

namespace toubilib\core\application\dto;

class UserDTO
{
    public string $id;
    public string $email;
    public int $role;

    public function __construct(
        string $id,
        string $email,
        int $role
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->role = $role;
    }

    /**
     * Crée un DTO à partir d'une entité User
     */
    public static function fromEntity(\toubilib\core\domain\entities\user\User $user): self
    {
        return new self(
            $user->id,
            $user->email,
            $user->role
        );
    }

    /**
     * Convertit le DTO en tableau associatif pour la sérialisation JSON
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'role' => $this->role,
            'role_name' => \toubilib\core\domain\entities\user\UserRole::toString($this->role)
        ];
    }
}