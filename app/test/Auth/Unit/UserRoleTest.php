<?php

namespace Tests\Auth\Unit;

use PHPUnit\Framework\TestCase;
use toubilib\core\domain\entities\user\UserRole;

class UserRoleTest extends TestCase
{
    public function testValidRoles(): void
    {
        $this->assertTrue(UserRole::isValid(UserRole::ADMIN));
        $this->assertTrue(UserRole::isValid(UserRole::USER));
    }

    public function testInvalidRole(): void
    {
        $this->assertFalse(UserRole::isValid(999));
        $this->assertFalse(UserRole::isValid(-1));
    }

    public function testToStringAdmin(): void
    {
        $this->assertEquals('admin', UserRole::toString(UserRole::ADMIN));
    }

    public function testToStringUser(): void
    {
        $this->assertEquals('user', UserRole::toString(UserRole::USER));
    }

    public function testToStringUnknown(): void
    {
        $this->assertEquals('unknown', UserRole::toString(999));
    }
}