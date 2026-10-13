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
}
