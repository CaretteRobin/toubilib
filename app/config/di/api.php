<?php

use Psr\Container\ContainerInterface;
use toubilib\api\actions\auth\LoginAction;
use toubilib\api\actions\auth\MeAction;
use toubilib\api\actions\praticien\ListerPraticiensAction;
use toubilib\core\application\usecases\ServicePraticienInterface;
use toubilib\api\actions\praticien\AfficherPraticienAction;
use toubilib\api\actions\praticien\ListerCreneauxOccupesAction;
use toubilib\api\actions\praticien\ListerAgendaAction;
use toubilib\api\actions\rdv\ConsulterRdvAction;
use toubilib\api\actions\rdv\CreerRdvAction;
use toubilib\api\actions\rdv\AnnulerRdvAction;
use toubilib\api\actions\rdv\ModifierStatutRdvAction;
use toubilib\api\middlewares\AuthenticatedMiddleware;
use toubilib\api\middlewares\AuthorizationMiddleware;
use toubilib\api\middlewares\CreateRendezVousMiddleware;
use toubilib\api\middlewares\OptionalAuthMiddleware;
use toubilib\core\application\usecases\ServiceRDVInterface;
use toubilib\core\application\usecases\AuthProviderInterface;
use toubilib\core\application\usecases\AuthorizationServiceInterface;
use toubilib\core\application\usecases\ServiceAuthInterface;

return [
    LoginAction::class => function (ContainerInterface $c): LoginAction {
        return new LoginAction($c->get(AuthProviderInterface::class));
    },
    MeAction::class => function (): MeAction {
        return new MeAction();
    },
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
    ListerAgendaAction::class => function (ContainerInterface $c): ListerAgendaAction {
        return new ListerAgendaAction($c->get(ServiceRDVInterface::class));
    },
    CreerRdvAction::class => function (ContainerInterface $c): CreerRdvAction {
        return new CreerRdvAction($c->get(ServiceRDVInterface::class));
    },
    AnnulerRdvAction::class => function (ContainerInterface $c): AnnulerRdvAction {
        return new AnnulerRdvAction($c->get(ServiceRDVInterface::class));
    },
    ModifierStatutRdvAction::class => function (ContainerInterface $c): ModifierStatutRdvAction {
        return new ModifierStatutRdvAction($c->get(ServiceRDVInterface::class));
    },
    CreateRendezVousMiddleware::class => function (): CreateRendezVousMiddleware {
        return new CreateRendezVousMiddleware();
    },
    AuthenticatedMiddleware::class => function (ContainerInterface $c): AuthenticatedMiddleware {
        return new AuthenticatedMiddleware($c->get(ServiceAuthInterface::class));
    },
    OptionalAuthMiddleware::class => function (ContainerInterface $c): OptionalAuthMiddleware {
        return new OptionalAuthMiddleware($c->get(ServiceAuthInterface::class));
    },
    AuthorizationMiddleware::class => function (ContainerInterface $c): AuthorizationMiddleware {
        return new AuthorizationMiddleware($c->get(AuthorizationServiceInterface::class));
    },
];
