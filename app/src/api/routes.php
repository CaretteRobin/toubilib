<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use toubilib\api\actions\praticien\ListerPraticiensAction;
use toubilib\api\actions\praticien\AfficherPraticienAction;
use toubilib\api\actions\praticien\ListerCreneauxOccupesAction;
use toubilib\api\actions\rdv\ConsulterRdvAction;

return function( \Slim\App $app):\Slim\App {
    // GET /praticiens: retourne la liste complète des praticiens
    $app->get('/praticiens', ListerPraticiensAction::class);
    // GET /praticiens/{id}: détail d'un praticien
    $app->get('/praticiens/{id}', AfficherPraticienAction::class);
    // GET /praticiens/{id}/rdv/occupes?de=YYYY-MM-DD&a=YYYY-MM-DD
    $app->get('/praticiens/{id}/rdv/occupes', ListerCreneauxOccupesAction::class);
    // GET /rdv/{id}: consulter un rendez-vous
    $app->get('/rdv/{id}', ConsulterRdvAction::class);
    return $app;
};
