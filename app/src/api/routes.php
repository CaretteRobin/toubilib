<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use toubilib\api\actions\auth\LoginAction;
use toubilib\api\actions\auth\MeAction;
use toubilib\api\actions\praticien\ListerPraticiensAction;
use toubilib\api\actions\praticien\AfficherPraticienAction;
use toubilib\api\actions\praticien\ListerCreneauxOccupesAction;
use toubilib\api\actions\praticien\ListerAgendaAction;
use toubilib\api\actions\rdv\ConsulterRdvAction;
use toubilib\api\actions\rdv\CreerRdvAction;
use toubilib\api\actions\rdv\AnnulerRdvAction;
use toubilib\api\actions\rdv\ModifierStatutRdvAction;
use toubilib\api\middlewares\CreateRendezVousMiddleware;
use toubilib\api\middlewares\AuthenticatedMiddleware;
use toubilib\api\middlewares\OptionalAuthMiddleware;
use toubilib\api\middlewares\RequireRoleMiddleware;

return function( \Slim\App $app):\Slim\App {
    // POST /auth/login: authentification et génération de token JWT
    $app->post('/auth/login', LoginAction::class);
    $app->get('/auth/me', MeAction::class)
        ->add(AuthenticatedMiddleware::class);

    // GET /praticiens: retourne la liste complète des praticiens
    $app->get('/praticiens', ListerPraticiensAction::class)
        ->add(OptionalAuthMiddleware::class);
    // GET /praticiens/{id}: détail d'un praticien
    $app->get('/praticiens/{id}', AfficherPraticienAction::class)
        ->add(OptionalAuthMiddleware::class);
    // GET /praticiens/{id}/rdv/occupes?de=YYYY-MM-DD&a=YYYY-MM-DD
    $app->get('/praticiens/{id}/rdv/occupes', ListerCreneauxOccupesAction::class)
        ->add(OptionalAuthMiddleware::class);
    // GET /praticiens/{id}/agenda?de=YYYY-MM-DD&a=YYYY-MM-DD (défaut journée courante)
    $app->get('/praticiens/{id}/agenda', ListerAgendaAction::class)
        ->add(new RequireRoleMiddleware(['admin', 'praticien']))
        ->add(AuthenticatedMiddleware::class);
    // GET /rdv/{id}: consulter un rendez-vous
    $app->get('/rdv/{id}', ConsulterRdvAction::class)
        ->add(new RequireRoleMiddleware(['admin', 'praticien']))
        ->add(AuthenticatedMiddleware::class);
    // POST /rdv: créer un rendez-vous
    $app->post('/rdv', CreerRdvAction::class)
        ->add(new RequireRoleMiddleware(['admin', 'praticien']))
        ->add(AuthenticatedMiddleware::class)
        ->add(CreateRendezVousMiddleware::class);
    // DELETE /rdv/{id}: annuler un rendez-vous
    $app->delete('/rdv/{id}', AnnulerRdvAction::class)
        ->add(new RequireRoleMiddleware(['admin', 'praticien']))
        ->add(AuthenticatedMiddleware::class);
    // PATCH /rdv/{id}: honorer / marquer absent
    $app->patch('/rdv/{id}', ModifierStatutRdvAction::class)
        ->add(new RequireRoleMiddleware(['admin', 'praticien']))
        ->add(AuthenticatedMiddleware::class);
    return $app;
};
