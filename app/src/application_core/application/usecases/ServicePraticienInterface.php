<?php

namespace toubilib\core\application\usecases;

interface ServicePraticienInterface
{
    /**
     * Retourne la liste complète des praticiens avec informations de base.
     * @return array<int, array{nom:string,prenom:string,ville:string,email:string,specialite:string}>
     */
    public function listerPraticiens(): array;
}

