<?php
declare(strict_types=1);

namespace toubilib\api\actions\rdv;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Ramsey\Uuid\Uuid;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpNotFoundException;
use toubilib\api\exceptions\HttpConflictException;
use Throwable;
use toubilib\api\actions\AbstractAction;
use toubilib\api\middlewares\AuthenticatedMiddleware;
use toubilib\core\application\exceptions\ApplicationException;
use toubilib\core\application\exceptions\ResourceNotFoundException;
use toubilib\core\application\exceptions\ValidationException;
use toubilib\core\application\usecases\AuthorizationServiceInterface;
use toubilib\core\application\usecases\ServiceRDVInterface;
use toubilib\core\application\dto\UserDTO;

class AnnulerRdvAction extends AbstractAction
{
    private ServiceRDVInterface $service;
    private AuthorizationServiceInterface $authorizationService;

    public function __construct(ServiceRDVInterface $service, AuthorizationServiceInterface $authorizationService)
    {
        $this->service = $service;
        $this->authorizationService = $authorizationService;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'] ?? '';

        if ($id === '') {
            throw new HttpBadRequestException($request, 'Identifiant de rendez-vous manquant.');
        }

        if (!Uuid::isValid($id)) {
            throw new HttpBadRequestException($request, 'Identifiant de rendez-vous invalide.');
        }

        try {
            /** @var UserDTO|null $user */
            $user = $request->getAttribute(AuthenticatedMiddleware::ATTRIBUTE_USER);
            if ($user === null) {
                throw new HttpInternalServerErrorException($request, 'Utilisateur introuvable dans la requête.');
            }

            $this->authorizationService->assertCanCancelRdv($user, $id);

            $dto = $this->service->annulerRendezVous($id);
            $payload = ['data' => $this->rdvResource($request, $dto)];
            return $this->respondWithJson($response, $payload, 200);
        } catch (ResourceNotFoundException $exception) {
            throw new HttpNotFoundException($request, $exception->getMessage(), $exception);
        } catch (ValidationException $exception) {
            throw new HttpConflictException($request, $exception->getMessage(), $exception);
        } catch (ApplicationException $exception) {
            throw new HttpBadRequestException($request, $exception->getMessage(), $exception);
        } catch (Throwable $exception) {
            throw new HttpInternalServerErrorException($request, 'Une erreur interne est survenue.', $exception);
        }
    }
}
