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
        $sql = 'SELECT * FROM rdv WHERE praticien_id = :pid AND date_heure_debut >= :de AND date_heure_fin <= :a ORDER BY date_heure_debut';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':pid' => $praticienId, ':de' => $de, ':a' => $a]);
        $rows = $stmt->fetchAll();
        $result = [];
        foreach ($rows as $r) {
            $result[] = new Rdv(
                (string)$r['id'],
                (string)$r['praticien_id'],
                (string)$r['patient_id'],
                $r['patient_email'] !== null ? (string)$r['patient_email'] : null,
                (string)$r['date_heure_debut'],
                isset($r['status']) ? (int)$r['status'] : null,
                isset($r['duree']) ? (int)$r['duree'] : 30,
                $r['date_heure_fin'] !== null ? (string)$r['date_heure_fin'] : null,
                $r['date_creation'] !== null ? (string)$r['date_creation'] : null,
                $r['motif_visite'] !== null ? (string)$r['motif_visite'] : null
            );
        }
        return $result;
    }

    public function findById(string $id): ?Rdv
    {
        $sql = 'SELECT * FROM rdv WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $r = $stmt->fetch();
        if (!$r) return null;
        return new Rdv(
            (string)$r['id'],
            (string)$r['praticien_id'],
            (string)$r['patient_id'],
            $r['patient_email'] !== null ? (string)$r['patient_email'] : null,
            (string)$r['date_heure_debut'],
            isset($r['status']) ? (int)$r['status'] : null,
            isset($r['duree']) ? (int)$r['duree'] : 30,
            $r['date_heure_fin'] !== null ? (string)$r['date_heure_fin'] : null,
            $r['date_creation'] !== null ? (string)$r['date_creation'] : null,
            $r['motif_visite'] !== null ? (string)$r['motif_visite'] : null
        );
    }

    public function save(Rdv $rdv): void
    {
        $sql = 'INSERT INTO rdv (id, praticien_id, patient_id, patient_email, date_heure_debut, status, duree, date_heure_fin, date_creation, motif_visite)
                VALUES (:id, :praticien_id, :patient_id, :patient_email, :date_heure_debut, :status, :duree, :date_heure_fin, :date_creation, :motif_visite)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $rdv->id,
            ':praticien_id' => $rdv->praticien_id,
            ':patient_id' => $rdv->patient_id,
            ':patient_email' => $rdv->patient_email,
            ':date_heure_debut' => $rdv->date_heure_debut,
            ':status' => $rdv->status,
            ':duree' => $rdv->duree,
            ':date_heure_fin' => $rdv->date_heure_fin,
            ':date_creation' => $rdv->date_creation,
            ':motif_visite' => $rdv->motif_visite,
        ]);
    }
}
