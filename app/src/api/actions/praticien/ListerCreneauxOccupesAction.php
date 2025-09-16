<?php
declare(strict_types=1);

namespace toubilib\api\actions\praticien;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
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

        $dateRule = v::date('Y-m-d');
        if (!$de || !$a || !$dateRule->validate($de) || !$dateRule->validate($a)) {
            return $this->respondWithError($response, 'Paramètres de et a requis au format Y-m-d', 400);
        }
        $start = $de . ' 00:00:00';
        $end = $a . ' 23:59:59';

        try {
            $slots = $this->service->listerCreneauxOccupes($id, $start, $end);
            return $this->respondWithJson($response, $slots);
        } catch (ApplicationException $exception) {
            return $this->respondWithError($response, $exception->getMessage(), 400);
        } catch (Throwable $exception) {
            return $this->respondWithError($response, 'Une erreur interne est survenue.', 500);
        }
    }
}
