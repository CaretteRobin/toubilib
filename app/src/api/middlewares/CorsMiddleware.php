<?php
declare(strict_types=1);

namespace toubilib\api\middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response;

class CorsMiddleware implements MiddlewareInterface
{
    /** @var string[] */
    private array $allowedOrigins;
    private string $allowedMethods;
    private string $allowedHeaders;
    private string $exposedHeaders;
    private int $maxAge;
    private bool $supportsCredentials;

    /**
     * @param array{allowed_origins?:string[],allowed_methods?:string,allowed_headers?:string,exposed_headers?:string,max_age?:int,supports_credentials?:bool} $config
     */
    public function __construct(array $config = [])
    {
        $this->allowedOrigins = $config['allowed_origins'] ?? ['*'];
        $this->allowedMethods = $config['allowed_methods'] ?? 'GET,POST,PATCH,DELETE,OPTIONS';
        $this->allowedHeaders = $config['allowed_headers'] ?? 'Content-Type, Authorization';
        $this->exposedHeaders = $config['exposed_headers'] ?? 'Location';
        $this->maxAge = $config['max_age'] ?? 86400;
        $this->supportsCredentials = $config['supports_credentials'] ?? false;
    }

    public function process(Request $request, Handler $handler): ResponseInterface
    {
        if (strtoupper($request->getMethod()) === 'OPTIONS') {
            $response = new Response(204);
            return $this->applyHeaders($request, $response);
        }

        $response = $handler->handle($request);
        return $this->applyHeaders($request, $response);
    }

    public function handlePreflight(Request $request, ResponseInterface $response): ResponseInterface
    {
        return $this->applyHeaders($request, $response->withStatus(204));
    }

    private function applyHeaders(Request $request, ResponseInterface $response): ResponseInterface
    {
        $originHeader = $request->getHeaderLine('Origin');
        $origin = $originHeader !== '' ? $originHeader : null;
        $allowedOrigin = $this->resolveOrigin($origin);

        if ($allowedOrigin !== null) {
            $response = $response->withHeader('Access-Control-Allow-Origin', $allowedOrigin);
            if ($allowedOrigin !== '*') {
                $response = $response->withHeader('Vary', 'Origin');
            }
            if ($this->supportsCredentials && $allowedOrigin !== '*') {
                $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
            }
        }

        return $response
            ->withHeader('Access-Control-Allow-Methods', $this->allowedMethods)
            ->withHeader('Access-Control-Allow-Headers', $this->allowedHeaders)
            ->withHeader('Access-Control-Expose-Headers', $this->exposedHeaders)
            ->withHeader('Access-Control-Max-Age', (string)$this->maxAge);
    }

    private function resolveOrigin(?string $origin): ?string
    {
        $hasWildcard = in_array('*', $this->allowedOrigins, true);

        if ($origin === null) {
            return $hasWildcard ? '*' : null;
        }

        if ($hasWildcard) {
            return $this->supportsCredentials ? $origin : '*';
        }

        return in_array($origin, $this->allowedOrigins, true) ? $origin : null;
    }
}
