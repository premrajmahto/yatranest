<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark m-0">Traveler Reviews Moderation</h3>
        <p class="text-muted small m-0">Review, approve, or reject customer feedback submitted on packages.</p>
    </div>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table mb-0 align-middle">
            <thead>
                <tr>
                    <th>Package</th>
                    <th>User</th>
                    <th>Rating</th>
                    <th>Review Content</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reviews)): ?>
                <tr><td colspan="6" class="text-center py-4 text-muted">No customer reviews submitted.</td></tr>
                <?php else: ?>
                <?php foreach ($reviews as $rev): ?>
                <tr>
                    <td class="small fw-semibold"><?= e($rev['package_title']) ?></td>
                    <td class="small"><?= e($rev['user_name']) ?></td>
                    <td class="text-warning small">
                        <?php for ($i = 0; $i < $rev['rating']; $i++): ?><i class="bi bi-star-fill"></i><?php endfor; ?>
                    </td>
                    <td class="small text-secondary" style="max-width: 300px;">
                        <strong><?= e($rev['review_title']) ?></strong><br>
                        "<?= e($rev['review_text']) ?>"
                    </td>
                    <td>
                        <span class="badge <?= $rev['status'] === 'approved' ? 'bg-success' : ($rev['status'] === 'rejected' ? 'bg-danger' : 'bg-warning text-dark') ?>" style="font-size: 0.72rem;">
                            <?= strtoupper($rev['status']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <?php if ($rev['status'] !== 'approved'): ?>
                            <form method="POST" action="<?= url('/admin/content/reviews/status/' . $rev['id']) ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="status" value="approved">
                                <button type="submit" class="btn btn-sm btn-outline-success">Approve</button>
                            </form>
                            <?php endif; ?>

                            <?php if ($rev['status'] !== 'rejected'): ?>
                            <form method="POST" action="<?= url('/admin/content/reviews/status/' . $rev['id']) ?>" class="d-inline ms-1">
                                <?= csrf_field() ?>
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Reject</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
