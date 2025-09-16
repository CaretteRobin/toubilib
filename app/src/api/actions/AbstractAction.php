<?php
declare(strict_types=1);

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface as Response;

abstract class AbstractAction
{
    /**
     * Encode un payload en JSON et force le Content-Type.
     */
    protected function respondWithJson(Response $response, mixed $payload, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }

    /**
     * Retourne une réponse JSON d'erreur avec message.
     */
    protected function respondWithError(Response $response, string $message, int $status): Response
    {
        $error = ['error' => ['message' => $message]];
        return $this->respondWithJson($response, $error, $status);
    }
}
