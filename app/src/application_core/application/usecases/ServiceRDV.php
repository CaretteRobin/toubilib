<?php

namespace toubilib\core\application\usecases;

use DateInterval;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use toubilib\core\application\dto\CreneauOccupeDTO;
use toubilib\core\application\dto\InputRendezVousDTO;
use toubilib\core\application\dto\RdvDTO;
use toubilib\core\application\exceptions\ResourceNotFoundException;
use toubilib\core\application\ports\RdvRepositoryInterface;
use toubilib\core\domain\entities\rdv\Rdv;

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

    public function consulterRdv(string $id): RdvDTO
    {
        $r = $this->rdvRepository->findById($id);
        if (!$r) {
            throw new ResourceNotFoundException(sprintf('Rendez-vous %s introuvable', $id));
        }
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

    public function creerRendezVous(InputRendezVousDTO $dto): RdvDTO
    {
        $id = Uuid::uuid4()->toString();
        $debut = new DateTimeImmutable($dto->date_heure_debut);
        $fin = $debut->add(new DateInterval('PT' . $dto->duree . 'M'));
        $creation = new DateTimeImmutable();

        $entity = new Rdv(
            $id,
            $dto->praticien_id,
            $dto->patient_id,
            null,
            $debut->format('Y-m-d H:i:s'),
            0,
            $dto->duree,
            $fin->format('Y-m-d H:i:s'),
            $creation->format('Y-m-d H:i:s'),
            $dto->motif_visite
        );

        $this->rdvRepository->save($entity);

        return new RdvDTO(
            $entity->id,
            $entity->praticien_id,
            $entity->patient_id,
            $entity->patient_email,
            $entity->date_heure_debut,
            $entity->status,
            $entity->duree,
            $entity->date_heure_fin,
            $entity->date_creation,
            $entity->motif_visite
        );
    }
}
