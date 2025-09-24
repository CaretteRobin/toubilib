<?php

namespace toubilib\core\application\usecases;

use DateInterval;
use DateTimeImmutable;
use Exception;
use Ramsey\Uuid\Uuid;
use toubilib\core\application\dto\CreneauOccupeDTO;
use toubilib\core\application\dto\InputRendezVousDTO;
use toubilib\core\application\dto\RdvDTO;
use toubilib\core\application\exceptions\ResourceNotFoundException;
use toubilib\core\application\exceptions\ValidationException;
use toubilib\core\application\ports\PatientRepositoryInterface;
use toubilib\core\application\ports\PraticienRepositoryInterface;
use toubilib\core\application\ports\RdvRepositoryInterface;
use toubilib\core\domain\entities\rdv\Rdv;
use toubilib\core\domain\exceptions\DomainException;

class ServiceRDV implements ServiceRDVInterface
{
    private RdvRepositoryInterface $rdvRepository;
    private PraticienRepositoryInterface $praticienRepository;
    private PatientRepositoryInterface $patientRepository;

    public function __construct(
        RdvRepositoryInterface $rdvRepository,
        PraticienRepositoryInterface $praticienRepository,
        PatientRepositoryInterface $patientRepository
    ) {
        $this->rdvRepository = $rdvRepository;
        $this->praticienRepository = $praticienRepository;
        $this->patientRepository = $patientRepository;
    }

    public function listerCreneauxOccupes(string $praticienId, string $dateDebut, string $dateFin): array
    {
        $this->getPraticienDetailOrFail($praticienId);

        $debut = $this->parseDate($dateDebut);
        $fin = $this->parseDate($dateFin);
        $this->assertPeriodeChronologique($debut, $fin);

        $rdvs = $this->rdvRepository->findByPraticienBetween(
            $praticienId,
            $debut->format('Y-m-d H:i:s'),
            $fin->format('Y-m-d H:i:s')
        );
        $slots = [];
        foreach ($rdvs as $rdv) {
            if ($rdv->isCancelled()) {
                continue;
            }
            $slots[] = new CreneauOccupeDTO(
                $rdv->date_heure_debut,
                $rdv->getDateHeureFin()->format('Y-m-d H:i:s')
            );
        }
        return $slots;
    }

    public function consulterRdv(string $id): RdvDTO
    {
        $rdv = $this->rdvRepository->findById($id);
        if (!$rdv) {
            throw new ResourceNotFoundException(sprintf('Rendez-vous %s introuvable', $id));
        }

        return $this->mapToDto($rdv);
    }

    public function creerRendezVous(InputRendezVousDTO $dto): RdvDTO
    {
        $debut = $this->parseDate($dto->dateHeureDebut);
        $this->assertDureeValide($dto->dureeMinutes);
        $fin = $debut->add(new DateInterval('PT' . $dto->dureeMinutes . 'M'));

        $praticien = $this->getPraticienDetailOrFail($dto->praticienId);

        $patient = $this->patientRepository->findById($dto->patientId);
        if (!$patient) {
            throw new ResourceNotFoundException(sprintf('Patient %s introuvable', $dto->patientId));
        }

        $motif = $this->resolveMotif($dto->motifId, $praticien->motifs);

        $this->assertCreneauOuvre($debut, $fin);
        $this->assertPraticienDisponible($dto->praticienId, $debut, $fin);

        $rdv = new Rdv(
            Uuid::uuid4()->toString(),
            $dto->praticienId,
            $dto->patientId,
            $patient->email,
            $debut->format('Y-m-d H:i:s'),
            Rdv::STATUS_SCHEDULED,
            $dto->dureeMinutes,
            $fin->format('Y-m-d H:i:s'),
            (new DateTimeImmutable('now'))->format('Y-m-d H:i:s'),
            $motif
        );

        $this->rdvRepository->save($rdv);

        return $this->mapToDto($rdv);
    }

