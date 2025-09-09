<?php

namespace toubilib\core\domain\entities\praticien;

class PraticienDetail
{
    public string $id;
    public string $nom;
    public string $prenom;
    public string $ville;
    public string $email;
    public string $telephone;
    public Specialite $specialite;
    public ?Structure $structure;
    /** @var MotifVisite[] */
    public array $motifs;
    /** @var MoyenPaiement[] */
    public array $moyens;

    public function __construct(
        string $id,
        string $nom,
        string $prenom,
        string $ville,
        string $email,
        string $telephone,
        Specialite $specialite,
        ?Structure $structure,
        array $motifs,
        array $moyens
    ) {
        $this->id = $id;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->ville = $ville;
        $this->email = $email;
        $this->telephone = $telephone;
        $this->specialite = $specialite;
        $this->structure = $structure;
        $this->motifs = $motifs;
        $this->moyens = $moyens;
    }
}

