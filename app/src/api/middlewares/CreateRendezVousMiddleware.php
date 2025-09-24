<?php
declare(strict_types=1);

namespace toubilib\api\middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator;
use toubilib\core\application\exceptions\ValidationException;

class CreateRendezVousMiddleware implements MiddlewareInterface
{
    public const ATTRIBUTE_PAYLOAD = 'rdv.payload';

    public function process(Request $request, Handler $handler): Response
    {
        $data = $request->getParsedBody();
        if (!is_array($data)) {
            throw new ValidationException('Corps de requête JSON invalide.');
        }

        $payload = $this->validate($data);
        $request = $request->withAttribute(self::ATTRIBUTE_PAYLOAD, $payload);

        return $handler->handle($request);
    }

    /**
     * @param array<string, mixed> $data
     * @return array{praticien_id: string, patient_id: string, motif_id: string, date_heure_debut: string, duree: int}
     */
    private function validate(array $data): array
    {
        $schema = Validator::arrayType()
            ->key('praticien_id', Validator::stringType()->notEmpty())
            ->key('patient_id', Validator::stringType()->notEmpty())
            ->key('motif_id', Validator::stringType()->notEmpty())
            ->key('date_heure_debut', Validator::date('Y-m-d H:i:s'))
            ->key('duree', Validator::intType()->positive());

        try {
            $schema->assert($data);
        } catch (NestedValidationException $exception) {
            throw new ValidationException($exception->getFullMessage(), previous: $exception);
        }

        return [
            'praticien_id' => trim((string)$data['praticien_id']),
            'patient_id' => trim((string)$data['patient_id']),
            'motif_id' => trim((string)$data['motif_id']),
            'date_heure_debut' => (string)$data['date_heure_debut'],
            'duree' => (int)$data['duree'],
        ];
    }
}
