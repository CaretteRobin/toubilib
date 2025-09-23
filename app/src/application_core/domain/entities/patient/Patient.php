<?php

namespace toubilib\core\domain\entities\patient;

class Patient
{
    public string $id;
    public string $nom;
    public string $prenom;
    public ?string $email;

    public function __construct(string $id, string $nom, string $prenom, ?string $email)
    {
        $this->id = $id;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
    }
}
