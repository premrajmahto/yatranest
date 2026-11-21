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

        <!-- Instant Travel Plan & WhatsApp Assistance Pill -->
        <div class="mt-4 text-center d-flex flex-wrap justify-content-center align-items-center gap-2">
            <button type="button" 
                    class="btn btn-warning fw-bold px-4 py-2 rounded-pill shadow-sm d-inline-flex align-items-center gap-2 btn-open-plan-modal"
                    title="Request A Travel Plan">
                <i class="bi bi-calendar2-heart-fill text-dark"></i>
                <span class="text-dark">Plan My Holidays</span>
            </button>
            <a href="<?= whatsapp_url('Hi YatraNest, I would like customized travel recommendations.') ?>" 
               target="_blank" 
               rel="noopener" 
               class="badge bg-dark bg-opacity-50 text-white text-decoration-none px-3 py-2 rounded-pill border border-white border-opacity-25 d-inline-flex align-items-center"
               title="WhatsApp: +91 60005 95647">
                <i class="bi bi-whatsapp text-success me-1"></i> Need instant help? Chat: <strong>+91 60005 95647</strong>
            </a>
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
                            <div class="d-flex align-items-center gap-2">
                                <a href="<?= whatsapp_url('Hi YatraNest, I would like to inquire about the package: ' . $pkg['title'] . ' (Starting from ' . format_currency($pkg['price_adult']) . '). Website link: ' . url('/packages/' . $pkg['slug'])) ?>" 
                                   target="_blank" 
                                   rel="noopener" 
                                   class="yn-btn-whatsapp-icon" 
                                   title="Direct WhatsApp Contact"
                                   aria-label="Direct WhatsApp Contact">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                                <a href="<?= url('/packages/' . $pkg['slug']) ?>" class="btn yn-btn-outline btn-sm px-2 px-md-3" title="View Detailed Itinerary">
                                    Details
                                </a>
                                <button type="button" 
                                        class="btn yn-btn-primary btn-sm px-2 px-md-3 btn-open-plan-modal"
                                        data-package-id="<?= $pkg['id'] ?>"
                                        data-package-title="<?= e($pkg['title']) ?>"
                                        data-package-dest="<?= e($pkg['destination_name']) ?>"
                                        title="Request Travel Plan for <?= e($pkg['title']) ?>">
                                    <i class="bi bi-calendar2-heart me-1"></i>Plan
                                </button>
                            </div>
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
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0 d-flex flex-wrap justify-content-lg-end gap-2">
                    <button type="button" class="btn btn-warning btn-lg fw-bold px-4 text-dark shadow btn-open-plan-modal" data-package-dest="Special Holiday Deal (WELCOME10)">
                        Plan with WELCOME10 <i class="bi bi-ticket-perforated ms-1"></i>
                    </button>
                    <a href="<?= url('/packages') ?>" class="btn btn-outline-light btn-lg px-3">
                        Explore
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
<style>
.yn-reviews-carousel-wrapper {
    overflow: hidden !important;
    position: relative !important;
    width: 100% !important;
    padding: 10px 0 20px 0 !important;
}
.yn-reviews-track {
    display: flex !important;
    flex-direction: row !important;
    flex-wrap: nowrap !important;
    transition: transform 0.45s cubic-bezier(0.25, 1, 0.5, 1) !important;
    will-change: transform;
    width: 100% !important;
}
.yn-reviews-slide {
    flex: 0 0 33.333333% !important;
    max-width: 33.333333% !important;
    width: 33.333333% !important;
    padding: 0 12px !important;
    box-sizing: border-box !important;
}
@media (max-width: 991.98px) {
    .yn-reviews-slide {
        flex: 0 0 50% !important;
        max-width: 50% !important;
        width: 50% !important;
    }
}
@media (max-width: 575.98px) {
    .yn-reviews-slide {
        flex: 0 0 100% !important;
        max-width: 100% !important;
        width: 100% !important;
    }
}
.yn-slider-btn {
    width: 44px !important;
    height: 44px !important;
    border-radius: 50% !important;
    border: 1.5px solid #cbd5e1 !important;
    background: #ffffff !important;
    color: #0d233a !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    cursor: pointer !important;
    transition: all 0.25s ease !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06) !important;
    padding: 0 !important;
    outline: none !important;
}
.yn-slider-btn:hover {
    background: #008080 !important;
    border-color: #008080 !important;
    color: #ffffff !important;
    transform: scale(1.08) !important;
    box-shadow: 0 4px 14px rgba(0, 128, 128, 0.3) !important;
}
.yn-slider-dots {
    display: flex !important;
    justify-content: center !important;
    gap: 8px !important;
    margin-top: 25px !important;
}
.yn-slider-dot {
    width: 10px !important;
    height: 10px !important;
    border-radius: 50% !important;
    background-color: #cbd5e1 !important;
    border: none !important;
    padding: 0 !important;
    cursor: pointer !important;
    transition: all 0.3s ease !important;
}
.yn-slider-dot.active {
    background-color: #ff6b35 !important;
    width: 26px !important;
    border-radius: 12px !important;
}
</style>

