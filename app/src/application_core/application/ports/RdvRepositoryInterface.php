<?php

namespace toubilib\core\application\ports;

use toubilib\core\domain\entities\rdv\Rdv;

interface RdvRepositoryInterface
{
    /**
     * Liste les rendez-vous d'un praticien sur une période [de, a].
     * @return Rdv[]
     */
    public function findByPraticienBetween(string $praticienId, string $de, string $a): array;

    /**
     * Consulte un rendez-vous par son identifiant.
     */
    public function findById(string $id): ?Rdv;

    /**
<<<<<<< Updated upstream
     * Persiste un rendez-vous.
     */
    public function save(Rdv $rdv): void;
=======
     * Persiste un rendez-vous (insert ou update).
     */
    public function save(Rdv $rdv): void;

    /**
     * Retourne la liste des RDV qui se chevauchent la période [de, a[.
     * @return Rdv[]
     */
    public function findOverlapping(string $praticienId, string $de, string $a): array;
>>>>>>> Stashed changes
}
