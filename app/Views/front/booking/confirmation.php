<div class="container py-5 my-3">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card p-5 border-0 shadow rounded-4 text-center bg-white">
                <?php if ($booking['booking_status'] === 'confirmed'): ?>
                    <div class="d-inline-flex p-3 bg-success-subtle text-success rounded-circle fs-1 mx-auto mb-3">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <h2 class="fw-extrabold text-dark mb-1">Booking Confirmed!</h2>
                    <p class="text-muted mb-4">Your tour has been successfully reserved. A confirmation email and tax invoice have been dispatched to <strong><?= e($booking['customer_email']) ?></strong>.</p>
                <?php elseif ($booking['booking_status'] === 'awaiting_payment'): ?>
                    <div class="d-inline-flex p-3 bg-warning-subtle text-warning rounded-circle fs-1 mx-auto mb-3">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <h2 class="fw-extrabold text-dark mb-1">Complete Online Payment</h2>
                    <p class="text-muted mb-4">Your temporary reservation hold is active. Please complete payment via the secure gateway below.</p>
                <?php else: ?>
                    <div class="d-inline-flex p-3 bg-primary-subtle text-primary rounded-circle fs-1 mx-auto mb-3">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <h2 class="fw-extrabold text-dark mb-1">Booking Received!</h2>
                    <p class="text-muted mb-4">Your Pay-Later booking request has been registered. Our operations team will verify your departure and confirm within 24 hours.</p>
                <?php endif; ?>

                <!-- Booking Reference Card -->
                <div class="p-4 bg-light rounded-4 text-start mb-4 border">
                    <div class="row g-3 align-items-center">
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Booking Reference Number</span>
                            <span class="fs-4 fw-extrabold text-primary font-monospace"><?= e($booking['booking_reference']) ?></span>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <span class="text-muted small d-block">Booking Status</span>
                            <span class="badge <?= $booking['booking_status'] === 'confirmed' ? 'bg-success' : 'bg-warning text-dark' ?> fs-6 px-3 py-2 text-uppercase">
                                <?= e($booking['booking_status']) ?>
                            </span>
                        </div>
                    </div>
                    <hr class="my-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Tour Package:</span>
                            <strong class="text-dark"><?= e($booking['package_title']) ?></strong>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small d-block">Departure Date:</span>
                            <strong class="text-dark"><?= date('d M Y', strtotime($booking['departure_date'])) ?></strong>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small d-block">Total Paid/Due:</span>
                            <strong class="text-danger fs-5"><?= format_currency($booking['total_amount']) ?></strong>
                        </div>
                    </div>
                </div>

                <!-- If awaiting online payment, embed Razorpay Checkout Script & Trigger -->
                <?php if ($booking['booking_status'] === 'awaiting_payment' && !empty($razorpay_order)): ?>
                <div class="p-4 bg-white border border-2 border-primary rounded-4 mb-4 text-center">
                    <h5 class="fw-bold mb-2">Proceed to Razorpay Payment</h5>
                    <p class="small text-muted mb-3">Amount Payable: <strong class="text-dark"><?= format_currency($booking['total_amount']) ?></strong></p>
                    <button id="rzp-pay-button" class="btn yn-btn-primary btn-lg px-5 py-3 fw-bold">
                        <i class="bi bi-credit-card me-2"></i> Pay Now with Razorpay
                    </button>
                </div>

                <!-- Official Razorpay Checkout Script -->
                <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
                <script>
                const options = {
                    "key": "<?= e($razorpay_key_id) ?>",
                    "amount": "<?= (int)round($booking['total_amount'] * 100) ?>",
                    "currency": "INR",
                    "name": "YatraNest Holidays",
                    "description": "Payment for Booking <?= e($booking['booking_reference']) ?>",
                    "order_id": "<?= e($razorpay_order['order_id']) ?>",
                    "handler": function (response){
                        // Redirect to server verification endpoint with official signature
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '<?= url('/payment/verify') ?>';

                        const fields = {
                            '_csrf_token': '<?= csrf_token() ?>',
                            'booking_id': '<?= $booking['id'] ?>',
                            'razorpay_order_id': response.razorpay_order_id,
                            'razorpay_payment_id': response.razorpay_payment_id,
                            'razorpay_signature': response.razorpay_signature
                        };

                        for (const key in fields) {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = key;
                            input.value = fields[key];
                            form.appendChild(input);
                        }

                        document.body.appendChild(form);
                        form.submit();
                    },
                    "prefill": {
                        "name": "<?= e($booking['customer_name']) ?>",
                        "email": "<?= e($booking['customer_email']) ?>",
                        "contact": "<?= e($booking['customer_phone'] ?? '') ?>"
                    },
                    "theme": {
                        "color": "#008080"
                    }
                };
                const rzp = new Razorpay(options);
                document.getElementById('rzp-pay-button').onclick = function(e){
                    rzp.open();
                    e.preventDefault();
                };
                </script>
                <?php endif; ?>

                <!-- Actions -->
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="<?= url('/booking/invoice/' . $booking['booking_reference']) ?>" target="_blank" class="btn btn-outline-dark px-4">
                        <i class="bi bi-file-earmark-text me-1"></i> View Tax Invoice
                    </a>
                    <a href="<?= url('/my-bookings/' . $booking['booking_reference']) ?>" class="btn yn-btn-secondary px-4">
                        <i class="bi bi-ticket-detailed me-1"></i> My Trip Details
                    </a>
                    <a href="<?= url('/') ?>" class="btn yn-btn-outline px-4">
                        <i class="bi bi-house me-1"></i> Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
