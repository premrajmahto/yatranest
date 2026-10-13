<div class="bg-light py-4 border-bottom mb-4">
    <div class="container">
        <h1 class="h3 fw-bold text-dark mb-1">Secure Tour Booking & Checkout</h1>
        <p class="text-muted small mb-0">Please verify your traveler details, apply coupons, and choose your payment option.</p>
    </div>
</div>

<div class="container mb-5">
    <!-- 15-Minute Seat Hold Notice -->
    <div class="alert alert-info d-flex align-items-center justify-content-between rounded-3 shadow-sm mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-stopwatch-fill fs-4 text-primary me-3"></i>
            <div>
                <strong class="d-block">Your Seats Are Temporarily Reserved!</strong>
                <span class="small text-muted">We have held <?= $adult_count + $child_count ?> seat(s) for your session. Complete checkout before the reservation timer expires.</span>
            </div>
        </div>
        <div class="text-end">
            <span class="badge bg-danger fs-6 px-3 py-2" id="holdCountdownTimer">15:00</span>
        </div>
    </div>

    <form action="<?= url('/booking/process') ?>" method="POST" id="checkoutForm">
        <?= csrf_field() ?>
        <input type="hidden" name="package_id" value="<?= $package['id'] ?>">
        <input type="hidden" name="departure_id" value="<?= $departure['id'] ?>">
        <input type="hidden" name="adult_count" id="adult_count" value="<?= (int)$adult_count ?>">
        <input type="hidden" name="child_count" id="child_count" value="<?= (int)$child_count ?>">
        <input type="hidden" name="infant_count" value="0">

        <div class="row g-4">
            <!-- Left: Travelers & Contact Details -->
            <div class="col-lg-8">
                <!-- Contact Information -->
                <div class="card p-4 border-0 shadow-sm rounded-4 mb-4 bg-white">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-person-lines-fill text-secondary me-2"></i>Primary Contact Details</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Customer Name *</label>
                            <input type="text" name="customer_name" class="form-control" required value="<?= e($user['name'] ?? old('customer_name')) ?>" placeholder="Full name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email Address *</label>
                            <input type="email" name="customer_email" class="form-control" required value="<?= e($user['email'] ?? old('customer_email')) ?>" placeholder="name@example.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Mobile Number *</label>
                            <input type="tel" name="customer_phone" class="form-control" required value="<?= e($user['phone'] ?? old('customer_phone')) ?>" placeholder="+91 98765 43210">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Pickup Location / City</label>
                            <input type="text" name="pickup_location" class="form-control" value="<?= e(old('pickup_location')) ?>" placeholder="e.g. Airport, Railway Station or Hotel">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Special Requests / Dietary Needs</label>
                            <textarea name="special_requests" class="form-control" rows="2" placeholder="e.g. Vegetarian meals, ground floor room, wheel-chair assistance..."><?= e(old('special_requests')) ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Traveler Manifest -->
                <div class="card p-4 border-0 shadow-sm rounded-4 mb-4 bg-white">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-passports text-secondary me-2"></i>Traveler Information (Passenger Roster)</h5>
                    <div id="travelers-list">
                        <?php for ($i = 0; $i < ($adult_count + $child_count); $i++):
                            $isChild = $i >= $adult_count;
                        ?>
                        <div class="card p-3 mb-3 traveler-card border-slate-200">
                            <h6 class="fw-bold mb-3 text-secondary">
                                <i class="bi bi-person-badge me-1"></i> Traveler #<?= $i + 1 ?> (<?= $isChild ? 'Child' : 'Adult' ?>)
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label small fw-semibold">Full Name *</label>
                                    <input type="text" name="travelers[<?= $i ?>][full_name]" class="form-control form-control-sm" required placeholder="As on Passport/Govt ID" value="<?= e(old("travelers.{$i}.full_name")) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Age *</label>
                                    <input type="number" name="travelers[<?= $i ?>][age]" class="form-control form-control-sm" required min="<?= $isChild ? 1 : 12 ?>" max="<?= $isChild ? 11 : 100 ?>" value="<?= $isChild ? 8 : 28 ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Gender *</label>
                                    <select name="travelers[<?= $i ?>][gender]" class="form-select form-select-sm" required>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <input type="hidden" name="travelers[<?= $i ?>][traveler_type]" value="<?= $isChild ? 'child' : 'adult' ?>">
                                </div>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Payment Method Selection -->
                <div class="card p-4 border-0 shadow-sm rounded-4 mb-4 bg-white">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-credit-card-2-front text-secondary me-2"></i>Choose Payment Method</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-check p-3 border rounded-3 bg-light">
                                <input class="form-check-input ms-0 me-3" type="radio" name="payment_method" id="pay_razorpay" value="razorpay" checked>
                                <label class="form-check-label fw-bold d-block" for="pay_razorpay">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span>Pay Online (Razorpay)</span>
                                        <span class="badge bg-success">Instant Confirmation</span>
                                    </div>
                                    <span class="small fw-normal text-muted d-block">UPI, Credit/Debit Cards, NetBanking, Wallets with 100% Secure SSL encryption.</span>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check p-3 border rounded-3 bg-light">
                                <input class="form-check-input ms-0 me-3" type="radio" name="payment_method" id="pay_later" value="pay_later">
                                <label class="form-check-label fw-bold d-block" for="pay_later">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span>Pay Later / At Office</span>
                                        <span class="badge bg-warning text-dark">Awaiting Review</span>
                                    </div>
                                    <span class="small fw-normal text-muted d-block">Hold reservation now and complete payment via bank transfer or at our branch office.</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Policy Agreement -->
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="termsCheck" required checked>
                    <label class="form-check-label small text-muted" for="termsCheck">
                        I confirm that I have reviewed the travelers list and agree to the <a href="<?= url('/terms') ?>" target="_blank">Terms of Service</a> and <a href="<?= url('/cancellation-policy') ?>" target="_blank">Cancellation & Refund Policy</a>.
                    </label>
                </div>
            </div>

            <!-- Right Column: Price Summary Sidebar -->
            <div class="col-lg-4">
                <div class="card p-4 border-0 shadow-sm rounded-4 bg-white sticky-top" style="top: 85px;">
                    <h5 class="fw-bold text-dark mb-3">Booking Summary</h5>

                    <div class="d-flex gap-3 align-items-center pb-3 border-bottom mb-3">
                        <img src="<?= e($package['featured_image']) ?>" alt="<?= e($package['title']) ?>" class="rounded-3 object-fit-cover" style="width: 70px; height: 70px;">
                        <div>
                            <h6 class="fw-bold text-dark mb-1 small"><?= e($package['title']) ?></h6>
                            <span class="small text-muted d-block"><i class="bi bi-calendar3 me-1"></i><?= date('d M Y', strtotime($departure['departure_date'])) ?></span>
                            <span class="small text-muted"><i class="bi bi-clock me-1"></i><?= (int)$package['duration_days'] ?>D / <?= (int)$package['duration_nights'] ?>N</span>
                        </div>
                    </div>

                    <!-- Coupon Code Input -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Have a Coupon Code?</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="coupon_code" id="couponInput" class="form-control text-uppercase" placeholder="e.g. WELCOME10" value="<?= e(old('coupon_code', $coupon_code ?? '')) ?>">
                            <button class="btn btn-outline-secondary" type="button" id="applyCouponBtn">Apply</button>
                        </div>
                        <div id="couponFeedback" class="small mt-1"></div>
                    </div>

                    <!-- Breakdown Table -->
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between small mb-2">
                            <span class="text-muted">Adults (<?= (int)$adult_count ?> &times; <?= format_currency($departure['adult_price'] ?? $package['price_adult']) ?>):</span>
                            <span><?= format_currency($pricing['adult_total']) ?></span>
                        </div>
                        <?php if ((int)$child_count > 0): ?>
                        <div class="d-flex justify-content-between small mb-2">
                            <span class="text-muted">Children (<?= (int)$child_count ?> &times; <?= format_currency($departure['child_price'] ?? $package['price_child']) ?>):</span>
                            <span><?= format_currency($pricing['child_total']) ?></span>
                        </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between small mb-2">
                            <span class="text-muted">Subtotal:</span>
                            <strong id="summarySubtotal"><?= format_currency($pricing['subtotal']) ?></strong>
                        </div>

                        <div class="d-flex justify-content-between small mb-2" id="discountRow" style="<?= $pricing['discount'] > 0 ? '' : 'display: none;' ?>">
                            <span class="text-success"><i class="bi bi-tag-fill me-1"></i>Coupon Discount:</span>
                            <strong class="text-success" id="summaryDiscount">-<?= format_currency($pricing['discount']) ?></strong>
                        </div>

                        <div class="d-flex justify-content-between small mb-2">
                            <span class="text-muted">GST Tax (<?= number_format((float)$pricing['tax_rate'], 1) ?>%):</span>
                            <span id="summaryTax"><?= format_currency($pricing['tax_amount']) ?></span>
                        </div>

                        <hr class="my-3">

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="fw-bold text-dark fs-6">Grand Total:</span>
                            <span class="fs-4 fw-extrabold text-danger" id="summaryTotal"><?= format_currency($pricing['total']) ?></span>
                        </div>

                        <button type="submit" class="btn yn-btn-primary w-100 py-3 fw-bold" id="confirmCheckoutBtn">
                            <i class="bi bi-shield-lock-fill me-1"></i> Confirm & Proceed
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Seat Hold 15-Minute Countdown
let holdSeconds = 15 * 60;
const timerEl = document.getElementById('holdCountdownTimer');

