<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark m-0">Destination Management</h3>
        <p class="text-muted small m-0">Manage holiday travel destinations, country listings, and descriptions.</p>
    </div>
    <a href="<?= url('/admin/destinations/create') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Add Destination
    </a>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table mb-0 align-middle">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Country / State</th>
                    <th>Featured</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($destinations)): ?>
                <tr><td colspan="6" class="text-center py-4 text-muted">No destinations found.</td></tr>
                <?php else: ?>
                <?php foreach ($destinations as $d): ?>
                <tr>
                    <td style="width: 70px;">
                        <img src="<?= e($d['image_url']) ?>" alt="" class="rounded-2 object-fit-cover" style="width: 55px; height: 42px;">
                    </td>
                    <td>
                        <strong class="text-dark d-block"><?= e($d['name']) ?></strong>
                        <span class="small text-muted font-monospace"><?= e($d['slug']) ?></span>
                    </td>
                    <td class="small">
                        <?= e($d['city'] ? $d['city'] . ', ' : '') ?><?= e($d['country']) ?>
                    </td>
                    <td>
                        <span class="badge <?= $d['is_featured'] ? 'bg-warning text-dark' : 'bg-light text-muted border' ?>">
                            <?= $d['is_featured'] ? 'Featured' : 'Standard' ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge <?= $d['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>" style="font-size: 0.72rem;">
                            <?= strtoupper($d['status']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="<?= url('/destinations/' . $d['slug']) ?>" target="_blank" class="btn btn-light border" title="Preview">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                            <a href="<?= url('/admin/destinations/edit/' . $d['id']) ?>" class="btn btn-light border" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="<?= url('/admin/destinations/delete/' . $d['id']) ?>" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this destination?');">
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
