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
     * Crée un utilisateur (usage administratif/tests).
     *
     * @param string $email Email du nouvel utilisateur
     * @param string $password Mot de passe en clair
     * @param int $role Rôle applicatif (cf. UserRole)
     * @throws InvalidCredentialsException Si les données sont invalides
     */
    public function createUser(string $email, string $password, int $role): UserDTO;
}
