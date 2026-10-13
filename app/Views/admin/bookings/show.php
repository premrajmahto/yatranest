<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <a href="<?= url('/admin/bookings') ?>" class="text-muted small text-decoration-none mb-1 d-inline-block">
            <i class="bi bi-arrow-left me-1"></i> Back to Bookings
        </a>
        <h3 class="fw-bold text-dark m-0">Booking Details: <span class="text-primary font-monospace"><?= e($booking['booking_reference']) ?></span></h3>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/booking/invoice/' . $booking['booking_reference']) ?>" target="_blank" class="btn btn-outline-dark btn-sm">
            <i class="bi bi-printer me-1"></i> View/Print Invoice
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Customer, Package, Travelers -->
    <div class="col-lg-8">
        <!-- Tour Package & Dates -->
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h5 class="admin-card-title">Tour Information</h5>
                <span class="badge bg-secondary"><?= e($booking['destination_name']) ?></span>
            </div>
            <div class="admin-card-body">
                <div class="row g-3">
                    <div class="col-md-7">
                        <strong class="d-block text-dark fs-6"><?= e($booking['package_title']) ?></strong>
                        <span class="text-muted small"><i class="bi bi-clock me-1"></i><?= (int)$booking['duration_days'] ?> Days / <?= (int)$booking['duration_nights'] ?> Nights</span>
                    </div>
                    <div class="col-md-5">
                        <div class="small">
                            <div><strong>Departure:</strong> <?= date('d M Y', strtotime($booking['departure_date'])) ?></div>
                            <div><strong>Return:</strong> <?= date('d M Y', strtotime($booking['return_date'])) ?></div>
                            <div><strong>Pickup:</strong> <?= e($booking['pickup_location'] ?? 'Not specified') ?></div>
                        </div>
                    </div>
                    <?php if (!empty($booking['special_requests'])): ?>
                    <div class="col-12 mt-2 pt-2 border-top">
                        <strong class="small text-dark">Special Requests / Remarks:</strong>
                        <p class="small text-muted mb-0"><?= nl2br(e($booking['special_requests'])) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Passenger Roster -->
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h5 class="admin-card-title">Passenger Manifest (<?= count($booking['travelers']) ?> Travelers)</h5>
            </div>
            <div class="table-responsive">
                <table class="table admin-table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Full Name</th>
                            <th>Age</th>
                            <th>Gender</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($booking['travelers'] as $idx => $tr): ?>
                        <tr>
                            <td class="small"><?= $idx + 1 ?></td>
                            <td class="fw-semibold small text-dark"><?= e($tr['full_name']) ?></td>
                            <td class="small"><?= (int)$tr['age'] ?> yrs</td>
                            <td class="small text-capitalize"><?= e($tr['gender']) ?></td>
                            <td class="small text-capitalize"><?= e($tr['traveler_type']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payment Records -->
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h5 class="admin-card-title">Gateway Payment Records</h5>
            </div>
            <div class="table-responsive">
                <table class="table admin-table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Txn Ref</th>
                            <th>Gateway</th>
                            <th>Payment ID</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($booking['payments'])): ?>
                        <tr><td colspan="6" class="text-center py-3 text-muted small">No payment transactions recorded yet.</td></tr>
                        <?php else: ?>
                        <?php foreach ($booking['payments'] as $p): ?>
                        <tr>
                            <td class="small font-monospace text-primary fw-bold"><?= e($p['transaction_reference']) ?></td>
                            <td class="small text-uppercase"><?= e($p['gateway']) ?></td>
                            <td class="small font-monospace text-muted"><?= e($p['gateway_payment_id'] ?? 'N/A') ?></td>
                            <td class="small fw-bold"><?= format_currency($p['amount']) ?></td>
                            <td>
                                <span class="badge <?= $p['status'] === 'paid' ? 'bg-success' : 'bg-warning text-dark' ?>" style="font-size: 0.7rem;">
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

        <!-- Audit History -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="admin-card-title">Status Change History</h5>
            </div>
            <div class="admin-card-body">
                <?php if (empty($booking['history'])): ?>
                <span class="text-muted small">No status changes recorded.</span>
                <?php else: ?>
                <?php foreach ($booking['history'] as $h): ?>
                <div class="mb-3 border-start border-2 border-primary ps-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-light text-dark border"><?= strtoupper($h['to_status']) ?></span>
                            <span class="small text-muted ms-2">by <?= e($h['changed_by_name'] ?? 'System/Client') ?></span>
                        </div>
                        <span class="text-muted" style="font-size: 0.72rem;"><?= format_date($h['created_at'], 'd M Y, H:i:s') ?></span>
                    </div>
                    <p class="small text-secondary mb-0 mt-1"><?= e($h['note'] ?? 'Status updated') ?></p>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Status & Admin Actions -->
    <div class="col-lg-4">
        <!-- Customer Details -->
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h5 class="admin-card-title">Customer</h5>
            </div>
            <div class="admin-card-body small">
                <div class="mb-2"><strong>Name:</strong> <?= e($booking['customer_name']) ?></div>
                <div class="mb-2"><strong>Email:</strong> <?= e($booking['customer_email']) ?></div>
                <div class="mb-2"><strong>Phone:</strong> <?= e($booking['customer_phone'] ?? 'N/A') ?></div>
            </div>
        </div>

        <!-- Financial Breakdown -->
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h5 class="admin-card-title">Billing Breakdown</h5>
            </div>
            <div class="admin-card-body small">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal:</span>
                    <strong><?= format_currency($booking['subtotal']) ?></strong>
                </div>
                <?php if ((float)$booking['discount_amount'] > 0): ?>
                <div class="d-flex justify-content-between mb-2 text-success">
                    <span>Discount (<?= e($booking['coupon_code'] ?? 'Coupon') ?>):</span>
                    <strong>-<?= format_currency($booking['discount_amount']) ?></strong>
                </div>
                <?php endif; ?>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">GST (<?= number_format((float)$booking['tax_rate'], 1) ?>%):</span>
                    <span><?= format_currency($booking['tax_amount']) ?></span>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between fw-bold fs-6">
                    <span>Grand Total:</span>
                    <span class="text-danger"><?= format_currency($booking['total_amount']) ?></span>
                </div>
            </div>
        </div>

        <!-- Administrative Actions Card -->
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h5 class="admin-card-title">Update Status & Lifecycle</h5>
            </div>
            <div class="admin-card-body">
                <form action="<?= url('/admin/bookings/status/' . $booking['id']) ?>" method="POST" class="mb-4">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Change Booking Status</label>
                        <select name="booking_status" class="form-select form-select-sm">
                            <option value="pending" <?= $booking['booking_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="awaiting_payment" <?= $booking['booking_status'] === 'awaiting_payment' ? 'selected' : '' ?>>Awaiting Payment</option>
                            <option value="confirmed" <?= $booking['booking_status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                            <option value="completed" <?= $booking['booking_status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="cancelled" <?= $booking['booking_status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Change Payment Status</label>
                        <select name="payment_status" class="form-select form-select-sm">
                            <option value="unpaid" <?= $booking['payment_status'] === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                            <option value="pending" <?= $booking['payment_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="paid" <?= $booking['payment_status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
                            <option value="failed" <?= $booking['payment_status'] === 'failed' ? 'selected' : '' ?>>Failed</option>
                            <option value="refunded" <?= $booking['payment_status'] === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Audit Change Note *</label>
                        <input type="text" name="note" class="form-control form-control-sm" required placeholder="e.g. Verified bank NEFT payment">
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">Update Booking Status</button>
                </form>

                <!-- Process Refund Action (If paid) -->
                <?php if ($booking['payment_status'] === 'paid'): ?>
                <div class="border-top pt-3">
                    <button class="btn btn-outline-danger btn-sm w-100" type="button" data-bs-toggle="collapse" data-bs-target="#refundFormCollapse">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Process Refund
                    </button>

                    <div class="collapse mt-3" id="refundFormCollapse">
                        <form action="<?= url('/admin/bookings/refund/' . $booking['id']) ?>" method="POST" onsubmit="return confirm('Process refund for this booking?');">
                            <?= csrf_field() ?>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Refund Amount (₹)</label>
                                <input type="number" step="0.01" name="refund_amount" class="form-control form-control-sm" required value="<?= (float)$booking['total_amount'] ?>" max="<?= (float)$booking['total_amount'] ?>">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Reason</label>
                                <input type="text" name="refund_reason" class="form-control form-control-sm" required placeholder="e.g. Customer cancelled 30 days prior">
                            </div>
                            <button type="submit" class="btn btn-danger btn-sm w-100">Disburse Refund</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Internal Admin Notes -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="admin-card-title">Internal Admin Notes</h5>
            </div>
            <div class="admin-card-body">
                <form action="<?= url('/admin/bookings/notes/' . $booking['id']) ?>" method="POST">
                    <?= csrf_field() ?>
                    <textarea name="internal_notes" class="form-control form-control-sm mb-2" rows="3" placeholder="Private internal staff notes..."><?= e($booking['internal_notes']) ?></textarea>
                    <button type="submit" class="btn btn-outline-secondary btn-sm w-100">Save Notes</button>
                </form>
            </div>
        </div>
    </div>
</div>
