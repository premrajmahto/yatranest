<?php

namespace App\Models;

use App\Core\Model;

class Booking extends Model {
    protected string $table = 'bookings';

    public function getFullDetails(int|string $idOrRef): ?array {
        $isRef = !is_numeric($idOrRef);
        $where = $isRef ? "b.booking_reference = :id" : "b.id = :id";

        $sql = "SELECT b.*,
                       u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone,
                       p.title AS package_title, p.slug AS package_slug, p.featured_image AS package_image,
                       p.duration_days, p.duration_nights, p.cancellation_policy_text,
                       d.name AS destination_name, d.country AS destination_country,
                       dep.departure_date, dep.return_date,
                       c.code AS coupon_code
                FROM bookings b
                JOIN users u ON b.customer_id = u.id
                JOIN tour_packages p ON b.package_id = p.id
                JOIN destinations d ON p.destination_id = d.id
                JOIN tour_departures dep ON b.departure_id = dep.id
                LEFT JOIN coupons c ON b.coupon_id = c.id
                WHERE $where
                LIMIT 1";

        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute([':id' => $idOrRef]);
        $booking = $stmt->fetch();

        if ($booking) {
            // Fetch travelers
            $travelerModel = new BookingTraveler();
            $booking['travelers'] = $travelerModel->where('booking_id = :bid', [':bid' => $booking['id']], 'id ASC');

            // Fetch payments
            $payModel = new Payment();
            $booking['payments'] = $payModel->where('booking_id = :bid', [':bid' => $booking['id']], 'id DESC');

            // Fetch status history
            $histStmt = $this->getDb()->prepare("
                SELECT h.*, u.name as changed_by_name
                FROM booking_status_history h
                LEFT JOIN users u ON h.changed_by_user_id = u.id
                WHERE h.booking_id = :bid
                ORDER BY h.id DESC
            ");
            $histStmt->execute([':bid' => $booking['id']]);
            $booking['history'] = $histStmt->fetchAll();
        }

        return $booking ?: null;
    }

    public function getForCustomer(int $customerId, ?string $filter = null): array {
        $where = "b.customer_id = :cid";
        $params = [':cid' => $customerId];

        if ($filter === 'upcoming') {
            $where .= " AND dep.departure_date >= CURRENT_DATE AND b.booking_status NOT IN ('cancelled', 'refunded')";
        } elseif ($filter === 'completed') {
            $where .= " AND (dep.departure_date < CURRENT_DATE OR b.booking_status = 'completed') AND b.booking_status != 'cancelled'";
        } elseif ($filter === 'cancelled') {
            $where .= " AND b.booking_status IN ('cancelled', 'refunded')";
        }

        $sql = "SELECT b.*, p.title AS package_title, p.slug AS package_slug, p.featured_image AS package_image,
                       d.name AS destination_name, dep.departure_date, dep.return_date
                FROM bookings b
                JOIN tour_packages p ON b.package_id = p.id
                JOIN destinations d ON p.destination_id = d.id
                JOIN tour_departures dep ON b.departure_id = dep.id
                WHERE $where
                ORDER BY b.id DESC";

        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
