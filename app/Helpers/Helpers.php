<?php

use App\Core\App;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Session;

if (!function_exists('app')) {
    function app(): App {
        return App::getInstance();
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed {
        $val = getenv($key);
        if ($val !== false && $val !== '') {
            return $val;
        }
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return $_ENV[$key];
        }
        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return $_SERVER[$key];
        }
        return $default;
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed {
        return app()->getConfig($key, $default);
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string {
        $baseUrl = rtrim(config('app.url', 'http://localhost:8000'), '/');
        $path = ltrim($path, '/');
        return $path === '' ? $baseUrl : $baseUrl . '/' . $path;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string {
        $cleanPath = ltrim($path, '/');
        $fullPath = dirname(__DIR__, 2) . '/public/assets/' . $cleanPath;
        $v = file_exists($fullPath) ? filemtime($fullPath) : '1.1';
        return url('assets/' . $cleanPath) . '?v=' . $v;
    }
}

if (!function_exists('upload_url')) {
    function upload_url(?string $path, string $fallback = 'assets/images/placeholder.jpg'): string {
        if (!empty($path) && (str_starts_with($path, 'http://') || str_starts_with($path, 'https://'))) {
            return $path;
        }
        if (!empty($path) && file_exists(dirname(__DIR__, 2) . '/public/' . ltrim($path, '/'))) {
            return url($path);
        }
        return !empty($path) ? url($path) : url($fallback);
    }
}

if (!function_exists('e')) {
    function e(?string $value): string {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        return Csrf::token();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string {
        return Csrf::field();
    }
}

if (!function_exists('auth')) {
    function auth(): Auth {
        return app()->getAuth();
    }
}

if (!function_exists('session')) {
    function session(?string $key = null, mixed $default = null): mixed {
        if ($key === null) {
            return Session::class;
        }
        return Session::get($key, $default);
    }
}

if (!function_exists('flash')) {
    function flash(string $key, ?string $message = null): mixed {
        if ($message !== null) {
            Session::setFlash($key, $message);
            return null;
        }
        return Session::getFlash($key);
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed {
        return Session::getOldInput($key, $default);
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path, int $status = 302): void {
        $target = str_starts_with($path, 'http://') || str_starts_with($path, 'https://') ? $path : url($path);
        header("Location: $target", true, $status);
        exit;
    }
}

if (!function_exists('format_currency')) {
    function format_currency(float|int|string|null $amount, string $symbol = '₹'): string {
        $num = (float)($amount ?? 0);
        return $symbol . ' ' . number_format($num, 2);
    }
}

if (!function_exists('format_date')) {
    function format_date(?string $date, string $format = 'd M Y'): string {
        if (empty($date)) return 'N/A';
        try {
            $dt = new \DateTime($date);
            return $dt->format($format);
        } catch (\Exception $e) {
            return $date;
        }
    }
}

if (!function_exists('view')) {
    function view(string $viewPath, array $data = [], ?string $layout = 'main'): void {
        App::getInstance()->renderView($viewPath, $data, $layout);
    }
}

if (!function_exists('whatsapp_phone')) {
    function whatsapp_phone(?string $phone = null): string {
        if ($phone === null) {
            try {
                $settingModel = new \App\Models\SiteSetting();
                $phone = $settingModel->get('company_whatsapp');
                if (empty($phone)) {
                    $phone = $settingModel->get('company_phone', '916000595647');
                }
            } catch (\Throwable $e) {
                $phone = '916000595647';
            }
        }
        $digits = preg_replace('/[^0-9]/', '', (string)$phone);
        if (strlen($digits) === 10) {
            $digits = '91' . $digits;
        }
        return !empty($digits) ? $digits : '916000595647';
    }
}

if (!function_exists('whatsapp_url')) {
    function whatsapp_url(string $customMessage = '', ?string $phone = null): string {
        $waPhone = whatsapp_phone($phone);
        $url = 'https://wa.me/' . $waPhone;
        if (!empty($customMessage)) {
            $url .= '?text=' . rawurlencode($customMessage);
        }
        return $url;
    }
}

