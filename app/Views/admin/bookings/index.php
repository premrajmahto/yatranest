<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold text-dark m-0">Booking Lifecycle Management</h3>
        <p class="text-muted small m-0">Monitor customer reservations, confirm pay-later requests, and audit payment settlements.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/admin/bookings/export?' . http_build_query($filters ?? [])) ?>" class="btn btn-outline-success btn-sm">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
        </a>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="admin-card mb-4">
    <div class="admin-card-body p-3">
        <form action="<?= url('/admin/bookings') ?>" method="GET" class="row g-2 align-items-center">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Ref, Customer, Email, Phone..." value="<?= e($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <select name="booking_status" class="form-select form-select-sm">
                    <option value="">All Booking Statuses</option>
                    <option value="pending" <?= ($filters['booking_status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="awaiting_payment" <?= ($filters['booking_status'] ?? '') === 'awaiting_payment' ? 'selected' : '' ?>>Awaiting Payment</option>
                    <option value="confirmed" <?= ($filters['booking_status'] ?? '') === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                    <option value="cancelled" <?= ($filters['booking_status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    <option value="completed" <?= ($filters['booking_status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="refunded" <?= ($filters['booking_status'] ?? '') === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="payment_status" class="form-select form-select-sm">
                    <option value="">All Payment Statuses</option>
                    <option value="paid" <?= ($filters['payment_status'] ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
                    <option value="unpaid" <?= ($filters['payment_status'] ?? '') === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                    <option value="refunded" <?= ($filters['payment_status'] ?? '') === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="package_id" class="form-select form-select-sm">
                    <option value="">All Tour Packages</option>
                    <?php foreach ($packages as $pkg): ?>
                    <option value="<?= $pkg['id'] ?>" <?= ($filters['package_id'] ?? '') == $pkg['id'] ? 'selected' : '' ?>>
                        <?= e(mb_strimwidth($pkg['title'], 0, 30, '...')) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                <a href="<?= url('/admin/bookings') ?>" class="btn btn-light border btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Bookings Table -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table mb-0 align-middle">
            <thead>
                <tr>
                    <th>Ref #</th>
                    <th>Customer</th>
                    <th>Package & Departure</th>
                    <th>Pax</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($bookings)): ?>
                <tr><td colspan="9" class="text-center py-4 text-muted">No bookings match the search criteria.</td></tr>
                <?php else: ?>
                <?php foreach ($bookings as $b): ?>
                <tr>
                    <td class="font-monospace fw-bold small text-primary">
                        <a href="<?= url('/admin/bookings/' . $b['id']) ?>" class="text-decoration-none">
                            <?= e($b['booking_reference']) ?>
                        </a>
                    </td>
                    <td>
                        <strong class="d-block small text-dark"><?= e($b['customer_name']) ?></strong>
                        <span class="small text-muted" style="font-size: 0.75rem;"><?= e($b['customer_phone'] ?? $b['customer_email']) ?></span>
                    </td>
                    <td>
                        <span class="small fw-semibold text-dark d-block"><?= e(mb_strimwidth($b['package_title'], 0, 24, '...')) ?></span>
                        <span class="text-muted" style="font-size: 0.72rem;"><i class="bi bi-calendar3 me-1"></i><?= date('d M Y', strtotime($b['departure_date'])) ?></span>
                    </td>
                    <td class="small"><?= (int)$b['adult_count'] + (int)$b['child_count'] ?> pax</td>
                    <td class="fw-bold small"><?= format_currency($b['total_amount']) ?></td>
                    <td>
                        <span class="badge <?= $b['payment_status'] === 'paid' ? 'bg-success' : 'bg-warning text-dark' ?>" style="font-size: 0.7rem;">
                            <?= strtoupper($b['payment_status']) ?>
                        </span>
                        <span class="text-muted d-block" style="font-size: 0.68rem;"><?= e($b['payment_method']) ?></span>
                    </td>
                    <td>
                        <span class="badge <?= $b['booking_status'] === 'confirmed' ? 'bg-success' : ($b['booking_status'] === 'cancelled' ? 'bg-danger' : 'bg-warning text-dark') ?>" style="font-size: 0.7rem;">
                            <?= strtoupper($b['booking_status']) ?>
                        </span>
                    </td>
                    <td class="small text-muted" style="font-size: 0.75rem;"><?= format_date($b['created_at'], 'd M, H:i') ?></td>
                    <td>
                        <a href="<?= url('/admin/bookings/' . $b['id']) ?>" class="btn btn-sm btn-light border py-1 px-2" title="View / Manage">
                            <i class="bi bi-gear-fill"></i> Manage
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
