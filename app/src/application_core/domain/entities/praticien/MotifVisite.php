<?php

namespace toubilib\core\domain\entities\praticien;

class MotifVisite
{
    public int $id;
    public string $libelle;

    public function __construct(int $id, string $libelle)
    {
        $this->id = $id;
        $this->libelle = $libelle;
    }
}

