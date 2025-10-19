<?php
declare(strict_types=1);

namespace toubilib\api\security;

use toubilib\core\application\dto\UserDTO;

interface JwtManagerInterface
{
    public function createAccessToken(UserDTO $user): string;

    public function createRefreshToken(UserDTO $user): string;

    public function decode(string $token, string $expectedType = 'access'): JwtPayload;

    public function getAccessTokenTtl(): int;

    public function getRefreshTokenTtl(): int;
}
