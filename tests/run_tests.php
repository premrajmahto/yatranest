<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/app/Helpers/Helpers.php';

use App\Core\App;
use App\Core\Csrf;
use App\Core\Database;
use App\Models\Booking;
use App\Models\Coupon;
use App\Models\Payment;
use App\Models\TourDeparture;
use App\Models\TourPackage;
use App\Models\User;
use App\Services\BookingService;
use App\Services\InvoiceService;
use App\Services\PricingService;
use App\Services\RazorpayService;

$app = new App(dirname(__DIR__));
$db = Database::getConnection();

echo "========================================================\n";
echo " YATRANEST AUTOMATED TEST SUITE & QA VERIFICATION\n";
echo "========================================================\n\n";

$passed = 0;
$failed = 0;

function assertTest(string $description, bool $condition): void {
    global $passed, $failed;
    if ($condition) {
        echo " [PASS] $description\n";
        $passed++;
    } else {
        echo " [FAIL] $description\n";
        $failed++;
    }
}

// ---------------------------------------------------------
// 1. Database & Seeding Integrity
// ---------------------------------------------------------
echo "1. DATABASE INTEGRITY TESTS\n";
$userCount = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
assertTest("Users table populated (Found $userCount)", $userCount >= 3);

$pkgCount = (int)$db->query("SELECT COUNT(*) FROM tour_packages")->fetchColumn();
assertTest("Tour packages populated (Found $pkgCount)", $pkgCount >= 6);

$depCount = (int)$db->query("SELECT COUNT(*) FROM tour_departures")->fetchColumn();
assertTest("Tour departures populated (Found $depCount)", $depCount >= 10);

$couponCount = (int)$db->query("SELECT COUNT(*) FROM coupons")->fetchColumn();
assertTest("Coupons populated (Found $couponCount)", $couponCount >= 3);

// ---------------------------------------------------------
// 2. PricingService Calculations
// ---------------------------------------------------------
echo "\n2. PRICING SERVICE & TAX CALCULATIONS\n";
$pricingService = new PricingService();

// Test: 2 Adults @ 10,000, 1 Child @ 5,000, GST 5%
$calc = $pricingService->calculate(10000.0, 2, 5000.0, 1, 0.0, 0, null, null);
$expectedSubtotal = 25000.00;
$expectedTax = round(25000.00 * 0.05, 2); // 1250.00
$expectedTotal = 26250.00;
assertTest("Pricing subtotal calculation (2 Adults, 1 Child)", abs($calc['subtotal'] - $expectedSubtotal) < 0.01);
assertTest("Pricing GST 5% calculation", abs($calc['tax_amount'] - $expectedTax) < 0.01);
assertTest("Pricing grand total calculation", abs($calc['total'] - $expectedTotal) < 0.01);

// Test: WELCOME10 coupon (10% off, max 2500)
$calcCoupon = $pricingService->calculate(10000.0, 2, 5000.0, 1, 0.0, 0, 'WELCOME10', null);
assertTest("Coupon WELCOME10 applied discount of 2500.00", abs($calcCoupon['discount'] - 2500.00) < 0.01);
$expectedTaxable = 22500.00;
$expectedCouponTax = round(22500.00 * 0.05, 2); // 1125.00
assertTest("Pricing with coupon tax recalculation", abs($calcCoupon['tax_amount'] - $expectedCouponTax) < 0.01);

// ---------------------------------------------------------
// 3. Coupon Validation Rules & Limits
// ---------------------------------------------------------
echo "\n3. COUPON VALIDATION & BUSINESS RULES\n";
$couponModel = new Coupon();

$resValid = $couponModel->findValidCoupon('WELCOME10', 15000.0);
assertTest("Valid coupon recognized", $resValid['valid'] === true);

$resMinSpend = $couponModel->findValidCoupon('WELCOME10', 2000.0); // min spend is 10000
assertTest("Coupon rejects booking below minimum amount", $resMinSpend['valid'] === false);

