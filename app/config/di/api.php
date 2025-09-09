<?php

use Psr\Container\ContainerInterface;
use toubilib\api\actions\praticien\ListerPraticiensAction;
use toubilib\core\application\usecases\ServicePraticienInterface;

return [
    ListerPraticiensAction::class => function (ContainerInterface $c): ListerPraticiensAction {
        return new ListerPraticiensAction($c->get(ServicePraticienInterface::class));
    },
];

