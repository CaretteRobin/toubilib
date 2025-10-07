<?php

namespace toubilib\core\application\dto;

class PraticienDTO implements \JsonSerializable
{
    public string $id;
    public string $nom;
    public string $prenom;
    public string $ville;
    public string $email;
    public string $specialite;

    public function __construct(string $id, string $nom, string $prenom, string $ville, string $email, string $specialite)
    {
        $this->id = $id;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->ville = $ville;
        $this->email = $email;
        $this->specialite = $specialite;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'ville' => $this->ville,
            'email' => $this->email,
            'specialite' => $this->specialite,
        ];
    }
}
