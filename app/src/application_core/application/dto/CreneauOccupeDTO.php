<?php

namespace toubilib\core\application\dto;

class CreneauOccupeDTO implements \JsonSerializable
{
    public string $debut;
    public string $fin;

    public function __construct(string $debut, string $fin)
    {
        $this->debut = $debut;
        $this->fin = $fin;
    }

    public function jsonSerialize(): array
    {
        return [
            'debut' => $this->debut,
            'fin' => $this->fin,
        ];
    }
}

