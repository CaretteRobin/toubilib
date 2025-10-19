<?php
declare(strict_types=1);

namespace toubilib\api\provider;

use toubilib\api\dto\AuthTokensDTO;

interface AuthProviderInterface
{
    /**
     * Authentifie l'utilisateur et génère les jetons associés.
     */
    public function signin(string $email, string $password): AuthTokensDTO;
}
