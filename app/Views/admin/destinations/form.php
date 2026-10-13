<?php
$isEdit = !empty($destination['id']);
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= url('/admin/destinations') ?>" class="text-muted small text-decoration-none mb-1 d-inline-block">
            <i class="bi bi-arrow-left me-1"></i> Back to Destinations
        </a>
        <h3 class="fw-bold text-dark m-0"><?= $isEdit ? 'Edit Destination' : 'Add New Destination' ?></h3>
    </div>
</div>

<form action="<?= $isEdit ? url('/admin/destinations/update/' . $destination['id']) : url('/admin/destinations/store') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5 class="admin-card-title">Destination Details</h5>
                </div>
                <div class="admin-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Destination Name *</label>
                            <input type="text" name="name" class="form-control" required value="<?= e($destination['name'] ?? old('name')) ?>" placeholder="e.g. Kashmir">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">URL Slug *</label>
                            <input type="text" name="slug" class="form-control" required value="<?= e($destination['slug'] ?? old('slug')) ?>" placeholder="kashmir">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">Country *</label>
                            <input type="text" name="country" class="form-control" required value="<?= e($destination['country'] ?? old('country', 'India')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">State / Region</label>
                            <input type="text" name="state" class="form-control" value="<?= e($destination['state'] ?? old('state')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">City</label>
                            <input type="text" name="city" class="form-control" value="<?= e($destination['city'] ?? old('city')) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-dark">Description</label>
                            <textarea name="description" class="form-control" rows="4"><?= e($destination['description'] ?? old('description')) ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-dark">Image URL</label>
                            <input type="text" name="image_url" class="form-control" value="<?= e($destination['image_url'] ?? old('image_url')) ?>" placeholder="https://images.unsplash.com/...">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5 class="admin-card-title">Settings</h5>
                </div>
                <div class="admin-card-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="active" <?= ($destination['status'] ?? old('status')) === 'active' ? 'selected' : '' ?>>Active (Visible)</option>
                            <option value="inactive" <?= ($destination['status'] ?? old('status')) === 'inactive' ? 'selected' : '' ?>>Inactive (Hidden)</option>
                        </select>
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="destFeatured" <?= !empty($destination['is_featured']) ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-semibold" for="destFeatured">Featured on Homepage</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold">
                        <?= $isEdit ? 'Save Changes' : 'Create Destination' ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
