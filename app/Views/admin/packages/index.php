<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark m-0">Tour Packages Management</h3>
        <p class="text-muted small m-0">Create, edit, publish, and manage holiday packages and itineraries.</p>
    </div>
    <a href="<?= url('/admin/packages/create') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Add New Package
    </a>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table mb-0 align-middle">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Package Title</th>
                    <th>Destination</th>
                    <th>Duration</th>
                    <th>Base Price (Adult)</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($packages)): ?>
                <tr><td colspan="7" class="text-center py-4 text-muted">No tour packages found.</td></tr>
                <?php else: ?>
                <?php foreach ($packages as $pkg): ?>
                <tr>
                    <td style="width: 70px;">
                        <img src="<?= e($pkg['featured_image']) ?>" alt="" class="rounded-2 object-fit-cover" style="width: 55px; height: 42px;">
                    </td>
                    <td>
                        <strong class="text-dark d-block"><?= e($pkg['title']) ?></strong>
                        <span class="small text-muted font-monospace"><?= e($pkg['slug']) ?></span>
                    </td>
                    <td class="small">
                        <span class="badge bg-light text-dark border"><?= e($pkg['destination_name']) ?></span>
                    </td>
                    <td class="small"><?= (int)$pkg['duration_days'] ?>D / <?= (int)$pkg['duration_nights'] ?>N</td>
                    <td class="fw-bold small text-dark"><?= format_currency($pkg['price_adult']) ?></td>
                    <td>
                        <span class="badge <?= $pkg['status'] === 'published' ? 'bg-success' : 'bg-secondary' ?>" style="font-size: 0.72rem;">
                            <?= strtoupper($pkg['status']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="<?= url('/packages/' . $pkg['slug']) ?>" target="_blank" class="btn btn-light border" title="Preview Live">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                            <a href="<?= url('/admin/packages/edit/' . $pkg['id']) ?>" class="btn btn-light border" title="Edit Package">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="<?= url('/admin/packages/delete/' . $pkg['id']) ?>" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this package?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-light border text-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
