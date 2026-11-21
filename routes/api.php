<?php

use App\Controllers\Front\BookingController;
use App\Controllers\Front\ContactController;
use App\Controllers\Front\PaymentController;

/** @var \App\Core\Router $router */

// Travel Plan Request endpoint
$router->post('/api/travel-plan/request', [ContactController::class, 'requestPlan']);

// Coupon validation endpoint
$router->post('/api/coupon/validate', [BookingController::class, 'validateCoupon']);

// Razorpay Webhook endpoint
$router->post('/api/webhook/razorpay', [PaymentController::class, 'webhook']);

