<?php

namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\BookingHold;
use App\Models\BookingTraveler;
use App\Models\Coupon;
use App\Models\Payment;
use App\Models\TourDeparture;
use App\Models\TourPackage;

class BookingService {
    private BookingHold $holdModel;
    private TourDeparture $departureModel;
    private Booking $bookingModel;

    public function __construct() {
        $this->holdModel = new BookingHold();
        $this->departureModel = new TourDeparture();
        $this->bookingModel = new Booking();
    }

    /**
     * Check available seats considering both departure available seats and unexpired holds.
     */
    public function getAvailableSeats(int $departureId, ?string $currentSessionId = null): int {
        // Prune expired holds first
        $this->holdModel->pruneExpired();

        $dep = $this->departureModel->find($departureId);
        if (!$dep || $dep['status'] !== 'open') {
            return 0;
        }

        // Active holds excluding current session's own hold if any
        $sql = "SELECT COALESCE(SUM(seats_held), 0) FROM booking_holds WHERE departure_id = :did AND expires_at > NOW()";
        $params = [':did' => $departureId];
        if ($currentSessionId !== null) {
            $sql .= " AND session_id != :sid";
            $params[':sid'] = $currentSessionId;
        }

        $stmt = $this->departureModel->query($sql, $params);
        $otherHolds = (int)$stmt->fetchColumn();

        $available = (int)$dep['available_seats'] - $otherHolds;
        return max(0, $available);
    }

    /**
     * Create or refresh a temporary seat hold (15 minutes).
     */
    public function createOrRefreshHold(int $departureId, string $sessionId, int $seats, ?int $userId = null): bool {
        $this->holdModel->pruneExpired();

        $available = $this->getAvailableSeats($departureId, $sessionId);
        if ($available < $seats) {
            return false;
        }

        $holdMinutes = (int)config('app.hold_expiration_minutes', 15);
        $expiresAt = date('Y-m-d H:i:s', time() + ($holdMinutes * 60));

        // Delete any existing hold for this session
        $this->departureModel->query("DELETE FROM booking_holds WHERE session_id = :sid", [':sid' => $sessionId]);

        // Insert new hold
        $this->holdModel->create([
            'departure_id' => $departureId,
            'session_id' => $sessionId,
            'user_id' => $userId,
            'seats_held' => $seats,
            'expires_at' => $expiresAt,
        ]);

        return true;
    }

    /**
     * Release hold for a session.
     */
    public function releaseHold(string $sessionId): void {
        $this->holdModel->releaseSessionHold($sessionId);
    }

