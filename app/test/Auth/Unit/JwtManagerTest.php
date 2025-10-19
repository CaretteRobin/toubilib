<?php
declare(strict_types=1);

namespace Tests\Auth\Unit;

use PHPUnit\Framework\TestCase;
use toubilib\api\security\InvalidTokenException;
use toubilib\api\security\JwtManager;
use toubilib\api\security\JwtPayload;
use toubilib\core\application\dto\UserDTO;
use toubilib\core\domain\entities\user\UserRole;

class JwtManagerTest extends TestCase
{
    private JwtManager $manager;

    protected function setUp(): void
    {
        $this->manager = new JwtManager('test-secret', 60, 600, 'tests');
    }

    public function testCreateAndDecodeAccessToken(): void
    {
        $user = new UserDTO('user-1', 'user@example.com', UserRole::USER);

        $token = $this->manager->createAccessToken($user);
        $this->assertNotEmpty($token);

        $payload = $this->manager->decode($token, 'access');
        $this->assertInstanceOf(JwtPayload::class, $payload);
        $this->assertSame('user-1', $payload->subject);
        $this->assertSame('user@example.com', $payload->email);
        $this->assertSame(UserRole::USER, $payload->role);
        $this->assertSame('access', $payload->type);
        $this->assertGreaterThan(time(), $payload->expiresAt);
    }

    public function testCreateRefreshToken(): void
    {
        $user = new UserDTO('user-2', 'user2@example.com', UserRole::PRATICIEN);

        $token = $this->manager->createRefreshToken($user);
        $payload = $this->manager->decode($token, 'refresh');

        $this->assertSame('refresh', $payload->type);
        $this->assertSame('user-2', $payload->subject);
    }

    public function testDecodeWithInvalidType(): void
    {
        $this->expectException(InvalidTokenException::class);

        $user = new UserDTO('user-3', 'user3@example.com', UserRole::ADMIN);
        $token = $this->manager->createRefreshToken($user);

        $this->manager->decode($token, 'access');
    }

    public function testDecodeInvalidToken(): void
    {
        $this->expectException(InvalidTokenException::class);
        $this->manager->decode('invalid-token');
    }
}
