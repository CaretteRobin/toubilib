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
    // Database settings for patients DB
    'db.pat' => [
        'host' => $_ENV['pat.host'] ?? 'toubipatient.db',
        'port' => (int)($_ENV['pat.port'] ?? 5432),
        'name' => $_ENV['pat.database'] ?? 'toubipat',
        'user' => $_ENV['pat.username'] ?? 'toubipat',
        'pass' => $_ENV['pat.password'] ?? 'toubipat',
    ],
    // Database settings for auth DB
    'db.auth' => [
        'host' => $_ENV['auth.host'] ?? 'toubiauth.db',
        'port' => (int)($_ENV['auth.port'] ?? 5432),
        'name' => $_ENV['auth.database'] ?? 'toubiauth',
        'user' => $_ENV['auth.username'] ?? 'toubiauth',
        'pass' => $_ENV['auth.password'] ?? 'toubiauth',
    ],
    // Auth settings
    'auth.jwt.secret' => $_ENV['AUTH_JWT_SECRET'] ?? 'your-super-secret-jwt-key-change-in-production',
    'auth.jwt.expiration' => (int)($_ENV['AUTH_JWT_EXPIRATION'] ?? 3600), // 1 hour by default
    'auth.jwt.refresh_expiration' => (int)($_ENV['AUTH_JWT_REFRESH_EXPIRATION'] ?? 604800), // 7 days by default
    'auth.jwt.issuer' => $_ENV['AUTH_JWT_ISSUER'] ?? 'toubilib',

    // CORS settings (aligné avec le cours 6)
    'cors' => [
        'allowed_origins' => array_map('trim', explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? '*')),
        'allowed_methods' => $_ENV['CORS_ALLOWED_METHODS'] ?? 'GET,POST,PATCH,DELETE,OPTIONS',
        'allowed_headers' => $_ENV['CORS_ALLOWED_HEADERS'] ?? 'Content-Type, Authorization',
        'exposed_headers' => $_ENV['CORS_EXPOSED_HEADERS'] ?? 'Location',
        'max_age' => (int)($_ENV['CORS_MAX_AGE'] ?? 86400),
        'supports_credentials' => filter_var($_ENV['CORS_SUPPORTS_CREDENTIALS'] ?? false, FILTER_VALIDATE_BOOLEAN),
    ],
];
