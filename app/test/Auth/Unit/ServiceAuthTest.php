<?php

namespace Tests\Auth\Unit;

use PHPUnit\Framework\TestCase;
use toubilib\core\application\usecases\ServiceAuth;
use toubilib\core\application\ports\UserRepositoryInterface;
use toubilib\core\domain\entities\user\User;
use toubilib\core\domain\entities\user\UserRole;
use toubilib\core\domain\exceptions\InvalidCredentialsException;
use toubilib\core\domain\exceptions\UserNotFoundException;
use toubilib\core\application\dto\UserDTO;

class ServiceAuthTest extends TestCase
{
    private $userRepositoryMock;
    private ServiceAuth $serviceAuth;

    protected function setUp(): void
    {
        $this->userRepositoryMock = $this->createMock(UserRepositoryInterface::class);
        $this->serviceAuth = new ServiceAuth($this->userRepositoryMock);
    }

    public function testAuthenticateSuccess(): void
    {
        $email = 'test@example.com';
        $password = 'password123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $user = new User('user-id', $email, $hashedPassword, UserRole::USER);

        $this->userRepositoryMock->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($user);

        $userDTO = $this->serviceAuth->authenticate($email, $password);

        $this->assertInstanceOf(UserDTO::class, $userDTO);
        $this->assertEquals('user-id', $userDTO->id);
        $this->assertEquals($email, $userDTO->email);
        $this->assertEquals(UserRole::USER, $userDTO->role);
    }

    public function testAuthenticateUserNotFound(): void
    {
        $email = 'nonexistent@example.com';
        $password = 'password123';

        $this->userRepositoryMock->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willThrowException(new UserNotFoundException($email, 'email'));

        $this->expectException(InvalidCredentialsException::class);
        $this->serviceAuth->authenticate($email, $password);
    }

    public function testAuthenticateInvalidPassword(): void
    {
        $email = 'test@example.com';
        $password = 'wrongpassword';
        $hashedPassword = password_hash('correctpassword', PASSWORD_DEFAULT);

        $user = new User('user-id', $email, $hashedPassword, UserRole::USER);

        $this->userRepositoryMock->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($user);

        $this->expectException(InvalidCredentialsException::class);
        $this->serviceAuth->authenticate($email, $password);
    }

    public function testGetUserById(): void
    {
        $userId = 'user-id';
        $user = new User(
            $userId,
            'test@example.com',
            password_hash('password', PASSWORD_DEFAULT),
            UserRole::USER
        );

        $this->userRepositoryMock->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);

        $userDTO = $this->serviceAuth->getUserById($userId);

        $this->assertInstanceOf(UserDTO::class, $userDTO);
        $this->assertEquals($userId, $userDTO->id);
    }

    public function testGetUserByEmail(): void
    {
        $email = 'test@example.com';
        $user = new User(
            'user-id',
            $email,
            password_hash('password', PASSWORD_DEFAULT),
            UserRole::USER
        );

        $this->userRepositoryMock->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($user);

        $userDTO = $this->serviceAuth->getUserByEmail($email);

        $this->assertInstanceOf(UserDTO::class, $userDTO);
        $this->assertEquals($email, $userDTO->email);
    }

    public function testCreateUser(): void
    {
        $email = 'new@example.com';
        $password = 'password123';
        $role = UserRole::USER;

        // Simulation de l'insertion : save() sera appelé avec le nouvel utilisateur
        $this->userRepositoryMock->expects($this->once())
            ->method('save')
            ->willReturnCallback(function(User $user) use ($email, $role) {
                $this->assertEquals($email, $user->email);
                $this->assertEquals($role, $user->role);
                $this->assertTrue($user->verifyPassword('password123'));
                return $user;
            });

        $userDTO = $this->serviceAuth->createUser($email, $password, $role);

        $this->assertInstanceOf(UserDTO::class, $userDTO);
        $this->assertEquals($email, $userDTO->email);
        $this->assertEquals($role, $userDTO->role);
    }
}
