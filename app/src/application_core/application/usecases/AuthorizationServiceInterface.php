<?php

namespace toubilib\core\application\usecases;

use toubilib\core\application\dto\RdvDTO;
use toubilib\core\application\dto\UserDTO;
use toubilib\core\application\exceptions\AuthorizationException;

interface AuthorizationServiceInterface
{
    /**
     * @throws AuthorizationException
     */
    public function assertCanAccessAgenda(UserDTO $user, string $praticienId): void;

    /**
     * @throws AuthorizationException
     */
    public function assertCanViewRdv(UserDTO $user, string $rdvId): RdvDTO;

    /**
     * Vérifie qu'un utilisateur peut créer un rendez-vous pour un patient donné.
     *
     * @throws AuthorizationException
     */
    public function assertCanCreateRdv(UserDTO $user, string $patientId): void;

    /**
     * Vérifie qu'un utilisateur peut annuler un rendez-vous.
     *
     * @throws AuthorizationException
     */
    public function assertCanCancelRdv(UserDTO $user, string $rdvId): RdvDTO;
}
