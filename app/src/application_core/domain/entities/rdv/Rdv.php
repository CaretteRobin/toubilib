<?php
namespace toubilib\core\domain\entities\rdv;

use DateInterval;
use DateTimeImmutable;
use toubilib\core\domain\exceptions\DomainException;

class Rdv
{
    public const STATUS_SCHEDULED = 0;
    public const STATUS_CANCELLED = 1;
    public const STATUS_COMPLETED = 2;
    public const STATUS_NO_SHOW = 3;

    public string $id;
    public string $praticien_id;
    public string $patient_id;
    public ?string $patient_email;
    public string $date_heure_debut; // Date/heure au format ISO Y-m-d H:i:s
    public ?int $status;
    public int $duree; // Durée en minutes
    public ?string $date_heure_fin; // Date/heure de fin au format ISO
    public ?string $date_creation; // Date de création au format ISO
    public ?string $motif_visite;

    public function __construct(
        string $id,
        string $praticien_id,
        string $patient_id,
        ?string $patient_email,
        string $date_heure_debut,
        ?int $status = self::STATUS_SCHEDULED,
        int $duree = 30,
        ?string $date_heure_fin = null,
        ?string $date_creation = null,
        ?string $motif_visite = null
    ) {
        $this->id = $id;
        $this->praticien_id = $praticien_id;
        $this->patient_id = $patient_id;
        $this->patient_email = $patient_email;
        $this->date_heure_debut = $date_heure_debut;
        $this->status = $status ?? self::STATUS_SCHEDULED;
        $this->duree = $duree;
        $this->date_heure_fin = $date_heure_fin ?? self::computeFin($date_heure_debut, $duree);
        $this->date_creation = $date_creation ?? (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');
        $this->motif_visite = $motif_visite;
    }

    public static function computeFin(string $dateDebut, int $duree): string
    {
        $dt = new DateTimeImmutable($dateDebut);
        $dt = $dt->add(new DateInterval('PT' . (int)$duree . 'M'));
        return $dt->format('Y-m-d H:i:s');
    }

    public function getDateHeureDebut(): DateTimeImmutable
    {
        return new DateTimeImmutable($this->date_heure_debut);
    }

    public function getDateHeureFin(): DateTimeImmutable
    {
        return new DateTimeImmutable($this->date_heure_fin ?? self::computeFin($this->date_heure_debut, $this->duree));
    }

    public function cancel(): void
    {
        if ($this->isCancelled()) {
            throw new DomainException('Le rendez-vous est déjà annulé.');
        }

        $now = new DateTimeImmutable('now');
        if ($this->getDateHeureDebut() <= $now) {
            throw new DomainException('Impossible d\'annuler un rendez-vous passé ou en cours.');
        }

        $this->status = self::STATUS_CANCELLED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function markHonored(): void
    {
        if ($this->isCancelled()) {
            throw new DomainException('Impossible d\'honorer un rendez-vous annulé.');
        }
        if ($this->status === self::STATUS_COMPLETED) {
            throw new DomainException('Le rendez-vous est déjà marqué comme honoré.');
        }
        if ($this->status === self::STATUS_NO_SHOW) {
            throw new DomainException('Impossible d\'honorer un rendez-vous marqué comme absent.');
        }
        $this->status = self::STATUS_COMPLETED;
    }

    public function markNoShow(): void
    {
        if ($this->isCancelled()) {
            throw new DomainException('Impossible de marquer absent un rendez-vous annulé.');
        }
        if ($this->status === self::STATUS_NO_SHOW) {
            throw new DomainException('Le rendez-vous est déjà marqué comme absent.');
        }
        if ($this->status === self::STATUS_COMPLETED) {
            throw new DomainException('Impossible de marquer absent un rendez-vous honoré.');
        }
        $this->status = self::STATUS_NO_SHOW;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function overlapsWith(Rdv $other): bool
    {
        $startA = $this->getDateHeureDebut();
        $endA = $this->getDateHeureFin();
        $startB = $other->getDateHeureDebut();
        $endB = $other->getDateHeureFin();
        return ($startA < $endB) && ($startB < $endA);
    }
}
