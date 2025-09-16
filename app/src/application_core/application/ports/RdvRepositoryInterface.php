<?php

namespace toubilib\core\application\ports;

use toubilib\core\domain\entities\rdv\Rdv;

interface RdvRepositoryInterface
{
    /**
     * Liste les rendez-vous d'un praticien sur une période [de, a].
     * @param string $praticienId
     * @param string $de ISO datetime (Y-m-d H:i:s)
     * @param string $a ISO datetime (Y-m-d H:i:s)
     * @return Rdv[]
     */
    public function findByPraticienBetween(string $praticienId, string $de, string $a): array;

    /**
     * Consulte un rendez-vous par son identifiant.
     * @param string $id
     * @return Rdv|null
     */
    public function findById(string $id): ?Rdv;

    /**
     * Persiste un rendez-vous.
     */
    public function save(Rdv $rdv): void;
}
