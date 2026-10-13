<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark m-0">Departures & Seat Inventory</h3>
        <p class="text-muted small m-0">Schedule tour departure batches, set seat capacities, and monitor remaining availability.</p>
    </div>
    <a href="<?= url('/admin/departures/create') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-calendar-plus me-1"></i> Add Departure Batch
    </a>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table mb-0 align-middle">
            <thead>
                <tr>
                    <th>Tour Package</th>
                    <th>Departure Date</th>
                    <th>Return Date</th>
                    <th>Total Seats</th>
                    <th>Available Seats</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($departures)): ?>
                <tr><td colspan="7" class="text-center py-4 text-muted">No departures scheduled.</td></tr>
                <?php else: ?>
                <?php foreach ($departures as $dep): ?>
                <tr>
                    <td>
                        <strong class="text-dark small d-block"><?= e($dep['package_title']) ?></strong>
                        <span class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-geo-alt me-1"></i><?= e($dep['destination_name']) ?></span>
                    </td>
                    <td class="small fw-semibold"><?= date('d M Y', strtotime($dep['departure_date'])) ?></td>
                    <td class="small text-muted"><?= date('d M Y', strtotime($dep['return_date'])) ?></td>
                    <td class="small"><?= (int)$dep['total_seats'] ?></td>
                    <td>
                        <span class="badge <?= $dep['available_seats'] > 5 ? 'bg-success' : ($dep['available_seats'] > 0 ? 'bg-warning text-dark' : 'bg-danger') ?>">
                            <?= (int)$dep['available_seats'] ?> seats left
                        </span>
                    </td>
                    <td>
                        <span class="badge <?= $dep['status'] === 'open' ? 'bg-primary' : ($dep['status'] === 'closed' ? 'bg-secondary' : 'bg-danger') ?>" style="font-size: 0.72rem;">
                            <?= strtoupper($dep['status']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="<?= url('/admin/departures/edit/' . $dep['id']) ?>" class="btn btn-light border" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="<?= url('/admin/departures/delete/' . $dep['id']) ?>" class="d-inline" onsubmit="return confirm('Delete this departure schedule?');">
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
