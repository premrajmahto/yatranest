<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark m-0">Customer Inquiries & Messages</h3>
        <p class="text-muted small m-0">Inquiries submitted via website contact form and hotel concierge requests.</p>
    </div>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table mb-0 align-middle">
            <thead>
                <tr>
                    <th>Sender Name</th>
                    <th>Email / Phone</th>
                    <th>Subject</th>
                    <th>Message Details</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($messages)): ?>
                <tr><td colspan="7" class="text-center py-4 text-muted">No contact inquiries received.</td></tr>
                <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                <tr>
                    <td><strong class="text-dark small"><?= e($msg['name']) ?></strong></td>
                    <td class="small">
                        <?= e($msg['email']) ?><br>
                        <span class="text-muted" style="font-size: 0.75rem;"><?= e($msg['phone'] ?? '') ?></span>
                    </td>
                    <td class="small fw-semibold text-primary"><?= e($msg['subject']) ?></td>
                    <td class="small text-secondary" style="max-width: 280px;"><?= nl2br(e($msg['message'])) ?></td>
                    <td class="small text-muted"><?= format_date($msg['created_at'], 'd M Y') ?></td>
                    <td>
                        <span class="badge <?= $msg['status'] === 'replied' ? 'bg-success' : ($msg['status'] === 'read' ? 'bg-info' : 'bg-warning text-dark') ?>" style="font-size: 0.72rem;">
                            <?= strtoupper($msg['status']) ?>
                        </span>
                    </td>
                    <td>
                        <form method="POST" action="<?= url('/admin/content/messages/status/' . $msg['id']) ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <?php if ($msg['status'] !== 'replied'): ?>
                            <input type="hidden" name="status" value="replied">
                            <button type="submit" class="btn btn-sm btn-outline-success">Mark Replied</button>
                            <?php else: ?>
                            <input type="hidden" name="status" value="read">
                            <button type="submit" class="btn btn-sm btn-light border text-muted">Mark Read</button>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
