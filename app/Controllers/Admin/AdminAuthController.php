<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;

class AdminAuthController extends Controller {
    public function showLogin(Request $request, Response $response): void {
        if (auth()->check() && auth()->isAdmin()) {
            $this->redirect('/admin');
        }
        $this->render('admin/auth/login', ['title' => 'Admin Sign In'], 'auth');
    }

    public function login(Request $request, Response $response): void {
        $email = strtolower(trim((string)$request->input('email', '')));
        $password = (string)$request->input('password', '');

        if (empty($email) || empty($password)) {
            $this->flash('error', 'Please provide administrative credentials.');
            $this->redirect('/admin/login');
        }

        if (auth()->attempt($email, $password)) {
            if (!auth()->isAdmin()) {
                auth()->logout();
                $this->flash('error', 'Access denied. Account lacks administrative privileges.');
                $this->redirect('/admin/login');
            }

            AuditLog::record('admin_login', 'user', auth()->id());
            $redirectTo = Session::get('redirect_after_admin_login') ?? '/admin';
            Session::remove('redirect_after_admin_login');

            $this->flash('success', 'Authenticated as ' . auth()->user()['name']);
            $this->redirect($redirectTo);
        } else {
            $this->flash('error', 'Invalid administrator email or password.');
            $this->redirect('/admin/login');
        }
    }
}
