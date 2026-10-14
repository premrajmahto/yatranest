<div class="position-relative text-white py-5" style="background: linear-gradient(180deg, rgba(13,35,58,0.7) 0%, rgba(13,35,58,0.9) 100%), url('<?= e($destination['image_url']) ?>') center/cover no-repeat; min-height: 280px;">
    <div class="container py-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-3">
                <li class="breadcrumb-item"><a href="<?= url('/') ?>" class="text-white-50 text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/destinations') ?>" class="text-white-50 text-decoration-none">Destinations</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page"><?= e($destination['name']) ?></li>
            </ol>
        </nav>
        <h1 class="display-4 fw-extrabold mb-2"><?= e($destination['name']) ?></h1>
        <p class="lead mb-0 text-white-50">
            <i class="bi bi-geo-alt-fill text-warning me-1"></i>
            <?= e($destination['city'] ? $destination['city'] . ', ' : '') ?><?= e($destination['state'] ? $destination['state'] . ', ' : '') ?><?= e($destination['country']) ?>
        </p>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4 mb-5">
        <div class="col-lg-8">
            <h3 class="fw-bold text-dark mb-3">About <?= e($destination['name']) ?></h3>
            <p class="text-secondary fs-5 leading-relaxed"><?= nl2br(e($destination['description'])) ?></p>
        </div>
        <div class="col-lg-4">
            <div class="card p-4 rounded-4 border-0 bg-light shadow-sm">
                <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-info-circle text-secondary me-2"></i>Destination Quick Facts</h5>
                <ul class="list-unstyled mb-0 d-flex flex-column gap-2 small">
                    <li class="d-flex justify-content-between border-bottom pb-2">
                        <span class="text-muted">Country:</span>
                        <strong class="text-dark"><?= e($destination['country']) ?></strong>
                    </li>
                    <li class="d-flex justify-content-between border-bottom pb-2">
                        <span class="text-muted">Best Known For:</span>
                        <strong class="text-dark">Scenic Landscapes & Holidays</strong>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Available Packages:</span>
                        <strong class="text-secondary"><?= count($packages) ?> Tour(s)</strong>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="border-top pt-5">
        <h3 class="fw-bold text-dark mb-4">Tour Packages in <?= e($destination['name']) ?></h3>

        <?php if (empty($packages)): ?>
        <div class="empty-state">
            <i class="bi bi-compass"></i>
            <h4>No Packages Currently Available</h4>
            <p>New curated packages for this destination will be published soon.</p>
            <a href="<?= url('/packages') ?>" class="btn yn-btn-primary btn-sm">Explore Other Packages</a>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($packages as $pkg): ?>
            <div class="col-lg-4 col-md-6">
                <div class="yn-card">
                    <div class="yn-card-img-wrapper">
                        <img src="<?= e($pkg['featured_image']) ?>" alt="<?= e($pkg['title']) ?>" loading="lazy">
                    </div>
                    <div class="card-body p-4 d-flex flex-column">
                        <span class="text-secondary small fw-bold text-uppercase mb-1"><?= e($destination['name']) ?></span>
                        <h5 class="card-title fw-bold text-dark mb-2">
                            <a href="<?= url('/packages/' . $pkg['slug']) ?>" class="text-dark text-decoration-none">
                                <?= e($pkg['title']) ?>
                            </a>
                        </h5>
                        <p class="text-muted small flex-grow-1"><?= e(mb_strimwidth($pkg['summary'] ?? '', 0, 90, '...')) ?></p>
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                            <div>
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
                                <a href="<?= url('/packages/' . $pkg['slug']) ?>" class="btn yn-btn-primary btn-sm">View Tour</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
