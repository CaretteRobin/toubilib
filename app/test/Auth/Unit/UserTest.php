<?php

namespace Tests\Auth\Unit;

use PHPUnit\Framework\TestCase;
use toubilib\core\domain\entities\user\User;
use toubilib\core\domain\entities\user\UserRole;

class UserTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User(
            'test-id-123',
            'test@example.com',
            password_hash('password123', PASSWORD_DEFAULT),
            UserRole::USER
        );
    }

    public function testConstructor(): void
    {
        $this->assertEquals('test-id-123', $this->user->id);
        $this->assertEquals('test@example.com', $this->user->email);
        $this->assertEquals(UserRole::USER, $this->user->role);
        $this->assertTrue(password_verify('password123', $this->user->passwordHash));
    }

    public function testVerifyPasswordWithCorrectPassword(): void
    {
        $this->assertTrue($this->user->verifyPassword('password123'));
    }

    public function testVerifyPasswordWithIncorrectPassword(): void
    {
        $this->assertFalse($this->user->verifyPassword('wrongpassword'));
    }

    public function testIsUserWhenRoleIsUser(): void
    {
        $this->assertTrue($this->user->isUser());
        $this->assertFalse($this->user->isAdmin());
    }

    public function testIsAdminWhenRoleIsAdmin(): void
    {
        $adminUser = new User(
            'admin-id-123',
            'admin@example.com',
            password_hash('password123', PASSWORD_DEFAULT),
            UserRole::ADMIN
        );

        $this->assertTrue($adminUser->isAdmin());
        $this->assertFalse($adminUser->isUser());
    }
}