<?php

namespace toubilib\core\application\actions;

use toubilib\core\application\usecases\ServiceAuthInterface;
use toubilib\core\domain\exceptions\InvalidCredentialsException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SigninAction
{
    private ServiceAuthInterface $authService;

    public function __construct(ServiceAuthInterface $authService)
    {
        $this->authService = $authService;
    }

    public function handle(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = json_decode($request->getBody()->getContents(), true);

        // Validate input
        if (empty($data['email']) || empty($data['password'])) {
            return $response->withStatus(400)->withJson(['error' => 'Email and password are required']);
        }

        try {
            // Authenticate user
            $user = $this->authService->authenticate($data['email'], $data['password']);

            // Generate tokens
            $accessToken = $this->authService->generateJwtToken($user);

            return $response->withJson([
                'access_token' => $accessToken,
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
            ]);
        } catch (InvalidCredentialsException $e) {
            return $response->withStatus(401)->withJson(['error' => 'Invalid credentials']);
        }
    }
}