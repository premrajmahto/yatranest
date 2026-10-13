<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\AuditLog;
use App\Models\User;

class CustomerManagementController extends Controller {
    public function index(Request $request, Response $response): void {
        $db = Database::getConnection();

        $sql = "SELECT u.id, u.name, u.email, u.phone, u.status, u.created_at,
                       COUNT(b.id) AS booking_count
                FROM users u
                JOIN user_roles ur ON u.id = ur.user_id
                JOIN roles r ON ur.role_id = r.id
                LEFT JOIN bookings b ON u.id = b.customer_id
                WHERE r.name = 'customer'
                GROUP BY u.id
                ORDER BY u.id DESC";
        $customers = $db->query($sql)->fetchAll();

        $this->render('admin/customers/index', [
            'title' => 'Customer Management',
            'customers' => $customers
        ], 'admin');
    }

    public function toggleStatus(Request $request, Response $response, string $id): void {
        $userModel = new User();
        $user = $userModel->find((int)$id);

        if (!$user) {
            $this->flash('error', 'User not found.');
            $this->redirect('/admin/customers');
        }

        $newStatus = $user['status'] === 'active' ? 'suspended' : 'active';
        $userModel->update((int)$id, ['status' => $newStatus]);

        AuditLog::record('customer_status_toggled', 'user', (int)$id, ['old' => $user['status']], ['new' => $newStatus]);

        $this->flash('success', "Customer status updated to " . strtoupper($newStatus));
        $this->redirect('/admin/customers');
    }
}
