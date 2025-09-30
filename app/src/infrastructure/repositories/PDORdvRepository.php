<?php

namespace toubilib\infra\repositories;

use toubilib\core\application\ports\RdvRepositoryInterface;
use toubilib\core\domain\entities\rdv\Rdv;

class PDORdvRepository implements RdvRepositoryInterface
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findByPraticienBetween(string $praticienId, string $de, string $a): array
    {
        $sql = $this->baseSelect()
            . ' WHERE praticien_id = :pid'
            . '   AND date_heure_debut < :fin'
            . '   AND fin_calc > :debut'
            . ' ORDER BY date_heure_debut ASC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':pid' => $praticienId, ':fin' => $a, ':debut' => $de]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($row) => $this->mapRowToRdv($row), $rows);
    }

    public function findOverlapping(string $praticienId, string $de, string $a): array
    {
        $sql = $this->baseSelect()
            . ' WHERE praticien_id = :pid'
            . '   AND date_heure_debut < :fin'
            . '   AND fin_calc > :debut'
            . ' ORDER BY date_heure_debut ASC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':pid' => $praticienId, ':fin' => $a, ':debut' => $de]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($row) => $this->mapRowToRdv($row), $rows);
    }

    public function findById(string $id): ?Rdv
    {
        $sql = $this->baseSelect() . ' WHERE id = :id LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? $this->mapRowToRdv($row) : null;
    }

    public function save(Rdv $rdv): void
    {
        $sql = 'INSERT INTO rdv (id, praticien_id, patient_id, patient_email, date_heure_debut, status, duree, date_heure_fin, date_creation, motif_visite)'
             . ' VALUES (:id, :praticien_id, :patient_id, :patient_email, :date_heure_debut, :status, :duree, :date_heure_fin, :date_creation, :motif_visite)'
             . ' ON CONFLICT (id) DO UPDATE SET'
             . ' praticien_id = EXCLUDED.praticien_id,'
             . ' patient_id = EXCLUDED.patient_id,'
             . ' patient_email = EXCLUDED.patient_email,'
             . ' date_heure_debut = EXCLUDED.date_heure_debut,'
             . ' status = EXCLUDED.status,'
             . ' duree = EXCLUDED.duree,'
             . ' date_heure_fin = EXCLUDED.date_heure_fin,'
             . ' date_creation = EXCLUDED.date_creation,'
             . ' motif_visite = EXCLUDED.motif_visite';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $rdv->id,
            ':praticien_id' => $rdv->praticien_id,
            ':patient_id' => $rdv->patient_id,
            ':patient_email' => $rdv->patient_email,
            ':date_heure_debut' => $rdv->date_heure_debut,
            ':status' => $rdv->status,
            ':duree' => $rdv->duree,
            ':date_heure_fin' => $rdv->getDateHeureFin()->format('Y-m-d H:i:s'),
            ':date_creation' => $rdv->date_creation,
            ':motif_visite' => $rdv->motif_visite,
        ]);
    }

    private function baseSelect(): string
    {
        return "SELECT rdv.*, COALESCE(date_heure_fin, date_heure_debut + (duree || ' minutes')::interval) AS fin_calc FROM rdv";
    }

    private function mapRowToRdv(array $row): Rdv
    {
        $fin = $row['date_heure_fin'] ?? null;
        if ($fin === null && isset($row['fin_calc'])) {
            $fin = $row['fin_calc'] instanceof \DateTimeInterface
                ? $row['fin_calc']->format('Y-m-d H:i:s')
                : (string)$row['fin_calc'];
        }

        return new Rdv(
            (string)$row['id'],
            (string)$row['praticien_id'],
            (string)$row['patient_id'],
            $row['patient_email'] !== null ? (string)$row['patient_email'] : null,
            (string)$row['date_heure_debut'],
            isset($row['status']) ? (int)$row['status'] : Rdv::STATUS_SCHEDULED,
            isset($row['duree']) ? (int)$row['duree'] : 30,
            $fin,
            $row['date_creation'] !== null ? (string)$row['date_creation'] : null,
            $row['motif_visite'] !== null ? (string)$row['motif_visite'] : null
        );
    }
}

