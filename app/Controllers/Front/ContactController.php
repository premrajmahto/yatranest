<?php

namespace App\Controllers\Front;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\AuditLog;
use App\Models\ContactMessage;

class ContactController extends Controller {
    public function show(Request $request, Response $response): void {
        $this->render('front/pages/contact', ['title' => 'Contact Us - 24/7 Holiday Assistance']);
    }

    public function submit(Request $request, Response $response): void {
        $data = $request->getAllPost();

        $rules = [
            'name' => 'required|min:2',
            'email' => 'required|email',
            'subject' => 'required|min:3',
            'message' => 'required|min:5',
        ];

        $errors = $this->validate($data, $rules);
        if ($errors) {
            $this->redirect('/contact');
        }

        $contactModel = new ContactMessage();
        $msgId = $contactModel->create([
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'phone' => trim((string)($data['phone'] ?? '')),
            'subject' => trim($data['subject']),
            'message' => trim($data['message']),
            'status' => 'new'
        ]);

        AuditLog::record('contact_inquiry_submitted', 'contact_message', $msgId);

        $this->flash('success', 'Thank you! Your message has been received. Our team will get back to you shortly.');
        $this->redirect('/contact');
    }

    public function requestPlan(Request $request, Response $response): void {
        $name = trim((string)$request->input('name', ''));
        $phone = trim((string)$request->input('phone', ''));
        $email = strtolower(trim((string)$request->input('email', '')));
        $destination = trim((string)$request->input('destination', ''));
        $currentCity = trim((string)$request->input('current_city', ''));
        $travelDate = trim((string)$request->input('travel_date', ''));

        if (empty($name) || empty($phone) || empty($email) || empty($destination)) {
            $response->json([
                'success' => false,
                'message' => 'Please fill in all required fields (Name, Phone, Email, Destination).'
            ], 422);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response->json([
                'success' => false,
                'message' => 'Please provide a valid email address.'
            ], 422);
            return;
        }

        $formattedDetails = "Custom Travel Plan Request:\n" .
                            "• Destination: " . $destination . "\n" .
                            "• Current City: " . ($currentCity ?: 'Not provided') . "\n" .
                            "• Preferred Date: " . ($travelDate ?: 'Flexible') . "\n" .
                            "• Phone: " . $phone . "\n" .
                            "• Email: " . $email;

        $contactModel = new ContactMessage();
        $msgId = $contactModel->create([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'subject' => "Travel Plan Request: " . $destination . ($currentCity ? " (from {$currentCity})" : ""),
            'message' => $formattedDetails,
            'status' => 'new'
        ]);

        AuditLog::record('travel_plan_requested', 'contact_message', $msgId);

        $response->json([
            'success' => true,
            'message' => 'Thank you! Your travel plan request has been received. Our holiday specialist will contact you shortly.',
            'data' => [
                'name' => $name,
                'destination' => $destination,
                'current_city' => $currentCity,
                'travel_date' => $travelDate,
                'phone' => $phone
            ]
        ]);
    }
}

