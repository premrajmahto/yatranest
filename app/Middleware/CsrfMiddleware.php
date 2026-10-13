<?php

namespace App\Middleware;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class CsrfMiddleware {
    private array $except = [
        '/api/webhook/razorpay',
    ];

    public function handle(Request $request, Response $response): bool {
        $method = $request->getMethod();
        $path = $request->getPath();

        // Safe methods don't require CSRF
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return true;
        }

        // Check exemptions
        foreach ($this->except as $exemptPath) {
            if ($path === $exemptPath || str_starts_with($path, $exemptPath)) {
                return true;
            }
        }

        $token = $request->input('_csrf_token') ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        if (!Csrf::validate($token)) {
            if ($request->isAjax() || $request->isJson()) {
                $response->json(['success' => false, 'error' => 'CSRF token mismatch. Please reload the page.'], 403);
            } else {
                Session::setFlash('error', 'Security token expired or invalid. Please try again.');
                $response->redirect($_SERVER['HTTP_REFERER'] ?? '/');
            }
            return false;
        }

        return true;
    }
}
