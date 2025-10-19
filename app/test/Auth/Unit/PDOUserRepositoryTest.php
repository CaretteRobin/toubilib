<?php

namespace Tests\Auth\Unit;

use PHPUnit\Framework\TestCase;
use toubilib\infra\repositories\PDOUserRepository;
use toubilib\core\domain\entities\user\User;
use toubilib\core\domain\entities\user\UserRole;
use toubilib\core\domain\exceptions\UserNotFoundException;
use toubilib\core\domain\exceptions\DuplicateUserException;

class PDOUserRepositoryTest extends TestCase
{
    private $pdoMock;
    private $stmtMock;
    private PDOUserRepository $repository;

    protected function setUp(): void
    {
        $this->pdoMock = $this->createMock(\PDO::class);
        $this->stmtMock = $this->createMock(\PDOStatement::class);
        $this->repository = new PDOUserRepository($this->pdoMock);
    }

    public function testFindByEmailSuccess(): void
    {
        $userData = [
            'id' => 'test-id',
            'email' => 'test@example.com',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'role' => UserRole::USER
        ];

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with('SELECT id, email, password, role FROM users WHERE email = :email')
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->with(['email' => 'test@example.com']);

        $this->stmtMock->expects($this->once())
            ->method('fetch')
            ->willReturn($userData);

        $user = $this->repository->findByEmail('test@example.com');

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('test-id', $user->id);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals(UserRole::USER, $user->role);
    }

    public function testFindByEmailNotFound(): void
    {
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('execute');

        $this->stmtMock->expects($this->once())
            ->method('fetch')
            ->willReturn(false);

        $this->expectException(UserNotFoundException::class);
        $this->repository->findByEmail('nonexistent@example.com');
    }

    public function testFindByIdSuccess(): void
    {
        $userData = [
            'id' => 'test-id',
            'email' => 'test@example.com',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'role' => UserRole::USER
        ];

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with('SELECT id, email, password, role FROM users WHERE id = :id')
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->with(['id' => 'test-id']);

        $this->stmtMock->expects($this->once())
            ->method('fetch')
            ->willReturn($userData);

        $user = $this->repository->findById('test-id');

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('test-id', $user->id);
    }

    public function testFindByIdNotFound(): void
    {
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('execute');

        $this->stmtMock->expects($this->once())
            ->method('fetch')
            ->willReturn(false);

        $this->expectException(UserNotFoundException::class);
        $this->repository->findById('nonexistent-id');
    }

    public function testSaveSuccess(): void
    {
        $user = new User(
            'new-id',
            'new@example.com',
            password_hash('password', PASSWORD_DEFAULT),
            UserRole::USER
        );

        // Première vérification : on teste l'existence de l'email (le résultat doit être négatif)
        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls(
                $this->stmtMock, // préparation pour findByEmail
                $this->stmtMock  // préparation pour l'insertion
            );

        $this->stmtMock->expects($this->exactly(2))
            ->method('execute');

        // La première lecture renvoie false (l'utilisateur est absent)
        $this->stmtMock->expects($this->once())
            ->method('fetch')
            ->willReturn(false);

        $savedUser = $this->repository->save($user);
        $this->assertEquals($user, $savedUser);
    }

    public function testCreateUser(): void
    {
        $email = 'create@example.com';
        $password = 'password123';
        $role = UserRole::USER;

        // Simulation de l'appel à findByEmail dans la méthode save
        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->exactly(2))
            ->method('execute');

        $this->stmtMock->expects($this->once())
            ->method('fetch')
            ->willReturn(false); // l'utilisateur est absent

        $user = $this->repository->createUser($email, $password, $role);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($email, $user->email);
        $this->assertEquals($role, $user->role);
        $this->assertTrue($user->verifyPassword($password));
    }
}
