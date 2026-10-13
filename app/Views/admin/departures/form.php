<?php
$isEdit = !empty($departure['id']);
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= url('/admin/departures') ?>" class="text-muted small text-decoration-none mb-1 d-inline-block">
            <i class="bi bi-arrow-left me-1"></i> Back to Departures
        </a>
        <h3 class="fw-bold text-dark m-0"><?= $isEdit ? 'Edit Departure Batch' : 'Create Departure Batch' ?></h3>
    </div>
</div>

<form action="<?= $isEdit ? url('/admin/departures/update/' . $departure['id']) : url('/admin/departures/store') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="row g-4 justify-content-center">
        <div class="col-lg-8">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5 class="admin-card-title">Schedule Information</h5>
                </div>
                <div class="admin-card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-dark">Tour Package *</label>
                            <select name="package_id" class="form-select" required>
                                <option value="">-- Select Package --</option>
                                <?php foreach ($packages as $pkg): ?>
                                <option value="<?= $pkg['id'] ?>" <?= ($departure['package_id'] ?? old('package_id')) == $pkg['id'] ? 'selected' : '' ?>>
                                    <?= e($pkg['title']) ?> (<?= (int)$pkg['duration_days'] ?> Days)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Departure Date *</label>
                            <input type="date" name="departure_date" class="form-control" required value="<?= e($departure['departure_date'] ?? old('departure_date')) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Return Date *</label>
                            <input type="date" name="return_date" class="form-control" required value="<?= e($departure['return_date'] ?? old('return_date')) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Total Capacity (Seats) *</label>
                            <input type="number" name="total_seats" class="form-control" required min="1" value="<?= e($departure['total_seats'] ?? old('total_seats', '20')) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Available Seats *</label>
                            <input type="number" name="available_seats" class="form-control" required min="0" value="<?= e($departure['available_seats'] ?? old('available_seats', '20')) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Adult Price Override (₹, optional)</label>
                            <input type="number" step="0.01" name="adult_price" class="form-control" value="<?= e($departure['adult_price'] ?? old('adult_price')) ?>" placeholder="Leave blank to use package base price">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Child Price Override (₹, optional)</label>
                            <input type="number" step="0.01" name="child_price" class="form-control" value="<?= e($departure['child_price'] ?? old('child_price')) ?>" placeholder="Leave blank to use package base price">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Status *</label>
                            <select name="status" class="form-select">
                                <option value="open" <?= ($departure['status'] ?? old('status')) === 'open' ? 'selected' : '' ?>>Open (Accepting Bookings)</option>
                                <option value="closed" <?= ($departure['status'] ?? old('status')) === 'closed' ? 'selected' : '' ?>>Closed (Departure Full)</option>
                                <option value="blocked" <?= ($departure['status'] ?? old('status')) === 'blocked' ? 'selected' : '' ?>>Blocked (Maintenance/Weather)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <?= $isEdit ? 'Save Changes' : 'Create Departure' ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