const countdownInterval = setInterval(function () {
    holdSeconds--;
    if (holdSeconds <= 0) {
        clearInterval(countdownInterval);
        timerEl.innerText = "EXPIRED";
        alert("Your temporary seat reservation has expired. The page will reload to recheck availability.");
        window.location.reload();
    } else {
        const mins = Math.floor(holdSeconds / 60);
        const secs = holdSeconds % 60;
        timerEl.innerText = String(mins).padStart(2, '0') + ":" + String(secs).padStart(2, '0');
    }
}, 1000);

// Coupon Code AJAX validation
document.getElementById('applyCouponBtn').addEventListener('click', function () {
    const code = document.getElementById('couponInput').value.trim();
    const feedback = document.getElementById('couponFeedback');
    if (!code) {
        feedback.innerHTML = '<span class="text-danger">Please enter a coupon code.</span>';
        return;
    }

    feedback.innerHTML = '<span class="text-muted">Validating coupon...</span>';

    fetch('<?= url('/api/coupon/validate') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?= csrf_token() ?>'
        },
        body: JSON.stringify({
            code: code,
            package_id: <?= (int)$package['id'] ?>,
            departure_id: <?= (int)$departure['id'] ?>,
            adult_count: <?= (int)$adult_count ?>,
            child_count: <?= (int)$child_count ?>
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.valid) {
            feedback.innerHTML = '<span class="text-success fw-bold"><i class="bi bi-check-circle"></i> ' + data.message + '</span>';
            document.getElementById('discountRow').style.display = 'flex';
            document.getElementById('summaryDiscount').innerText = '-₹ ' + parseFloat(data.discount).toFixed(2);
            document.getElementById('summaryTax').innerText = '₹ ' + parseFloat(data.pricing.tax_amount).toFixed(2);
            document.getElementById('summaryTotal').innerText = '₹ ' + parseFloat(data.pricing.total).toFixed(2);
        } else {
            feedback.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle"></i> ' + data.message + '</span>';
        }
    })
    .catch(err => {
        feedback.innerHTML = '<span class="text-danger">Error validating coupon.</span>';
    });
});
</script>
