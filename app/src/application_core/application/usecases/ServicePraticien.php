<?php

namespace toubilib\core\application\usecases;

use toubilib\core\application\dto\PraticienDTO;
use toubilib\core\application\dto\PraticienDetailDTO;
use toubilib\core\application\exceptions\ResourceNotFoundException;
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
                $p->id,
                $p->nom,
                $p->prenom,
                $p->ville,
                $p->email,
                $p->specialite->libelle
            );
        }
        return $dtos;
    }

    public function afficherPraticien(string $id): PraticienDetailDTO
    {
        $detail = $this->praticienRepository->findDetailById($id);
        if ($detail === null) {
            throw new ResourceNotFoundException(sprintf('Praticien %s introuvable', $id));
        }

        $structure = null;
        if ($detail->structure) {
            $structure = [
                'nom' => $detail->structure->nom,
                'adresse' => $detail->structure->adresse,
                'ville' => $detail->structure->ville,
                'code_postal' => $detail->structure->code_postal,
                'telephone' => $detail->structure->telephone,
            ];
        }

        $motifs = array_map(fn($m) => $m->libelle, $detail->motifs);
        $moyens = array_map(fn($m) => $m->libelle, $detail->moyens);

        return new PraticienDetailDTO(
            $detail->id,
            $detail->nom,
            $detail->prenom,
            $detail->ville,
            $detail->email,
            $detail->telephone,
            $detail->specialite->libelle,
            $structure,
            $motifs,
            $moyens
        );
    }
}
