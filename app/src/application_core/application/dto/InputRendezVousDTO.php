<?php

namespace toubilib\core\application\dto;

class InputRendezVousDTO
{
    public string $praticienId;
    public string $patientId;
    public string $dateHeureDebut; // Date/heure au format ISO
    public string $motifId;
    public int $dureeMinutes;

    public function __construct(string $praticienId, string $patientId, string $dateHeureDebut, string $motifId, int $dureeMinutes)
    {
        $this->praticienId = $praticienId;
        $this->patientId = $patientId;
        $this->dateHeureDebut = $dateHeureDebut;
        $this->motifId = $motifId;
        $this->dureeMinutes = $dureeMinutes;
    }
}
