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
}

