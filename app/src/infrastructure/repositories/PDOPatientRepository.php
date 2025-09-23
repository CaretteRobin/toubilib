<?php

namespace toubilib\infra\repositories;

use PDO;
use toubilib\core\application\ports\PatientRepositoryInterface;
use toubilib\core\domain\entities\patient\Patient;

class PDOPatientRepository implements PatientRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(string $id): ?Patient
    {
        $sql = 'SELECT id, nom, prenom, email FROM patient WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        return new Patient(
            (string)$row['id'],
            (string)$row['nom'],
            (string)$row['prenom'],
            $row['email'] !== null ? (string)$row['email'] : null
        );
    }
}
