<?php

namespace toubilib\core\application\usecases;

use toubilib\core\application\dto\UserDTO;
use toubilib\core\application\ports\UserRepositoryInterface;
use toubilib\core\domain\exceptions\InvalidCredentialsException;
use toubilib\core\domain\exceptions\UserNotFoundException;
use toubilib\core\domain\entities\user\UserRole;
use toubilib\core\domain\entities\user\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Ramsey\Uuid\Uuid;

class ServiceAuth implements ServiceAuthInterface
{
    private UserRepositoryInterface $userRepository;
    private string $jwtSecret;
    private int $jwtExpiration;

    public function __construct(
        UserRepositoryInterface $userRepository,
        string $jwtSecret,
        int $jwtExpiration = 3600 // 1 heure par défaut
    ) {
        $this->userRepository = $userRepository;
        $this->jwtSecret = $jwtSecret;
        $this->jwtExpiration = $jwtExpiration;
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

    public function generateJwtToken(UserDTO $user, ?int $customExpiration = null): string
    {
        $expiration = $customExpiration ?? $this->jwtExpiration;
        
        $payload = [
            'iss' => 'toubilib',                    // Issuer
            'iat' => time(),                        // Issued at
            'exp' => time() + $expiration,          // Expiration
            'sub' => $user->id,                     // Subject (user ID)
            'email' => $user->email,
            'role' => $user->role
        ];

        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }

    public function verifyJwtToken(string $token): UserDTO
    {
        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            
            // Vérifier que l'utilisateur existe toujours
            $user = $this->getUserById($decoded->sub);
            
            return $user;
        } catch (\Exception $e) {
            throw new InvalidCredentialsException();
        }
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
            throw new \InvalidArgumentException("Invalid role: {$role}");
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
