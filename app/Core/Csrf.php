<?php

namespace App\Core;

class Csrf {
    public static function token(): string {
        Session::start();
        $token = Session::get('_csrf_token');
        if (empty($token)) {
            $token = bin2hex(random_bytes(32));
            Session::set('_csrf_token', $token);
        }
        return $token;
    }

    public static function field(): string {
        $token = self::token();
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function validate(?string $token): bool {
        if (empty($token)) {
            return false;
        }
        $validToken = Session::get('_csrf_token');
        if (empty($validToken)) {
            return false;
        }
        return hash_equals($validToken, $token);
    }
}
