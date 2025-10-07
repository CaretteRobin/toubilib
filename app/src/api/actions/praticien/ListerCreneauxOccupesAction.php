<?php
declare(strict_types=1);

namespace toubilib\api\actions\praticien;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Ramsey\Uuid\Uuid;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpInternalServerErrorException;
use Throwable;
use toubilib\api\actions\AbstractAction;
use toubilib\core\application\exceptions\ApplicationException;
use toubilib\core\application\usecases\ServiceRDVInterface;

class ListerCreneauxOccupesAction extends AbstractAction
{
    private ServiceRDVInterface $service;

    public function __construct(ServiceRDVInterface $service)
    {
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'] ?? '';
        $query = $request->getQueryParams();
        $de = $query['de'] ?? null;
        $a = $query['a'] ?? null;

        if (!Uuid::isValid($id)) {
            throw new HttpBadRequestException($request, 'Identifiant praticien invalide.');
        }

        $dateRule = v::date('Y-m-d');
        if (!$de || !$a || !$dateRule->validate($de) || !$dateRule->validate($a)) {
            throw new HttpBadRequestException($request, 'Paramètres de et a requis au format Y-m-d');
        }
        $start = $de . ' 00:00:00';
        $end = $a . ' 23:59:59';

        try {
            $slots = $this->service->listerCreneauxOccupes($id, $start, $end);
            $items = array_map(static function ($dto) {
                $attributes = $dto->jsonSerialize();
                return [
                    'type' => 'creneau_occupe',
                    'attributes' => $attributes,
                ];
            }, $slots);

            $self = (string)$request->getUri();
            $payload = [
                'data' => $items,
                '_links' => [
                    'self' => ['href' => $self, 'method' => 'GET'],
                    'praticien' => ['href' => '/praticiens/' . $id, 'method' => 'GET'],
                    'agenda' => ['href' => '/praticiens/' . $id . '/agenda?de=' . $de . '&a=' . $a, 'method' => 'GET'],
                ],
            ];

            return $this->respondWithJson($response, $payload);
        } catch (ApplicationException $exception) {
            throw new HttpBadRequestException($request, $exception->getMessage(), $exception);
        } catch (Throwable $exception) {
            throw new HttpInternalServerErrorException($request, 'Une erreur interne est survenue.', $exception);
        }
    }
}
