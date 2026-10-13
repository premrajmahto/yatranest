<div class="bg-light py-5 mb-5 border-bottom">
    <div class="container text-center">
        <h1 class="fw-extrabold display-5 text-dark mb-2">Explore Destinations</h1>
        <p class="text-muted lead mx-auto" style="max-width: 650px;">
            Discover incredible domestic escapes across India and premier international gateways.
        </p>
    </div>
</div>

<div class="container mb-5 pb-5">
    <!-- Filter Search Form -->
    <div class="card p-3 border-0 shadow-sm mb-4 bg-white rounded-3">
        <form action="<?= url('/destinations') ?>" method="GET" class="row g-3 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search by city, state or country..." value="<?= e($search ?? '') ?>">
                </div>
            </div>
            <div class="col-md-4">
                <select name="country" class="form-select">
                    <option value="">All Countries</option>
                    <?php foreach ($countries as $c): ?>
                    <option value="<?= e($c) ?>" <?= ($selectedCountry ?? '') === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn yn-btn-primary w-100">Filter Destinations</button>
            </div>
        </form>
    </div>

    <!-- Destinations Grid -->
    <?php if (empty($destinations)): ?>
    <div class="empty-state">
        <i class="bi bi-geo-alt"></i>
        <h4>No Destinations Found</h4>
        <p>Try searching with another keyword or country filter.</p>
        <a href="<?= url('/destinations') ?>" class="btn yn-btn-outline btn-sm">Clear Filters</a>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($destinations as $dest): ?>
        <div class="col-lg-3 col-md-6">
            <a href="<?= url('/destinations/' . $dest['slug']) ?>" class="text-decoration-none">
                <div class="dest-card">
                    <img src="<?= e($dest['image_url']) ?>" alt="<?= e($dest['name']) ?>" loading="lazy">
                    <div class="dest-card-gradient">
                        <span class="badge bg-warning text-dark align-self-start mb-2 px-2 py-1 small fw-bold">
                            <?= (int)($dest['package_count'] ?? 0) ?> Packages
                        </span>
                        <h4 class="fw-bold mb-1 text-white"><?= e($dest['name']) ?></h4>
                        <span class="small text-white-50"><i class="bi bi-geo-alt-fill me-1"></i><?= e($dest['country']) ?></span>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