    public function annulerRendezVous(string $id): void
    {
        $rdv = $this->rdvRepository->findById($id);
        if (!$rdv) {
            throw new ResourceNotFoundException(sprintf('Rendez-vous %s introuvable', $id));
        }

        try {
            $rdv->cancel();
        } catch (DomainException $exception) {
            throw new ValidationException($exception->getMessage(), previous: $exception);
        }

        $this->rdvRepository->save($rdv);
    }

    public function listerAgenda(string $praticienId, string $dateDebut, string $dateFin): array
    {
        $this->getPraticienDetailOrFail($praticienId);

        $debut = $this->parseDate($dateDebut);
        $fin = $this->parseDate($dateFin);
        $this->assertPeriodeChronologique($debut, $fin);

        $rdvs = $this->rdvRepository->findByPraticienBetween(
            $praticienId,
            $debut->format('Y-m-d H:i:s'),
            $fin->format('Y-m-d H:i:s')
        );
        $agenda = [];
        foreach ($rdvs as $rdv) {
            $agenda[] = $this->mapToDto($rdv);
        }
        return $agenda;
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
            $rdv->getDateHeureFin()->format('Y-m-d H:i:s'),
            $rdv->date_creation,
            $rdv->motif_visite
        );
    }

    private function parseDate(string $date): DateTimeImmutable
    {
        try {
            return new DateTimeImmutable($date);
        } catch (Exception $exception) {
            throw new ValidationException('Format de date invalide, attendu ISO Y-m-d H:i:s', previous: $exception);
        }
    }

    private function assertDureeValide(int $duree): void
    {
        if ($duree <= 0) {
            throw new ValidationException('La durée du rendez-vous doit être strictement positive.');
        }
    }

    private function assertCreneauOuvre(DateTimeImmutable $debut, DateTimeImmutable $fin): void
    {
        if ($debut->format('Y-m-d') !== $fin->format('Y-m-d')) {
            throw new ValidationException('Le rendez-vous doit se dérouler sur une seule journée.');
        }

        $jour = (int)$debut->format('N');
        if ($jour > 5) {
            throw new ValidationException('Le rendez-vous doit être planifié du lundi au vendredi.');
        }

        $heureOuverture = $debut->setTime(8, 0);
        $heureFermeture = $debut->setTime(19, 0);
        if ($debut < $heureOuverture || $fin > $heureFermeture) {
            throw new ValidationException('Le rendez-vous doit être planifié entre 08:00 et 19:00.');
        }
    }

    private function assertPraticienDisponible(string $praticienId, DateTimeImmutable $debut, DateTimeImmutable $fin): void
    {
        $overlaps = $this->rdvRepository->findOverlapping(
            $praticienId,
            $debut->format('Y-m-d H:i:s'),
            $fin->format('Y-m-d H:i:s')
        );

        foreach ($overlaps as $rdv) {
            if ($rdv->isCancelled()) {
                continue;
            }
            throw new ValidationException('Le praticien n\'est pas disponible sur ce créneau.');
        }
    }

    private function assertPeriodeChronologique(DateTimeImmutable $debut, DateTimeImmutable $fin): void
    {
        if ($fin <= $debut) {
            throw new ValidationException('La date de fin doit être postérieure à la date de début.');
        }
    }

    /**
     * @param array<int, mixed> $motifs
     */
    private function resolveMotif(string $motifId, array $motifs): string
    {
        foreach ($motifs as $motif) {
            if ((string)$motif->id === (string)$motifId) {
                return $motif->libelle;
            }
        }

        throw new ValidationException('Motif de visite invalide pour ce praticien.');
    }

    private function getPraticienDetailOrFail(string $praticienId)
    {
        $detail = $this->praticienRepository->findDetailById($praticienId);
        if ($detail === null) {
            throw new ResourceNotFoundException(sprintf('Praticien %s introuvable', $praticienId));
        }

        return $detail;
    }
}
