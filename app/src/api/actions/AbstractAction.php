<?php
declare(strict_types=1);

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\api\middlewares\AuthenticatedMiddleware;
use toubilib\core\application\dto\PraticienDTO;
use toubilib\core\application\dto\RdvDTO;
use toubilib\core\application\dto\UserDTO;
use toubilib\core\domain\entities\rdv\Rdv;
use toubilib\core\domain\entities\user\UserRole;

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
            '_links' => $this->rdvLinks($request, $dto),
        ];
    }

    protected function praticienResource(Request $request, PraticienDTO $dto): array
    {
        $data = $dto->jsonSerialize();
        $id = $data['id'];
        unset($data['id']);

        /** @var UserDTO|null $user */
        $user = $request->getAttribute(AuthenticatedMiddleware::ATTRIBUTE_USER);

        $links = [
            'self' => ['href' => '/praticiens/' . $id, 'method' => 'GET'],
            'rdv_occupes' => ['href' => '/praticiens/' . $id . '/rdv/occupes', 'method' => 'GET'],
        ];

        if ($this->userHasAnyRole($user, ['admin', 'praticien'])) {
            $links['agenda'] = ['href' => '/praticiens/' . $id . '/agenda', 'method' => 'GET'];
            $links['creer_rdv'] = ['href' => '/rdv', 'method' => 'POST'];
        }

        return [
            'id' => $id,
            'type' => 'praticien',
            'attributes' => $data,
            '_links' => $links,
        ];
    }

    protected function rdvLinks(Request $request, RdvDTO $dto): array
    {
        $links = [
            'self' => ['href' => '/rdv/' . $dto->id, 'method' => 'GET'],
            'praticien' => ['href' => '/praticiens/' . $dto->praticien_id, 'method' => 'GET'],
        ];

        /** @var UserDTO|null $user */
        $user = $request->getAttribute(AuthenticatedMiddleware::ATTRIBUTE_USER);

        $status = $dto->status ?? Rdv::STATUS_SCHEDULED;
        if ($this->userHasAnyRole($user, ['admin', 'praticien'])) {
            $links['agenda'] = ['href' => '/praticiens/' . $dto->praticien_id . '/agenda', 'method' => 'GET'];
            if ($status !== Rdv::STATUS_CANCELLED) {
                $links['annuler'] = ['href' => '/rdv/' . $dto->id, 'method' => 'DELETE'];
            }
            if ($status !== Rdv::STATUS_COMPLETED && $status !== Rdv::STATUS_CANCELLED) {
                $links['honorer'] = [
                    'href' => '/rdv/' . $dto->id,
                    'method' => 'PATCH',
                    'payload' => ['status' => 'honore'],
                ];
            }
            if ($status !== Rdv::STATUS_NO_SHOW && $status !== Rdv::STATUS_CANCELLED) {
                $links['absent'] = [
                    'href' => '/rdv/' . $dto->id,
                    'method' => 'PATCH',
                    'payload' => ['status' => 'absent'],
                ];
            }
        }

        return $links;
    }

    protected function collectionLinks(string $href): array
    {
        return [
            'self' => ['href' => $href, 'method' => 'GET'],
        ];
    }

    protected function userHasAnyRole(?UserDTO $user, array $roles): bool
    {
        if ($user === null) {
            return false;
        }
        $roleName = strtolower(UserRole::toString($user->role));
        $allowed = array_map('strtolower', $roles);
        return in_array($roleName, $allowed, true);
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
