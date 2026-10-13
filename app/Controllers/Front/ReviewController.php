<?php

namespace App\Controllers\Front;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Review;

class ReviewController extends Controller {
    public function submit(Request $request, Response $response): void {
        if (!auth()->check()) {
            $this->flash('error', 'Please log in to submit a review.');
            $this->redirect('/login');
        }

        $data = $request->getAllPost();
        $rules = [
            'package_id' => 'required|integer',
            'rating' => 'required|integer|min:1|max:5',
            'review_title' => 'required|min:3|max:200',
            'review_text' => 'required|min:10',
        ];

        $errors = $this->validate($data, $rules);
        if ($errors) {
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/my-bookings');
        }

        $bookingId = (int)($data['booking_id'] ?? 0);
        $userId = auth()->id();

        // Check if customer booked this tour
        if ($bookingId > 0) {
            $bookingModel = new Booking();
            $booking = $bookingModel->find($bookingId);
            if (!$booking || (int)$booking['customer_id'] !== $userId) {
                $this->flash('error', 'Invalid booking reference.');
                $this->redirect('/my-bookings');
            }
        }

        $reviewModel = new Review();
        $revId = $reviewModel->create([
            'package_id' => (int)$data['package_id'],
            'user_id' => $userId,
            'booking_id' => $bookingId ?: null,
            'rating' => (int)$data['rating'],
            'review_title' => trim($data['review_title']),
            'review_text' => trim($data['review_text']),
            'status' => 'approved', // Auto-approved for customer satisfaction in demo
        ]);

        AuditLog::record('review_submitted', 'review', $revId);

        $this->flash('success', 'Thank you! Your review has been submitted.');
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/my-bookings');
    }
}
