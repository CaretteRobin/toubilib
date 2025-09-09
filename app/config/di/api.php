<?php

use Psr\Container\ContainerInterface;
use toubilib\api\actions\praticien\ListerPraticiensAction;
use toubilib\core\application\usecases\ServicePraticienInterface;
use toubilib\api\actions\praticien\AfficherPraticienAction;
use toubilib\api\actions\praticien\ListerCreneauxOccupesAction;
use toubilib\api\actions\rdv\ConsulterRdvAction;
use toubilib\core\application\usecases\ServiceRDVInterface;

return [
    ListerPraticiensAction::class => function (ContainerInterface $c): ListerPraticiensAction {
        return new ListerPraticiensAction($c->get(ServicePraticienInterface::class));
    },
    AfficherPraticienAction::class => function (ContainerInterface $c): AfficherPraticienAction {
        return new AfficherPraticienAction($c->get(ServicePraticienInterface::class));
    },
    ListerCreneauxOccupesAction::class => function (ContainerInterface $c): ListerCreneauxOccupesAction {
        return new ListerCreneauxOccupesAction($c->get(ServiceRDVInterface::class));
    },
    ConsulterRdvAction::class => function (ContainerInterface $c): ConsulterRdvAction {
        return new ConsulterRdvAction($c->get(ServiceRDVInterface::class));
    },
];
