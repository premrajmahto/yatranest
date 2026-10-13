<div class="container py-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <a href="<?= url('/my-bookings') ?>" class="text-muted small text-decoration-none mb-1 d-inline-block">
                <i class="bi bi-arrow-left me-1"></i> Back to My Bookings
            </a>
            <h3 class="fw-bold text-dark m-0">Booking: <span class="text-primary font-monospace"><?= e($booking['booking_reference']) ?></span></h3>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url('/booking/invoice/' . $booking['booking_reference']) ?>" target="_blank" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-file-earmark-text me-1"></i> Tax Invoice
            </a>
            <?php if (in_array($booking['booking_status'], ['confirmed', 'pending', 'awaiting_payment'], true)): ?>
            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#cancelModal">
                <i class="bi bi-x-circle me-1"></i> Cancel Booking
            </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left: Tour Details & Travelers -->
        <div class="col-lg-8">
            <!-- Tour Overview Card -->
            <div class="card p-4 border-0 shadow-sm rounded-4 bg-white mb-4">
                <div class="d-flex gap-4 flex-column flex-sm-row align-items-sm-center">
                    <img src="<?= e($booking['package_image']) ?>" alt="" class="rounded-3 object-fit-cover" style="width: 120px; height: 100px;">
                    <div>
                        <span class="badge bg-secondary mb-1"><?= e($booking['destination_name']) ?>, <?= e($booking['destination_country']) ?></span>
                        <h5 class="fw-bold text-dark mb-1">
                            <a href="<?= url('/packages/' . $booking['package_slug']) ?>" class="text-dark text-decoration-none">
                                <?= e($booking['package_title']) ?>
                            </a>
                        </h5>
                        <div class="d-flex flex-wrap gap-3 small text-muted">
                            <span><i class="bi bi-calendar3 text-primary me-1"></i><strong>Departure:</strong> <?= date('d M Y', strtotime($booking['departure_date'])) ?></span>
                            <span><i class="bi bi-calendar-check text-secondary me-1"></i><strong>Return:</strong> <?= date('d M Y', strtotime($booking['return_date'])) ?></span>
                            <span><i class="bi bi-clock me-1"></i><?= (int)$booking['duration_days'] ?>D / <?= (int)$booking['duration_nights'] ?>N</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Travelers Manifest -->
            <div class="card p-4 border-0 shadow-sm rounded-4 bg-white mb-4">
                <h5 class="fw-bold text-dark mb-3"><i class="bi bi-people text-secondary me-2"></i>Traveler Roster (<?= count($booking['travelers']) ?> Pax)</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light small">
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

            <!-- Booking Status Audit Trail -->
            <div class="card p-4 border-0 shadow-sm rounded-4 bg-white mb-4">
                <h5 class="fw-bold text-dark mb-3"><i class="bi bi-clock-history text-secondary me-2"></i>Status History</h5>
                <div class="timeline small">
                    <?php foreach ($booking['history'] as $h): ?>
                    <div class="mb-3 border-start border-2 border-primary ps-3 position-relative">
                        <span class="badge bg-light text-dark border me-2"><?= strtoupper($h['to_status']) ?></span>
                        <span class="text-muted" style="font-size: 0.75rem;"><?= format_date($h['created_at'], 'd M Y, h:i A') ?></span>
                        <p class="mb-0 text-secondary mt-1"><?= e($h['note'] ?? 'Status updated') ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Review Tour if completed -->
            <?php if ($booking['booking_status'] === 'completed' || strtotime($booking['return_date']) < time()): ?>
            <div class="card p-4 border-0 shadow-sm rounded-4 bg-white">
                <h5 class="fw-bold text-dark mb-2"><i class="bi bi-star-fill text-warning me-2"></i>Review This Tour</h5>
                <p class="text-muted small mb-3">Share your feedback to help fellow travelers.</p>

                <form action="<?= url('/reviews/submit') ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                    <input type="hidden" name="package_id" value="<?= $booking['package_id'] ?>">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Rating *</label>
                            <select name="rating" class="form-select form-select-sm" required>
                                <option value="5">★★★★★ (5 - Excellent)</option>
                                <option value="4">★★★★☆ (4 - Good)</option>
                                <option value="3">★★★☆☆ (3 - Average)</option>
                                <option value="2">★★☆☆☆ (2 - Poor)</option>
                                <option value="1">★☆☆☆☆ (1 - Terrible)</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Review Title *</label>
                            <input type="text" name="review_title" class="form-control form-control-sm" required placeholder="e.g. Unforgettable family experience!">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Review Details *</label>
                            <textarea name="review_text" class="form-control form-control-sm" rows="3" required placeholder="Tell us about the hotel, sightseeing, guides..."></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn yn-btn-primary btn-sm">Submit Traveler Review</button>
                        </div>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right: Payment & Summary -->
        <div class="col-lg-4">
            <div class="card p-4 border-0 shadow-sm rounded-4 bg-white mb-4">
                <h5 class="fw-bold text-dark mb-3">Payment Summary</h5>

                <div class="d-flex justify-content-between small mb-2">
                    <span class="text-muted">Booking Status:</span>
                    <span class="badge <?= $booking['booking_status'] === 'confirmed' ? 'bg-success' : ($booking['booking_status'] === 'cancelled' ? 'bg-danger' : 'bg-warning text-dark') ?>">
                        <?= strtoupper($booking['booking_status']) ?>
                    </span>
                </div>

                <div class="d-flex justify-content-between small mb-2">
                    <span class="text-muted">Payment Status:</span>
                    <span class="badge <?= $booking['payment_status'] === 'paid' ? 'bg-success' : 'bg-warning text-dark' ?>">
                        <?= strtoupper($booking['payment_status']) ?>
                    </span>
                </div>

                <div class="d-flex justify-content-between small mb-2">
                    <span class="text-muted">Payment Method:</span>
                    <strong class="text-dark text-capitalize"><?= str_replace('_', ' ', $booking['payment_method']) ?></strong>
                </div>

                <hr class="my-3">

                <div class="d-flex justify-content-between small mb-2">
                    <span class="text-muted">Base Fare (Subtotal):</span>
                    <span><?= format_currency($booking['subtotal']) ?></span>
                </div>

                <?php if ((float)$booking['discount_amount'] > 0): ?>
                <div class="d-flex justify-content-between small mb-2 text-success">
                    <span>Discount (<?= e($booking['coupon_code'] ?? 'Coupon') ?>):</span>
                    <span>-<?= format_currency($booking['discount_amount']) ?></span>
                </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between small mb-2">
                    <span class="text-muted">GST (<?= number_format((float)$booking['tax_rate'], 1) ?>%):</span>
                    <span><?= format_currency($booking['tax_amount']) ?></span>
                </div>

                <div class="d-flex justify-content-between fw-bold fs-5 text-dark border-top pt-2 mt-2">
                    <span>Total Amount:</span>
                    <span class="text-danger"><?= format_currency($booking['total_amount']) ?></span>
                </div>
            </div>

            <!-- Cancellation Policy Notice -->
            <div class="card p-4 border-0 shadow-sm rounded-4 bg-light">
                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-shield-exclamation text-warning me-1"></i>Cancellation Rules</h6>
                <div class="small text-muted leading-relaxed" style="font-size: 0.8rem;">
                    <?= nl2br(e($booking['cancellation_policy_text'] ?? 'Standard refund terms apply.')) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancellation Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-danger" id="cancelModalLabel"><i class="bi bi-exclamation-triangle me-2"></i>Cancel Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('/booking/cancel') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">

                <div class="modal-body">
                    <p class="small text-muted mb-3">
                        Are you sure you want to cancel booking <strong><?= e($booking['booking_reference']) ?></strong>?
                        Refunds (if applicable) will be processed in accordance with the package cancellation policy.
                    </p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Reason for Cancellation *</label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="Please let us know why you need to cancel this trip..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Keep Booking</button>
                    <button type="submit" class="btn btn-danger btn-sm">Confirm Cancellation</button>
                </div>
            </form>
        </div>
    </div>
</div>
