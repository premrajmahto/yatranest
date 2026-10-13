<!-- Hero Section -->
<section class="hero-section">
    <div class="container text-center">
        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-semibold mb-3">
            <i class="bi bi-stars me-1"></i> Explore Over 100+ Curated Holidays
        </span>
        <h1 class="display-4 fw-extrabold text-white mb-3">Discover The World's Wonder With Us</h1>
        <p class="lead text-white-50 mx-auto" style="max-width: 700px;">
            Handcrafted holiday packages, verified accommodations, luxury houseboats, and stress-free vacations across India & beyond.
        </p>

        <!-- Search Card -->
        <div class="hero-search-card text-start">
            <form action="<?= url('/packages') ?>" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label fw-bold small text-muted"><i class="bi bi-geo-alt text-danger me-1"></i> Destination</label>
                        <select name="destination_id" class="form-select border-0 bg-light py-2">
                            <option value="">All Destinations</option>
                            <?php foreach ($destinations as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= e($d['name']) ?> (<?= e($d['country']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <label class="form-label fw-bold small text-muted"><i class="bi bi-compass text-teal me-1"></i> Travel Type</label>
                        <select name="category_id" class="form-select border-0 bg-light py-2">
                            <option value="">All Travel Styles</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <label class="form-label fw-bold small text-muted"><i class="bi bi-clock-history text-primary me-1"></i> Trip Duration</label>
                        <select name="duration" class="form-select border-0 bg-light py-2">
                            <option value="">Any Duration</option>
                            <option value="1-3">1 - 3 Days (Weekend)</option>
                            <option value="4-7">4 - 7 Days (Popular)</option>
                            <option value="8+">8+ Days (Grand Tour)</option>
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <button type="submit" class="btn yn-btn-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-search"></i> Search Holidays
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Travel Category Pills -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-3 justify-content-center">
            <?php foreach ($categories as $cat): ?>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?= url('/packages?category_id=' . $cat['id']) ?>" class="card text-center p-3 text-decoration-none border-0 shadow-sm h-100 text-dark bg-white rounded-3 transition-hover">
                    <i class="bi <?= e($cat['icon']) ?> fs-2 text-secondary mb-2"></i>
                    <h6 class="fw-bold mb-0 small"><?= e($cat['name']) ?></h6>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Popular Destinations Section -->
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <span class="text-secondary fw-bold text-uppercase small">Trending Hotspots</span>
                <h2 class="fw-bold text-dark mb-0">Popular Destinations</h2>
            </div>
            <a href="<?= url('/destinations') ?>" class="btn yn-btn-outline btn-sm">
                View All Destinations <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php foreach ($featured_destinations as $dest): ?>
            <div class="col-lg-3 col-md-6">
                <a href="<?= url('/destinations/' . $dest['slug']) ?>" class="text-decoration-none">
                    <div class="dest-card">
                        <img src="<?= e($dest['image_url']) ?>" alt="<?= e($dest['name']) ?>" loading="lazy">
                        <div class="dest-card-gradient">
                            <h5 class="fw-bold mb-1"><?= e($dest['name']) ?></h5>
                            <span class="small text-white-50"><i class="bi bi-geo-alt-fill me-1"></i><?= e($dest['country']) ?></span>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Tour Packages Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <span class="text-warning fw-bold text-uppercase small">Top Handpicked Journeys</span>
                <h2 class="fw-bold text-dark mb-0">Featured Holiday Packages</h2>
            </div>
            <a href="<?= url('/packages') ?>" class="btn yn-btn-outline btn-sm">
                Explore All Packages <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php foreach ($featured_packages as $pkg): ?>
            <div class="col-lg-4 col-md-6">
                <div class="yn-card">
                    <div class="yn-card-img-wrapper">
                        <img src="<?= e($pkg['featured_image']) ?>" alt="<?= e($pkg['title']) ?>" loading="lazy">
                        <div class="yn-badge-overlay">
                            <?php if ($pkg['is_domestic']): ?>
                                <span class="yn-badge-domestic"><i class="bi bi-flag-fill me-1"></i>Domestic</span>
                            <?php else: ?>
                                <span class="yn-badge-intl"><i class="bi bi-globe me-1"></i>International</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-secondary small fw-bold text-uppercase">
                                <i class="bi bi-geo-alt me-1"></i><?= e($pkg['destination_name']) ?>
                            </span>
                            <div class="text-warning small fw-bold">
                                <i class="bi bi-star-fill"></i> <?= number_format((float)($pkg['avg_rating'] ?? 5.0), 1) ?> (<?= (int)($pkg['review_count'] ?? 1) ?>)
                            </div>
                        </div>

                        <h5 class="card-title fw-bold text-dark mb-2">
                            <a href="<?= url('/packages/' . $pkg['slug']) ?>" class="text-dark text-decoration-none">
                                <?= e($pkg['title']) ?>
                            </a>
                        </h5>

                        <p class="card-text text-muted small flex-grow-1 mb-3">
                            <?= e(mb_strimwidth($pkg['summary'] ?? '', 0, 95, '...')) ?>
                        </p>

                        <div class="d-flex align-items-center gap-3 text-muted small mb-3 pb-3 border-bottom">
                            <span><i class="bi bi-calendar3 me-1 text-primary"></i> <?= (int)$pkg['duration_days'] ?>D / <?= (int)$pkg['duration_nights'] ?>N</span>
                            <span><i class="bi bi-people me-1 text-primary"></i> Max <?= (int)$pkg['group_size_max'] ?> Pax</span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            <div>
                                <span class="text-muted small d-block" style="font-size: 0.75rem;">Starting from</span>
                                <span class="yn-price-tag"><?= format_currency($pkg['price_adult']) ?></span>
                                <span class="text-muted small">/person</span>
                            </div>
                            <a href="<?= url('/packages/' . $pkg['slug']) ?>" class="btn yn-btn-primary btn-sm px-3">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Promotional Coupon Banner -->
<section class="py-5">
    <div class="container">
        <div class="p-5 rounded-4 text-white shadow" style="background: linear-gradient(135deg, #0d233a 0%, #008080 100%);">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold mb-3">LIMITED TIME PROMOTION</span>
                    <h2 class="display-6 fw-bold mb-2">Save 10% on Your First Holiday Booking</h2>
                    <p class="lead mb-0 text-white-50">Apply coupon code <strong class="text-warning bg-dark px-2 py-1 rounded">WELCOME10</strong> at checkout and enjoy up to ₹2,500 instant discount on all packages.</p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <a href="<?= url('/packages') ?>" class="btn btn-warning btn-lg fw-bold px-4 text-dark">
                        Claim Your Discount <i class="bi bi-ticket-perforated ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="text-secondary fw-bold text-uppercase small">Peace Of Mind</span>
            <h2 class="fw-bold text-dark">Why Travelers Choose YatraNest</h2>
        </div>

        <div class="row g-4">
            <div class="col-md-3 text-center">
                <div class="p-4 bg-white rounded-4 shadow-sm h-100">
                    <div class="d-inline-flex p-3 bg-light rounded-circle text-primary fs-2 mb-3">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h5 class="fw-bold">Verified Partners</h5>
                    <p class="text-muted small mb-0">Every hotel, cab operator, and local guide is vetted for quality and safety standards.</p>
                </div>
            </div>

            <div class="col-md-3 text-center">
                <div class="p-4 bg-white rounded-4 shadow-sm h-100">
                    <div class="d-inline-flex p-3 bg-light rounded-circle text-success fs-2 mb-3">
                        <i class="bi bi-tag"></i>
                    </div>
                    <h5 class="fw-bold">Transparent Pricing</h5>
                    <p class="text-muted small mb-0">No hidden charges or surprise taxes. Transparent itemized breakdown before checkout.</p>
                </div>
            </div>

            <div class="col-md-3 text-center">
                <div class="p-4 bg-white rounded-4 shadow-sm h-100">
                    <div class="d-inline-flex p-3 bg-light rounded-circle text-warning fs-2 mb-3">
                        <i class="bi bi-lightning-charge"></i>
                    </div>
                    <h5 class="fw-bold">Instant Confirmation</h5>
                    <p class="text-muted small mb-0">Real-time seat reservation, verified Razorpay payment, and immediate digital invoice receipt.</p>
                </div>
            </div>

            <div class="col-md-3 text-center">
                <div class="p-4 bg-white rounded-4 shadow-sm h-100">
                    <div class="d-inline-flex p-3 bg-light rounded-circle text-danger fs-2 mb-3">
                        <i class="bi bi-headset"></i>
                    </div>
                    <h5 class="fw-bold">24/7 Travel Assistance</h5>
                    <p class="text-muted small mb-0">Dedicated destination managers on call from pickup to final departure drop-off.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Customer Reviews Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <span class="text-secondary fw-bold text-uppercase small">Real Stories</span>
            <h2 class="fw-bold text-dark">What Our Travelers Say</h2>
        </div>

        <div class="row g-4">
            <?php if (empty($reviews)): ?>
            <div class="col-12 text-center py-4 text-muted">
                <i class="bi bi-chat-heart fs-2 d-block mb-2 text-secondary"></i>
                <p>Authentic reviews from verified travelers will be published here shortly.</p>
            </div>
            <?php else: ?>
            <?php foreach ($reviews as $rev): ?>
            <?php 
                $revTitle = $rev['review_title'] ?? $rev['title'] ?? 'Wonderful Experience';
                $revText = $rev['review_text'] ?? $rev['text'] ?? '';
                $userName = $rev['user_name'] ?? 'Verified Traveler';
                $rating = (int)($rev['rating'] ?? 5);
                $initial = strtoupper(substr($userName, 0, 1));
            ?>
            <div class="col-lg-4 col-md-6">
                <div class="card p-4 rounded-4 border-0 shadow-sm h-100 bg-white d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="text-warning">
                            <?php for ($i = 0; $i < $rating; $i++): ?><i class="bi bi-star-fill"></i><?php endfor; ?>
                            <?php for ($i = $rating; $i < 5; $i++): ?><i class="bi bi-star text-muted opacity-25"></i><?php endfor; ?>
                        </div>
                        <?php if (!empty($rev['package_title'])): ?>
                        <span class="badge bg-light text-primary border small text-truncate" style="max-width: 140px;" title="<?= e($rev['package_title']) ?>">
                            <?= e($rev['package_title']) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <h6 class="fw-bold text-dark mb-2">"<?= e($revTitle) ?>"</h6>
                    <p class="text-muted small flex-grow-1">"<?= e($revText) ?>"</p>
                    <div class="d-flex align-items-center gap-2 pt-3 border-top mt-auto">
                        <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center fw-bold text-primary" style="width: 38px; height: 38px;">
                            <?= e($initial) ?>
                        </div>
                        <div>
                            <strong class="d-block small text-dark"><?= e($userName) ?></strong>
                            <span class="text-muted" style="font-size: 0.75rem;">Verified Traveler</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>
