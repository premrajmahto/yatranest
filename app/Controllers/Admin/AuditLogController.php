<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\AuditLog;

class AuditLogController extends Controller {
    public function index(Request $request, Response $response): void {
        $auditModel = new AuditLog();
        $logs = $auditModel->all('id DESC');

        $this->render('admin/audit/index', [
            'title' => 'Security Audit Logs',
            'logs' => array_slice($logs, 0, 100)
        ], 'admin');
    }
}