    /**
     * Create a booking with strict concurrency locking and price calculation.
     */
    public function createBooking(array $bookingData, array $travelers, string $sessionId): array {
        $db = Database::getConnection();
        $db->beginTransaction();

        try {
            // Prune expired holds
            $this->holdModel->pruneExpired();

            $departureId = (int)$bookingData['departure_id'];
            $requiredSeats = (int)$bookingData['adult_count'] + (int)$bookingData['child_count'];

            // Row-level lock on departure record
            $dep = $this->departureModel->findWithLock($departureId);
            if (!$dep || $dep['status'] !== 'open') {
                $db->rollBack();
                return ['success' => false, 'error' => 'Selected tour departure is not open for booking.'];
            }

            // Check holds held by OTHERS
            $stmt = $db->prepare("SELECT COALESCE(SUM(seats_held), 0) FROM booking_holds WHERE departure_id = :did AND session_id != :sid AND expires_at > NOW()");
            $stmt->execute([':did' => $departureId, ':sid' => $sessionId]);
            $otherHolds = (int)$stmt->fetchColumn();

            $effectiveAvailable = (int)$dep['available_seats'] - $otherHolds;

            if ($effectiveAvailable < $requiredSeats) {
                $db->rollBack();
                return ['success' => false, 'error' => "Not enough seats available. Only $effectiveAvailable seats remain."];
            }

            // Calculate verified pricing on server
            $pkgModel = new TourPackage();
            $package = $pkgModel->find($bookingData['package_id']);
            if (!$package) {
                $db->rollBack();
                return ['success' => false, 'error' => 'Tour package not found.'];
            }

            $adultPrice = $dep['adult_price'] !== null ? (float)$dep['adult_price'] : (float)$package['price_adult'];
            $childPrice = $dep['child_price'] !== null ? (float)$dep['child_price'] : (float)$package['price_child'];
            $infantPrice = (float)$package['price_infant'];

            $pricingService = new PricingService();
            $priceSummary = $pricingService->calculate(
                $adultPrice,
                (int)$bookingData['adult_count'],
                $childPrice,
                (int)$bookingData['child_count'],
                $infantPrice,
                (int)$bookingData['infant_count'],
                $bookingData['coupon_code'] ?? null,
                (int)$bookingData['customer_id']
            );

            // Generate unique booking reference YN-YYYYMMDD-RANDOM
            $bookingRef = 'YN-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

            $isPayLater = ($bookingData['payment_method'] ?? 'razorpay') === 'pay_later';
            $bookingStatus = $isPayLater ? 'pending' : 'awaiting_payment';
            $paymentStatus = 'unpaid';

            // Insert Booking
            $bookingId = $this->bookingModel->create([
                'booking_reference' => $bookingRef,
                'customer_id' => $bookingData['customer_id'],
                'package_id' => $package['id'],
                'departure_id' => $dep['id'],
                'adult_count' => $bookingData['adult_count'],
                'child_count' => $bookingData['child_count'],
                'infant_count' => $bookingData['infant_count'],
                'subtotal' => $priceSummary['subtotal'],
                'discount_amount' => $priceSummary['discount'],
                'tax_rate' => $priceSummary['tax_rate'],
                'tax_amount' => $priceSummary['tax_amount'],
                'total_amount' => $priceSummary['total'],
                'payment_status' => $paymentStatus,
                'booking_status' => $bookingStatus,
                'payment_method' => $bookingData['payment_method'] ?? 'razorpay',
                'pickup_location' => $bookingData['pickup_location'] ?? null,
                'special_requests' => $bookingData['special_requests'] ?? null,
                'coupon_id' => $priceSummary['coupon']['id'] ?? null,
            ]);

            // Insert Travelers
            $travelerModel = new BookingTraveler();
            foreach ($travelers as $t) {
                $travelerModel->create([
                    'booking_id' => $bookingId,
                    'full_name' => $t['full_name'],
                    'age' => (int)$t['age'],
                    'gender' => $t['gender'] ?? 'male',
                    'traveler_type' => $t['traveler_type'] ?? 'adult',
                    'id_proof_type' => $t['id_proof_type'] ?? null,
                    'id_proof_number' => $t['id_proof_number'] ?? null,
                ]);
            }

            // Deduct seats if pay_later; for online payment, the hold keeps seats reserved until payment confirms!
            if ($isPayLater) {
                $newAvailable = (int)$dep['available_seats'] - $requiredSeats;
                $this->departureModel->update($dep['id'], ['available_seats' => $newAvailable]);
                // Remove hold
                $this->departureModel->query("DELETE FROM booking_holds WHERE session_id = :sid", [':sid' => $sessionId]);
            }

            // Record status history
            $this->recordStatusHistory($bookingId, null, $bookingStatus, auth()->id(), 'Booking initialized via website.');

            $db->commit();

            AuditLog::record('booking_created', 'booking', $bookingId, null, ['reference' => $bookingRef, 'total' => $priceSummary['total']]);

            return [
                'success' => true,
                'booking_id' => $bookingId,
                'booking_reference' => $bookingRef,
                'total_amount' => $priceSummary['total'],
                'pricing' => $priceSummary,
                'is_pay_later' => $isPayLater,
            ];
        } catch (\Throwable $e) {
            $db->rollBack();
            Logger::error("Booking creation failed: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ['success' => false, 'error' => 'An error occurred while creating your booking. Please try again.'];
        }
    }

    /**
     * Confirm a booking after successful payment or admin approval.
     */
    public function confirmBooking(int $bookingId, ?string $paymentRef = null, ?int $changedBy = null, ?string $note = null): bool {
        $db = Database::getConnection();
        $db->beginTransaction();

        try {
            $booking = $this->bookingModel->find($bookingId);
            if (!$booking) {
                $db->rollBack();
                return false;
            }

            if ($booking['booking_status'] === 'confirmed') {
                $db->commit();
                return true; // Already confirmed, idempotent
            }

            $dep = $this->departureModel->findWithLock($booking['departure_id']);
            $requiredSeats = (int)$booking['adult_count'] + (int)$booking['child_count'];

            // If it was awaiting online payment, seats hadn't been deducted yet from available_seats
            if ($booking['payment_method'] === 'razorpay') {
                if ((int)$dep['available_seats'] < $requiredSeats) {
                    // Overbooking exception scenario!
                    $this->bookingModel->update($bookingId, [
                        'booking_status' => 'pending',
                        'payment_status' => 'paid',
                        'internal_notes' => 'ATTENTION: Payment verified but seats were already taken. Needs admin review.'
                    ]);
                    $this->recordStatusHistory($bookingId, $booking['booking_status'], 'pending', $changedBy, 'Payment received after seat hold expiration. Flagged for review.');
                    $db->commit();
                    Logger::warning("Overbooking exception for booking {$booking['booking_reference']}");
                    return false;
                }

                $newAvailable = (int)$dep['available_seats'] - $requiredSeats;
                $this->departureModel->update($dep['id'], ['available_seats' => $newAvailable]);
            }

            // Update booking status
            $this->bookingModel->update($bookingId, [
                'booking_status' => 'confirmed',
                'payment_status' => 'paid',
            ]);

            // Record coupon redemption if coupon was applied
            if (!empty($booking['coupon_id'])) {
                $couponModel = new Coupon();
                $couponModel->query("UPDATE coupons SET used_count = used_count + 1 WHERE id = :cid", [':cid' => $booking['coupon_id']]);
                $db->prepare("INSERT INTO coupon_redemptions (coupon_id, booking_id, user_id, discount_applied) VALUES (:cid, :bid, :uid, :disc)")
                    ->execute([
                        ':cid' => $booking['coupon_id'],
                        ':bid' => $bookingId,
                        ':uid' => $booking['customer_id'],
                        ':disc' => $booking['discount_amount']
                    ]);
            }

            // Record history
            $this->recordStatusHistory($bookingId, $booking['booking_status'], 'confirmed', $changedBy, $note ?? 'Booking confirmed with verified payment.');

            $db->commit();

            AuditLog::record('booking_confirmed', 'booking', $bookingId, ['old_status' => $booking['booking_status']], ['new_status' => 'confirmed']);

            return true;
        } catch (\Throwable $e) {
            $db->rollBack();
            Logger::error("Confirm booking failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cancel a booking and return seats to inventory.
     */
    public function cancelBooking(int $bookingId, string $reason, ?int $changedBy = null): bool {
        $db = Database::getConnection();
        $db->beginTransaction();

        try {
            $booking = $this->bookingModel->find($bookingId);
            if (!$booking || in_array($booking['booking_status'], ['cancelled', 'refunded'], true)) {
                $db->rollBack();
                return false;
            }

            $oldStatus = $booking['booking_status'];

            // Return seats to departure if booking was confirmed or pay_later pending
            if ($oldStatus === 'confirmed' || ($booking['payment_method'] === 'pay_later' && $oldStatus === 'pending')) {
                $dep = $this->departureModel->findWithLock($booking['departure_id']);
                if ($dep) {
                    $seatsToReturn = (int)$booking['adult_count'] + (int)$booking['child_count'];
                    $newAvailable = min((int)$dep['total_seats'], (int)$dep['available_seats'] + $seatsToReturn);
                    $this->departureModel->update($dep['id'], ['available_seats' => $newAvailable]);
                }
            }

            $this->bookingModel->update($bookingId, [
                'booking_status' => 'cancelled',
                'internal_notes' => ($booking['internal_notes'] ?? '') . "\nCancellation reason: " . $reason
            ]);

            $this->recordStatusHistory($bookingId, $oldStatus, 'cancelled', $changedBy, "Booking cancelled: $reason");

            $db->commit();
            AuditLog::record('booking_cancelled', 'booking', $bookingId, ['old_status' => $oldStatus], ['new_status' => 'cancelled', 'reason' => $reason]);

            return true;
        } catch (\Throwable $e) {
            $db->rollBack();
            Logger::error("Cancel booking failed: " . $e->getMessage());
            return false;
        }
    }

    public function recordStatusHistory(int $bookingId, ?string $fromStatus, string $toStatus, ?int $changedBy, ?string $note): void {
        $stmt = Database::getConnection()->prepare("
            INSERT INTO booking_status_history (booking_id, from_status, to_status, changed_by_user_id, note)
            VALUES (:bid, :from_s, :to_s, :uid, :note)
        ");
        $stmt->execute([
            ':bid' => $bookingId,
            ':from_s' => $fromStatus,
            ':to_s' => $toStatus,
            ':uid' => $changedBy,
            ':note' => $note
        ]);
    }
}
