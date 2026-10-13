<?php

namespace App\Controllers\Front;

use App\Core\Controller;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Models\Booking;
use App\Services\MailService;
use App\Services\RazorpayService;

class PaymentController extends Controller {
    public function verify(Request $request, Response $response): void {
        $bookingId = (int)$request->input('booking_id');
        $orderId = trim((string)$request->input('razorpay_order_id'));
        $paymentId = trim((string)$request->input('razorpay_payment_id'));
        $signature = trim((string)$request->input('razorpay_signature'));

        $bookingModel = new Booking();
        $booking = $bookingModel->find($bookingId);

        if (!$booking) {
            $this->flash('error', 'Booking record not found for payment verification.');
            $this->redirect('/');
        }

        $razorpayService = new RazorpayService();

        // Check signature cryptographically
        $isValid = $razorpayService->verifyPaymentSignature($orderId, $paymentId, $signature);

        if (!$isValid) {
            Logger::security("Invalid Razorpay payment signature for Booking {$booking['booking_reference']}");
            $this->flash('error', 'Payment verification failed: Invalid cryptographic gateway signature.');
            $this->redirect('/booking/confirmation/' . $booking['booking_reference']);
        }

        // Record payment & confirm booking
        $result = $razorpayService->recordSuccessfulPayment($bookingId, $orderId, $paymentId, $signature, (float)$booking['total_amount']);

        if (!$result['success']) {
            $this->flash('error', $result['error'] ?? 'Payment recording error.');
            $this->redirect('/booking/confirmation/' . $booking['booking_reference']);
        }

        // Send booking confirmation email with invoice details
        $fullBooking = $bookingModel->getFullDetails($bookingId);
        $mailService = new MailService();
        $mailService->sendBookingConfirmation($fullBooking);

        $this->flash('success', 'Payment verified successfully! Your booking is now confirmed.');
        $this->redirect('/booking/confirmation/' . $booking['booking_reference']);
    }

    public function webhook(Request $request, Response $response): void {
        $rawBody = $request->getRawBody();
        $signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

        $razorpayService = new RazorpayService();
        $result = $razorpayService->processWebhook($rawBody, $signature);

        $response->setStatusCode($result['status']);
        $response->json(['message' => $result['message']], $result['status']);
    }
}
