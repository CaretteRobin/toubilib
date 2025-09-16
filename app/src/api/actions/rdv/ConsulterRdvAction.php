<?php
declare(strict_types=1);

namespace toubilib\api\actions\rdv;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\core\application\usecases\ServiceRDVInterface;

class ConsulterRdvAction
{
    private ServiceRDVInterface $service;

    public function __construct(ServiceRDVInterface $service)
    {
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'] ?? '';
        $dto = $this->service->consulterRdv($id);
        if ($dto === null) {
            $response->getBody()->write(json_encode(['error' => 'RDV non trouvé']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
        $payload = json_encode($dto, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}

