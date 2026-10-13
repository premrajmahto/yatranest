<?php
$isEdit = !empty($package['id']);
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= url('/admin/packages') ?>" class="text-muted small text-decoration-none mb-1 d-inline-block">
            <i class="bi bi-arrow-left me-1"></i> Back to Packages
        </a>
        <h3 class="fw-bold text-dark m-0"><?= $isEdit ? 'Edit Tour Package' : 'Create New Tour Package' ?></h3>
    </div>
</div>

<form action="<?= $isEdit ? url('/admin/packages/update/' . $package['id']) : url('/admin/packages/store') ?>" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="row g-4">
        <!-- Main Form Details -->
        <div class="col-lg-8">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5 class="admin-card-title">Basic Information</h5>
                </div>
                <div class="admin-card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold text-dark">Package Title *</label>
                            <input type="text" name="title" class="form-control" required value="<?= e($package['title'] ?? old('title')) ?>" placeholder="e.g. Kashmir Paradise: Srinagar, Gulmarg & Pahalgam">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">URL Slug *</label>
                            <input type="text" name="slug" class="form-control" required value="<?= e($package['slug'] ?? old('slug')) ?>" placeholder="kashmir-paradise">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Destination *</label>
                            <select name="destination_id" class="form-select" required>
                                <option value="">-- Choose Destination --</option>
                                <?php foreach ($destinations as $d): ?>
                                <option value="<?= $d['id'] ?>" <?= ($package['destination_id'] ?? old('destination_id')) == $d['id'] ? 'selected' : '' ?>>
                                    <?= e($d['name']) ?> (<?= e($d['country']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Category / Travel Style *</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- Choose Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($package['category_id'] ?? old('category_id')) == $cat['id'] ? 'selected' : '' ?>>
                                    <?= e($cat['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold text-dark">Summary / Catchphrase</label>
                            <textarea name="summary" class="form-control" rows="2" placeholder="Short description for listing cards"><?= e($package['summary'] ?? old('summary')) ?></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold text-dark">Full Package Description *</label>
                            <textarea name="description" class="form-control" rows="6" required placeholder="Detailed tour background..."><?= e($package['description'] ?? old('description')) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pricing & Capacity -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5 class="admin-card-title">Pricing & Capacity</h5>
                </div>
                <div class="admin-card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">Adult Price (₹) *</label>
                            <input type="number" step="0.01" name="price_adult" class="form-control" required value="<?= e($package['price_adult'] ?? old('price_adult')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">Child Price (₹)</label>
                            <input type="number" step="0.01" name="price_child" class="form-control" value="<?= e($package['price_child'] ?? old('price_child', '0')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">Infant Price (₹)</label>
                            <input type="number" step="0.01" name="price_infant" class="form-control" value="<?= e($package['price_infant'] ?? old('price_infant', '0')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">Duration Days *</label>
                            <input type="number" name="duration_days" class="form-control" required min="1" value="<?= e($package['duration_days'] ?? old('duration_days', '5')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">Duration Nights *</label>
                            <input type="number" name="duration_nights" class="form-control" required min="0" value="<?= e($package['duration_nights'] ?? old('duration_nights', '4')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">Max Group Size (Pax)</label>
                            <input type="number" name="group_size_max" class="form-control" value="<?= e($package['group_size_max'] ?? old('group_size_max', '20')) ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inclusions, Exclusions, Hotel, Policies -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5 class="admin-card-title">Details & Policies</h5>
                </div>
                <div class="admin-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Inclusions</label>
                            <textarea name="inclusions" class="form-control" rows="4" placeholder="• Hotel stay&#10;• Daily Breakfast&#10;• AC cab transfers"><?= e($package['inclusions'] ?? old('inclusions')) ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Exclusions</label>
                            <textarea name="exclusions" class="form-control" rows="4" placeholder="• Airfare&#10;• Personal expenses&#10;• Optional adventure activities"><?= e($package['exclusions'] ?? old('exclusions')) ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Hotel / Stay Details</label>
                            <textarea name="hotel_details" class="form-control" rows="3"><?= e($package['hotel_details'] ?? old('hotel_details')) ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Cancellation Policy</label>
                            <textarea name="cancellation_policy_text" class="form-control" rows="3"><?= e($package['cancellation_policy_text'] ?? old('cancellation_policy_text')) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Options: Status, Image, SEO -->
        <div class="col-lg-4">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5 class="admin-card-title">Publish Settings</h5>
                </div>
                <div class="admin-card-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="published" <?= ($package['status'] ?? old('status')) === 'published' ? 'selected' : '' ?>>Published (Live)</option>
                            <option value="draft" <?= ($package['status'] ?? old('status')) === 'draft' ? 'selected' : '' ?>>Draft (Hidden)</option>
                            <option value="archived" <?= ($package['status'] ?? old('status')) === 'archived' ? 'selected' : '' ?>>Archived</option>
                        </select>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="isFeaturedCheck" <?= !empty($package['is_featured']) ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-semibold" for="isFeaturedCheck">Show on Featured Home List</label>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_domestic" value="1" id="isDomesticCheck" <?= !isset($package['is_domestic']) || $package['is_domestic'] ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-semibold" for="isDomesticCheck">Domestic Tour (India)</label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Featured Image URL</label>
                        <input type="text" name="featured_image" class="form-control form-control-sm mb-2" value="<?= e($package['featured_image'] ?? old('featured_image')) ?>" placeholder="https://images.unsplash.com/...">
                        <?php if (!empty($package['featured_image'])): ?>
                        <img src="<?= e($package['featured_image']) ?>" alt="" class="w-100 rounded-3 object-fit-cover mt-2" style="height: 140px;">
                        <?php endif; ?>
                    </div>

                    <hr>

                    <button type="submit" class="btn btn-primary w-100 fw-bold">
                        <?= $isEdit ? 'Save Changes' : 'Create Package' ?>
                    </button>
                </div>
            </div>

            <!-- SEO Settings -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5 class="admin-card-title">SEO Metadata</h5>
                </div>
                <div class="admin-card-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">SEO Page Title</label>
                        <input type="text" name="seo_title" class="form-control form-control-sm" value="<?= e($package['seo_title'] ?? old('seo_title')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Meta Description</label>
                        <textarea name="seo_meta_description" class="form-control form-control-sm" rows="3"><?= e($package['seo_meta_description'] ?? old('seo_meta_description')) ?></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
