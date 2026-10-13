<?php

namespace App\Controllers\Front;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\MailService;

class AuthController extends Controller {
    public function showLogin(Request $request, Response $response): void {
        if (auth()->check()) {
            $this->redirect(auth()->isAdmin() ? '/admin' : '/dashboard');
        }
        $this->render('front/auth/login', ['title' => 'Sign In - YatraNest'], 'auth');
    }

    public function login(Request $request, Response $response): void {
        $email = strtolower(trim((string)$request->input('email', '')));
        $password = (string)$request->input('password', '');

        if (empty($email) || empty($password)) {
            $this->flash('error', 'Please provide both email and password.');
            $this->redirect('/login');
        }

        // Login rate limiting check
        $attemptsKey = 'login_attempts_' . md5($email);
        $attempts = (int)Session::get($attemptsKey, 0);

        if ($attempts >= 5) {
            $this->flash('error', 'Too many failed login attempts. Please try again after 15 minutes.');
            $this->redirect('/login');
        }

        if (auth()->attempt($email, $password)) {
            Session::remove($attemptsKey);
            AuditLog::record('user_login', 'user', auth()->id());

            $redirectTo = Session::get('redirect_after_login') ?? (auth()->isAdmin() ? '/admin' : '/dashboard');
            Session::remove('redirect_after_login');

            $this->flash('success', 'Welcome back, ' . auth()->user()['name'] . '!');
            $this->redirect($redirectTo);
        } else {
            Session::set($attemptsKey, $attempts + 1);
            $this->flash('error', 'Invalid email address or password.');
            $this->redirect('/login');
        }
    }

    public function showRegister(Request $request, Response $response): void {
        if (auth()->check()) {
            $this->redirect('/dashboard');
        }
        $this->render('front/auth/register', ['title' => 'Register - YatraNest'], 'auth');
    }

    public function register(Request $request, Response $response): void {
        $data = $request->getAllPost();

        $rules = [
            'name' => 'required|min:2|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|phone',
            'password' => 'required|min:6',
            'password_confirmation' => 'required|matches:password',
        ];

        $errors = $this->validate($data, $rules);
        if ($errors) {
            $this->redirect('/register');
        }

        $userModel = new User();
        $userId = $userModel->create([
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'phone' => trim($data['phone']),
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'status' => 'active',
        ]);

        $userModel->assignRole($userId, 'customer');
        $user = $userModel->find($userId);

        auth()->login($user);
        AuditLog::record('user_registered', 'user', $userId);

        $this->flash('success', 'Account registered successfully! Welcome to YatraNest.');
        $this->redirect('/dashboard');
    }

    public function logout(Request $request, Response $response): void {
        AuditLog::record('user_logout', 'user', auth()->id());
        auth()->logout();
        $this->flash('info', 'You have been logged out securely.');
        $this->redirect('/');
    }

    public function showForgotPassword(Request $request, Response $response): void {
        $this->render('front/auth/forgot_password', ['title' => 'Forgot Password'], 'auth');
    }

    public function forgotPassword(Request $request, Response $response): void {
        $email = strtolower(trim((string)$request->input('email', '')));
        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour

            $db = Database::getConnection();
            $stmt = $db->prepare("REPLACE INTO password_reset_tokens (email, token, expires_at) VALUES (:email, :token, :expires)");
            $stmt->execute([':email' => $email, ':token' => $token, ':expires' => $expiresAt]);

            $resetLink = url('/reset-password?token=' . $token . '&email=' . urlencode($email));
            $mailService = new MailService();
            $mailService->sendPasswordReset($email, $user['name'], $resetLink);
        }

        // Generic safe message to prevent email enumeration
        $this->flash('info', 'If an account exists with that email address, password reset instructions have been dispatched.');
        $this->redirect('/login');
    }

    public function showResetPassword(Request $request, Response $response): void {
        $token = $request->getQuery('token');
        $email = $request->getQuery('email');

        if (!$token || !$email) {
            $this->flash('error', 'Invalid password reset link.');
            $this->redirect('/forgot-password');
        }

        $this->render('front/auth/reset_password', [
            'title' => 'Set New Password',
            'token' => $token,
            'email' => $email
        ], 'auth');
    }

    public function resetPassword(Request $request, Response $response): void {
        $data = $request->getAllPost();
        $rules = [
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:6',
            'password_confirmation' => 'required|matches:password',
        ];

        $errors = $this->validate($data, $rules);
        if ($errors) {
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/forgot-password');
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM password_reset_tokens WHERE email = :email AND token = :token AND expires_at > NOW() LIMIT 1");
        $stmt->execute([':email' => $data['email'], ':token' => $data['token']]);
        $resetRecord = $stmt->fetch();

        if (!$resetRecord) {
            $this->flash('error', 'This reset link has expired or is invalid. Please request a new one.');
            $this->redirect('/forgot-password');
        }

        $userModel = new User();
        $user = $userModel->findByEmail($data['email']);
        if ($user) {
            $userModel->update($user['id'], [
                'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT)
            ]);
            $db->prepare("DELETE FROM password_reset_tokens WHERE email = :email")->execute([':email' => $data['email']]);
            $this->flash('success', 'Your password has been successfully reset. Please log in.');
            $this->redirect('/login');
        } else {
            $this->flash('error', 'User not found.');
            $this->redirect('/login');
        }
    }
}
