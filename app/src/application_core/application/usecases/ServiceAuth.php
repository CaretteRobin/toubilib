<?php

namespace toubilib\core\application\usecases;

use toubilib\core\application\dto\UserDTO;
use toubilib\core\application\ports\UserRepositoryInterface;
use toubilib\core\domain\exceptions\InvalidCredentialsException;
use toubilib\core\domain\exceptions\UserNotFoundException;
use toubilib\core\domain\entities\user\UserRole;
use toubilib\core\domain\entities\user\User;
use Ramsey\Uuid\Uuid;

class ServiceAuth implements ServiceAuthInterface
{
    private UserRepositoryInterface $userRepository;
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function authenticate(string $email, string $password): UserDTO
    {
        $this->validateAuthenticationData($email, $password);

        try {
            $user = $this->userRepository->findByEmail($email);
        } catch (UserNotFoundException $e) {
            throw new InvalidCredentialsException();
        }

        if (!$user->verifyPassword($password)) {
            throw new InvalidCredentialsException();
        }

        return UserDTO::fromEntity($user);
    }

    public function getUserById(string $id): UserDTO
    {
        $user = $this->userRepository->findById($id);
        return UserDTO::fromEntity($user);
    }

    public function getUserByEmail(string $email): UserDTO
    {
        $user = $this->userRepository->findByEmail($email);
        return UserDTO::fromEntity($user);
    }

    /**
     * Valide les données d'authentification
     */
    private function validateAuthenticationData(string $email, string $password): void
    {
        if (empty($email) || empty($password)) {
            throw new InvalidCredentialsException();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidCredentialsException();
        }
    }

    /**
     * Crée un utilisateur (méthode utilitaire pour les tests et l'administration)
     */
    public function createUser(string $email, string $password, int $role): UserDTO
    {
        $this->validateAuthenticationData($email, $password);
        
        if (!UserRole::isValid($role)) {
            throw new \InvalidArgumentException("Rôle invalide : {$role}");
        }

        $user = new User(
            Uuid::uuid4()->toString(),
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            $role
        );
        
        $savedUser = $this->userRepository->save($user);
        return UserDTO::fromEntity($savedUser);
    }
}
