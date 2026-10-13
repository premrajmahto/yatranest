<?php

return [
    'key_id' => $_ENV['RAZORPAY_KEY_ID'] ?? '',
    'key_secret' => $_ENV['RAZORPAY_KEY_SECRET'] ?? '',
    'webhook_secret' => $_ENV['RAZORPAY_WEBHOOK_SECRET'] ?? '',
    'mode' => $_ENV['RAZORPAY_MODE'] ?? 'test',
];
