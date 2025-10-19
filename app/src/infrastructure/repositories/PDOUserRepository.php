<?php

namespace toubilib\infra\repositories;

use toubilib\core\application\ports\UserRepositoryInterface;
use toubilib\core\domain\entities\user\User;
use toubilib\core\domain\entities\user\UserRole;
use toubilib\core\domain\exceptions\UserNotFoundException;
use toubilib\core\domain\exceptions\DuplicateUserException;
use Ramsey\Uuid\Uuid;

class PDOUserRepository implements UserRepositoryInterface
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findByEmail(string $email): User
    {
        $sql = 'SELECT id, email, password, role FROM users WHERE email = :email';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        
        $row = $stmt->fetch();
        if (!$row) {
            throw new UserNotFoundException($email, 'email');
        }

        return $this->mapRowToUser($row);
    }

    public function findById(string $id): User
    {
        $sql = 'SELECT id, email, password, role FROM users WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $row = $stmt->fetch();
        if (!$row) {
            throw new UserNotFoundException($id, 'ID');
        }

        return $this->mapRowToUser($row);
    }

    public function save(User $user): User
    {
        // Vérifier si l'email existe déjà
        try {
            $this->findByEmail($user->email);
            throw new DuplicateUserException($user->email);
        } catch (UserNotFoundException $e) {
            // C'est ce qu'on veut, l'email n'existe pas
        }

        $sql = 'INSERT INTO users (id, email, password, role) VALUES (:id, :email, :password, :role)';
        $stmt = $this->pdo->prepare($sql);
        
        try {
            $stmt->execute([
                'id' => $user->id,
                'email' => $user->email,
                'password' => $user->passwordHash,
                'role' => $user->role
            ]);
            
            return $user;
        } catch (\PDOException $e) {
            // Gestion des erreurs de contrainte d'unicité
            if ($e->getCode() == '23505') { // Violation contrainte unique PostgreSQL
                throw new DuplicateUserException($user->email);
            }
            throw $e;
        }
    }

    public function update(User $user): User
    {
        // Vérifier que l'utilisateur existe
        $this->findById($user->id);

        $sql = 'UPDATE users SET email = :email, password = :password, role = :role WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        
        try {
            $stmt->execute([
                'id' => $user->id,
                'email' => $user->email,
                'password' => $user->passwordHash,
                'role' => $user->role
            ]);
            
            return $user;
        } catch (\PDOException $e) {
            // Gestion des erreurs de contrainte d'unicité sur email
            if ($e->getCode() == '23505') {
                throw new DuplicateUserException($user->email);
            }
            throw $e;
        }
    }

    public function delete(string $id): void
    {
        // Vérifier que l'utilisateur existe
        $this->findById($id);

        $sql = 'DELETE FROM users WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
    }

    /**
     * Crée un nouvel utilisateur avec un mot de passe hashé
     */
    public function createUser(string $email, string $password, int $role = UserRole::PATIENT): User
    {
        if (!UserRole::isValid($role)) {
            throw new \InvalidArgumentException("Rôle invalide : {$role}");
        }

        $user = new User(
            Uuid::uuid4()->toString(),
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            $role
        );

        return $this->save($user);
    }

    /**
     * Mappe une ligne de base de données vers une entité User
     */
    private function mapRowToUser(array $row): User
    {
        return new User(
            (string)$row['id'],
            (string)$row['email'],
            (string)$row['password'],
            (int)$row['role']
        );
    }
}
