<?php

return [
    'host' => $_ENV['MAIL_HOST'] ?? 'smtp.mailtrap.io',
    'port' => (int)($_ENV['MAIL_PORT'] ?? 2525),
    'username' => $_ENV['MAIL_USERNAME'] ?? '',
    'password' => $_ENV['MAIL_PASSWORD'] ?? '',
    'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
    'from_address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'no-reply@yatranest.com',
    'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'YatraNest Holidays',
];
