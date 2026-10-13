<?php

$defaultUrl = (!empty($_SERVER['HTTP_HOST']))
    ? ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']
    : 'http://localhost:8000';

return [
    'name' => env('APP_NAME', 'YatraNest'),
    'env' => env('APP_ENV', 'development'),
    'debug' => filter_var(env('APP_DEBUG', true), FILTER_VALIDATE_BOOLEAN),
    'url' => rtrim((string)env('APP_URL', $defaultUrl), '/'),
    'secret' => env('APP_SECRET', 'yatranest_secret_key_default'),
    'session_lifetime' => (int)env('SESSION_LIFETIME', 7200),
    'currency' => env('DEFAULT_CURRENCY', 'INR'),
    'currency_symbol' => env('CURRENCY_SYMBOL', '₹'),
    'hold_expiration_minutes' => (int)env('HOLD_EXPIRATION_MINUTES', 15),
    'timezone' => env('APP_TIMEZONE', 'Asia/Kolkata'),
];
