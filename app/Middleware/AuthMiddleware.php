<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AuthMiddleware {
    public function handle(Request $request, Response $response): bool {
        if (!auth()->check()) {
            if ($request->isAjax() || $request->isJson()) {
                $response->json(['success' => false, 'error' => 'Authentication required.'], 401);
            } else {
                Session::setFlash('error', 'Please login to access this page.');
                Session::set('redirect_after_login', $request->getPath());
                $response->redirect('/login');
            }
            return false;
        }
        return true;
    }
}
