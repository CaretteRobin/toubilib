<?php

namespace toubilib\core\application\usecases;

interface ServicePraticienInterface
{
    /**
     * Retourne la liste complète des praticiens avec informations de base.
     * @return \toubilib\core\application\dto\PraticienDTO[]
     */
    public function listerPraticiens(): array;

    /**
     * Retourne le détail d'un praticien par son identifiant.
     * @param string $id
     * @throws \toubilib\core\application\exceptions\ResourceNotFoundException
     */
    public function afficherPraticien(string $id): \toubilib\core\application\dto\PraticienDetailDTO;
}
