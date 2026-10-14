<?php

use App\Controllers\Admin\AdminAuthController;
use App\Controllers\Admin\AdminDashboardController;
use App\Controllers\Admin\AuditLogController;
use App\Controllers\Admin\BookingManagementController;
use App\Controllers\Admin\ContentManagementController;
use App\Controllers\Admin\CouponManagementController;
use App\Controllers\Admin\CustomerManagementController;
use App\Controllers\Admin\DepartureManagementController;
use App\Controllers\Admin\DestinationManagementController;
use App\Controllers\Admin\PackageManagementController;
use App\Controllers\Admin\PaymentManagementController;
use App\Controllers\Admin\SettingsController;
use App\Controllers\Front\AuthController;
use App\Controllers\Front\BookingController;
use App\Controllers\Front\ContactController;
use App\Controllers\Front\CustomerDashboardController;
use App\Controllers\Front\DestinationController;
use App\Controllers\Front\HomeController;
use App\Controllers\Front\InstallController;
use App\Controllers\Front\PaymentController;
use App\Controllers\Front\ReviewController;
use App\Controllers\Front\StaticPageController;
use App\Controllers\Front\TourPackageController;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;

/** @var \App\Core\Router $router */

// Public Pages
$router->get('/', [HomeController::class, 'index']);
$router->post('/newsletter', [HomeController::class, 'newsletter'], [CsrfMiddleware::class]);
$router->get('/destinations', [DestinationController::class, 'index']);
$router->get('/destinations/{slug}', [DestinationController::class, 'detail']);
$router->get('/fix-destinations', [DestinationController::class, 'fixDestinations']);
$router->get('/packages', [TourPackageController::class, 'index']);
$router->get('/packages/{slug}', [TourPackageController::class, 'detail']);
$router->get('/hotels', [StaticPageController::class, 'hotels']);
$router->get('/about', [StaticPageController::class, 'about']);
$router->get('/faq', [StaticPageController::class, 'faq']);
$router->get('/privacy', [StaticPageController::class, 'privacy']);
$router->get('/terms', [StaticPageController::class, 'terms']);
$router->get('/cancellation-policy', [StaticPageController::class, 'cancellation']);
$router->get('/contact', [ContactController::class, 'show']);
$router->post('/contact/submit', [ContactController::class, 'submit'], [CsrfMiddleware::class]);
$router->get('/install', [InstallController::class, 'index']);
$router->post('/install', [InstallController::class, 'setup']);

// Customer Authentication
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login'], [CsrfMiddleware::class]);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register'], [CsrfMiddleware::class]);
$router->post('/logout', [AuthController::class, 'logout'], [CsrfMiddleware::class]);
$router->get('/forgot-password', [AuthController::class, 'showForgotPassword']);
$router->post('/forgot-password', [AuthController::class, 'forgotPassword'], [CsrfMiddleware::class]);
$router->get('/reset-password', [AuthController::class, 'showResetPassword']);
$router->post('/reset-password', [AuthController::class, 'resetPassword'], [CsrfMiddleware::class]);

// Tour Booking Lifecycle
$router->get('/booking/checkout', [BookingController::class, 'checkout']);
$router->post('/booking/process', [BookingController::class, 'process'], [CsrfMiddleware::class]);
$router->get('/booking/confirmation/{reference}', [BookingController::class, 'confirmation']);
$router->get('/booking/invoice/{reference}', [BookingController::class, 'invoice']);
$router->post('/booking/cancel', [BookingController::class, 'cancel'], [CsrfMiddleware::class, AuthMiddleware::class]);

// Razorpay Payment Gateway Verification
$router->post('/payment/verify', [PaymentController::class, 'verify'], [CsrfMiddleware::class]);

