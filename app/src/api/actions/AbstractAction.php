<?php
declare(strict_types=1);

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\core\application\dto\RdvDTO;
use toubilib\core\application\dto\PraticienDTO;
use toubilib\core\domain\entities\rdv\Rdv;

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

    protected function rdvResource(Request $request, RdvDTO $dto): array
    {
        $attributes = $dto->jsonSerialize();
        unset($attributes['id']);
        $attributes['status_label'] = $this->statusLabel($dto->status ?? Rdv::STATUS_SCHEDULED);

        return [
            'id' => $dto->id,
            'type' => 'rdv',
            'attributes' => $attributes,
            '_links' => $this->rdvLinks($dto->id, $dto->praticien_id),
        ];
    }

    protected function praticienResource(Request $request, PraticienDTO $dto): array
    {
        $data = $dto->jsonSerialize();
        $id = $data['id'];
        unset($data['id']);

        return [
            'id' => $id,
            'type' => 'praticien',
            'attributes' => $data,
            '_links' => [
                'self' => ['href' => '/praticiens/' . $id, 'method' => 'GET'],
                'rdv_occupes' => ['href' => '/praticiens/' . $id . '/rdv/occupes', 'method' => 'GET'],
                'agenda' => ['href' => '/praticiens/' . $id . '/agenda', 'method' => 'GET'],
            ],
        ];
    }

    protected function rdvLinks(string $rdvId, string $praticienId): array
    {
        return [
            'self' => ['href' => '/rdv/' . $rdvId, 'method' => 'GET'],
            'praticien' => ['href' => '/praticiens/' . $praticienId, 'method' => 'GET'],
            'agenda' => ['href' => '/praticiens/' . $praticienId . '/agenda', 'method' => 'GET'],
            'annuler' => ['href' => '/rdv/' . $rdvId, 'method' => 'DELETE'],
            'honorer' => ['href' => '/rdv/' . $rdvId, 'method' => 'PATCH', 'payload' => ['status' => 'honore']],
            'absent' => ['href' => '/rdv/' . $rdvId, 'method' => 'PATCH', 'payload' => ['status' => 'absent']],
        ];
    }

    protected function collectionLinks(string $href): array
    {
        return [
            'self' => ['href' => $href, 'method' => 'GET'],
        ];
    }

    private function statusLabel(int $status): string
    {
        return match ($status) {
            Rdv::STATUS_SCHEDULED => 'planifie',
            Rdv::STATUS_CANCELLED => 'annule',
            Rdv::STATUS_COMPLETED => 'honore',
            Rdv::STATUS_NO_SHOW => 'non_honore',
            default => 'inconnu',
        };
    }
}
