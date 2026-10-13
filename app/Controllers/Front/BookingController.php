<?php

namespace App\Controllers\Front;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Booking;
use App\Models\Coupon;
use App\Models\TourDeparture;
use App\Models\TourPackage;
use App\Models\User;
use App\Services\BookingService;
use App\Services\InvoiceService;
use App\Services\MailService;
use App\Services\PricingService;
use App\Services\RazorpayService;

class BookingController extends Controller {
    public function checkout(Request $request, Response $response): void {
        $packageId = (int)$request->getQuery('package_id');
        $departureId = (int)$request->getQuery('departure_id');
        $adultCount = max(1, (int)$request->getQuery('adult_count', 1));
        $childCount = max(0, (int)$request->getQuery('child_count', 0));
        $totalSeats = $adultCount + $childCount;

        if (!$packageId || !$departureId) {
            $this->flash('error', 'Please choose a tour package and departure date.');
            $this->redirect('/packages');
        }

        $pkgModel = new TourPackage();
        $package = $pkgModel->find($packageId);
        $depModel = new TourDeparture();
        $departure = $depModel->find($departureId);

        if (!$package || !$departure || $departure['status'] !== 'open') {
            $this->flash('error', 'The selected departure is not available for booking.');
            $this->redirect('/packages');
        }

        $bookingService = new BookingService();
        $sessionId = session_id();

        // Create or refresh 15-minute seat reservation hold
        $holdSuccess = $bookingService->createOrRefreshHold($departureId, $sessionId, $totalSeats, auth()->id());
        if (!$holdSuccess) {
            $this->flash('error', 'Sorry, there are not enough remaining seats for your party on this departure.');
            $this->redirect('/packages/' . $package['slug']);
        }

        $adultPrice = $departure['adult_price'] !== null ? (float)$departure['adult_price'] : (float)$package['price_adult'];
        $childPrice = $departure['child_price'] !== null ? (float)$departure['child_price'] : (float)$package['price_child'];

        $pricingService = new PricingService();
        $pricing = $pricingService->calculate($adultPrice, $adultCount, $childPrice, $childCount, 0, 0, null, auth()->id());

        $this->render('front/booking/checkout', [
            'title' => 'Checkout - ' . $package['title'],
            'package' => $package,
            'departure' => $departure,
            'adult_count' => $adultCount,
            'child_count' => $childCount,
            'pricing' => $pricing,
            'user' => auth()->user()
        ]);
    }

    public function process(Request $request, Response $response): void {
        $data = $request->getAllPost();

        $rules = [
            'package_id' => 'required|integer',
            'departure_id' => 'required|integer',
            'adult_count' => 'required|integer|min:1',
            'child_count' => 'integer|min:0',
            'customer_name' => 'required|min:2',
            'customer_email' => 'required|email',
            'customer_phone' => 'required|phone',
            'payment_method' => 'required|in:razorpay,pay_later',
        ];

        $errors = $this->validate($data, $rules);
        if ($errors) {
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/packages');
        }

        // Validate traveler names
        $travelers = $data['travelers'] ?? [];
        $totalPax = (int)$data['adult_count'] + (int)$data['child_count'];

        if (count($travelers) < $totalPax) {
            $this->flash('error', 'Please provide names and details for all travelers.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/packages');
        }

        // Ensure user account exists or create guest account
        $userId = auth()->id();
        $userModel = new User();

        if (!$userId) {
            $email = strtolower(trim($data['customer_email']));
            $existing = $userModel->findByEmail($email);

            if ($existing) {
                $userId = (int)$existing['id'];
                // Log user in automatically
                auth()->login($existing);
            } else {
                $randomPassword = bin2hex(random_bytes(6));
                $newUserId = $userModel->create([
                    'name' => trim($data['customer_name']),
                    'email' => $email,
                    'phone' => trim($data['customer_phone']),
                    'password_hash' => password_hash($randomPassword, PASSWORD_DEFAULT),
                    'status' => 'active',
                ]);
                $userModel->assignRole($newUserId, 'customer');
                $newUser = $userModel->find($newUserId);
                auth()->login($newUser);
                $userId = $newUserId;

                // Send welcome account credentials email
                $mailService = new MailService();
                $mailService->send(
                    $email,
                    $data['customer_name'],
                    'Welcome to YatraNest - Your Account Details',
                    "<p>Hello {$data['customer_name']},</p><p>An account has been created for you to track your tour booking. Your login password is: <strong>$randomPassword</strong></p><p>Please change your password upon logging in.</p>"
                );
            }
        }

        $bookingService = new BookingService();
        $sessionId = session_id();

        $bookingPayload = [
            'package_id' => (int)$data['package_id'],
            'departure_id' => (int)$data['departure_id'],
            'customer_id' => $userId,
            'adult_count' => (int)$data['adult_count'],
            'child_count' => (int)$data['child_count'],
            'infant_count' => 0,
            'payment_method' => $data['payment_method'],
            'coupon_code' => !empty($data['coupon_code']) ? trim($data['coupon_code']) : null,
            'pickup_location' => $data['pickup_location'] ?? null,
            'special_requests' => $data['special_requests'] ?? null,
        ];

        $result = $bookingService->createBooking($bookingPayload, $travelers, $sessionId);

        if (!$result['success']) {
            $this->flash('error', $result['error'] ?? 'Booking could not be created.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/packages');
        }

        $bookingId = $result['booking_id'];
        $bookingRef = $result['booking_reference'];

        // If Pay Later
        if ($result['is_pay_later']) {
            $bookingModel = new Booking();
            $fullBooking = $bookingModel->getFullDetails($bookingId);

            $mailService = new MailService();
            $mailService->sendBookingConfirmation($fullBooking);

            $this->flash('success', 'Your booking has been received! Our travel team will confirm shortly.');
            $this->redirect('/booking/confirmation/' . $bookingRef);
        }

        // Pay Online with Razorpay: initiate Razorpay Order
        $razorpayService = new RazorpayService();
        $order = $razorpayService->createOrder($bookingId, $result['total_amount'], 'INR');

        if (!$order['success']) {
            $this->flash('error', 'Payment gateway initialization failed. Please try again.');
            $this->redirect('/booking/confirmation/' . $bookingRef);
        }

        $this->redirect('/booking/confirmation/' . $bookingRef);
    }

