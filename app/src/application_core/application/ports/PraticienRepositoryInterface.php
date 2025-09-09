<?php

namespace toubilib\core\application\ports;

use toubilib\core\domain\entities\praticien\Praticien;

interface PraticienRepositoryInterface
{
    /**
     * Récupère la liste complète des praticiens.
     * @return Praticien[]
     */
    public function findAll(): array;

    /**
     * Récupère le détail d'un praticien par son identifiant.
     * Contient les contacts, l'adresse (via structure), la spécialité,
     * les motifs de visite et les moyens de paiement.
     * @param string $id
     * @return \toubilib\core\domain\entities\praticien\PraticienDetail|null
     */
    public function findDetailById(string $id): ?\toubilib\core\domain\entities\praticien\PraticienDetail;
}
