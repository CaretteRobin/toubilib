<?php
declare(strict_types=1);

namespace toubilib\api\security;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use toubilib\core\application\dto\UserDTO;

class JwtManager implements JwtManagerInterface
{
    private string $secret;
    private int $accessTtl;
    private int $refreshTtl;
    private string $issuer;

    public function __construct(string $secret, int $accessTtl, int $refreshTtl, string $issuer = 'toubilib')
    {
        $this->secret = $secret;
        $this->accessTtl = $accessTtl;
        $this->refreshTtl = $refreshTtl;
        $this->issuer = $issuer;
    }

    public function createAccessToken(UserDTO $user): string
    {
        return $this->encode($user, 'access', $this->accessTtl);
    }

    public function createRefreshToken(UserDTO $user): string
    {
        return $this->encode($user, 'refresh', $this->refreshTtl);
    }

    public function decode(string $token, string $expectedType = 'access'): JwtPayload
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, 'HS256'));
        } catch (\Throwable $exception) {
            throw new InvalidTokenException('Jeton invalide ou expiré.', $exception);
        }

        if (!isset($decoded->type) || !is_string($decoded->type)) {
            throw new InvalidTokenException('Type de jeton absent.');
        }
        if ($decoded->type !== $expectedType) {
            throw new InvalidTokenException('Type de jeton inattendu.');
        }

        return new JwtPayload(
            (string)$decoded->sub,
            (string)($decoded->email ?? ''),
            isset($decoded->role) ? (int)$decoded->role : 0,
            (string)$decoded->type,
            isset($decoded->exp) ? (int)$decoded->exp : 0
        );
    }

    public function getAccessTokenTtl(): int
    {
        return $this->accessTtl;
    }

    public function getRefreshTokenTtl(): int
    {
        return $this->refreshTtl;
    }

    private function encode(UserDTO $user, string $type, int $ttl): string
    {
        $now = time();

        $payload = [
            'iss' => $this->issuer,
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $ttl,
            'sub' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'type' => $type,
        ];

        return JWT::encode($payload, $this->secret, 'HS256');
    }
}