    public function confirmation(Request $request, Response $response, string $bookingReference): void {
        $bookingModel = new Booking();
        $booking = $bookingModel->getFullDetails($bookingReference);

        if (!$booking) {
            $response->setStatusCode(404);
            $this->render('front/errors/404', ['title' => 'Booking Not Found']);
            return;
        }

        // Authorize: customer must own the booking or be admin
        if (!auth()->isAdmin() && auth()->id() !== (int)$booking['customer_id']) {
            $this->flash('error', 'You are not authorized to view this booking.');
            $this->redirect('/login');
        }

        $razorpayOrder = null;
        $razorpayService = new RazorpayService();

        if ($booking['booking_status'] === 'awaiting_payment') {
            $razorpayOrder = $razorpayService->createOrder((int)$booking['id'], (float)$booking['total_amount'], 'INR');
        }

        $this->render('front/booking/confirmation', [
            'title' => 'Booking Confirmation - ' . $booking['booking_reference'],
            'booking' => $booking,
            'razorpay_order' => $razorpayOrder,
            'razorpay_key_id' => $razorpayService->getKeyId()
        ]);
    }

    public function invoice(Request $request, Response $response, string $bookingReference): void {
        $bookingModel = new Booking();
        $booking = $bookingModel->getFullDetails($bookingReference);

        if (!$booking) {
            $response->setStatusCode(404);
            die('Invoice not found');
        }

        // Customer access authorization
        if (!auth()->isAdmin() && auth()->id() !== (int)$booking['customer_id']) {
            $response->setStatusCode(403);
            die('Unauthorized access to invoice');
        }

        $invoiceService = new InvoiceService();
        $html = $invoiceService->renderInvoiceHtml($booking);
        echo $html;
        exit;
    }

    public function cancel(Request $request, Response $response): void {
        $bookingId = (int)$request->input('booking_id');
        $reason = trim((string)$request->input('reason', 'Customer requested cancellation'));

        $bookingModel = new Booking();
        $booking = $bookingModel->find($bookingId);

        if (!$booking || (!auth()->isAdmin() && auth()->id() !== (int)$booking['customer_id'])) {
            $this->flash('error', 'Unauthorized cancellation request.');
            $this->redirect('/my-bookings');
        }

        $bookingService = new BookingService();
        $cancelled = $bookingService->cancelBooking($bookingId, $reason, auth()->id());

        if ($cancelled) {
            $this->flash('success', 'Your booking cancellation request has been processed.');
        } else {
            $this->flash('error', 'Unable to cancel this booking.');
        }

        $this->redirect('/my-bookings/' . $booking['booking_reference']);
    }

    public function validateCoupon(Request $request, Response $response): void {
        $data = $request->getJson();
        $code = strtoupper(trim($data['code'] ?? ''));
        $packageId = (int)($data['package_id'] ?? 0);
        $departureId = (int)($data['departure_id'] ?? 0);
        $adultCount = max(1, (int)($data['adult_count'] ?? 1));
        $childCount = max(0, (int)($data['child_count'] ?? 0));

        $pkgModel = new TourPackage();
        $package = $pkgModel->find($packageId);
        $depModel = new TourDeparture();
        $dep = $depModel->find($departureId);

        if (!$package || !$dep) {
            $response->json(['valid' => false, 'message' => 'Tour information invalid.'], 400);
            return;
        }

        $adultPrice = $dep['adult_price'] !== null ? (float)$dep['adult_price'] : (float)$package['price_adult'];
        $childPrice = $dep['child_price'] !== null ? (float)$dep['child_price'] : (float)$package['price_child'];

        $pricingService = new PricingService();
        $pricing = $pricingService->calculate($adultPrice, $adultCount, $childPrice, $childCount, 0, 0, $code, auth()->id());

        if (!empty($pricing['coupon'])) {
            $response->json([
                'valid' => true,
                'discount' => $pricing['discount'],
                'message' => $pricing['coupon_message'] ?? 'Coupon applied successfully!',
                'pricing' => $pricing
            ]);
        } else {
            $response->json([
                'valid' => false,
                'message' => $pricing['coupon_message'] ?? 'Invalid or inapplicable coupon code.'
            ]);
        }
    }
}
