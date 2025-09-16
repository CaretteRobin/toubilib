<?php

namespace toubilib\core\domain\entities\rdv;

class Rdv
{
    public string $id;
    public string $praticien_id;
    public string $patient_id;
    public ?string $patient_email;
    public string $date_heure_debut; // ISO string
    public ?int $status;
    public int $duree;
    public ?string $date_heure_fin; // ISO string
    public ?string $date_creation; // ISO string
    public ?string $motif_visite;

    public function __construct(
        string $id,
        string $praticien_id,
        string $patient_id,
        ?string $patient_email,
        string $date_heure_debut,
        ?int $status,
        int $duree,
        ?string $date_heure_fin,
        ?string $date_creation,
        ?string $motif_visite
    ) {
        $this->id = $id;
        $this->praticien_id = $praticien_id;
        $this->patient_id = $patient_id;
        $this->patient_email = $patient_email;
        $this->date_heure_debut = $date_heure_debut;
        $this->status = $status;
        $this->duree = $duree;
        $this->date_heure_fin = $date_heure_fin;
        $this->date_creation = $date_creation;
        $this->motif_visite = $motif_visite;
    }
}

