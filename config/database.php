<?php

$connection = env('DB_CONNECTION', 'mysql');

return [
    'connection' => $connection,
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => (int)env('DB_PORT', 3306),
    'database' => env('DB_NAME', env('DB_DATABASE', 'yatranest_db')),
    'username' => env('DB_USER', 'root'),
    'password' => env('DB_PASS', ''),
    'charset' => 'utf8mb4',
    'options' => [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES => false,
    ],
];
