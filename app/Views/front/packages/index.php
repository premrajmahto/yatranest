<div class="bg-light py-5 mb-4 border-bottom">
    <div class="container text-center">
        <h1 class="fw-extrabold display-5 text-dark mb-2">Tour Packages</h1>
        <p class="text-muted lead mx-auto" style="max-width: 650px;">
            Find and book handcrafted vacation packages with verified itineraries and instant confirmation.
        </p>
    </div>
</div>

<div class="container mb-5">
    <div class="row g-4">
        <!-- Sidebar Filters -->
        <div class="col-lg-3">
            <div class="card p-4 border-0 shadow-sm rounded-4 bg-white sticky-top" style="top: 85px; z-index: 10;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold m-0"><i class="bi bi-funnel text-secondary me-2"></i>Filters</h5>
                    <a href="<?= url('/packages') ?>" class="text-muted small text-decoration-none">Reset All</a>
                </div>

                <form action="<?= url('/packages') ?>" method="GET">
                    <!-- Keyword Search -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Keyword</label>
                        <input type="text" name="keyword" class="form-control form-control-sm" placeholder="e.g. Kashmir, Safari..." value="<?= e($filters['keyword'] ?? '') ?>">
                    </div>

                    <!-- Destination -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Destination</label>
                        <select name="destination_id" class="form-select form-select-sm">
                            <option value="">All Destinations</option>
                            <?php foreach ($destinations as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= ($filters['destination_id'] ?? '') == $d['id'] ? 'selected' : '' ?>>
                                <?= e($d['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Category -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Travel Type</label>
                        <select name="category_id" class="form-select form-select-sm">
                            <option value="">All Travel Types</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($filters['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Domestic / International -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Scope</label>
                        <select name="is_domestic" class="form-select form-select-sm">
                            <option value="">Domestic & International</option>
                            <option value="1" <?= ($filters['is_domestic'] ?? '') === '1' ? 'selected' : '' ?>>Domestic Only</option>
                            <option value="0" <?= ($filters['is_domestic'] ?? '') === '0' ? 'selected' : '' ?>>International Only</option>
                        </select>
                    </div>

                    <!-- Duration -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Duration</label>
                        <select name="duration" class="form-select form-select-sm">
                            <option value="">Any Duration</option>
                            <option value="1-3" <?= ($filters['duration'] ?? '') === '1-3' ? 'selected' : '' ?>>1 - 3 Days</option>
                            <option value="4-7" <?= ($filters['duration'] ?? '') === '4-7' ? 'selected' : '' ?>>4 - 7 Days</option>
                            <option value="8+" <?= ($filters['duration'] ?? '') === '8+' ? 'selected' : '' ?>>8+ Days</option>
                        </select>
                    </div>

                    <!-- Price Range -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Max Budget (₹)</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" name="min_price" class="form-control form-control-sm" placeholder="Min" value="<?= e($filters['min_price'] ?? '') ?>">
                            </div>
                            <div class="col-6">
                                <input type="number" name="max_price" class="form-control form-control-sm" placeholder="Max" value="<?= e($filters['max_price'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Sort -->
                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-muted">Sort By</label>
                        <select name="sort" class="form-select form-select-sm">
                            <option value="popular" <?= ($sort ?? '') === 'popular' ? 'selected' : '' ?>>Most Popular</option>
                            <option value="price_asc" <?= ($sort ?? '') === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="price_desc" <?= ($sort ?? '') === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                            <option value="duration" <?= ($sort ?? '') === 'duration' ? 'selected' : '' ?>>Trip Duration</option>
                            <option value="rating" <?= ($sort ?? '') === 'rating' ? 'selected' : '' ?>>Customer Rating</option>
                            <option value="newest" <?= ($sort ?? '') === 'newest' ? 'selected' : '' ?>>Newest Added</option>
                        </select>
                    </div>

                    <button type="submit" class="btn yn-btn-primary w-100 btn-sm py-2">
                        Apply Filters
                    </button>
                </form>
            </div>
        </div>

        <!-- Package Results Grid -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <span class="text-muted small">
                    Showing <strong><?= count($packages) ?></strong> of <strong><?= (int)$total ?></strong> tour packages
                </span>
            </div>

            <?php if (empty($packages)): ?>
            <div class="empty-state bg-white rounded-4 shadow-sm p-5">
                <i class="bi bi-search"></i>
                <h4>No Tour Packages Found</h4>
                <p>We couldn't find any packages matching your filter criteria. Try expanding your search options.</p>
                <a href="<?= url('/packages') ?>" class="btn yn-btn-outline btn-sm">Clear All Filters</a>
            </div>
            <?php else: ?>
            <div class="row g-4">
                <?php foreach ($packages as $pkg): ?>
                <div class="col-md-6 col-xl-4">
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
                                    <i class="bi bi-star-fill"></i> <?= number_format((float)($pkg['avg_rating'] ?? 5.0), 1) ?>
                                </div>
                            </div>

                            <h5 class="card-title fw-bold text-dark mb-2">
                                <a href="<?= url('/packages/' . $pkg['slug']) ?>" class="text-dark text-decoration-none">
                                    <?= e($pkg['title']) ?>
                                </a>
                            </h5>

                            <p class="card-text text-muted small flex-grow-1 mb-3">
                                <?= e(mb_strimwidth($pkg['summary'] ?? '', 0, 85, '...')) ?>
                            </p>

                            <div class="d-flex align-items-center gap-3 text-muted small mb-3 pb-3 border-bottom">
                                <span><i class="bi bi-clock me-1 text-primary"></i> <?= (int)$pkg['duration_days'] ?>D / <?= (int)$pkg['duration_nights'] ?>N</span>
                                <span><i class="bi bi-tag me-1 text-secondary"></i> <?= e($pkg['category_name']) ?></span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <div>
                                    <span class="yn-price-tag"><?= format_currency($pkg['price_adult']) ?></span>
                                    <span class="text-muted small">/pax</span>
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
                                    <a href="<?= url('/packages/' . $pkg['slug']) ?>" class="btn yn-btn-primary btn-sm">
                                        Book Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($pages > 1): ?>
            <nav class="mt-5" aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                        <a class="page-link" href="<?= url('/packages?' . http_build_query(array_merge($filters, ['page' => $i, 'sort' => $sort]))) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>
</div>
