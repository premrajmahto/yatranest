<!-- Package Header Breadcrumb & Hero -->
<div class="bg-light py-4 border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2 small">
                <li class="breadcrumb-item"><a href="<?= url('/') ?>" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/packages') ?>" class="text-decoration-none">Tour Packages</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/destinations/' . $package['destination_id']) ?>" class="text-decoration-none"><?= e($package['destination_name']) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= e($package['title']) ?></li>
            </ol>
        </nav>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h1 class="fw-extrabold h2 text-dark mb-1"><?= e($package['title']) ?></h1>
                <div class="d-flex flex-wrap align-items-center gap-3 small text-muted">
                    <span><i class="bi bi-geo-alt-fill text-danger me-1"></i><?= e($package['destination_name']) ?>, <?= e($package['country']) ?></span>
                    <span><i class="bi bi-clock-fill text-primary me-1"></i><?= (int)$package['duration_days'] ?> Days / <?= (int)$package['duration_nights'] ?> Nights</span>
                    <span><i class="bi bi-people-fill text-secondary me-1"></i>Max Group: <?= (int)$package['group_size_max'] ?></span>
                    <span class="text-warning fw-bold"><i class="bi bi-star-fill me-1"></i><?= number_format((float)($package['avg_rating'] ?? 5.0), 1) ?> (<?= count($reviews) ?> Reviews)</span>
                </div>
            </div>
            <div>
                <span class="text-muted small d-block text-end">Price Per Adult</span>
                <span class="fs-2 fw-extrabold text-danger"><?= format_currency($package['price_adult']) ?></span>
                <span class="text-muted small"> + taxes</span>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row g-5">
        <!-- Left Column: Details, Itinerary, Hotels, Policies, Reviews -->
        <div class="col-lg-8">
            <!-- Hero Image / Gallery -->
            <div class="rounded-4 overflow-hidden mb-4 shadow-sm" style="max-height: 480px;">
                <img src="<?= e($package['featured_image']) ?>" alt="<?= e($package['title']) ?>" class="w-100 h-100 object-fit-cover">
            </div>

            <!-- Highlights / Feature Pills -->
            <div class="mb-4">
                <span class="feature-pill"><i class="bi bi-check2-circle text-success"></i> Instant Confirmation</span>
                <span class="feature-pill"><i class="bi bi-cup-hot text-primary"></i> Breakfast & Dinner Included</span>
                <span class="feature-pill"><i class="bi bi-car-front text-teal"></i> Private AC Transfers</span>
                <span class="feature-pill"><i class="bi bi-shield-check text-secondary"></i> Professional Tour Guide</span>
            </div>

            <!-- Overview Section -->
            <div class="mb-5">
                <h4 class="fw-bold text-dark mb-3">Overview</h4>
                <div class="text-secondary leading-relaxed fs-6">
                    <?= nl2br(e($package['description'])) ?>
                </div>
            </div>

            <!-- Day-by-Day Itinerary Accordion -->
            <div class="mb-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-bold text-dark m-0">Detailed Tour Itinerary</h4>
                    <span class="badge bg-secondary px-3 py-2"><?= count($itineraries) ?> Days Plan</span>
                </div>

                <div class="accordion accordion-itinerary" id="itineraryAccordion">
                    <?php foreach ($itineraries as $idx => $day): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading<?= $day['id'] ?>">
                            <button class="accordion-button <?= $idx === 0 ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $day['id'] ?>" aria-expanded="<?= $idx === 0 ? 'true' : 'false' ?>" aria-controls="collapse<?= $day['id'] ?>">
                                <span class="badge bg-primary me-3">Day <?= (int)$day['day_number'] ?></span>
                                <?= e($day['title']) ?>
                            </button>
                        </h2>
                        <div id="collapse<?= $day['id'] ?>" class="accordion-collapse collapse <?= $idx === 0 ? 'show' : '' ?>" aria-labelledby="heading<?= $day['id'] ?>" data-bs-parent="#itineraryAccordion">
                            <div class="accordion-body bg-white p-4">
                                <p class="text-secondary mb-3"><?= nl2br(e($day['description'])) ?></p>
                                <div class="d-flex flex-wrap gap-3 small text-muted border-top pt-2">
                                    <?php if (!empty($day['meals_included'])): ?>
                                    <span><i class="bi bi-egg-fried text-warning me-1"></i><strong>Meals:</strong> <?= e($day['meals_included']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($day['accommodation'])): ?>
                                    <span><i class="bi bi-building text-info me-1"></i><strong>Stay:</strong> <?= e($day['accommodation']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Inclusions & Exclusions -->
            <div class="mb-5">
                <h4 class="fw-bold text-dark mb-3">Inclusions & Exclusions</h4>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card p-4 rounded-4 border-0 bg-success-subtle h-100">
                            <h6 class="fw-bold text-success mb-3"><i class="bi bi-check-circle-fill me-2"></i>What's Included</h6>
                            <div class="small text-dark leading-relaxed">
                                <?= nl2br(e($package['inclusions'])) ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card p-4 rounded-4 border-0 bg-danger-subtle h-100">
                            <h6 class="fw-bold text-danger mb-3"><i class="bi bi-x-circle-fill me-2"></i>What's Excluded</h6>
                            <div class="small text-dark leading-relaxed">
                                <?= nl2br(e($package['exclusions'])) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accommodation Details -->
            <?php if (!empty($package['hotel_details'])): ?>
            <div class="mb-5">
                <h4 class="fw-bold text-dark mb-3"><i class="bi bi-building me-2 text-secondary"></i>Hotel & Accommodation</h4>
                <div class="card p-4 rounded-4 border-0 bg-light">
                    <div class="text-secondary leading-relaxed">
                        <?= nl2br(e($package['hotel_details'])) ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Cancellation Policy -->
            <div class="mb-5">
                <h4 class="fw-bold text-dark mb-3"><i class="bi bi-file-earmark-ruled me-2 text-warning"></i>Cancellation & Refund Policy</h4>
                <div class="card p-4 rounded-4 border-0 bg-light">
                    <div class="small text-muted leading-relaxed">
                        <?= nl2br(e($package['cancellation_policy_text'])) ?>
                    </div>
                </div>
            </div>

            <!-- Customer Reviews -->
            <div class="mb-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold text-dark m-0">Customer Reviews</h4>
                    <span class="badge bg-warning text-dark px-3 py-2 fw-bold">
                        ★ <?= number_format((float)($package['avg_rating'] ?? 5.0), 1) ?> / 5.0
                    </span>
                </div>

                <?php if (empty($reviews)): ?>
                <div class="p-4 bg-light rounded-4 text-center text-muted">
                    <i class="bi bi-chat-square-text fs-2 d-block mb-2 text-secondary"></i>
                    No traveler reviews yet for this tour. Be the first to book and share your story!
                </div>
                <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($reviews as $rev): ?>
                    <div class="card p-4 rounded-4 border-0 shadow-sm bg-white">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-dark m-0"><?= e($rev['review_title']) ?></h6>
                            <div class="text-warning small">
                                <?php for ($i = 0; $i < $rev['rating']; $i++): ?><i class="bi bi-star-fill"></i><?php endfor; ?>
                            </div>
                        </div>
                        <p class="text-muted small mb-2">"<?= e($rev['review_text']) ?>"</p>
                        <div class="small text-secondary fw-semibold">
                            &mdash; <?= e($rev['user_name']) ?> <span class="text-muted fw-normal">&bull; <?= format_date($rev['created_at']) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Column: Sticky Booking / Availability Checker -->
        <div class="col-lg-4">
            <div class="sticky-booking-card">
                <h4 class="fw-bold text-dark mb-1">Book This Tour</h4>
                <p class="small text-muted mb-4">Select departure date and travelers to check live seat inventory.</p>

                <?php if (empty($departures)): ?>
                <div class="alert alert-warning small">
                    <i class="bi bi-calendar-x me-1"></i> No upcoming departures currently open for online booking. Please contact our support team.
                </div>
                <a href="<?= url('/contact') ?>" class="btn yn-btn-outline w-100">Enquire for Custom Dates</a>
                <?php else: ?>

                <form action="<?= url('/booking/checkout') ?>" method="GET" id="bookingWidgetForm">
                    <input type="hidden" name="package_id" value="<?= $package['id'] ?>">

                    <!-- Select Departure Date -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Select Departure Date *</label>
                        <select name="departure_id" id="departureSelect" class="form-select form-select-sm" required>
                            <option value="">-- Choose Departure Date --</option>
                            <?php foreach ($departures as $dep): ?>
                            <option value="<?= $dep['id'] ?>"
                                    data-available="<?= $dep['real_available_seats'] ?>"
                                    data-adult-price="<?= $dep['adult_price'] ?? $package['price_adult'] ?>"
                                    data-child-price="<?= $dep['child_price'] ?? $package['price_child'] ?>"
                                    <?= $dep['real_available_seats'] <= 0 ? 'disabled' : '' ?>>
                                <?= date('d M Y', strtotime($dep['departure_date'])) ?>
                                (<?= $dep['real_available_seats'] ?> seats left)
                                <?= $dep['real_available_seats'] <= 0 ? '[SOLD OUT]' : '' ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="seatStatusNotice" class="form-text small mt-1"></div>
                    </div>

                    <!-- Adults -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Adults (12+ yrs)</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="number" name="adult_count" id="widgetAdults" class="form-control" value="2" min="1" max="20" required>
                        </div>
                    </div>

                    <!-- Children -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Children (2-11 yrs)</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-person-heart"></i></span>
                            <input type="number" name="child_count" id="widgetChildren" class="form-control" value="0" min="0" max="10">
                        </div>
                        <span class="text-muted small" style="font-size: 0.72rem;">Child Price: <?= format_currency($package['price_child']) ?></span>
                    </div>

                    <!-- Live Estimate Box -->
                    <div class="p-3 bg-light rounded-3 mb-4 border">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Estimated Subtotal:</span>
                            <strong id="previewSubtotal"><?= format_currency($package['price_adult'] * 2) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Taxes (GST 5%):</span>
                            <span id="previewTax" class="text-muted"><?= format_currency($package['price_adult'] * 2 * 0.05) ?></span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark">Est. Total:</span>
                            <span id="previewTotal" class="fs-5 fw-bold text-danger"><?= format_currency($package['price_adult'] * 2 * 1.05) ?></span>
                        </div>
                    </div>

                    <button type="submit" class="btn yn-btn-primary w-100 py-2 fw-bold mb-2" id="bookNowBtn">
                        <i class="bi bi-lightning-fill me-1"></i> Proceed to Book Now
                    </button>
                </form>

                <div class="text-center mt-3">
                    <a href="<?= url('/contact') ?>" class="text-secondary small text-decoration-none">
                        <i class="bi bi-chat-left-dots me-1"></i> Have questions? Enquire Now
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const depSelect = document.getElementById('departureSelect');
    const adultsInput = document.getElementById('widgetAdults');
    const childrenInput = document.getElementById('widgetChildren');
    const subtotalEl = document.getElementById('previewSubtotal');
    const taxEl = document.getElementById('previewTax');
    const totalEl = document.getElementById('previewTotal');
    const seatNotice = document.getElementById('seatStatusNotice');
    const bookBtn = document.getElementById('bookNowBtn');

    const defaultAdultPrice = <?= (float)$package['price_adult'] ?>;
    const defaultChildPrice = <?= (float)$package['price_child'] ?>;

    function recalculate() {
        const adults = parseInt(adultsInput.value) || 1;
        const children = parseInt(childrenInput.value) || 0;
        const totalPax = adults + children;

        let adultPrice = defaultAdultPrice;
        let childPrice = defaultChildPrice;
        let available = 999;

        if (depSelect && depSelect.value) {
            const selectedOpt = depSelect.options[depSelect.selectedIndex];
            adultPrice = parseFloat(selectedOpt.dataset.adultPrice) || defaultAdultPrice;
            childPrice = parseFloat(selectedOpt.dataset.childPrice) || defaultChildPrice;
            available = parseInt(selectedOpt.dataset.available) || 0;

            if (totalPax > available) {
                seatNotice.innerHTML = '<span class="text-danger fw-bold"><i class="bi bi-exclamation-triangle"></i> Exceeds available seats (' + available + ' remaining)!</span>';
                if (bookBtn) bookBtn.disabled = true;
            } else {
                seatNotice.innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> ' + available + ' seats available for this departure.</span>';
                if (bookBtn) bookBtn.disabled = false;
            }
        }

        const subtotal = (adults * adultPrice) + (children * childPrice);
        const tax = subtotal * 0.05;
        const total = subtotal + tax;

        subtotalEl.innerText = '₹ ' + subtotal.toLocaleString('en-IN', {minimumFractionDigits: 2});
        taxEl.innerText = '₹ ' + tax.toLocaleString('en-IN', {minimumFractionDigits: 2});
        totalEl.innerText = '₹ ' + total.toLocaleString('en-IN', {minimumFractionDigits: 2});
    }

    if (depSelect) depSelect.addEventListener('change', recalculate);
    if (adultsInput) adultsInput.addEventListener('input', recalculate);
    if (childrenInput) childrenInput.addEventListener('input', recalculate);
});
</script>
