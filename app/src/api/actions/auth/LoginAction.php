<?php
declare(strict_types=1);

namespace toubilib\api\actions\auth;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpUnauthorizedException;
use Throwable;
use toubilib\api\actions\AbstractAction;
use toubilib\core\application\dto\UserDTO;
use toubilib\core\application\exceptions\InvalidCredentialsException;
use toubilib\core\application\usecases\ServiceAuthInterface;
use toubilib\core\domain\entities\user\UserRole;

class LoginAction extends AbstractAction
{
    private ServiceAuthInterface $authService;
    private int $tokenTtl;

    public function __construct(ServiceAuthInterface $authService, int $tokenTtl)
    {
        $this->authService = $authService;
        $this->tokenTtl = $tokenTtl;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $payload = $request->getParsedBody();
        if (!is_array($payload)) {
            throw new HttpBadRequestException($request, 'Payload JSON invalide.');
        }

        $schema = v::arrayType()
            ->key('email', v::stringType()->email())
            ->key('password', v::stringType()->notEmpty());

        try {
            $schema->assert($payload);
        } catch (\Respect\Validation\Exceptions\NestedValidationException $exception) {
            throw new HttpBadRequestException($request, $exception->getFullMessage());
        }

        try {
            $user = $this->authService->authenticate($payload['email'], $payload['password']);
            $accessToken = $this->authService->generateJwtToken($user);
            $refreshToken = $this->authService->generateJwtToken($user, 7 * 24 * 3600); // 7 jours
        } catch (InvalidCredentialsException $exception) {
            throw new HttpUnauthorizedException($request, 'Identifiants incorrects.', $exception);
        } catch (Throwable $exception) {
            throw new HttpInternalServerErrorException($request, 'Une erreur interne est survenue.', $exception);
        }

        $data = $this->buildResponseData($request, $user, $accessToken, $refreshToken);
        return $this->respondWithJson($response, $data, 200)
            ->withHeader('Cache-Control', 'no-store')
            ->withHeader('Pragma', 'no-cache');
    }

    private function buildResponseData(Request $request, UserDTO $user, string $accessToken, string $refreshToken): array
    {
        $userResource = [
            'id' => $user->id,
            'type' => 'user',
            'attributes' => [
                'email' => $user->email,
                'role' => $user->role,
                'role_name' => UserRole::toString($user->role),
            ],
            '_links' => [
                'self' => ['href' => '/auth/me', 'method' => 'GET'],
            ],
        ];

        $links = [
            'self' => ['href' => (string)$request->getUri(), 'method' => 'POST'],
            'praticiens' => ['href' => '/praticiens', 'method' => 'GET'],
            'me' => ['href' => '/auth/me', 'method' => 'GET'],
        ];

        $roleName = UserRole::toString($user->role);
        if (in_array($roleName, ['praticien', 'admin'], true)) {
            $links['creer_rdv'] = ['href' => '/rdv', 'method' => 'POST'];
        }

        return [
            'data' => [
                'type' => 'auth',
                'attributes' => [
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken,
                    'token_type' => 'Bearer',
                    'expires_in' => $this->tokenTtl,
                ],
                'relationships' => [
                    'user' => [
                        'data' => ['id' => $user->id, 'type' => 'user'],
                    ],
                ],
                '_links' => $links,
            ],
            'included' => [$userResource],
        ];
    }
}
