<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark m-0">Payment Transactions & Gateway Audits</h3>
        <p class="text-muted small m-0">Verified Razorpay transactions, webhook status events, and processed refunds.</p>
    </div>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table mb-0 align-middle">
            <thead>
                <tr>
                    <th>Txn Ref</th>
                    <th>Booking Ref</th>
                    <th>Gateway Order ID</th>
                    <th>Gateway Payment ID</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payments)): ?>
                <tr><td colspan="7" class="text-center py-4 text-muted">No payment transactions recorded.</td></tr>
                <?php else: ?>
                <?php foreach ($payments as $p): ?>
                <tr>
                    <td class="font-monospace fw-bold small text-primary"><?= e($p['transaction_reference']) ?></td>
                    <td>
                        <a href="<?= url('/admin/bookings/' . $p['booking_id']) ?>" class="small font-monospace text-decoration-none">
                            <?= e($p['booking_reference']) ?>
                        </a>
                    </td>
                    <td class="small font-monospace text-muted"><?= e($p['gateway_order_id'] ?? 'N/A') ?></td>
                    <td class="small font-monospace text-dark"><?= e($p['gateway_payment_id'] ?? 'N/A') ?></td>
                    <td class="fw-bold small"><?= format_currency($p['amount']) ?></td>
                    <td>
                        <span class="badge <?= $p['status'] === 'paid' ? 'bg-success' : ($p['status'] === 'refunded' ? 'bg-danger' : 'bg-warning text-dark') ?>" style="font-size: 0.72rem;">
                            <?= strtoupper($p['status']) ?>
                        </span>
                    </td>
                    <td class="small text-muted"><?= format_date($p['created_at'], 'd M Y, H:i') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