$resInvalid = $couponModel->findValidCoupon('NONEXISTENT99', 50000.0);
assertTest("Non-existent coupon returns invalid", $resInvalid['valid'] === false);

// ---------------------------------------------------------
// 4. Seat Inventory & 15-Minute Temporary Hold
// ---------------------------------------------------------
echo "\n4. CONCURRENCY & SEAT INVENTORY PROTECTION\n";
$bookingService = new BookingService();
$depModel = new TourDeparture();

$firstDep = $depModel->where("status = 'open' AND available_seats > 5", [], 'id ASC', 1)[0];
$depId = (int)$firstDep['id'];
$initialSeats = (int)$firstDep['available_seats'];

// Check initial availability
$availableNow = $bookingService->getAvailableSeats($depId);
assertTest("Initial seat availability matches departure records", $availableNow === $initialSeats);

// Establish temporary 15-minute hold for session 'test_sess_A' for 4 seats
$sessionA = 'test_session_user_A';
$holdA = $bookingService->createOrRefreshHold($depId, $sessionA, 4);
assertTest("Session A successfully holds 4 seats", $holdA === true);

// Verify other shoppers (session B) now see 4 fewer seats!
$sessionB = 'test_session_user_B';
$availableForB = $bookingService->getAvailableSeats($depId, $sessionB);
assertTest("Session B sees inventory decremented by Session A's active hold", $availableForB === ($initialSeats - 4));

// Release Session A hold
$bookingService->releaseHold($sessionA);
$availableAfterRelease = $bookingService->getAvailableSeats($depId, $sessionB);
assertTest("Released hold restores seat inventory immediately", $availableAfterRelease === $initialSeats);

// ---------------------------------------------------------
// 5. Booking Creation & Overbooking Rejection
// ---------------------------------------------------------
echo "\n5. BOOKING LIFECYCLE & OVERBOOKING PROTECTION\n";
$userModel = new User();
$testCustomer = $userModel->findByEmail('customer@example.com');
$pkg = (new TourPackage())->find($firstDep['package_id']);

// Attempt booking with MORE seats than total available (should FAIL)
$excessData = [
    'package_id' => $pkg['id'],
    'departure_id' => $depId,
    'customer_id' => $testCustomer['id'],
    'adult_count' => $initialSeats + 10,
    'child_count' => 0,
    'infant_count' => 0,
    'payment_method' => 'pay_later',
];
$failResult = $bookingService->createBooking($excessData, [], 'test_sess_excess');
assertTest("Overbooking attempt exceeding capacity is rejected", $failResult['success'] === false);

// Valid booking creation
$validBookingData = [
    'package_id' => $pkg['id'],
    'departure_id' => $depId,
    'customer_id' => $testCustomer['id'],
    'adult_count' => 2,
    'child_count' => 1,
    'infant_count' => 0,
    'payment_method' => 'pay_later',
    'coupon_code' => 'WELCOME10',
    'pickup_location' => 'Airport Terminal 2',
    'special_requests' => 'Vegetarian meal preference'
];
$travelers = [
    ['full_name' => 'Rahul Sharma', 'age' => 32, 'gender' => 'male', 'traveler_type' => 'adult'],
    ['full_name' => 'Ananya Sharma', 'age' => 29, 'gender' => 'female', 'traveler_type' => 'adult'],
    ['full_name' => 'Aarav Sharma', 'age' => 6, 'gender' => 'male', 'traveler_type' => 'child'],
];

$bookingResult = $bookingService->createBooking($validBookingData, $travelers, 'test_sess_valid');
assertTest("Valid booking creation succeeded with unique reference", $bookingResult['success'] === true && !empty($bookingResult['booking_reference']));

$createdBookingId = $bookingResult['booking_id'];
$bookingModel = new Booking();
$createdBooking = $bookingModel->getFullDetails($createdBookingId);
assertTest("Passenger manifest saved correctly (3 travelers)", count($createdBooking['travelers']) === 3);

