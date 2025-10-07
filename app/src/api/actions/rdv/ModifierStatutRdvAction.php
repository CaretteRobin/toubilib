<?php
declare(strict_types=1);

namespace toubilib\api\actions\rdv;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Ramsey\Uuid\Uuid;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpNotFoundException;
use Throwable;
use toubilib\api\actions\AbstractAction;
use toubilib\api\exceptions\HttpUnprocessableEntityException;
use toubilib\core\application\exceptions\ResourceNotFoundException;
use toubilib\core\application\exceptions\ValidationException;
use toubilib\core\application\usecases\ServiceRDVInterface;

class ModifierStatutRdvAction extends AbstractAction
{
    private ServiceRDVInterface $service;

    public function __construct(ServiceRDVInterface $service)
    {
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'] ?? '';
        if (!Uuid::isValid($id)) {
            throw new HttpBadRequestException($request, 'Identifiant de rendez-vous invalide.');
        }

        $payload = $request->getParsedBody();
        if (!is_array($payload) || !array_key_exists('status', $payload)) {
            throw new HttpBadRequestException($request, 'Payload JSON invalide: champ status requis.');
        }

        $status = strtolower(trim((string)$payload['status']));
        try {
            switch ($status) {
                case 'honore':
                case 'honoré':
                    $dto = $this->service->honorerRendezVous($id);
                    break;
                case 'absent':
                case 'non_honore':
                case 'non_honoré':
                    $dto = $this->service->marquerRendezVousAbsent($id);
                    break;
                default:
                    throw new HttpBadRequestException($request, 'Statut demandé invalide. Valeurs acceptées: honore, absent.');
            }
        } catch (ResourceNotFoundException $exception) {
            throw new HttpNotFoundException($request, $exception->getMessage(), $exception);
        } catch (ValidationException $exception) {
            throw new HttpUnprocessableEntityException($request, $exception->getMessage(), $exception);
        } catch (Throwable $exception) {
            throw new HttpInternalServerErrorException($request, 'Une erreur interne est survenue.', $exception);
        }

        $body = ['data' => $this->rdvResource($request, $dto)];
        return $this->respondWithJson($response, $body, 200);
    }
}
