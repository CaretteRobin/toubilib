<?php

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Psr\Container\ContainerInterface;

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

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware($container->get('displayErrorDetails'), false, false)
    ->getDefaultErrorHandler()
    ->forceContentType('application/json');

$app = (require_once __DIR__ . '/../src/api/routes.php')($app);

return $app;
