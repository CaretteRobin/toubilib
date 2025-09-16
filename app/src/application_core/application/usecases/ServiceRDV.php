<?php

namespace toubilib\core\application\usecases;

use toubilib\core\application\dto\CreneauOccupeDTO;
use toubilib\core\application\dto\RdvDTO;
use toubilib\core\application\ports\RdvRepositoryInterface;

class ServiceRDV implements ServiceRDVInterface
{
    private RdvRepositoryInterface $rdvRepository;

    public function __construct(RdvRepositoryInterface $rdvRepository)
    {
        $this->rdvRepository = $rdvRepository;
    }

    public function listerCreneauxOccupes(string $praticienId, string $dateDebut, string $dateFin): array
    {
        $rdvs = $this->rdvRepository->findByPraticienBetween($praticienId, $dateDebut, $dateFin);
        $slots = [];
        foreach ($rdvs as $r) {
            $slots[] = new CreneauOccupeDTO($r->date_heure_debut, $r->date_heure_fin ?? $r->date_heure_debut);
        }
        return $slots;
    }

    public function consulterRdv(string $id): ?RdvDTO
    {
        $r = $this->rdvRepository->findById($id);
        if (!$r) return null;
        return new RdvDTO(
            $r->id,
            $r->praticien_id,
            $r->patient_id,
            $r->patient_email,
            $r->date_heure_debut,
            $r->status,
            $r->duree,
            $r->date_heure_fin,
            $r->date_creation,
            $r->motif_visite
        );
    }
}

