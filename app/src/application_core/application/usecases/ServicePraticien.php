<?php

namespace toubilib\core\application\usecases;

use toubilib\core\application\dto\PraticienDTO;
use toubilib\core\application\ports\PraticienRepositoryInterface;



class ServicePraticien implements ServicePraticienInterface
{
    private PraticienRepositoryInterface $praticienRepository;

    public function __construct(PraticienRepositoryInterface $praticienRepository)
    {
        $this->praticienRepository = $praticienRepository;
    }

    public function listerPraticiens(): array {
        $entities = $this->praticienRepository->findAll();
        $dtos = [];
        foreach ($entities as $p) {
            $dtos[] = new PraticienDTO(
                $p->nom,
                $p->prenom,
                $p->ville,
                $p->email,
                $p->specialite->libelle
            );
        }
        return $dtos;
    }
}
