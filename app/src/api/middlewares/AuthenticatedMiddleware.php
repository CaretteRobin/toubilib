<?php
declare(strict_types=1);

namespace toubilib\api\middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Exception\HttpUnauthorizedException;
use toubilib\api\security\InvalidTokenException;
use toubilib\api\security\JwtManagerInterface;
use toubilib\core\application\usecases\ServiceAuthInterface;
use toubilib\core\domain\exceptions\UserNotFoundException;

class AuthenticatedMiddleware implements MiddlewareInterface
{
    public const ATTRIBUTE_USER = 'auth.user';

    private JwtManagerInterface $jwtManager;
    private ServiceAuthInterface $authService;

    public function __construct(JwtManagerInterface $jwtManager, ServiceAuthInterface $authService)
    {
        $this->jwtManager = $jwtManager;
        $this->authService = $authService;
    }

    public function process(Request $request, Handler $handler): Response
    {
        $authorization = $request->getHeaderLine('Authorization');
        if (!$authorization || !preg_match('/^Bearer\s+(.*)$/i', $authorization, $matches)) {
            throw new HttpUnauthorizedException($request, 'Jeton d\'authentification requis.');
        }

        $token = $matches[1];

        try {
            $payload = $this->jwtManager->decode($token, 'access');
            $user = $this->authService->getUserById($payload->subject);
        } catch (InvalidTokenException|UserNotFoundException $exception) {
            throw new HttpUnauthorizedException($request, 'Jeton invalide ou expiré.', $exception);
        }

        $request = $request->withAttribute(self::ATTRIBUTE_USER, $user);
        return $handler->handle($request);
    }
}
