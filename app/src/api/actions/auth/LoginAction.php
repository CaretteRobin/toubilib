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
use toubilib\api\dto\AuthTokensDTO;
use toubilib\api\provider\AuthProviderInterface;
use toubilib\core\domain\exceptions\InvalidCredentialsException;
use toubilib\core\domain\entities\user\UserRole;

class LoginAction extends AbstractAction
{
    private AuthProviderInterface $authProvider;

    public function __construct(AuthProviderInterface $authProvider)
    {
        $this->authProvider = $authProvider;
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
            $auth = $this->authProvider->signin($payload['email'], $payload['password']);
        } catch (InvalidCredentialsException $exception) {
            throw new HttpUnauthorizedException($request, 'Identifiants incorrects.', $exception);
        } catch (Throwable $exception) {
            throw new HttpInternalServerErrorException($request, 'Une erreur interne est survenue.', $exception);
        }

        $data = $this->buildResponseData($request, $auth);
        return $this->respondWithJson($response, $data, 200)
            ->withHeader('Cache-Control', 'no-store')
            ->withHeader('Pragma', 'no-cache');
    }

    private function buildResponseData(Request $request, AuthTokensDTO $auth): array
    {
        $userResource = [
            'id' => $auth->user->id,
            'type' => 'user',
            'attributes' => [
                'email' => $auth->user->email,
                'role' => $auth->user->role,
                'role_name' => UserRole::toString($auth->user->role),
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

        $roleName = UserRole::toString($auth->user->role);
        if (in_array($roleName, ['praticien', 'admin'], true)) {
            $links['creer_rdv'] = ['href' => '/rdv', 'method' => 'POST'];
        }

        return [
            'data' => [
                'type' => 'auth',
                'attributes' => [
                    'access_token' => $auth->accessToken,
                    'refresh_token' => $auth->refreshToken,
                    'token_type' => 'Bearer',
                    'expires_in' => $auth->expiresIn,
                    'refresh_expires_in' => $auth->refreshExpiresIn,
                ],
                'relationships' => [
                    'user' => [
                        'data' => ['id' => $auth->user->id, 'type' => 'user'],
                    ],
                ],
                '_links' => $links,
            ],
            'included' => [$userResource],
        ];
    }
}
