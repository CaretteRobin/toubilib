<?php
declare(strict_types=1);

namespace toubilib\api\provider;

use toubilib\api\dto\AuthTokensDTO;
use toubilib\api\security\JwtManagerInterface;
use toubilib\core\application\usecases\ServiceAuthInterface;

class AuthProvider implements AuthProviderInterface
{
    private ServiceAuthInterface $authService;
    private JwtManagerInterface $jwtManager;

    public function __construct(ServiceAuthInterface $authService, JwtManagerInterface $jwtManager)
    {
        $this->authService = $authService;
        $this->jwtManager = $jwtManager;
    }

    public function signin(string $email, string $password): AuthTokensDTO
    {
        $user = $this->authService->authenticate($email, $password);

        return new AuthTokensDTO(
            $user,
            $this->jwtManager->createAccessToken($user),
            $this->jwtManager->createRefreshToken($user),
            $this->jwtManager->getAccessTokenTtl(),
            $this->jwtManager->getRefreshTokenTtl()
        );
    }
}
