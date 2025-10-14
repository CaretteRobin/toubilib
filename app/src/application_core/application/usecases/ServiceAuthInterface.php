<?php

namespace toubilib\core\application\usecases;

use toubilib\core\application\dto\UserDTO;
use toubilib\core\domain\exceptions\InvalidCredentialsException;
use toubilib\core\domain\exceptions\UserNotFoundException;

interface ServiceAuthInterface
{
    /**
     * Authentifie un utilisateur avec ses identifiants
     * 
     * @param string $email L'email de l'utilisateur
     * @param string $password Le mot de passe en clair
     * @return UserDTO Les informations de l'utilisateur authentifié
     * @throws InvalidCredentialsException Si les identifiants sont incorrects
     */
    public function authenticate(string $email, string $password): UserDTO;

    /**
     * Récupère un utilisateur par son ID
     * 
     * @param string $id L'ID de l'utilisateur
     * @return UserDTO Les informations de l'utilisateur
     * @throws UserNotFoundException Si l'utilisateur n'est pas trouvé
     */
    public function getUserById(string $id): UserDTO;

    /**
     * Récupère un utilisateur par son email
     * 
     * @param string $email L'email de l'utilisateur
     * @return UserDTO Les informations de l'utilisateur
     * @throws UserNotFoundException Si l'utilisateur n'est pas trouvé
     */
    public function getUserByEmail(string $email): UserDTO;

    /**
     * Crée un nouveau token JWT pour un utilisateur
     * 
     * @param UserDTO $user L'utilisateur pour lequel créer le token
     * @return string Le token JWT
     */
    public function generateJwtToken(UserDTO $user, string $type = 'access'): string;

    /**
     * Vérifie et décode un token JWT
     * 
     * @param string $token Le token JWT à vérifier
     * @param string $expectedType Type de token attendu (access|refresh)
     * @return UserDTO L'utilisateur correspondant au token
     * @throws InvalidCredentialsException Si le token est invalide ou expiré
     */
    public function verifyJwtToken(string $token, string $expectedType = 'access'): UserDTO;

    /**
     * Retourne la durée de vie d'un type de token.
     */
    public function getTokenTtl(string $type = 'access'): int;
}