<section class="py-5 bg-white position-relative overflow-hidden">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-4">
            <div>
                <span class="text-secondary fw-bold text-uppercase small">Real Stories</span>
                <h2 class="fw-bold text-dark m-0">What Our Travelers Say</h2>
            </div>
            <!-- Slider Controls -->
            <div class="d-flex gap-2 mt-3 mt-md-0">
                <button type="button" class="yn-slider-btn yn-slider-prev" aria-label="Previous Reviews" title="Previous Reviews" style="width: 42px; height: 42px; border-radius: 50%; border: 1.5px solid #cbd5e1; background: #fff; color: #0d233a; display: inline-flex; align-items: center; justify-content: center; cursor: pointer;">
                    <i class="bi bi-chevron-left fs-5"></i>
                </button>
                <button type="button" class="yn-slider-btn yn-slider-next" aria-label="Next Reviews" title="Next Reviews" style="width: 42px; height: 42px; border-radius: 50%; border: 1.5px solid #cbd5e1; background: #fff; color: #0d233a; display: inline-flex; align-items: center; justify-content: center; cursor: pointer;">
                    <i class="bi bi-chevron-right fs-5"></i>
                </button>
            </div>
        </div>

        <?php if (empty($reviews)): ?>
        <div class="text-center py-4 text-muted">
            <i class="bi bi-chat-heart fs-2 d-block mb-2 text-secondary"></i>
            <p>Authentic reviews from verified travelers will be published here shortly.</p>
        </div>
        <?php else: ?>
        <div class="yn-reviews-carousel-wrapper" style="overflow: hidden; position: relative; width: 100%; padding: 10px 0 20px 0;">
            <div class="yn-reviews-track" id="reviewsSliderTrack" style="display: flex; flex-direction: row; flex-wrap: nowrap; transition: transform 0.45s cubic-bezier(0.25, 1, 0.5, 1); will-change: transform; width: 100%;">
                <?php foreach ($reviews as $rev): ?>
                <?php 
                    $revTitle = $rev['review_title'] ?? $rev['title'] ?? 'Wonderful Experience';
                    $revText = $rev['review_text'] ?? $rev['text'] ?? '';
                    $userName = $rev['user_name'] ?? 'Verified Traveler';
                    $rating = (int)($rev['rating'] ?? 5);
                    $initial = strtoupper(substr($userName, 0, 1));
                ?>
                <div class="yn-reviews-slide flex-shrink-0" style="padding: 0 12px; box-sizing: border-box;">
                    <div class="card p-4 rounded-4 border-0 shadow-sm h-100 bg-white d-flex flex-column" style="border: 1px solid var(--yn-gray-200) !important;">
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
            </div>

            <!-- Dots Indicators -->
            <div class="yn-slider-dots" id="reviewsSliderDots"></div>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
