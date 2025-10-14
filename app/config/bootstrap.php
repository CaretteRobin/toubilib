<?php

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

// Load environment variables from app/config/.env
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Build PHP-DI container with factories (no autowire)
$containerBuilder = new ContainerBuilder();
$containerBuilder->useAutowiring(false);

// Load DI definitions from three files: settings, services, api
$definitions = [];
foreach (['settings', 'services', 'api'] as $file) {
    $path = __DIR__ . '/di/' . $file . '.php';
    if (file_exists($path)) {
        /** @var array $defs */
        $defs = require $path;
        $definitions[] = $defs;
    }
}

foreach ($definitions as $defs) {
    $containerBuilder->addDefinitions($defs);
}

$container = $containerBuilder->build();
AppFactory::setContainer($container);
$app = AppFactory::create();

$app->options('/{routes:.+}', function (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET,POST,PATCH,DELETE,OPTIONS')
        ->withHeader('Access-Control-Expose-Headers', 'Location');
});

$app->add(function (ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET,POST,PATCH,DELETE,OPTIONS')
        ->withHeader('Access-Control-Expose-Headers', 'Location');
});

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware($container->get('displayErrorDetails'), false, false)
    ->getDefaultErrorHandler()
    ->forceContentType('application/json');

$app = (require_once __DIR__ . '/../src/api/routes.php')($app);

return $app;
