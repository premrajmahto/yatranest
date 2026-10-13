<?php

return [
    'host' => env('MAIL_HOST', 'smtp.mailtrap.io'),
    'port' => (int)env('MAIL_PORT', 2525),
    'username' => env('MAIL_USERNAME', ''),
    'password' => env('MAIL_PASSWORD', ''),
    'encryption' => env('MAIL_ENCRYPTION', 'tls'),
    'from_address' => env('MAIL_FROM_ADDRESS', 'no-reply@yatranest.com'),
    'from_name' => env('MAIL_FROM_NAME', 'YatraNest Holidays'),
];
