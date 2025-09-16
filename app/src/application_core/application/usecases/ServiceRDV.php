<?php

namespace toubilib\core\application\usecases;

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
        return $this->mapToDto($r);
    }

    public function creerRendezVous(InputRendezVousDTO $dto): RdvDTO
    {
        $entity = $this->mapFromDto($dto);
        $this->rdvRepository->save($entity);

        return $this->mapToDto($entity);
    }

    private function mapFromDto(InputRendezVousDTO $dto): Rdv
    {
        $dateDebut = (new DateTimeImmutable($dto->date_heure_debut))->format('Y-m-d H:i:s');

        return new Rdv(
            Uuid::uuid4()->toString(),
            $dto->praticien_id,
            $dto->patient_id,
            null,
            $dateDebut,
            Rdv::STATUS_SCHEDULED,
            $dto->duree,
            null,
            null,
            $dto->motif_visite
        );
    }

    private function mapToDto(Rdv $rdv): RdvDTO
    {
        return new RdvDTO(
            $rdv->id,
            $rdv->praticien_id,
            $rdv->patient_id,
            $rdv->patient_email,
            $rdv->date_heure_debut,
            $rdv->status,
            $rdv->duree,
            $rdv->date_heure_fin,
            $rdv->date_creation,
            $rdv->motif_visite
        );
    }
}
