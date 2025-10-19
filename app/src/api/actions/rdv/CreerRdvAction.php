<?php
declare(strict_types=1);

namespace toubilib\api\actions\rdv;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpNotFoundException;
use toubilib\api\exceptions\HttpUnprocessableEntityException;
use Throwable;
use toubilib\api\actions\AbstractAction;
use toubilib\api\middlewares\AuthenticatedMiddleware;
use toubilib\api\middlewares\CreateRendezVousMiddleware;
use toubilib\core\application\dto\InputRendezVousDTO;
use toubilib\core\application\exceptions\ApplicationException;
use toubilib\core\application\exceptions\ResourceNotFoundException;
use toubilib\core\application\exceptions\ValidationException;
use toubilib\core\application\usecases\AuthorizationServiceInterface;
use toubilib\core\application\usecases\ServiceRDVInterface;
use toubilib\core\application\dto\UserDTO;

class CreerRdvAction extends AbstractAction
{
    private ServiceRDVInterface $service;
    private AuthorizationServiceInterface $authorizationService;

    public function __construct(ServiceRDVInterface $service, AuthorizationServiceInterface $authorizationService)
    {
        $this->service = $service;
        $this->authorizationService = $authorizationService;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $payload = $request->getAttribute(CreateRendezVousMiddleware::ATTRIBUTE_PAYLOAD);
        if (!is_array($payload)) {
            throw new HttpBadRequestException($request, 'Données de rendez-vous manquantes.');
        }

        $dto = new InputRendezVousDTO(
            $payload['praticien_id'],
            $payload['patient_id'],
            $payload['date_heure_debut'],
            $payload['motif_id'],
            $payload['duree']
        );

        try {
            /** @var UserDTO|null $user */
            $user = $request->getAttribute(AuthenticatedMiddleware::ATTRIBUTE_USER);
            if ($user === null) {
                throw new HttpInternalServerErrorException($request, 'Utilisateur introuvable dans la requête.');
            }

            $this->authorizationService->assertCanCreateRdv($user, $dto->patientId);

            $rdv = $this->service->creerRendezVous($dto);
            $resource = ['data' => $this->rdvResource($request, $rdv)];
            $location = '/rdv/' . $rdv->id;
            return $this->respondWithJson($response, $resource, 201)
                ->withHeader('Location', $location);
        } catch (ValidationException $exception) {
            throw new HttpUnprocessableEntityException($request, $exception->getMessage(), $exception);
        } catch (ResourceNotFoundException $exception) {
            throw new HttpNotFoundException($request, $exception->getMessage(), $exception);
        } catch (ApplicationException $exception) {
            throw new HttpBadRequestException($request, $exception->getMessage(), $exception);
        } catch (Throwable $exception) {
            throw new HttpInternalServerErrorException($request, 'Une erreur interne est survenue.', $exception);
        }
    }
}
