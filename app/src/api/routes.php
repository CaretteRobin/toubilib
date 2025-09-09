<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use toubilib\api\actions\praticien\ListerPraticiensAction;

return function( \Slim\App $app):\Slim\App {
    // GET /praticiens: retourne la liste complète des praticiens
    $app->get('/praticiens', ListerPraticiensAction::class);
    return $app;
};
