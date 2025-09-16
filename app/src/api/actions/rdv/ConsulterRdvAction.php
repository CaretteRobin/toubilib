<?php
declare(strict_types=1);

namespace toubilib\api\actions\rdv;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
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

        try {
            $dto = $this->service->consulterRdv($id);
            return $this->respondWithJson($response, $dto);
        } catch (ResourceNotFoundException $exception) {
            return $this->respondWithError($response, $exception->getMessage(), 404);
        } catch (ApplicationException $exception) {
            return $this->respondWithError($response, $exception->getMessage(), 400);
        } catch (Throwable $exception) {
            return $this->respondWithError($response, 'Une erreur interne est survenue.', 500);
        }
    }
}
