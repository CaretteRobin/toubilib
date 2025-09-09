<?php

namespace toubilib\core\domain\entities\praticien;

class Structure
{
    public string $id;
    public string $nom;
    public string $adresse;
    public ?string $ville;
    public ?string $code_postal;
    public ?string $telephone;

    public function __construct(
        string $id,
        string $nom,
        string $adresse,
        ?string $ville,
        ?string $code_postal,
        ?string $telephone
    ) {
        $this->id = $id;
        $this->nom = $nom;
        $this->adresse = $adresse;
        $this->ville = $ville;
        $this->code_postal = $code_postal;
        $this->telephone = $telephone;
    }
}

