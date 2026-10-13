<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark m-0">Security & Operational Audit Logs</h3>
        <p class="text-muted small m-0">Immutable records of booking status modifications, payment settlements, and administrative events.</p>
    </div>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table mb-0 align-middle">
            <thead>
                <tr>
                    <th>Action</th>
                    <th>Entity</th>
                    <th>Entity ID</th>
                    <th>User ID</th>
                    <th>IP Address</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                <tr><td colspan="6" class="text-center py-4 text-muted">No audit events recorded.</td></tr>
                <?php else: ?>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td>
                        <span class="badge bg-light text-primary border font-monospace"><?= e($log['action']) ?></span>
                    </td>
                    <td class="small text-capitalize"><?= e($log['entity_type'] ?? 'General') ?></td>
                    <td class="small font-monospace"><?= e((string)($log['entity_id'] ?? 'N/A')) ?></td>
                    <td class="small"><?= e((string)($log['user_id'] ?? 'System')) ?></td>
                    <td class="small font-monospace text-muted"><?= e($log['ip_address'] ?? '127.0.0.1') ?></td>
                    <td class="small text-muted"><?= format_date($log['created_at'], 'd M Y, H:i:s') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