// Verify seats deducted from departure for pay_later booking
$depAfterBooking = $depModel->find($depId);
assertTest("Departure available seats deducted correctly for confirmed/pay-later reservation", (int)$depAfterBooking['available_seats'] === ($initialSeats - 3));

// ---------------------------------------------------------
// 6. Razorpay Cryptographic Signature Verification
// ---------------------------------------------------------
echo "\n6. RAZORPAY GATEWAY SIGNATURE VERIFICATION\n";
$razorpayService = new RazorpayService();
$secret = 'rzp_test_secretSample67890';
$orderId = 'order_test_987654321';
$paymentId = 'pay_test_123456789';

// Legitimate signature computed via HMAC SHA256
$validSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $secret);
$verifySuccess = $razorpayService->verifyPaymentSignature($orderId, $paymentId, $validSignature);
assertTest("Cryptographically valid Razorpay signature verified", $verifySuccess === true);

// Tampered signature attempt
$tamperedSignature = $validSignature . 'corrupt';
$verifyTampered = $razorpayService->verifyPaymentSignature($orderId, $paymentId, $tamperedSignature);
assertTest("Tampered signature is strictly rejected", $verifyTampered === false);

// Webhook raw body HMAC-SHA256 verification
$rawPayload = json_encode(['event' => 'payment.captured', 'id' => 'evt_12345']);
$webhookSecret = 'whsec_sampleRazorpayWebhookSecret';
$validWebhookSig = hash_hmac('sha256', $rawPayload, $webhookSecret);
$verifyWebhook = $razorpayService->verifyWebhookSignature($rawPayload, $validWebhookSig);
assertTest("Razorpay raw payload webhook signature verified", $verifyWebhook === true);

// ---------------------------------------------------------
// 7. Cancellation & Seat Restitution
// ---------------------------------------------------------
echo "\n7. CANCELLATION WORKFLOW & INVENTORY RESTITUTION\n";
$cancelled = $bookingService->cancelBooking($createdBookingId, 'Customer schedule change', $testCustomer['id']);
assertTest("Booking cancellation processed successfully", $cancelled === true);

// Check if seats were restored to departure
$depAfterCancel = $depModel->find($depId);
assertTest("Seats restored to inventory upon cancellation", (int)$depAfterCancel['available_seats'] === $initialSeats);

// ---------------------------------------------------------
// 8. Invoice Generation Audit
// ---------------------------------------------------------
echo "\n8. TAX INVOICE GENERATION\n";
$invoiceService = new InvoiceService();
$invoiceHtml = $invoiceService->renderInvoiceHtml($createdBooking);
assertTest("Invoice contains booking reference", str_contains($invoiceHtml, $createdBooking['booking_reference']));
assertTest("Invoice contains company GSTIN", str_contains($invoiceHtml, '29ABCDE1234F1Z5'));
assertTest("Invoice contains passenger names", str_contains($invoiceHtml, 'Rahul Sharma'));

// ---------------------------------------------------------
// 9. Security & Anti-XSS Escaping
// ---------------------------------------------------------
echo "\n9. SECURITY & SANITIZATION AUDITS\n";
$maliciousInput = '<script>alert("XSS")</script>&"\'';
$escaped = e($maliciousInput);
assertTest("XSS payload properly escaped", !str_contains($escaped, '<script>') && str_contains($escaped, '&lt;script&gt;'));

// CSRF token generation
$token = Csrf::token();
assertTest("CSRF token generated (64-char hex string)", strlen($token) === 64);
assertTest("CSRF validation matches true token", Csrf::validate($token) === true);
assertTest("CSRF validation fails on invalid token", Csrf::validate('invalid_token') === false);

echo "\n========================================================\n";
echo " TEST RESULTS: $passed PASSED, $failed FAILED\n";
echo "========================================================\n";

if ($failed === 0) {
    echo "🎉 ALL TESTS PASSED SUCCESSFULLY! APPLICATION IS READY.\n\n";
    exit(0);
} else {
    echo "❌ SOME TESTS FAILED. PLEASE REVIEW OUTPUT.\n\n";
    exit(1);
}
