<?php
declare(strict_types=1);

namespace toubilib\api\actions\auth;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpInternalServerErrorException;
use Throwable;
use toubilib\api\actions\AbstractAction;
use toubilib\api\middlewares\AuthenticatedMiddleware;
use toubilib\core\application\dto\UserDTO;
use toubilib\core\domain\entities\user\UserRole;

class MeAction extends AbstractAction
{
    public function __invoke(Request $request, Response $response): Response
    {
        try {
            /** @var UserDTO|null $user */
            $user = $request->getAttribute(AuthenticatedMiddleware::ATTRIBUTE_USER);
            if ($user === null) {
                // Ce middleware ne devrait jamais laisser passer une requête sans utilisateur
                throw new \RuntimeException('Utilisateur non disponible dans la requête.');
            }

            $resource = [
                'id' => $user->id,
                'type' => 'user',
                'attributes' => [
                    'email' => $user->email,
                    'role' => $user->role,
                    'role_name' => UserRole::toString($user->role),
                ],
                '_links' => [
                    'self' => ['href' => '/auth/me', 'method' => 'GET'],
                    'logout' => ['href' => '/auth/logout', 'method' => 'POST'],
                ],
            ];

            // Nettoyer les liens qui n'ont pas encore d'implémentation réelle
            unset($resource['_links']['logout']);

            return $this->respondWithJson($response, ['data' => $resource]);
        } catch (Throwable $exception) {
            throw new HttpInternalServerErrorException($request, 'Une erreur interne est survenue.', $exception);
        }
    }
}
