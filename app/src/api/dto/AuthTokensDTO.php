<?php
declare(strict_types=1);

namespace toubilib\api\dto;

use JsonSerializable;
use toubilib\core\application\dto\UserDTO;

class AuthTokensDTO implements JsonSerializable
{
    public UserDTO $user;
    public string $accessToken;
    public string $refreshToken;
    public int $expiresIn;
    public int $refreshExpiresIn;

    public function __construct(
        UserDTO $user,
        string $accessToken,
        string $refreshToken,
        int $expiresIn,
        int $refreshExpiresIn
    ) {
        $this->user = $user;
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->expiresIn = $expiresIn;
        $this->refreshExpiresIn = $refreshExpiresIn;
    }

    public function jsonSerialize(): array
    {
        return [
            'user' => $this->user->toArray(),
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'expires_in' => $this->expiresIn,
            'refresh_expires_in' => $this->refreshExpiresIn,
        ];
    }
}
