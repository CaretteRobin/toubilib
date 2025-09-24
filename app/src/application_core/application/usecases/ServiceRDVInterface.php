<?php

namespace toubilib\core\application\usecases;

use toubilib\core\application\dto\CreneauOccupeDTO;
use toubilib\core\application\dto\InputRendezVousDTO;
use toubilib\core\application\dto\RdvDTO;

interface ServiceRDVInterface
{
    /**
     * Liste les créneaux occupés pour un praticien sur une période.
     * @return CreneauOccupeDTO[]
     */
    public function listerCreneauxOccupes(string $praticienId, string $dateDebut, string $dateFin): array;

    /**
     * Retourne un RDV par son identifiant.
     * @throws \toubilib\core\application\exceptions\ResourceNotFoundException
     */
    public function consulterRdv(string $id): RdvDTO;

    /**
     * Crée un nouveau rendez-vous et retourne les informations persistées.
     * @throws \toubilib\core\application\exceptions\ValidationException
     * @throws \toubilib\core\application\exceptions\ResourceNotFoundException
     */
    public function creerRendezVous(InputRendezVousDTO $dto): RdvDTO;

    /**
     * Annule un rendez-vous existant.
     * @throws \toubilib\core\application\exceptions\ResourceNotFoundException
     * @throws \toubilib\core\application\exceptions\ValidationException
     */
    public function annulerRendezVous(string $id): void;

    /**
     * Retourne l'agenda d'un praticien sur une période.
     * @return RdvDTO[]
     */
    public function listerAgenda(string $praticienId, string $dateDebut, string $dateFin): array;
}
