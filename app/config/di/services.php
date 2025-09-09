<?php

use Psr\Container\ContainerInterface;
use toubilib\core\application\ports\PraticienRepositoryInterface;
use toubilib\infra\repositories\PDOPraticienRepository;
use toubilib\core\application\usecases\ServicePraticienInterface;
use toubilib\core\application\usecases\ServicePraticien;

return [
    // PDO connection factory
    PDO::class => function (ContainerInterface $c): PDO {
        $cfg = $c->get('db.prat');
        $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s', $cfg['host'], $cfg['port'], $cfg['name']);
        return new PDO($dsn, $cfg['user'], $cfg['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    },

    // Repository factory
    PraticienRepositoryInterface::class => function (ContainerInterface $c): PraticienRepositoryInterface {
        return new PDOPraticienRepository($c->get(PDO::class));
    },

    // Service factory
    ServicePraticienInterface::class => function (ContainerInterface $c): ServicePraticienInterface {
        return new ServicePraticien($c->get(PraticienRepositoryInterface::class));
    },
];

