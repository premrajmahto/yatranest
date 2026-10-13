<?php

namespace App\Core;

class Session {
    private static bool $started = false;

    public static function start(): void {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        $lifetime = (int)config('app.session_lifetime', 7200);
        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path' => '/',
            'domain' => '',
            'secure' => $isSecure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        ini_set('session.gc_maxlifetime', (string)$lifetime);
        ini_set('session.use_strict_mode', '1');

        session_start();
        self::$started = true;

        // Initialize flash storage
        if (!isset($_SESSION['_flash'])) {
            $_SESSION['_flash'] = [];
        }
        if (!isset($_SESSION['_old'])) {
            $_SESSION['_old'] = [];
        }
    }

    public static function regenerate(): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public static function set(string $key, mixed $value): void {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function setFlash(string $key, string $message): void {
        self::start();
        $_SESSION['_flash'][$key] = $message;
    }

    public static function getFlash(string $key): ?string {
        self::start();
        if (isset($_SESSION['_flash'][$key])) {
            $msg = $_SESSION['_flash'][$key];
            unset($_SESSION['_flash'][$key]);
            return $msg;
        }
        return null;
    }

    public static function setOldInput(array $input): void {
        self::start();
        // Exclude passwords
        unset($input['password'], $input['password_confirmation'], $input['current_password']);
        $_SESSION['_old'] = $input;
    }

    public static function getOldInput(string $key, mixed $default = ''): mixed {
        self::start();
        return $_SESSION['_old'][$key] ?? $default;
    }

    public static function clearOldInput(): void {
        self::start();
        $_SESSION['_old'] = [];
    }

    public static function destroy(): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            session_destroy();
            self::$started = false;
        }
    }
}