// Customer Dashboard
$router->get('/dashboard', [CustomerDashboardController::class, 'dashboard'], [AuthMiddleware::class]);
$router->get('/profile', [CustomerDashboardController::class, 'profile'], [AuthMiddleware::class]);
$router->post('/profile/update', [CustomerDashboardController::class, 'updateProfile'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->post('/profile/password', [CustomerDashboardController::class, 'updatePassword'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->get('/my-bookings', [CustomerDashboardController::class, 'bookings'], [AuthMiddleware::class]);
$router->get('/my-bookings/{reference}', [CustomerDashboardController::class, 'bookingDetail'], [AuthMiddleware::class]);
$router->post('/reviews/submit', [ReviewController::class, 'submit'], [CsrfMiddleware::class, AuthMiddleware::class]);

// Admin Authentication
$router->get('/admin/login', [AdminAuthController::class, 'showLogin']);
$router->post('/admin/login', [AdminAuthController::class, 'login'], [CsrfMiddleware::class]);

// Admin Dashboard & Management
$router->get('/admin', [AdminDashboardController::class, 'index'], [AdminMiddleware::class]);

// Admin Tour Packages
$router->get('/admin/packages', [PackageManagementController::class, 'index'], [AdminMiddleware::class]);
$router->get('/admin/packages/create', [PackageManagementController::class, 'create'], [AdminMiddleware::class]);
$router->post('/admin/packages/store', [PackageManagementController::class, 'store'], [CsrfMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/packages/edit/{id}', [PackageManagementController::class, 'edit'], [AdminMiddleware::class]);
$router->post('/admin/packages/update/{id}', [PackageManagementController::class, 'update'], [CsrfMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/packages/delete/{id}', [PackageManagementController::class, 'delete'], [CsrfMiddleware::class, AdminMiddleware::class]);

// Admin Destinations
$router->get('/admin/destinations', [DestinationManagementController::class, 'index'], [AdminMiddleware::class]);
$router->get('/admin/destinations/create', [DestinationManagementController::class, 'create'], [AdminMiddleware::class]);
$router->post('/admin/destinations/store', [DestinationManagementController::class, 'store'], [CsrfMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/destinations/edit/{id}', [DestinationManagementController::class, 'edit'], [AdminMiddleware::class]);
$router->post('/admin/destinations/update/{id}', [DestinationManagementController::class, 'update'], [CsrfMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/destinations/delete/{id}', [DestinationManagementController::class, 'delete'], [CsrfMiddleware::class, AdminMiddleware::class]);

// Admin Departures & Seats
$router->get('/admin/departures', [DepartureManagementController::class, 'index'], [AdminMiddleware::class]);
$router->get('/admin/departures/create', [DepartureManagementController::class, 'create'], [AdminMiddleware::class]);
$router->post('/admin/departures/store', [DepartureManagementController::class, 'store'], [CsrfMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/departures/edit/{id}', [DepartureManagementController::class, 'edit'], [AdminMiddleware::class]);
$router->post('/admin/departures/update/{id}', [DepartureManagementController::class, 'update'], [CsrfMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/departures/delete/{id}', [DepartureManagementController::class, 'delete'], [CsrfMiddleware::class, AdminMiddleware::class]);

// Admin Bookings
$router->get('/admin/bookings/export', [BookingManagementController::class, 'exportCsv'], [AdminMiddleware::class]);
$router->get('/admin/bookings', [BookingManagementController::class, 'index'], [AdminMiddleware::class]);
$router->get('/admin/bookings/{id}', [BookingManagementController::class, 'show'], [AdminMiddleware::class]);
$router->post('/admin/bookings/status/{id}', [BookingManagementController::class, 'updateStatus'], [CsrfMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/bookings/notes/{id}', [BookingManagementController::class, 'updateNotes'], [CsrfMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/bookings/refund/{id}', [BookingManagementController::class, 'processRefund'], [CsrfMiddleware::class, AdminMiddleware::class]);

// Admin Payments
$router->get('/admin/payments', [PaymentManagementController::class, 'index'], [AdminMiddleware::class]);

// Admin Coupons
$router->get('/admin/coupons', [CouponManagementController::class, 'index'], [AdminMiddleware::class]);
$router->get('/admin/coupons/create', [CouponManagementController::class, 'create'], [AdminMiddleware::class]);
$router->post('/admin/coupons/store', [CouponManagementController::class, 'store'], [CsrfMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/coupons/edit/{id}', [CouponManagementController::class, 'edit'], [AdminMiddleware::class]);
$router->post('/admin/coupons/update/{id}', [CouponManagementController::class, 'update'], [CsrfMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/coupons/delete/{id}', [CouponManagementController::class, 'delete'], [CsrfMiddleware::class, AdminMiddleware::class]);

// Admin Customers
$router->get('/admin/customers', [CustomerManagementController::class, 'index'], [AdminMiddleware::class]);
$router->post('/admin/customers/toggle-status/{id}', [CustomerManagementController::class, 'toggleStatus'], [CsrfMiddleware::class, AdminMiddleware::class]);

// Admin Content
$router->get('/admin/content/messages', [ContentManagementController::class, 'messages'], [AdminMiddleware::class]);
$router->post('/admin/content/messages/status/{id}', [ContentManagementController::class, 'updateMessageStatus'], [CsrfMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/content/reviews', [ContentManagementController::class, 'reviews'], [AdminMiddleware::class]);
$router->post('/admin/content/reviews/status/{id}', [ContentManagementController::class, 'updateReviewStatus'], [CsrfMiddleware::class, AdminMiddleware::class]);

// Admin Settings & Audit
$router->get('/admin/settings', [SettingsController::class, 'index'], [AdminMiddleware::class]);
$router->post('/admin/settings/update', [SettingsController::class, 'update'], [CsrfMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/audit', [AuditLogController::class, 'index'], [AdminMiddleware::class]);
