<?php

namespace toubilib\core\application\ports;

use toubilib\core\domain\entities\user\User;
use toubilib\core\domain\exceptions\UserNotFoundException;
use toubilib\core\domain\exceptions\DuplicateUserException;

interface UserRepositoryInterface
{
    /**
     * Trouve un utilisateur par son email
     * 
     * @param string $email L'email de l'utilisateur
     * @return User L'utilisateur trouvé
     * @throws UserNotFoundException Si l'utilisateur n'est pas trouvé
     */
    public function findByEmail(string $email): User;

    /**
     * Trouve un utilisateur par son ID
     * 
     * @param string $id L'ID de l'utilisateur
     * @return User L'utilisateur trouvé
     * @throws UserNotFoundException Si l'utilisateur n'est pas trouvé
     */
    public function findById(string $id): User;

    /**
     * Sauvegarde un nouvel utilisateur
     * 
     * @param User $user L'utilisateur à sauvegarder
     * @return User L'utilisateur sauvegardé
     * @throws DuplicateUserException Si l'email existe déjà
     */
    public function save(User $user): User;

    /**
     * Met à jour un utilisateur existant
     * 
     * @param User $user L'utilisateur à mettre à jour
     * @return User L'utilisateur mis à jour
     * @throws UserNotFoundException Si l'utilisateur n'existe pas
     */
    public function update(User $user): User;

    /**
     * Supprime un utilisateur
     * 
     * @param string $id L'ID de l'utilisateur à supprimer
     * @throws UserNotFoundException Si l'utilisateur n'existe pas
     */
    public function delete(string $id): void;
}