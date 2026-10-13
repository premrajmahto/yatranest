<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark m-0">Coupons & Promotional Codes</h3>
        <p class="text-muted small m-0">Create percentage and fixed discount coupons with usage rules and expiry dates.</p>
    </div>
    <a href="<?= url('/admin/coupons/create') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-tag me-1"></i> Create Coupon
    </a>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table mb-0 align-middle">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Value</th>
                    <th>Min Amount</th>
                    <th>Max Discount</th>
                    <th>Usage / Limit</th>
                    <th>Validity</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($coupons)): ?>
                <tr><td colspan="9" class="text-center py-4 text-muted">No coupons created yet.</td></tr>
                <?php else: ?>
                <?php foreach ($coupons as $c): ?>
                <tr>
                    <td>
                        <span class="badge bg-light text-primary border font-monospace fs-6 px-2 py-1"><?= e($c['code']) ?></span>
                    </td>
                    <td class="small text-capitalize"><?= e($c['discount_type']) ?></td>
                    <td class="small fw-bold">
                        <?= $c['discount_type'] === 'percentage' ? number_format((float)$c['discount_value'], 1) . '%' : format_currency($c['discount_value']) ?>
                    </td>
                    <td class="small"><?= format_currency($c['min_booking_amount']) ?></td>
                    <td class="small"><?= $c['max_discount'] !== null ? format_currency($c['max_discount']) : 'No cap' ?></td>
                    <td class="small"><?= (int)$c['used_count'] ?> / <?= $c['usage_limit'] ?? '∞' ?></td>
                    <td class="small text-muted"><?= date('d M Y', strtotime($c['expiry_date'])) ?></td>
                    <td>
                        <span class="badge <?= $c['is_active'] ? 'bg-success' : 'bg-secondary' ?>" style="font-size: 0.72rem;">
                            <?= $c['is_active'] ? 'Active' : 'Disabled' ?>
                        </span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="<?= url('/admin/coupons/edit/' . $c['id']) ?>" class="btn btn-light border" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="<?= url('/admin/coupons/delete/' . $c['id']) ?>" class="d-inline" onsubmit="return confirm('Delete this coupon?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-light border text-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
