<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark m-0">Customer Account Management</h3>
        <p class="text-muted small m-0">View registered travelers, manage account status, and monitor trip activity.</p>
    </div>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table mb-0 align-middle">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Email Address</th>
                    <th>Mobile Phone</th>
                    <th>Bookings Count</th>
                    <th>Registered</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                <tr><td colspan="7" class="text-center py-4 text-muted">No customer accounts registered.</td></tr>
                <?php else: ?>
                <?php foreach ($customers as $c): ?>
                <tr>
                    <td>
                        <strong class="text-dark d-block"><?= e($c['name']) ?></strong>
                    </td>
                    <td class="small"><?= e($c['email']) ?></td>
                    <td class="small"><?= e($c['phone'] ?? 'N/A') ?></td>
                    <td>
                        <span class="badge bg-light text-primary border small"><?= (int)($c['booking_count'] ?? 0) ?> trips</span>
                    </td>
                    <td class="small text-muted"><?= format_date($c['created_at'], 'd M Y') ?></td>
                    <td>
                        <span class="badge <?= $c['status'] === 'active' ? 'bg-success' : 'bg-danger' ?>" style="font-size: 0.72rem;">
                            <?= strtoupper($c['status']) ?>
                        </span>
                    </td>
                    <td>
                        <form method="POST" action="<?= url('/admin/customers/toggle-status/' . $c['id']) ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <?php if ($c['status'] === 'active'): ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Suspend Account" onclick="return confirm('Suspend this customer account?');">
                                Suspend
                            </button>
                            <?php else: ?>
                            <button type="submit" class="btn btn-sm btn-outline-success" title="Activate Account">
                                Activate
                            </button>
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
