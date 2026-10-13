<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Request;

class AuditLog extends Model {
    protected string $table = 'audit_logs';

    public static function record(string $action, ?string $entityType = null, ?int $entityId = null, ?array $old = null, ?array $new = null): void {
        $userId = auth()->id();
        $request = Request::createFromGlobals();

        $log = new self();
        $log->create([
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $old ? json_encode($old) : null,
            'new_values' => $new ? json_encode($new) : null,
            'ip_address' => $request->getIp(),
            'user_agent' => substr($request->getUserAgent(), 0, 500),
        ]);
    }
}
