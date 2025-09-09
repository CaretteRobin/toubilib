<?php

namespace toubilib\infra\repositories;

use toubilib\core\application\ports\PraticienRepositoryInterface;
use toubilib\core\domain\entities\praticien\Praticien;
use toubilib\core\domain\entities\praticien\Specialite;

class PDOPraticienRepository implements PraticienRepositoryInterface
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return Praticien[]
     */
    public function findAll(): array
    {
        $sql = 'SELECT p.id, p.nom, p.prenom, p.ville, p.email, s.id AS specialite_id, s.libelle AS specialite_libelle
                FROM praticien p
                JOIN specialite s ON s.id = p.specialite_id
                ORDER BY p.nom, p.prenom';
        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll();
        $result = [];
        foreach ($rows as $row) {
            $spec = new Specialite((int)$row['specialite_id'], (string)$row['specialite_libelle']);
            $result[] = new Praticien(
                (string)$row['id'],
                (string)$row['nom'],
                (string)$row['prenom'],
                (string)$row['ville'],
                (string)$row['email'],
                $spec
            );
        }
        return $result;
    }
}
