<?php

namespace App\Core;

use PDO;

class Auth {
    private ?array $currentUser = null;

    public function check(): bool {
        return Session::has('user_id');
    }

    public function id(): ?int {
        return Session::get('user_id');
    }

    public function user(): ?array {
        if (!$this->check()) {
            return null;
        }

        if ($this->currentUser === null) {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                SELECT u.*, GROUP_CONCAT(r.name) as roles_list
                FROM users u
                LEFT JOIN user_roles ur ON u.id = ur.user_id
                LEFT JOIN roles r ON ur.role_id = r.id
                WHERE u.id = :id AND u.status = 'active'
                GROUP BY u.id
            ");
            $stmt->execute([':id' => $this->id()]);
            $user = $stmt->fetch();
            if ($user) {
                $user['roles'] = !empty($user['roles_list']) ? explode(',', $user['roles_list']) : [];
                $this->currentUser = $user;
            } else {
                $this->logout();
            }
        }
        return $this->currentUser;
    }

    public function hasRole(string ...$roles): bool {
        $user = $this->user();
        if (!$user || empty($user['roles'])) {
            return false;
        }

        // Super admin has all permissions
        if (in_array('super_admin', $user['roles'], true)) {
            return true;
        }

        foreach ($roles as $role) {
            if (in_array($role, $user['roles'], true)) {
                return true;
            }
        }
        return false;
    }

    public function isAdmin(): bool {
        return $this->hasRole('super_admin', 'booking_manager', 'content_manager');
    }

    public function login(array $user): void {
        Session::regenerate();
        Session::set('user_id', (int)$user['id']);
        Session::set('user_name', $user['name']);
        Session::set('user_email', $user['email']);
        $this->currentUser = null;
    }

    public function attempt(string $email, string $password): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email AND status = 'active' LIMIT 1");
        $stmt->execute([':email' => strtolower(trim($email))]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Rehash if necessary
            if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $update = $db->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
                $update->execute([':hash' => $newHash, ':id' => $user['id']]);
            }
            $this->login($user);
            return true;
        }

        return false;
    }

    public function logout(): void {
        Session::remove('user_id');
        Session::remove('user_name');
        Session::remove('user_email');
        $this->currentUser = null;
        Session::destroy();
    }
}
