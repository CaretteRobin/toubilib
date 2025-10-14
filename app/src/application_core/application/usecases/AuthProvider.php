<?php

namespace toubilib\core\application\usecases;

use toubilib\core\application\dto\AuthTokensDTO;

class AuthProvider implements AuthProviderInterface
{
    private ServiceAuthInterface $authService;

    public function __construct(ServiceAuthInterface $authService)
    {
        $this->authService = $authService;
    }

    public function signin(string $email, string $password): AuthTokensDTO
    {
        $user = $this->authService->authenticate($email, $password);
        $accessToken = $this->authService->generateJwtToken($user, 'access');
        $refreshToken = $this->authService->generateJwtToken($user, 'refresh');

        return new AuthTokensDTO(
            $user,
            $accessToken,
            $refreshToken,
            $this->authService->getTokenTtl('access'),
            $this->authService->getTokenTtl('refresh')
        );
    }
}
