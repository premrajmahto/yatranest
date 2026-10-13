<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class RoleMiddleware {
    private array $requiredRoles;

    public function __construct(string ...$roles) {
        $this->requiredRoles = $roles;
    }

    public function handle(Request $request, Response $response): bool {
        if (!auth()->check()) {
            $response->redirect('/admin/login');
            return false;
        }

        if (!auth()->hasRole(...$this->requiredRoles)) {
            Session::setFlash('error', 'You do not have permission to perform this administrative action.');
            $response->redirect('/admin');
            return false;
        }

        return true;
    }
}
