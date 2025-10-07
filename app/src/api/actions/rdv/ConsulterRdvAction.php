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
use toubilib\core\application\exceptions\ApplicationException;
use toubilib\core\application\exceptions\ResourceNotFoundException;
use toubilib\core\application\usecases\ServiceRDVInterface;

class ConsulterRdvAction extends AbstractAction
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

        try {
            $dto = $this->service->consulterRdv($id);
            $payload = ['data' => $this->rdvResource($request, $dto)];
            return $this->respondWithJson($response, $payload);
        } catch (ResourceNotFoundException $exception) {
            throw new HttpNotFoundException($request, $exception->getMessage(), $exception);
        } catch (ApplicationException $exception) {
            throw new HttpBadRequestException($request, $exception->getMessage(), $exception);
        } catch (Throwable $exception) {
            throw new HttpInternalServerErrorException($request, $exception->getMessage(), $exception);
        }
    }
}
