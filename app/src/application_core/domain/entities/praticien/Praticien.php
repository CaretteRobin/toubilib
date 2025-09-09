<?php

namespace toubilib\core\domain\entities\praticien;


class Praticien
{
    public string $id;
    public string $nom;
    public string $prenom;
    public string $ville;
    public string $email;
    public Specialite $specialite;

    public function __construct(
        string $id,
        string $nom,
        string $prenom,
        string $ville,
        string $email,
        Specialite $specialite
    ) {
        $this->id = $id;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->ville = $ville;
        $this->email = $email;
        $this->specialite = $specialite;
    }

}
