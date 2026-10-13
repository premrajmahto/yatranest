<?php

namespace App\Services;

use App\Core\Logger;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\PaymentWebhookEvent;
use App\Models\Refund;
use Razorpay\Api\Api;

class RazorpayService {
    private string $keyId;
    private string $keySecret;
    private string $webhookSecret;
    private string $mode;
    private ?Api $api = null;

    public function __construct() {
        $this->keyId = (string)config('razorpay.key_id', '');
        $this->keySecret = (string)config('razorpay.key_secret', '');
        $this->webhookSecret = (string)config('razorpay.webhook_secret', '');
        $this->mode = (string)config('razorpay.mode', 'test');

        if (!empty($this->keyId) && !empty($this->keySecret)) {
            try {
                $this->api = new Api($this->keyId, $this->keySecret);
            } catch (\Throwable $e) {
                Logger::error("Razorpay SDK initialization failed: " . $e->getMessage());
            }
        }
    }

    public function getKeyId(): string {
        return $this->keyId;
    }

    /**
     * Create a Razorpay Order on server.
     */
    public function createOrder(int $bookingId, float $amount, string $currency = 'INR'): array {
        $amountInPaise = (int)round($amount * 100);
        $receipt = 'RCPT-' . $bookingId . '-' . time();

        $orderData = [
            'receipt' => $receipt,
            'amount' => $amountInPaise,
            'currency' => $currency,
            'notes' => [
                'booking_id' => (string)$bookingId,
                'app' => 'YatraNest'
            ]
        ];

        // If credentials are live or test SDK succeeds
        if ($this->api !== null && !empty($this->keyId) && !str_starts_with($this->keyId, 'rzp_test_sample')) {
            try {
                $order = $this->api->order->create($orderData);
                return [
                    'success' => true,
                    'order_id' => $order['id'],
                    'amount' => $amountInPaise,
                    'currency' => $currency,
                    'receipt' => $receipt,
                ];
            } catch (\Throwable $e) {
                Logger::error("Razorpay API order create error: " . $e->getMessage());
                return ['success' => false, 'error' => $e->getMessage()];
            }
        }

        // Test/Sandbox development mode: generate a valid-format test order ID
        $mockOrderId = 'order_test_' . bin2hex(random_bytes(8));
        return [
            'success' => true,
            'order_id' => $mockOrderId,
            'amount' => $amountInPaise,
            'currency' => $currency,
            'receipt' => $receipt,
        ];
    }

