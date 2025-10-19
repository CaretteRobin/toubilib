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
use toubilib\core\domain\entities\praticien\MotifVisite;
use toubilib\core\domain\entities\praticien\PraticienDetail;
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
        if ($rdv === null) {
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
        if ($patient === null) {
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

    public function annulerRendezVous(string $id): RdvDTO
    {
        $rdv = $this->rdvRepository->findById($id);
        if ($rdv === null) {
            throw new ResourceNotFoundException(sprintf('Rendez-vous %s introuvable', $id));
        }

        try {
            $rdv->cancel();
        } catch (DomainException $exception) {
            throw new ValidationException($exception->getMessage(), previous: $exception);
        }

        $this->rdvRepository->save($rdv);

        return $this->mapToDto($rdv);
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

        return array_map(fn(Rdv $rdv) => $this->mapToDto($rdv), $rdvs);
    }

    public function honorerRendezVous(string $id): RdvDTO
    {
        $rdv = $this->rdvRepository->findById($id);
        if ($rdv === null) {
            throw new ResourceNotFoundException(sprintf('Rendez-vous %s introuvable', $id));
        }

        try {
            $rdv->markHonored();
        } catch (DomainException $exception) {
            throw new ValidationException($exception->getMessage(), previous: $exception);
        }

        $this->rdvRepository->save($rdv);

        return $this->mapToDto($rdv);
    }

    public function marquerRendezVousAbsent(string $id): RdvDTO
    {
        $rdv = $this->rdvRepository->findById($id);
        if ($rdv === null) {
            throw new ResourceNotFoundException(sprintf('Rendez-vous %s introuvable', $id));
        }

        try {
            $rdv->markNoShow();
        } catch (DomainException $exception) {
            throw new ValidationException($exception->getMessage(), previous: $exception);
        }

        $this->rdvRepository->save($rdv);

        return $this->mapToDto($rdv);
    }

    /**
     * Transforme l'entité domaine en DTO exploitable par les actions.
     */
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

    private function parseDate(string $value): DateTimeImmutable
    {
        try {
            return new DateTimeImmutable($value);
        } catch (Exception $exception) {
            throw new ValidationException(sprintf('Date invalide : %s', $value), previous: $exception);
        }
    }

    private function assertPeriodeChronologique(DateTimeImmutable $debut, DateTimeImmutable $fin): void
    {
        if ($fin <= $debut) {
            throw new ValidationException('La date de fin doit être postérieure à la date de début.');
        }
    }

    private function assertDureeValide(int $minutes): void
    {
        if ($minutes <= 0) {
            throw new ValidationException('La durée du rendez-vous doit être supérieure à zéro.');
        }
    }

    private function assertCreneauOuvre(DateTimeImmutable $debut, DateTimeImmutable $fin): void
    {
        if ($debut < new DateTimeImmutable('now')) {
            throw new ValidationException('Impossible de créer un rendez-vous dans le passé.');
        }

        if ($fin <= $debut) {
            throw new ValidationException('Le créneau doit se terminer après son heure de début.');
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
            if (!$rdv->isCancelled()) {
                throw new ValidationException('Le praticien est déjà occupé sur ce créneau.');
            }
        }
    }

    /**
     * Vérifie que le motif saisi appartient bien au praticien.
     */
    private function resolveMotif(string $motifId, array $motifs): ?string
    {
        if ($motifId === '') {
            return null;
        }

        /** @var MotifVisite $motif */
        foreach ($motifs as $motif) {
            if ((string)$motif->id === (string)$motifId) {
                return $motif->libelle;
            }
        }

        throw new ValidationException(sprintf('Motif %s invalide pour ce praticien.', $motifId));
    }

    private function getPraticienDetailOrFail(string $id): PraticienDetail
    {
        $praticien = $this->praticienRepository->findDetailById($id);
        if ($praticien === null) {
            throw new ResourceNotFoundException(sprintf('Praticien %s introuvable', $id));
        }

        return $praticien;
    }
}
