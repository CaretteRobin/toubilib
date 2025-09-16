<?php
declare(strict_types=1);

namespace toubilib\api\actions\praticien;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use toubilib\core\application\usecases\ServiceRDVInterface;

class ListerCreneauxOccupesAction
{
    private ServiceRDVInterface $service;

    public function __construct(ServiceRDVInterface $service)
    {
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'] ?? '';
        $query = $request->getQueryParams();
        $de = $query['de'] ?? null;
        $a = $query['a'] ?? null;

        $dateRule = v::date('Y-m-d');
        if (!$de || !$a || !$dateRule->validate($de) || !$dateRule->validate($a)) {
            $response->getBody()->write(json_encode(['error' => 'Paramètres de et a requis au format Y-m-d']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        $start = $de . ' 00:00:00';
        $end = $a . ' 23:59:59';

        $slots = $this->service->listerCreneauxOccupes($id, $start, $end);
        $payload = json_encode($slots, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}

