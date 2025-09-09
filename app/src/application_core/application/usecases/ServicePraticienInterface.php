<?php

namespace toubilib\core\application\usecases;

interface ServicePraticienInterface
{
    /**
     * Retourne la liste complète des praticiens avec informations de base.
     * @return \toubilib\core\application\dto\PraticienDTO[]
     */
    public function listerPraticiens(): array;
}
