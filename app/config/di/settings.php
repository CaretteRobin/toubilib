<?php

return [
    // App settings
    'displayErrorDetails' => (bool)($_ENV['APP_DEBUG'] ?? true),

    // Database settings for practitioners DB
    'db.prat' => [
        'host' => $_ENV['prat.host'] ?? $_ENV['PRAT_DB_HOST'] ?? 'toubiprati.db',
        'port' => (int)($_ENV['prat.port'] ?? $_ENV['PRAT_DB_PORT'] ?? 5432),
        'name' => $_ENV['prat.database'] ?? $_ENV['PRAT_DB_NAME'] ?? 'toubiprat',
        'user' => $_ENV['prat.username'] ?? $_ENV['PRAT_DB_USER'] ?? 'toubiprat',
        'pass' => $_ENV['prat.password'] ?? $_ENV['PRAT_DB_PASS'] ?? 'toubiprat',
    ],
    // Database settings for RDV DB
    'db.rdv' => [
        'host' => $_ENV['rdv.host'] ?? 'toubirdv.db',
        'port' => (int)($_ENV['rdv.port'] ?? 5432),
        'name' => $_ENV['rdv.database'] ?? 'toubirdv',
        'user' => $_ENV['rdv.username'] ?? 'toubirdv',
        'pass' => $_ENV['rdv.password'] ?? 'toubirdv',
    ],
];
