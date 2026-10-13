<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;

class PaymentManagementController extends Controller {
    public function index(Request $request, Response $response): void {
        $db = Database::getConnection();

        $sql = "SELECT p.*, b.booking_reference
                FROM payments p
                JOIN bookings b ON p.booking_id = b.id
                ORDER BY p.id DESC";
        $payments = $db->query($sql)->fetchAll();

        $this->render('admin/payments/index', [
            'title' => 'Payment Audits & Settlements',
            'payments' => $payments
        ], 'admin');
    }
}
