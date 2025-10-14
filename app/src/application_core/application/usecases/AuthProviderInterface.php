<?php

namespace toubilib\core\application\usecases;

use toubilib\core\application\dto\AuthTokensDTO;

interface AuthProviderInterface
{
    /**
     * Vérifie les identifiants fournis et renvoie les tokens correspondants.
     */
    public function signin(string $email, string $password): AuthTokensDTO;
}
