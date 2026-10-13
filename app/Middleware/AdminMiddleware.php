<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AdminMiddleware {
    public function handle(Request $request, Response $response): bool {
        if (!auth()->check()) {
            Session::setFlash('error', 'Administrator login required.');
            Session::set('redirect_after_admin_login', $request->getPath());
            $response->redirect('/admin/login');
            return false;
        }

        if (!auth()->isAdmin()) {
            Session::setFlash('error', 'Access denied. You do not have administrative privileges.');
            $response->redirect('/');
            return false;
        }

        return true;
    }
}
