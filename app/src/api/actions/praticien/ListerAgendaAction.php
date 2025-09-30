<?php
declare(strict_types=1);

namespace toubilib\api\actions\praticien;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Ramsey\Uuid\Uuid;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpNotFoundException;
use Throwable;
use toubilib\api\actions\AbstractAction;
use toubilib\core\application\exceptions\ResourceNotFoundException;
use toubilib\core\application\exceptions\ValidationException;
use toubilib\core\application\usecases\ServiceRDVInterface;

class ListerAgendaAction extends AbstractAction
{
    private ServiceRDVInterface $service;

    public function __construct(ServiceRDVInterface $service)
    {
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'] ?? '';
        if ($id === '') {
            throw new HttpBadRequestException($request, 'Identifiant praticien manquant.');
        }

        if (!Uuid::isValid($id)) {
            throw new HttpBadRequestException($request, 'Identifiant praticien invalide.');
        }

        $query = $request->getQueryParams();
        $de = $query['de'] ?? null;
        $a = $query['a'] ?? null;

        $dateRule = v::date('Y-m-d');
        if ($de !== null && !$dateRule->validate($de)) {
            throw new HttpBadRequestException($request, 'Paramètre de invalide, attendu Y-m-d.');
        }
        if ($a !== null && !$dateRule->validate($a)) {
            throw new HttpBadRequestException($request, 'Paramètre a invalide, attendu Y-m-d.');
        }

        if ($de === null || $a === null) {
            $today = (new \DateTimeImmutable('now'))->format('Y-m-d');
            $de = $de ?? $today;
            $a = $a ?? $today;
        }

        $debut = $de . ' 00:00:00';
        $fin = $a . ' 23:59:59';

        try {
            $agenda = $this->service->listerAgenda($id, $debut, $fin);
            return $this->respondWithJson($response, $agenda);
        } catch (ResourceNotFoundException $exception) {
            throw new HttpNotFoundException($request, $exception->getMessage(), $exception);
        } catch (ValidationException $exception) {
            throw new HttpBadRequestException($request, $exception->getMessage(), $exception);
        } catch (Throwable $exception) {
            throw new HttpInternalServerErrorException($request, 'Une erreur interne est survenue.', $exception);
        }
    }
}
