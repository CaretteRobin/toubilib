<?php

namespace toubilib\infra\repositories;

use toubilib\core\application\ports\PraticienRepositoryInterface;
use toubilib\core\domain\entities\praticien\Praticien;
use toubilib\core\domain\entities\praticien\Specialite;
use toubilib\core\domain\entities\praticien\PraticienDetail;
use toubilib\core\domain\entities\praticien\Structure;
use toubilib\core\domain\entities\praticien\MotifVisite;
use toubilib\core\domain\entities\praticien\MoyenPaiement;

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

    public function findDetailById(string $id): ?PraticienDetail
    {
        $sql = 'SELECT p.*, s.id AS specialite_id, s.libelle AS specialite_libelle,
                       st.id AS structure_id, st.nom AS structure_nom, st.adresse AS structure_adresse,
                       st.ville AS structure_ville, st.code_postal AS structure_cp, st.telephone AS structure_tel
                FROM praticien p
                JOIN specialite s ON s.id = p.specialite_id
                LEFT JOIN structure st ON st.id = p.structure_id
                WHERE p.id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) return null;

        $spec = new Specialite((int)$row['specialite_id'], (string)$row['specialite_libelle']);
        $structure = null;
        if ($row['structure_id']) {
            $structure = new Structure(
                (string)$row['structure_id'],
                (string)$row['structure_nom'],
                (string)$row['structure_adresse'],
                $row['structure_ville'] !== null ? (string)$row['structure_ville'] : null,
                $row['structure_cp'] !== null ? (string)$row['structure_cp'] : null,
                $row['structure_tel'] !== null ? (string)$row['structure_tel'] : null
            );
        }

        // motifs
        $sqlMotifs = 'SELECT m.id, m.libelle FROM praticien2motif pm JOIN motif_visite m ON m.id = pm.motif_id WHERE pm.praticien_id = :id ORDER BY m.libelle';
        $stmtM = $this->pdo->prepare($sqlMotifs);
        $stmtM->execute([':id' => $id]);
        $motifs = [];
        foreach ($stmtM->fetchAll() as $m) {
            $motifs[] = new MotifVisite((int)$m['id'], (string)$m['libelle']);
        }

        // moyens
        $sqlMoyens = 'SELECT mp.id, mp.libelle FROM praticien2moyen pm JOIN moyen_paiement mp ON mp.id = pm.moyen_id WHERE pm.praticien_id = :id ORDER BY mp.libelle';
        $stmtMo = $this->pdo->prepare($sqlMoyens);
        $stmtMo->execute([':id' => $id]);
        $moyens = [];
        foreach ($stmtMo->fetchAll() as $mo) {
            $moyens[] = new MoyenPaiement((int)$mo['id'], (string)$mo['libelle']);
        }

        return new PraticienDetail(
            (string)$row['id'],
            (string)$row['nom'],
            (string)$row['prenom'],
            (string)$row['ville'],
            (string)$row['email'],
            (string)$row['telephone'],
            $spec,
            $structure,
            $motifs,
            $moyens
        );
    }
}