(function() {
    function setupReviewsSlider() {
        var track = document.getElementById('reviewsSliderTrack');
        if (!track || track.dataset.sliderInitialized) return;
        track.dataset.sliderInitialized = 'true';

        var slides = track.querySelectorAll('.yn-reviews-slide');
        if (!slides || !slides.length) return;

        var prevBtn = document.querySelector('.yn-slider-prev');
        var nextBtn = document.querySelector('.yn-slider-next');
        var dotsContainer = document.getElementById('reviewsSliderDots');

        var currentIndex = 0;
        var autoplayTimer = null;

        function getVisibleCount() {
            if (window.innerWidth < 576) return 1;
            if (window.innerWidth < 992) return 2;
            return 3;
        }

        function getMaxIndex() {
            return Math.max(0, slides.length - getVisibleCount());
        }

        function updateDots() {
            if (!dotsContainer) return;
            dotsContainer.innerHTML = '';
            var maxIdx = getMaxIndex();
            if (maxIdx <= 0) {
                dotsContainer.style.display = 'none';
                return;
            }
            dotsContainer.style.display = 'flex';
            for (var i = 0; i <= maxIdx; i++) {
                (function(idx) {
                    var dot = document.createElement('button');
                    dot.type = 'button';
                    dot.className = 'yn-slider-dot' + (idx === currentIndex ? ' active' : '');
                    dot.setAttribute('aria-label', 'Go to review slide ' + (idx + 1));
                    dot.addEventListener('click', function(e) {
                        e.preventDefault();
                        goToSlide(idx);
                        restartAutoplay();
                    });
                    dotsContainer.appendChild(dot);
                })(i);
            }
        }

        function goToSlide(index) {
            var maxIdx = getMaxIndex();
            if (index < 0) {
                currentIndex = maxIdx;
            } else if (index > maxIdx) {
                currentIndex = 0;
            } else {
                currentIndex = index;
            }

            var slide = slides[0];
            var slideWidth = slide ? slide.getBoundingClientRect().width : 0;
            if (slideWidth > 0) {
                track.style.transform = 'translateX(-' + (currentIndex * slideWidth) + 'px)';
            } else {
                var visible = getVisibleCount();
                track.style.transform = 'translateX(-' + (currentIndex * (100 / visible)) + '%)';
            }

            if (dotsContainer) {
                var dots = dotsContainer.querySelectorAll('.yn-slider-dot');
                dots.forEach(function(d, idx) {
                    d.classList.toggle('active', idx === currentIndex);
                });
            }
        }

        function nextSlide() {
            goToSlide(currentIndex + 1);
        }

        function prevSlide() {
            goToSlide(currentIndex - 1);
        }

        if (nextBtn) {
            nextBtn.onclick = function(e) {
                e.preventDefault();
                nextSlide();
                restartAutoplay();
            };
        }

        if (prevBtn) {
            prevBtn.onclick = function(e) {
                e.preventDefault();
                prevSlide();
                restartAutoplay();
            };
        }

        // Touch support (swipe)
        var touchStartX = 0;
        var touchEndX = 0;

        track.addEventListener('touchstart', function(e) {
            if (e.touches && e.touches[0]) {
                touchStartX = e.touches[0].clientX;
            }
            stopAutoplay();
        }, { passive: true });

        track.addEventListener('touchend', function(e) {
            if (e.changedTouches && e.changedTouches[0]) {
                touchEndX = e.changedTouches[0].clientX;
            }
            var diff = touchStartX - touchEndX;
            if (Math.abs(diff) > 40) {
                if (diff > 0) nextSlide();
                else prevSlide();
            }
            restartAutoplay();
        }, { passive: true });

        // Autoplay with hover pause
        function startAutoplay() {
            stopAutoplay();
            if (slides.length > getVisibleCount()) {
                autoplayTimer = setInterval(nextSlide, 4500);
            }
        }

        function stopAutoplay() {
            if (autoplayTimer) {
                clearInterval(autoplayTimer);
                autoplayTimer = null;
            }
        }

        function restartAutoplay() {
            stopAutoplay();
            startAutoplay();
        }

        var wrapper = track.closest('.yn-reviews-carousel-wrapper');
        if (wrapper) {
            wrapper.addEventListener('mouseenter', stopAutoplay);
            wrapper.addEventListener('mouseleave', startAutoplay);
        }

        window.addEventListener('resize', function() {
            var maxIdx = getMaxIndex();
            if (currentIndex > maxIdx) currentIndex = maxIdx;
            updateDots();
            goToSlide(currentIndex);
        });

        // Initialize display
        updateDots();
        goToSlide(0);
        startAutoplay();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupReviewsSlider);
    } else {
        setupReviewsSlider();
    }
})();
</script>

