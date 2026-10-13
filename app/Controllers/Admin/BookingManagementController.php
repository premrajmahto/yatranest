<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\TourPackage;
use App\Services\BookingService;
use App\Services\RazorpayService;

class BookingManagementController extends Controller {
    public function index(Request $request, Response $response): void {
        $db = Database::getConnection();

        $search = trim((string)$request->getQuery('search', ''));
        $bookingStatus = trim((string)$request->getQuery('booking_status', ''));
        $paymentStatus = trim((string)$request->getQuery('payment_status', ''));
        $packageId = (int)$request->getQuery('package_id', 0);

        $where = ["1=1"];
        $params = [];

        if ($search !== '') {
            $where[] = "(b.booking_reference LIKE :s OR u.name LIKE :s OR u.email LIKE :s OR u.phone LIKE :s)";
            $params[':s'] = "%$search%";
        }

        if ($bookingStatus !== '') {
            $where[] = "b.booking_status = :bs";
            $params[':bs'] = $bookingStatus;
        }

        if ($paymentStatus !== '') {
            $where[] = "b.payment_status = :ps";
            $params[':ps'] = $paymentStatus;
        }

        if ($packageId > 0) {
            $where[] = "b.package_id = :pid";
            $params[':pid'] = $packageId;
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT b.*, u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone,
                       p.title AS package_title, dep.departure_date
                FROM bookings b
                JOIN users u ON b.customer_id = u.id
                JOIN tour_packages p ON b.package_id = p.id
                JOIN tour_departures dep ON b.departure_id = dep.id
                WHERE $whereClause
                ORDER BY b.id DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $bookings = $stmt->fetchAll();

        $pkgModel = new TourPackage();
        $packages = $pkgModel->all('title ASC');

        $this->render('admin/bookings/index', [
            'title' => 'Bookings Management',
            'bookings' => $bookings,
            'packages' => $packages,
            'filters' => [
                'search' => $search,
                'booking_status' => $bookingStatus,
                'payment_status' => $paymentStatus,
                'package_id' => $packageId ?: ''
            ]
        ], 'admin');
    }

    public function show(Request $request, Response $response, string $id): void {
        $bookingModel = new Booking();
        $booking = $bookingModel->getFullDetails((int)$id);

        if (!$booking) {
            $this->flash('error', 'Booking not found.');
            $this->redirect('/admin/bookings');
        }

        $this->render('admin/bookings/show', [
            'title' => 'Manage Booking - ' . $booking['booking_reference'],
            'booking' => $booking
        ], 'admin');
    }

    public function updateStatus(Request $request, Response $response, string $id): void {
        $bookingModel = new Booking();
        $booking = $bookingModel->find((int)$id);

        if (!$booking) {
            $this->flash('error', 'Booking not found.');
            $this->redirect('/admin/bookings');
        }

        $newBookingStatus = $request->input('booking_status');
        $newPaymentStatus = $request->input('payment_status');
        $note = trim((string)$request->input('note', 'Updated by Administrator'));

        $bookingService = new BookingService();

        // If transitioning to confirmed from pending
        if ($newBookingStatus === 'confirmed' && $booking['booking_status'] !== 'confirmed') {
            $bookingService->confirmBooking((int)$id, null, auth()->id(), $note);
        } elseif ($newBookingStatus === 'cancelled' && $booking['booking_status'] !== 'cancelled') {
            $bookingService->cancelBooking((int)$id, $note, auth()->id());
        } else {
            $bookingModel->update((int)$id, [
                'booking_status' => $newBookingStatus,
                'payment_status' => $newPaymentStatus,
            ]);
            $bookingService->recordStatusHistory((int)$id, $booking['booking_status'], $newBookingStatus, auth()->id(), $note);
        }

        AuditLog::record('admin_booking_status_updated', 'booking', (int)$id, [
            'old_b_status' => $booking['booking_status'],
            'old_p_status' => $booking['payment_status']
        ], [
            'new_b_status' => $newBookingStatus,
            'new_p_status' => $newPaymentStatus,
            'note' => $note
        ]);

        $this->flash('success', 'Booking status updated successfully.');
        $this->redirect('/admin/bookings/' . $id);
    }

    public function updateNotes(Request $request, Response $response, string $id): void {
        $notes = trim((string)$request->input('internal_notes', ''));
        $bookingModel = new Booking();
        $bookingModel->update((int)$id, ['internal_notes' => $notes]);

        $this->flash('success', 'Internal notes updated.');
        $this->redirect('/admin/bookings/' . $id);
    }

    public function processRefund(Request $request, Response $response, string $id): void {
        $bookingModel = new Booking();
        $booking = $bookingModel->find((int)$id);

        if (!$booking) {
            $this->flash('error', 'Booking not found.');
            $this->redirect('/admin/bookings');
        }

        $refundAmount = (float)$request->input('refund_amount');
        $refundReason = trim((string)$request->input('refund_reason', 'Admin issued refund'));

        $paymentModel = new Payment();
        $payment = $paymentModel->where("booking_id = :bid AND status = 'paid'", [':bid' => $id], 'id DESC', 1);

        if (empty($payment)) {
            $this->flash('error', 'No paid payment transaction found for this booking.');
            $this->redirect('/admin/bookings/' . $id);
        }

        $paidTxn = $payment[0];
        $razorpayService = new RazorpayService();
        $result = $razorpayService->processRefund((int)$id, (int)$paidTxn['id'], $refundAmount, $refundReason, auth()->id());

        if ($result['success']) {
            $this->flash('success', "Refund of " . format_currency($refundAmount) . " processed successfully! Ref: {$result['refund_reference']}");
        } else {
            $this->flash('error', $result['error'] ?? 'Refund processing failed.');
        }

        $this->redirect('/admin/bookings/' . $id);
    }

    public function exportCsv(Request $request, Response $response): void {
        $db = Database::getConnection();

        $stmt = $db->query("
            SELECT b.booking_reference, u.name AS customer_name, u.email, u.phone,
                   p.title AS tour_package, dep.departure_date,
                   (b.adult_count + b.child_count) as total_pax,
                   b.subtotal, b.discount_amount, b.tax_amount, b.total_amount,
                   b.payment_method, b.payment_status, b.booking_status, b.created_at
            FROM bookings b
            JOIN users u ON b.customer_id = u.id
            JOIN tour_packages p ON b.package_id = p.id
            JOIN tour_departures dep ON b.departure_id = dep.id
            ORDER BY b.id DESC
        ");
        $rows = $stmt->fetchAll();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=yatranest-bookings-' . date('Ymd-His') . '.csv');

        $out = fopen('php://output', 'w');
        fputcsv($out, [
            'Booking Reference', 'Customer Name', 'Email', 'Phone',
            'Tour Package', 'Departure Date', 'Total Pax',
            'Subtotal (INR)', 'Discount (INR)', 'Tax (INR)', 'Total Amount (INR)',
            'Payment Method', 'Payment Status', 'Booking Status', 'Booking Date'
        ]);

        foreach ($rows as $row) {
            fputcsv($out, [
                $row['booking_reference'],
                $row['customer_name'],
                $row['email'],
                $row['phone'],
                $row['tour_package'],
                $row['departure_date'],
                $row['total_pax'],
                $row['subtotal'],
                $row['discount_amount'],
                $row['tax_amount'],
                $row['total_amount'],
                $row['payment_method'],
                $row['payment_status'],
                $row['booking_status'],
                $row['created_at'],
            ]);
        }
        fclose($out);
        exit;
    }
}
