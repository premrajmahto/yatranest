<?php

return [
    'name' => $_ENV['APP_NAME'] ?? 'YatraNest',
    'env' => $_ENV['APP_ENV'] ?? 'development',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN),
    'url' => rtrim($_ENV['APP_URL'] ?? 'http://localhost:8000', '/'),
    'secret' => $_ENV['APP_SECRET'] ?? 'yatranest_secret_key_default',
    'session_lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 7200),
    'currency' => $_ENV['DEFAULT_CURRENCY'] ?? 'INR',
    'currency_symbol' => $_ENV['CURRENCY_SYMBOL'] ?? '₹',
    'hold_expiration_minutes' => (int)($_ENV['HOLD_EXPIRATION_MINUTES'] ?? 15),
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'Asia/Kolkata',
];
