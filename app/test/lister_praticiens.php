<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Initialisation de l'application Slim et du conteneur
$app = require_once __DIR__ . '/../config/bootstrap.php';
$container = $app->getContainer();

/** @var \toubilib\core\application\usecases\ServicePraticienInterface $service */
$service = $container->get(\toubilib\core\application\usecases\ServicePraticienInterface::class);

$dtos = $service->listerPraticiens();
echo json_encode($dtos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
