<?php

namespace Tests\Auth\Unit;

use PHPUnit\Framework\TestCase;
use toubilib\core\application\dto\UserDTO;
use toubilib\core\domain\entities\user\User;
use toubilib\core\domain\entities\user\UserRole;

class UserDTOTest extends TestCase
{
    public function testConstructor(): void
    {
        $dto = new UserDTO('test-id', 'test@example.com', UserRole::USER);
        
        $this->assertEquals('test-id', $dto->id);
        $this->assertEquals('test@example.com', $dto->email);
        $this->assertEquals(UserRole::USER, $dto->role);
    }

    public function testFromEntity(): void
    {
        $user = new User(
            'entity-id',
            'entity@example.com',
            password_hash('password', PASSWORD_DEFAULT),
            UserRole::ADMIN
        );

        $dto = UserDTO::fromEntity($user);

        $this->assertEquals('entity-id', $dto->id);
        $this->assertEquals('entity@example.com', $dto->email);
        $this->assertEquals(UserRole::ADMIN, $dto->role);
    }

    public function testToArray(): void
    {
        $dto = new UserDTO('test-id', 'test@example.com', UserRole::USER);
        $array = $dto->toArray();

        $expected = [
            'id' => 'test-id',
            'email' => 'test@example.com',
            'role' => UserRole::USER,
            'role_name' => 'user'
        ];

        $this->assertEquals($expected, $array);
    }
}