<?php


namespace toubilib\core\application\middleware;

use toubilib\core\application\usecases\ServiceAuthInterface;
use toubilib\core\domain\exceptions\InvalidCredentialsException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthMiddleware
{
    private ServiceAuthInterface $authService;

    public function __construct(ServiceAuthInterface $authService)
    {
        $this->authService = $authService;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $response->withStatus(401)->withJson(['error' => 'Authorization token is required']);
        }

        $token = $matches[1];

        try {
            $user = $this->authService->verifyJwtToken($token);

            // Inject user profile into the request
            $request = $request->withAttribute('user', $user);

            return $next($request, $response);
        } catch (InvalidCredentialsException $e) {
            return $response->withStatus(401)->withJson(['error' => 'Invalid or expired token']);
        }
    }
}