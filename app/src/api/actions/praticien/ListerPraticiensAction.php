<?php
declare(strict_types=1);

namespace toubilib\api\actions\praticien;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpInternalServerErrorException;
use Throwable;
use toubilib\api\actions\AbstractAction;
use toubilib\core\application\exceptions\ApplicationException;
use toubilib\core\application\usecases\ServicePraticienInterface;

class ListerPraticiensAction extends AbstractAction
{
    private ServicePraticienInterface $service;

    public function __construct(ServicePraticienInterface $service)
    {
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        try {
            $dtos = $this->service->listerPraticiens();
            $resources = array_map(fn($dto) => $this->praticienResource($request, $dto), $dtos);
            $payload = [
                'data' => $resources,
                '_links' => $this->collectionLinks((string)$request->getUri()),
            ];
            return $this->respondWithJson($response, $payload);
        } catch (ApplicationException $exception) {
            throw new HttpBadRequestException($request, $exception->getMessage(), $exception);
        } catch (Throwable $exception) {
            throw new HttpInternalServerErrorException($request, 'Une erreur interne est survenue.', $exception);
        }
    }
}
