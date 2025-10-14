<?php

use Psr\Container\ContainerInterface;
use toubilib\core\application\ports\PraticienRepositoryInterface;
use toubilib\infra\repositories\PDOPraticienRepository;
use toubilib\core\application\usecases\ServicePraticienInterface;
use toubilib\core\application\usecases\ServicePraticien;
use toubilib\core\application\ports\RdvRepositoryInterface;
use toubilib\infra\repositories\PDORdvRepository;
use toubilib\core\application\usecases\ServiceRDVInterface;
use toubilib\core\application\usecases\ServiceRDV;
use toubilib\core\application\ports\PatientRepositoryInterface;
use toubilib\infra\repositories\PDOPatientRepository;
use toubilib\core\application\ports\UserRepositoryInterface;
use toubilib\infra\repositories\PDOUserRepository;
use toubilib\core\application\usecases\ServiceAuthInterface;
use toubilib\core\application\usecases\ServiceAuth;
use toubilib\core\application\usecases\ServiceAuthorization;

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

    // RDV PDO connection
    'pdo.rdv' => function (ContainerInterface $c): PDO {
        $cfg = $c->get('db.rdv');
        $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s', $cfg['host'], $cfg['port'], $cfg['name']);
        return new PDO($dsn, $cfg['user'], $cfg['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    },

    // Patient PDO connection
    'pdo.pat' => function (ContainerInterface $c): PDO {
        $cfg = $c->get('db.pat');
        $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s', $cfg['host'], $cfg['port'], $cfg['name']);
        return new PDO($dsn, $cfg['user'], $cfg['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    },

    // RDV repository and service
    RdvRepositoryInterface::class => function (ContainerInterface $c): RdvRepositoryInterface {
        return new PDORdvRepository($c->get('pdo.rdv'));
    },
    ServiceRDVInterface::class => function (ContainerInterface $c): ServiceRDVInterface {
        return new ServiceRDV(
            $c->get(RdvRepositoryInterface::class),
            $c->get(PraticienRepositoryInterface::class),
            $c->get(PatientRepositoryInterface::class)
        );
    },

    PatientRepositoryInterface::class => function (ContainerInterface $c): PatientRepositoryInterface {
        return new PDOPatientRepository($c->get('pdo.pat'));
    },

    // Auth PDO connection
    'pdo.auth' => function (ContainerInterface $c): PDO {
        $cfg = $c->get('db.auth');
        $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s', $cfg['host'], $cfg['port'], $cfg['name']);
        return new PDO($dsn, $cfg['user'], $cfg['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    },

    // Auth repository and service
    UserRepositoryInterface::class => function (ContainerInterface $c): UserRepositoryInterface {
        return new PDOUserRepository($c->get('pdo.auth'));
    },
    
    ServiceAuthInterface::class => function (ContainerInterface $c): ServiceAuthInterface {
        return new ServiceAuth(
            $c->get(UserRepositoryInterface::class),
            $c->get('auth.jwt.secret'),
            $c->get('auth.jwt.expiration')
        );
    },

    // Authorization service
    ServiceAuthorization::class => function (ContainerInterface $c): ServiceAuthorization {
        return new ServiceAuthorization(
            $c->get(RdvRepositoryInterface::class)
        );
    },
];
