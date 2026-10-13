<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\AuditLog;
use App\Models\ContactMessage;
use App\Models\Review;

class ContentManagementController extends Controller {
    public function messages(Request $request, Response $response): void {
        $msgModel = new ContactMessage();
        $this->render('admin/content/contact_messages', [
            'title' => 'Customer Inquiries & Messages',
            'messages' => $msgModel->all('id DESC')
        ], 'admin');
    }

    public function updateMessageStatus(Request $request, Response $response, string $id): void {
        $status = $request->input('status', 'read');
        $msgModel = new ContactMessage();
        $msgModel->update((int)$id, ['status' => $status]);

        $this->flash('success', 'Message status updated.');
        $this->redirect('/admin/content/messages');
    }

    public function reviews(Request $request, Response $response): void {
        $db = Database::getConnection();
        $sql = "SELECT r.*, p.title AS package_title, u.name AS user_name
                FROM reviews r
                JOIN tour_packages p ON r.package_id = p.id
                JOIN users u ON r.user_id = u.id
                ORDER BY r.id DESC";
        $reviews = $db->query($sql)->fetchAll();

        $this->render('admin/content/reviews', [
            'title' => 'Reviews Moderation',
            'reviews' => $reviews
        ], 'admin');
    }

    public function updateReviewStatus(Request $request, Response $response, string $id): void {
        $status = $request->input('status', 'approved');
        $revModel = new Review();
        $revModel->update((int)$id, ['status' => $status]);

        AuditLog::record('review_status_updated', 'review', (int)$id, null, ['status' => $status]);

        $this->flash('success', 'Review status updated to ' . strtoupper($status));
        $this->redirect('/admin/content/reviews');
    }
}
