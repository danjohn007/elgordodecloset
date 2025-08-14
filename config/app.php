<?php

return [
    'name' => 'Restaurant Reservations',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'timezone' => 'America/Mexico_City',
    
    'session' => [
        'lifetime' => (int) ($_ENV['SESSION_LIFETIME'] ?? 3600),
        'name' => 'restaurant_session',
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
    ],
    
    'csrf' => [
        'secret' => $_ENV['CSRF_SECRET'] ?? 'default-csrf-secret-change-in-production',
    ],
    
    'mail' => [
        'host' => $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com',
        'port' => (int) ($_ENV['MAIL_PORT'] ?? 587),
        'username' => $_ENV['MAIL_USERNAME'] ?? '',
        'password' => $_ENV['MAIL_PASSWORD'] ?? '',
        'from_email' => $_ENV['MAIL_FROM_EMAIL'] ?? 'noreply@restaurant-reservations.com',
        'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'Restaurant Reservations',
    ],
    
    'rate_limit' => [
        'login' => (int) ($_ENV['RATE_LIMIT_LOGIN'] ?? 5),
        'reservation' => (int) ($_ENV['RATE_LIMIT_RESERVATION'] ?? 10),
    ],
];