    /**
     * Verify payment signature cryptographically:
     * generated_signature = hash_hmac('sha256', $orderId . '|' . $paymentId, $keySecret)
     */
    public function verifyPaymentSignature(string $orderId, string $paymentId, string $signature): bool {
        if (empty($orderId) || empty($paymentId) || empty($signature)) {
            return false;
        }

        $expected = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->keySecret);
        return hash_equals($expected, $signature);
    }

    /**
     * Process verified payment on the server.
     */
    public function recordSuccessfulPayment(int $bookingId, string $orderId, string $paymentId, string $signature, float $amount): array {
        $bookingModel = new Booking();
        $booking = $bookingModel->find($bookingId);

        if (!$booking) {
            return ['success' => false, 'error' => 'Booking not found.'];
        }

        // Verify amount
        $expectedAmount = (float)$booking['total_amount'];
        if (abs($expectedAmount - $amount) > 0.05) {
            Logger::error("Payment amount mismatch for booking {$booking['booking_reference']}. Expected: $expectedAmount, Paid: $amount");
            return ['success' => false, 'error' => 'Payment amount mismatch.'];
        }

        $paymentModel = new Payment();

        // Check for duplicate payment record
        $existing = $paymentModel->findBy('gateway_payment_id', $paymentId);
        if ($existing) {
            return ['success' => true, 'payment_id' => $existing['id'], 'duplicate' => true];
        }

        $txRef = 'TXN-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

        $newPaymentId = $paymentModel->create([
            'booking_id' => $bookingId,
            'transaction_reference' => $txRef,
            'gateway' => 'razorpay',
            'gateway_order_id' => $orderId,
            'gateway_payment_id' => $paymentId,
            'gateway_signature' => $signature,
            'amount' => $amount,
            'currency' => 'INR',
            'status' => 'paid',
            'gateway_response' => json_encode([
                'razorpay_order_id' => $orderId,
                'razorpay_payment_id' => $paymentId,
                'verified_at' => date('Y-m-d H:i:s'),
            ]),
        ]);

        // Confirm the booking
        $bookingService = new BookingService();
        $bookingService->confirmBooking($bookingId, $txRef, null, "Razorpay payment $paymentId verified successfully.");

        AuditLog::record('payment_completed', 'payment', $newPaymentId, null, [
            'booking_id' => $bookingId,
            'amount' => $amount,
            'gateway_payment_id' => $paymentId
        ]);

        return ['success' => true, 'payment_id' => $newPaymentId, 'transaction_reference' => $txRef];
    }

    /**
     * Verify webhook signature:
     * generated_signature = hash_hmac('sha256', $rawBody, $webhookSecret)
     */
    public function verifyWebhookSignature(string $rawBody, string $signature): bool {
        if (empty($rawBody) || empty($signature) || empty($this->webhookSecret)) {
            return false;
        }

        $expected = hash_hmac('sha256', $rawBody, $this->webhookSecret);
        return hash_equals($expected, $signature);
    }

    /**
     * Process Webhook Event idempotently.
     */
    public function processWebhook(string $rawBody, string $signature): array {
        if (!$this->verifyWebhookSignature($rawBody, $signature)) {
            Logger::security("Invalid Razorpay webhook signature attempt.");
            return ['status' => 400, 'message' => 'Invalid signature'];
        }

        $payload = json_decode($rawBody, true);
        if (!is_array($payload)) {
            return ['status' => 400, 'message' => 'Invalid JSON payload'];
        }

        $eventId = $payload['event_id'] ?? ($payload['id'] ?? null);
        $eventType = $payload['event'] ?? 'unknown';

        if (empty($eventId)) {
            return ['status' => 400, 'message' => 'Missing event ID'];
        }

        $eventModel = new PaymentWebhookEvent();

        // Idempotency check: if already processed, return 200 OK immediately
        if ($eventModel->isProcessed($eventId)) {
            return ['status' => 200, 'message' => 'Event already processed'];
        }

        // Record incoming event
        $eventModel->create([
            'event_id' => $eventId,
            'event_type' => $eventType,
            'payload' => $rawBody,
            'status' => 'processed',
        ]);

        // Process known event types
        if ($eventType === 'payment.captured') {
            $paymentData = $payload['payload']['payment']['entity'] ?? [];
            $orderId = $paymentData['order_id'] ?? '';
            $paymentId = $paymentData['id'] ?? '';
            $amountInPaise = $paymentData['amount'] ?? 0;
            $amount = $amountInPaise / 100;
            $bookingId = (int)($paymentData['notes']['booking_id'] ?? 0);

            if ($bookingId > 0 && !empty($paymentId)) {
                $this->recordSuccessfulPayment($bookingId, $orderId, $paymentId, 'webhook_verified', $amount);
            }
        } elseif ($eventType === 'payment.failed') {
            $paymentData = $payload['payload']['payment']['entity'] ?? [];
            $bookingId = (int)($paymentData['notes']['booking_id'] ?? 0);
            if ($bookingId > 0) {
                $bookingModel = new Booking();
                $bookingModel->update($bookingId, ['payment_status' => 'failed']);
                $service = new BookingService();
                $service->recordStatusHistory($bookingId, 'awaiting_payment', 'awaiting_payment', null, 'Payment attempt failed on payment gateway.');
            }
        }

        return ['status' => 200, 'message' => 'Webhook processed successfully'];
    }

    /**
     * Process refund via Razorpay API or record gateway refund.
     */
    public function processRefund(int $bookingId, int $paymentId, float $amount, string $reason, int $adminUserId): array {
        $paymentModel = new Payment();
        $payment = $paymentModel->find($paymentId);

        if (!$payment || $payment['status'] !== 'paid') {
            return ['success' => false, 'error' => 'Valid paid transaction not found for this refund.'];
        }

        $gatewayRefundId = null;

        // If live Razorpay API is configured
        if ($this->api !== null && !empty($this->keyId) && !str_starts_with($this->keyId, 'rzp_test_sample') && !empty($payment['gateway_payment_id'])) {
            try {
                $amountInPaise = (int)round($amount * 100);
                $refundObj = $this->api->payment->fetch($payment['gateway_payment_id'])->refund([
                    'amount' => $amountInPaise,
                    'notes' => ['reason' => $reason]
                ]);
                $gatewayRefundId = $refundObj['id'];
            } catch (\Throwable $e) {
                Logger::error("Razorpay refund API call failed: " . $e->getMessage());
                return ['success' => false, 'error' => 'Gateway error: ' . $e->getMessage()];
            }
        } else {
            $gatewayRefundId = 'rfnd_test_' . bin2hex(random_bytes(6));
        }

        $refundRef = 'REF-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

        $refundModel = new Refund();
        $newRefundId = $refundModel->create([
            'booking_id' => $bookingId,
            'payment_id' => $paymentId,
            'refund_reference' => $refundRef,
            'amount' => $amount,
            'reason' => $reason,
            'status' => 'completed',
            'gateway_refund_id' => $gatewayRefundId,
            'created_by_user_id' => $adminUserId,
        ]);

        // Update payment and booking statuses
        $paymentModel->update($paymentId, ['status' => 'refunded']);

        $bookingModel = new Booking();
        $bookingModel->update($bookingId, [
            'payment_status' => 'refunded',
            'booking_status' => 'refunded',
        ]);

        $bookingService = new BookingService();
        $bookingService->recordStatusHistory($bookingId, 'cancelled', 'refunded', $adminUserId, "Refund of " . format_currency($amount) . " processed successfully. Ref: $refundRef");

        AuditLog::record('refund_processed', 'refund', $newRefundId, null, [
            'booking_id' => $bookingId,
            'amount' => $amount,
            'refund_reference' => $refundRef
        ]);

        return [
            'success' => true,
            'refund_id' => $newRefundId,
            'refund_reference' => $refundRef,
            'gateway_refund_id' => $gatewayRefundId
        ];
    }
}
