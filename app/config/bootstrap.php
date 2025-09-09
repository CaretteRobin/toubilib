<?php

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;

// Load environment variables from app/config/.env
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Build PHP-DI container with core services
$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions([
    // App settings
    'displayErrorDetails' => fn() => (bool)($_ENV['APP_DEBUG'] ?? true),

    // PDO for practitioners database
    \PDO::class => function () {
        // Prefer keys from app/config/.env (.dist) then fallback to PRAT_DB_* if provided
        $host = $_ENV['prat.host'] ?? $_ENV['PRAT_DB_HOST'] ?? 'toubiprati.db';
        $port = (int)($_ENV['prat.port'] ?? $_ENV['PRAT_DB_PORT'] ?? 5432);
        $db   = $_ENV['prat.database'] ?? $_ENV['PRAT_DB_NAME'] ?? 'toubiprat';
        $user = $_ENV['prat.username'] ?? $_ENV['PRAT_DB_USER'] ?? 'toubiprat';
        $pass = $_ENV['prat.password'] ?? $_ENV['PRAT_DB_PASS'] ?? 'toubiprat';
        $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s', $host, $port, $db);
        $pdo = new \PDO($dsn, $user, $pass, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    },

    // Repository binding
    toubilib\core\application\ports\PraticienRepositoryInterface::class => DI\autowire(\toubilib\infra\repositories\PDOPraticienRepository::class),

    // Service binding
    toubilib\core\application\usecases\ServicePraticienInterface::class => DI\autowire(\toubilib\core\application\usecases\ServicePraticien::class),
]);

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
