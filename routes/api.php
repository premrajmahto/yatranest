<?php

use App\Controllers\Front\BookingController;
use App\Controllers\Front\PaymentController;

/** @var \App\Core\Router $router */

// Coupon validation endpoint
$router->post('/api/coupon/validate', [BookingController::class, 'validateCoupon']);

// Razorpay Webhook endpoint
$router->post('/api/webhook/razorpay', [PaymentController::class, 'webhook']);