<!-- Embedded Data for Instant Booking Modal -->
<script id="ynPackagesData" type="application/json"><?= json_encode($packages_map ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?></script>
<script id="ynDeparturesData" type="application/json"><?= json_encode($departures_by_package ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?></script>

<!-- Request A Travel Plan Modal (Matches uploaded screenshot) -->
<div class="modal fade yn-plan-modal" id="requestTravelPlanModal" tabindex="-1" aria-labelledby="requestTravelPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

            <!-- Form Container -->
            <div id="travelPlanFormContainer">
                <h3 class="yn-plan-title" id="requestTravelPlanModalLabel">Request A Travel Plan</h3>

                <form id="travelPlanForm" method="POST" action="<?= url('/travel-plan/request') ?>">
                    <?= csrf_field() ?>

                    <!-- Name Field -->
                    <div class="yn-plan-group">
                        <div class="yn-plan-icon">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <input type="text" name="name" class="yn-plan-input" placeholder="Your Name *" required>
                    </div>

                    <!-- Phone Field -->
                    <div class="yn-plan-group">
                        <div class="yn-plan-icon">
                            <i class="bi bi-telephone-fill"></i>
                        </div>
                        <input type="tel" name="phone" id="planPhoneInput" class="yn-plan-input" placeholder="+91" value="+91 " required>
                    </div>

                    <!-- Email Field -->
                    <div class="yn-plan-group">
                        <div class="yn-plan-icon">
                            <i class="bi bi-envelope-fill"></i>
                        </div>
                        <input type="email" name="email" class="yn-plan-input" placeholder="Email Id*" required>
                    </div>

                    <!-- Travel Destination Field -->
                    <div class="yn-plan-group">
                        <div class="yn-plan-icon">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <input type="text" name="destination" id="planDestinationInput" class="yn-plan-input" placeholder="Travel Destination*" list="travelDestList" required>
                        <datalist id="travelDestList">
                            <?php foreach (($destinations ?? []) as $d): ?>
                            <option value="<?= e($d['name']) ?> (<?= e($d['country']) ?>)"></option>
                            <?php endforeach; ?>
                            <option value="Kashmir"></option>
                            <option value="Kerala"></option>
                            <option value="Himachal & Manali"></option>
                            <option value="Goa"></option>
                            <option value="Sikkim & Darjeeling"></option>
                            <option value="Dubai"></option>
                            <option value="Bali"></option>
                            <option value="Thailand"></option>
                            <option value="Rajasthan"></option>
                        </datalist>
                    </div>

                    <!-- Current City Field -->
                    <div class="yn-plan-group">
                        <div class="yn-plan-icon">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <input type="text" name="current_city" class="yn-plan-input" placeholder="Your Current City Name*" required>
                    </div>

                    <!-- Travel Date Field -->
                    <div class="yn-plan-group">
                        <div class="yn-plan-icon">
                            <i class="bi bi-calendar3"></i>
                        </div>
                        <input type="text" 
                               name="travel_date" 
                               id="planDateInput" 
                               class="yn-plan-input" 
                               placeholder="Travel Date*" 
                               onfocus="(this.type='date'); try{this.showPicker();}catch(e){}" 
                               onblur="if(!this.value){this.type='text';}" 
                               min="<?= date('Y-m-d') ?>" 
                               required>
                    </div>

                    <div id="planFormError" class="alert alert-danger py-2 px-3 small d-none mb-3"></div>

                    <!-- Submit Button -->
                    <button type="submit" id="planSubmitBtn" class="yn-plan-btn">
                        <span>Plan My Holidays</span>
                    </button>
                </form>
            </div>

            <!-- Success Confirmation Screen -->
            <div id="travelPlanSuccess" class="yn-plan-success d-none">
                <div class="text-success mb-3">
                    <i class="bi bi-check-circle-fill" style="font-size: 3.5rem;"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2">Request Received!</h4>
                <p class="text-muted small mb-3" id="planSuccessMessage">
                    Thank you! Our holiday specialist is crafting your customized itinerary and will get in touch with you shortly.
                </p>
                <div class="d-flex flex-column gap-2 mt-4">
                    <a href="#" id="planSuccessWhatsApp" target="_blank" rel="noopener" class="btn btn-success fw-bold py-2 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-whatsapp"></i> Chat on WhatsApp Now
                    </a>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Booking Popup Modal -->
<div class="modal fade yn-booking-modal" id="quickBookingModal" tabindex="-1" aria-labelledby="quickBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header yn-booking-modal-header text-white d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-warning text-dark rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-calendar2-check-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="quickBookingModalLabel">Quick Tour Booking</h5>
                        <small class="text-white-50">15-minute seat hold protection & transparent pricing</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <form id="quickBookingForm" action="<?= url('/booking/checkout') ?>" method="GET">
                    <!-- Step 1: Package Selector -->
                    <div class="mb-3">
                        <label for="quickBookingPackageSelect" class="form-label fw-bold text-dark small d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-compass text-teal me-1"></i> 1. Select Tour Package *</span>
                            <span class="badge bg-light text-muted fw-normal border" id="packageCountBadge"><?= count($all_packages ?? []) ?> Packages Available</span>
                        </label>
                        <select name="package_id" id="quickBookingPackageSelect" class="form-select form-select-lg fw-semibold" required>
                            <?php foreach (($all_packages ?? []) as $pkg): ?>
                            <option value="<?= $pkg['id'] ?>">
                                <?= $pkg['is_domestic'] ? '🇮🇳 [Domestic]' : '✈️ [Intl]' ?> <?= e($pkg['title']) ?> &mdash; From <?= format_currency($pkg['price_adult']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Package Live Preview Card -->
                    <div class="yn-booking-preview-card mb-4" id="packagePreviewCard">
                        <img src="" alt="Package Thumbnail" id="previewPkgImage">
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                <span class="badge bg-secondary-subtle text-secondary small" id="previewPkgDest">Destination</span>
                                <span class="badge bg-primary-subtle text-primary small" id="previewPkgDuration">Duration</span>
                            </div>
                            <h6 class="fw-bold text-dark text-truncate mb-1" id="previewPkgTitle">Package Title</h6>
                            <div class="small text-muted">
                                Base Adult Price: <strong class="text-danger" id="previewPkgAdultRate">₹0</strong>
                                &bull; Child: <span class="text-muted" id="previewPkgChildRate">₹0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Departure Date Selector -->
                    <div class="mb-4">
                        <label for="quickBookingDepartureSelect" class="form-label fw-bold text-dark small d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-calendar-event text-primary me-1"></i> 2. Choose Departure Date *</span>
                            <span id="depLiveStatus" class="small fw-semibold text-success"></span>
                        </label>
                        <select name="departure_id" id="quickBookingDepartureSelect" class="form-select form-select-lg" required>
                            <!-- Populated dynamically by JavaScript -->
                        </select>

                        <!-- Alert if no departure open -->
                        <div id="noDeparturesAlert" class="alert alert-warning mt-2 d-none py-2 px-3 small">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div>
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                    <span>No online scheduled departures open for this package right now.</span>
                                </div>
                                <a href="#" id="customDateWhatsAppBtn" target="_blank" rel="noopener" class="btn btn-sm btn-success fw-bold">
                                    <i class="bi bi-whatsapp me-1"></i> Request Custom Date
                                </a>
                            </div>
                        </div>

                        <!-- Over-capacity seat notice -->
                        <div id="overCapacityAlert" class="alert alert-danger mt-2 d-none py-2 px-3 small">
                            <i class="bi bi-person-x-fill me-1"></i>
                            <span id="overCapacityMessage">Selected travelers exceed available seats.</span>
                        </div>
                    </div>

                    <!-- Step 3: Travelers Count -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark small mb-2">
                            <i class="bi bi-people text-warning me-1"></i> 3. Select Travelers
                        </label>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <div class="fw-bold small text-dark">Adults</div>
                                            <div class="text-muted" style="font-size: 0.72rem;">12+ years</div>
                                        </div>
                                        <div class="yn-stepper-group">
                                            <button type="button" class="yn-stepper-btn" id="adultMinusBtn" aria-label="Decrease Adults">&minus;</button>
                                            <input type="number" name="adult_count" id="quickAdultCount" class="yn-stepper-input" value="2" min="1" max="20" readonly>
                                            <button type="button" class="yn-stepper-btn" id="adultPlusBtn" aria-label="Increase Adults">&plus;</button>
                                        </div>
                                    </div>
                                    <div class="text-end small text-muted" id="adultFareSubtitle">2 × ₹0</div>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <div class="fw-bold small text-dark">Children</div>
                                            <div class="text-muted" style="font-size: 0.72rem;">2-11 years</div>
                                        </div>
                                        <div class="yn-stepper-group">
                                            <button type="button" class="yn-stepper-btn" id="childMinusBtn" aria-label="Decrease Children">&minus;</button>
                                            <input type="number" name="child_count" id="quickChildCount" class="yn-stepper-input" value="0" min="0" max="10" readonly>
                                            <button type="button" class="yn-stepper-btn" id="childPlusBtn" aria-label="Increase Children">&plus;</button>
                                        </div>
                                    </div>
                                    <div class="text-end small text-muted" id="childFareSubtitle">0 × ₹0</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Live Estimate Breakdown -->
                    <div class="yn-estimate-box mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Subtotal (Fare):</span>
                            <span class="fw-semibold text-dark" id="calcSubtotal">₹0</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Taxes & Govt GST (5%):</span>
                            <span class="text-muted" id="calcGst">₹0</span>
                        </div>
                        <hr class="my-2 text-muted">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold text-dark fs-6">Estimated Total</div>
                                <small class="text-muted" style="font-size: 0.72rem;">All inclusive &bull; No hidden charges</small>
                            </div>
                            <div class="text-end">
                                <div class="fs-4 fw-extrabold text-danger" id="calcGrandTotal">₹0</div>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-top border-light-subtle d-flex align-items-center gap-2 small text-success fw-semibold">
                            <i class="bi bi-tag-fill"></i>
                            <span>Use coupon <code class="text-dark bg-warning-subtle px-2 py-0.5 rounded fw-bold">WELCOME10</code> at checkout for up to ₹2,500 off!</span>
                        </div>
                    </div>

                    <!-- Trust & Guarantee Badges -->
                    <div class="d-flex flex-wrap gap-2 justify-content-center pt-1 mb-2">
                        <span class="yn-guarantee-pill"><i class="bi bi-shield-check text-success"></i> 15-Min Guaranteed Hold</span>
                        <span class="yn-guarantee-pill"><i class="bi bi-credit-card-2-front text-primary"></i> Razorpay & Pay Later</span>
                        <span class="yn-guarantee-pill"><i class="bi bi-headset text-warning"></i> 24/7 Concierge Support</span>
                    </div>

                    <div class="modal-footer px-0 pb-0 pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <a href="#" id="modalDirectWhatsApp" target="_blank" rel="noopener" class="text-success text-decoration-none small fw-semibold d-inline-flex align-items-center gap-1">
                            <i class="bi bi-whatsapp fs-6"></i> Chat with Tour Specialist
                        </a>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" id="quickBookingSubmitBtn" class="btn yn-btn-primary px-4 py-2 fw-bold d-inline-flex align-items-center gap-2">
                                <span>Proceed to Checkout</span>
                                <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Quick Booking Modal JavaScript -->
<script>
(function() {
    'use strict';

    function initQuickBooking() {
        var modalEl = document.getElementById('quickBookingModal');
        if (!modalEl) return;

        var packagesDataEl = document.getElementById('ynPackagesData');
        var departuresDataEl = document.getElementById('ynDeparturesData');
        var packagesMap = {};
        var departuresByPkg = {};

        try {
            if (packagesDataEl) packagesMap = JSON.parse(packagesDataEl.textContent || '{}');
            if (departuresDataEl) departuresByPkg = JSON.parse(departuresDataEl.textContent || '{}');
        } catch (e) {
            console.error('Error parsing booking data:', e);
        }

        var pkgSelect = document.getElementById('quickBookingPackageSelect');
        var depSelect = document.getElementById('quickBookingDepartureSelect');
        var adultInput = document.getElementById('quickAdultCount');
        var childInput = document.getElementById('quickChildCount');
        var adultMinusBtn = document.getElementById('adultMinusBtn');
        var adultPlusBtn = document.getElementById('adultPlusBtn');
        var childMinusBtn = document.getElementById('childMinusBtn');
        var childPlusBtn = document.getElementById('childPlusBtn');
        var adultFareSubtitle = document.getElementById('adultFareSubtitle');
        var childFareSubtitle = document.getElementById('childFareSubtitle');
        var calcSubtotal = document.getElementById('calcSubtotal');
        var calcGst = document.getElementById('calcGst');
        var calcGrandTotal = document.getElementById('calcGrandTotal');
        var depLiveStatus = document.getElementById('depLiveStatus');
        var noDeparturesAlert = document.getElementById('noDeparturesAlert');
        var overCapacityAlert = document.getElementById('overCapacityAlert');
        var overCapacityMessage = document.getElementById('overCapacityMessage');
        var customDateWhatsAppBtn = document.getElementById('customDateWhatsAppBtn');
        var modalDirectWhatsApp = document.getElementById('modalDirectWhatsApp');
        var submitBtn = document.getElementById('quickBookingSubmitBtn');

        var previewImage = document.getElementById('previewPkgImage');
        var previewTitle = document.getElementById('previewPkgTitle');
        var previewDest = document.getElementById('previewPkgDest');
        var previewDuration = document.getElementById('previewPkgDuration');
        var previewAdultRate = document.getElementById('previewPkgAdultRate');
        var previewChildRate = document.getElementById('previewPkgChildRate');

        var modalInstance = null;
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        }

        function formatINR(val) {
            return '₹' + Math.round(val).toLocaleString('en-IN');
        }

        function getSelectedPackage() {
            var pkgId = pkgSelect.value;
            return packagesMap[pkgId] || null;
        }

        function getSelectedDeparture() {
            var selectedOption = depSelect.options[depSelect.selectedIndex];
            if (!selectedOption || !selectedOption.value) return null;
            return {
                id: parseInt(selectedOption.value, 10),
                available_seats: parseInt(selectedOption.getAttribute('data-available') || '0', 10),
                adult_price: parseFloat(selectedOption.getAttribute('data-adult-price') || '0'),
                child_price: parseFloat(selectedOption.getAttribute('data-child-price') || '0'),
                date_text: selectedOption.getAttribute('data-date-text') || ''
            };
        }

        function updatePackagePreview() {
            var pkg = getSelectedPackage();
            if (!pkg) return;

            if (previewImage) previewImage.src = pkg.featured_image || 'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?w=300';
            if (previewTitle) previewTitle.textContent = pkg.title;
            if (previewDest) previewDest.textContent = pkg.destination_name || (pkg.is_domestic ? 'Domestic' : 'International');
            if (previewDuration) previewDuration.textContent = pkg.duration_days + 'D / ' + pkg.duration_nights + 'N';
            if (previewAdultRate) previewAdultRate.textContent = formatINR(pkg.price_adult);
            if (previewChildRate) previewChildRate.textContent = formatINR(pkg.price_child);
        }

        function populateDepartures() {
            var pkgId = pkgSelect.value;
            var departures = departuresByPkg[pkgId] || [];

            depSelect.innerHTML = '';
            overCapacityAlert.classList.add('d-none');

            if (departures.length === 0) {
                depSelect.classList.add('d-none');
                noDeparturesAlert.classList.remove('d-none');
                depLiveStatus.textContent = '';
                submitBtn.disabled = true;

                var pkg = getSelectedPackage();
                var waMsg = encodeURIComponent('Hi YatraNest, I would like to inquire about customized travel dates for: ' + (pkg ? pkg.title : 'this tour'));
                customDateWhatsAppBtn.href = 'https://api.whatsapp.com/send?phone=916000595647&text=' + waMsg;
                return;
            }

            depSelect.classList.remove('d-none');
            noDeparturesAlert.classList.add('d-none');
            submitBtn.disabled = false;

            var defaultSelected = false;
            departures.forEach(function(dep, idx) {
                var opt = document.createElement('option');
                opt.value = dep.id;
                opt.setAttribute('data-available', dep.available_seats);
                opt.setAttribute('data-adult-price', dep.adult_price !== null ? dep.adult_price : (packagesMap[pkgId] ? packagesMap[pkgId].price_adult : 0));
                opt.setAttribute('data-child-price', dep.child_price !== null ? dep.child_price : (packagesMap[pkgId] ? packagesMap[pkgId].price_child : 0));
                opt.setAttribute('data-date-text', dep.formatted_date);

                if (dep.available_seats <= 0) {
                    opt.disabled = true;
                    opt.textContent = '📅 ' + dep.formatted_date + ' [SOLD OUT]';
                } else {
                    opt.textContent = '📅 ' + dep.formatted_date + ' (' + dep.available_seats + ' seats available)';
                    if (!defaultSelected) {
                        opt.selected = true;
                        defaultSelected = true;
                    }
                }
                depSelect.appendChild(opt);
            });

            if (!defaultSelected && depSelect.options.length > 0) {
                depSelect.options[0].selected = true;
            }
        }

        function recalculate() {
            var pkg = getSelectedPackage();
            var dep = getSelectedDeparture();

            var adults = parseInt(adultInput.value, 10) || 1;
            var children = parseInt(childInput.value, 10) || 0;

            // Enforce bounds
            if (adults < 1) adults = 1;
            if (adults > 20) adults = 20;
            if (children < 0) children = 0;
            if (children > 10) children = 10;

            adultInput.value = adults;
            childInput.value = children;

            adultMinusBtn.disabled = (adults <= 1);
            adultPlusBtn.disabled = (adults >= 20);
            childMinusBtn.disabled = (children <= 0);
            childPlusBtn.disabled = (children >= 10);

            var adultPrice = (dep && dep.adult_price > 0) ? dep.adult_price : (pkg ? pkg.price_adult : 0);
            var childPrice = (dep && dep.child_price > 0) ? dep.child_price : (pkg ? pkg.price_child : 0);

            var adultTotal = adults * adultPrice;
            var childTotal = children * childPrice;
            var subtotal = adultTotal + childTotal;
            var gst = subtotal * 0.05;
            var grandTotal = subtotal + gst;

            if (adultFareSubtitle) adultFareSubtitle.textContent = adults + ' × ' + formatINR(adultPrice) + ' = ' + formatINR(adultTotal);
            if (childFareSubtitle) childFareSubtitle.textContent = children + ' × ' + formatINR(childPrice) + ' = ' + formatINR(childTotal);
            if (calcSubtotal) calcSubtotal.textContent = formatINR(subtotal);
            if (calcGst) calcGst.textContent = formatINR(gst);
            if (calcGrandTotal) calcGrandTotal.textContent = formatINR(grandTotal);

            // Check seat inventory capacity
            if (dep) {
                var totalPax = adults + children;
                var seatsLeft = dep.available_seats;
                depLiveStatus.textContent = seatsLeft + ' seats left on selected date';

                if (totalPax > seatsLeft) {
                    overCapacityAlert.classList.remove('d-none');
                    overCapacityMessage.textContent = 'Only ' + seatsLeft + ' seat' + (seatsLeft === 1 ? '' : 's') + ' available on this date. Please adjust travelers (' + totalPax + ' selected) or pick another departure.';
                    submitBtn.disabled = true;
                } else {
                    overCapacityAlert.classList.add('d-none');
                    submitBtn.disabled = false;
                }
            } else {
                depLiveStatus.textContent = '';
            }

            // Update WhatsApp Link
            if (modalDirectWhatsApp && pkg) {
                var dateText = dep ? dep.date_text : 'Upcoming departure';
                var text = 'Hi YatraNest, I would like to book ' + pkg.title + ' on ' + dateText + ' for ' + adults + ' Adults' + (children > 0 ? ' & ' + children + ' Children' : '') + '. Est Total: ' + formatINR(grandTotal);
                modalDirectWhatsApp.href = 'https://api.whatsapp.com/send?phone=916000595647&text=' + encodeURIComponent(text);
            }
        }

        // Stepper Event Listeners
        adultMinusBtn.addEventListener('click', function() {
            var v = parseInt(adultInput.value, 10) || 1;
            if (v > 1) {
                adultInput.value = v - 1;
                recalculate();
            }
        });

        adultPlusBtn.addEventListener('click', function() {
            var v = parseInt(adultInput.value, 10) || 1;
            if (v < 20) {
                adultInput.value = v + 1;
                recalculate();
            }
        });

        childMinusBtn.addEventListener('click', function() {
            var v = parseInt(childInput.value, 10) || 0;
            if (v > 0) {
                childInput.value = v - 1;
                recalculate();
            }
        });

        childPlusBtn.addEventListener('click', function() {
            var v = parseInt(childInput.value, 10) || 0;
            if (v < 10) {
                childInput.value = v + 1;
                recalculate();
            }
        });

        pkgSelect.addEventListener('change', function() {
            updatePackagePreview();
            populateDepartures();
            recalculate();
        });

        depSelect.addEventListener('change', function() {
            recalculate();
        });

        // Trigger Buttons click listener across the page
        document.querySelectorAll('.btn-open-booking-modal').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var pkgId = this.getAttribute('data-package-id');

                if (pkgId && packagesMap[pkgId]) {
                    pkgSelect.value = pkgId;
                } else if (pkgSelect.options.length > 0) {
                    // Default to current or first
                    if (!pkgSelect.value) {
                        pkgSelect.selectedIndex = 0;
                    }
                }

                updatePackagePreview();
                populateDepartures();
                recalculate();

                if (modalInstance) {
                    modalInstance.show();
                } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    new bootstrap.Modal(modalEl).show();
                }
            });
        });

        // Initial setup for the first package
        updatePackagePreview();
        populateDepartures();
        recalculate();
    }

    // Initialize Request A Travel Plan Modal (Matches uploaded screenshot)
    function initTravelPlanModal() {
        var planModalEl = document.getElementById('requestTravelPlanModal');
        if (!planModalEl) return;

        var planForm = document.getElementById('travelPlanForm');
        var planFormContainer = document.getElementById('travelPlanFormContainer');
        var planSuccess = document.getElementById('travelPlanSuccess');
        var planError = document.getElementById('planFormError');
        var planSubmitBtn = document.getElementById('planSubmitBtn');
        var planSuccessMsg = document.getElementById('planSuccessMessage');
        var planSuccessWA = document.getElementById('planSuccessWhatsApp');
        var phoneInput = document.getElementById('planPhoneInput');
        var destInput = document.getElementById('planDestinationInput');

        var planModalInstance = null;
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            planModalInstance = bootstrap.Modal.getOrCreateInstance(planModalEl);
        }

        // Phone input handling to keep +91 prefix intact
        if (phoneInput) {
            phoneInput.addEventListener('focus', function() {
                if (!this.value || this.value.trim() === '') {
                    this.value = '+91 ';
                }
            });
            phoneInput.addEventListener('input', function() {
                if (!this.value.startsWith('+91')) {
                    var raw = this.value.replace(/^\+91\s*/, '').replace(/[^\d\s]/g, '');
                    this.value = '+91 ' + raw;
                }
            });
        }

        // Handle buttons with .btn-open-plan-modal across the page
        document.querySelectorAll('.btn-open-plan-modal').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var destName = this.getAttribute('data-package-dest') || this.getAttribute('data-package-title') || '';
                if (destInput && destName) {
                    destInput.value = destName;
                }

                // Reset view if already submitted previously
                if (planFormContainer && planSuccess) {
                    planFormContainer.classList.remove('d-none');
                    planSuccess.classList.add('d-none');
                }
                if (planError) {
                    planError.classList.add('d-none');
                }

                if (planModalInstance) {
                    planModalInstance.show();
                } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    new bootstrap.Modal(planModalEl).show();
                }
            });
        });

        // AJAX Form Submission
        if (planForm) {
            planForm.addEventListener('submit', function(e) {
                e.preventDefault();
                if (planError) planError.classList.add('d-none');
                if (planSubmitBtn) {
                    planSubmitBtn.disabled = true;
                    planSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Submitting...';
                }

                var formData = new FormData(planForm);

                fetch('<?= url('/travel-plan/request') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(function(res) {
                    return res.json();
                })
                .then(function(data) {
                    if (planSubmitBtn) {
                        planSubmitBtn.disabled = false;
                        planSubmitBtn.innerHTML = '<span>Plan My Holidays</span>';
                    }

                    if (data.success) {
                        planFormContainer.classList.add('d-none');
                        planSuccess.classList.remove('d-none');

                        var name = data.data ? data.data.name : '';
                        var dest = data.data ? data.data.destination : '';
                        var phone = data.data ? data.data.phone : '';
                        var city = data.data ? data.data.current_city : '';
                        var date = data.data ? data.data.travel_date : '';

                        if (planSuccessMsg) {
                            planSuccessMsg.innerHTML = 'Thank you, <strong>' + (name || 'Traveler') + '</strong>! Your holiday request for <strong>' + (dest || 'your destination') + '</strong> has been received.<br><span class="text-muted small">Our holiday specialist is preparing your personalized itinerary and will contact you at <strong>' + (phone || '') + '</strong> shortly.</span>';
                        }

                        if (planSuccessWA) {
                            var waText = 'Hi YatraNest, I just submitted a travel plan request for ' + dest + (city ? ' from ' + city : '') + (date ? ' on ' + date : '') + '. My name is ' + name + '.';
                            planSuccessWA.href = 'https://api.whatsapp.com/send?phone=916000595647&text=' + encodeURIComponent(waText);
                        }
                    } else {
                        if (planError) {
                            planError.textContent = data.message || 'Please check all required fields and try again.';
                            planError.classList.remove('d-none');
                        }
                    }
                })
                .catch(function(err) {
                    console.error('Travel plan request error:', err);
                    if (planSubmitBtn) {
                        planSubmitBtn.disabled = false;
                        planSubmitBtn.innerHTML = '<span>Plan My Holidays</span>';
                    }
                    if (planError) {
                        planError.textContent = 'A network error occurred. Please try again or chat with our team on WhatsApp.';
                        planError.classList.remove('d-none');
                    }
                });
            });
        }

        // Auto-open Request A Travel Plan popup when website opens (1-second delay for smooth page render)
        setTimeout(function() {
            try {
                if (planModalInstance) {
                    planModalInstance.show();
                } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    var m = bootstrap.Modal.getOrCreateInstance(planModalEl);
                    m.show();
                }
            } catch (err) {
                console.warn('Auto-open plan modal error:', err);
            }
        }, 1000);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initQuickBooking();
            initTravelPlanModal();
        });
    } else {
        initQuickBooking();
        initTravelPlanModal();
    }
})();
</script>

