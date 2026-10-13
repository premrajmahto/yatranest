<?php

namespace App\Controllers\Front;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\User;

class CustomerDashboardController extends Controller {
    public function dashboard(Request $request, Response $response): void {
        $userId = auth()->id();
        $bookingModel = new Booking();

        $allBookings = $bookingModel->getForCustomer($userId);
        $upcoming = $bookingModel->getForCustomer($userId, 'upcoming');
        $completed = $bookingModel->getForCustomer($userId, 'completed');

        $this->render('front/customer/dashboard', [
            'title' => 'Customer Dashboard - My Holidays',
            'user' => auth()->user(),
            'bookings' => $allBookings,
            'upcoming_bookings' => $upcoming,
            'completed_bookings' => $completed
        ]);
    }

    public function profile(Request $request, Response $response): void {
        $this->render('front/customer/profile', [
            'title' => 'Account Settings & Security',
            'user' => auth()->user()
        ]);
    }

    public function updateProfile(Request $request, Response $response): void {
        $userId = auth()->id();
        $name = trim((string)$request->input('name'));
        $phone = trim((string)$request->input('phone'));

        if (empty($name)) {
            $this->flash('error', 'Name cannot be empty.');
            $this->redirect('/profile');
        }

        $userModel = new User();
        $userModel->update($userId, [
            'name' => $name,
            'phone' => $phone
        ]);

        AuditLog::record('profile_updated', 'user', $userId);
        $this->flash('success', 'Profile information updated successfully.');
        $this->redirect('/profile');
    }

    public function updatePassword(Request $request, Response $response): void {
        $userId = auth()->id();
        $currentPassword = (string)$request->input('current_password');
        $newPassword = (string)$request->input('new_password');
        $confirmPassword = (string)$request->input('new_password_confirmation');

        if (strlen($newPassword) < 6) {
            $this->flash('error', 'New password must be at least 6 characters long.');
            $this->redirect('/profile');
        }

        if ($newPassword !== $confirmPassword) {
            $this->flash('error', 'New password and confirmation do not match.');
            $this->redirect('/profile');
        }

        $userModel = new User();
        $user = $userModel->find($userId);

        if (!password_verify($currentPassword, $user['password_hash'])) {
            $this->flash('error', 'Incorrect current password provided.');
            $this->redirect('/profile');
        }

        $userModel->update($userId, [
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT)
        ]);

        AuditLog::record('password_changed', 'user', $userId);
        $this->flash('success', 'Password updated successfully!');
        $this->redirect('/profile');
    }

    public function bookings(Request $request, Response $response): void {
        $userId = auth()->id();
        $filter = $request->getQuery('filter');

        $bookingModel = new Booking();
        $bookings = $bookingModel->getForCustomer($userId, $filter);

        $this->render('front/customer/bookings', [
            'title' => 'My Tour Bookings',
            'user' => auth()->user(),
            'bookings' => $bookings,
            'filter' => $filter
        ]);
    }

    public function bookingDetail(Request $request, Response $response, string $bookingReference): void {
        $bookingModel = new Booking();
        $booking = $bookingModel->getFullDetails($bookingReference);

        if (!$booking) {
            $response->setStatusCode(404);
            $this->render('front/errors/404', ['title' => 'Booking Not Found']);
            return;
        }

        if (!auth()->isAdmin() && auth()->id() !== (int)$booking['customer_id']) {
            $this->flash('error', 'Unauthorized access to booking.');
            $this->redirect('/my-bookings');
        }

        $this->render('front/customer/booking_detail', [
            'title' => 'Trip Details - ' . $booking['booking_reference'],
            'booking' => $booking,
            'user' => auth()->user()
        ]);
    }
}
