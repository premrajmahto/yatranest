<?php
$isEdit = !empty($coupon['id']);
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= url('/admin/coupons') ?>" class="text-muted small text-decoration-none mb-1 d-inline-block">
            <i class="bi bi-arrow-left me-1"></i> Back to Coupons
        </a>
        <h3 class="fw-bold text-dark m-0"><?= $isEdit ? 'Edit Coupon' : 'Create New Coupon' ?></h3>
    </div>
</div>

<form action="<?= $isEdit ? url('/admin/coupons/update/' . $coupon['id']) : url('/admin/coupons/store') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="row g-4 justify-content-center">
        <div class="col-lg-8">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5 class="admin-card-title">Coupon Details</h5>
                </div>
                <div class="admin-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Coupon Code *</label>
                            <input type="text" name="code" class="form-control text-uppercase font-monospace" required value="<?= e($coupon['code'] ?? old('code')) ?>" placeholder="e.g. SUMMER15">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Discount Type *</label>
                            <select name="discount_type" class="form-select">
                                <option value="percentage" <?= ($coupon['discount_type'] ?? old('discount_type')) === 'percentage' ? 'selected' : '' ?>>Percentage (%)</option>
                                <option value="fixed" <?= ($coupon['discount_type'] ?? old('discount_type')) === 'fixed' ? 'selected' : '' ?>>Fixed Amount (₹)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">Discount Value *</label>
                            <input type="number" step="0.01" name="discount_value" class="form-control" required value="<?= e($coupon['discount_value'] ?? old('discount_value', '10')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">Min Booking Amount (₹)</label>
                            <input type="number" step="0.01" name="min_booking_amount" class="form-control" value="<?= e($coupon['min_booking_amount'] ?? old('min_booking_amount', '5000')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">Max Discount Cap (₹, optional)</label>
                            <input type="number" step="0.01" name="max_discount" class="form-control" value="<?= e($coupon['max_discount'] ?? old('max_discount')) ?>" placeholder="Leave blank for unlimited">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Start Date *</label>
                            <input type="date" name="start_date" class="form-control" required value="<?= e($coupon['start_date'] ?? old('start_date', date('Y-m-d'))) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Expiry Date *</label>
                            <input type="date" name="expiry_date" class="form-control" required value="<?= e($coupon['expiry_date'] ?? old('expiry_date', date('Y-m-d', strtotime('+3 months')))) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Overall Usage Limit</label>
                            <input type="number" name="usage_limit" class="form-control" value="<?= e($coupon['usage_limit'] ?? old('usage_limit', '100')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Per Customer Limit</label>
                            <input type="number" name="per_user_limit" class="form-control" value="<?= e($coupon['per_user_limit'] ?? old('per_user_limit', '1')) ?>">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="couponActiveCheck" <?= !isset($coupon['is_active']) || $coupon['is_active'] ? 'checked' : '' ?>>
                                <label class="form-check-label small fw-semibold" for="couponActiveCheck">Coupon Active (Redeemable)</label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <?= $isEdit ? 'Save Changes' : 'Create Coupon' ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
