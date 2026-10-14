<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark m-0">System & Platform Settings</h3>
        <p class="text-muted small m-0">Configure taxation rates, payment gateway credentials, email transport, and company branding.</p>
    </div>
</div>

<form action="<?= url('/admin/settings/update') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="row g-4">
        <!-- General & Company Settings -->
        <div class="col-lg-6">
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h5 class="admin-card-title"><i class="bi bi-building me-2 text-primary"></i>Company Information</h5>
                </div>
                <div class="admin-card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-dark">Company Legal Name</label>
                            <input type="text" name="company_name" class="form-control" value="<?= e($settings['company_name'] ?? 'YatraNest Holidays Pvt Ltd') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Official Contact Phone</label>
                            <input type="tel" name="company_phone" class="form-control" value="<?= e($settings['company_phone'] ?? '+91 98765 43210') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Official WhatsApp Number (Direct Contact)</label>
                            <input type="tel" name="company_whatsapp" class="form-control" placeholder="+91 98765 43210" value="<?= e($settings['company_whatsapp'] ?? ($settings['company_phone'] ?? '+91 98765 43210')) ?>">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-semibold text-dark">Official Support Email</label>
                            <input type="email" name="company_email" class="form-control" value="<?= e($settings['company_email'] ?? 'support@yatranest.com') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-dark">Registered Address</label>
                            <textarea name="company_address" class="form-control" rows="2"><?= e($settings['company_address'] ?? 'Suite 402, Travel Towers, MG Road, Bengaluru, India') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Company GSTIN Number</label>
                            <input type="text" name="company_gstin" class="form-control" value="<?= e($settings['company_gstin'] ?? '29ABCDE1234F1Z5') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking & Tax Configuration -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5 class="admin-card-title"><i class="bi bi-calculator me-2 text-secondary"></i>Booking & Tax Rules</h5>
                </div>
                <div class="admin-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">GST / Tax Rate (%)</label>
                            <input type="number" step="0.01" name="tax_rate" class="form-control" required value="<?= e($settings['tax_rate'] ?? '5.00') ?>">
                            <span class="text-muted small" style="font-size: 0.75rem;">Applied on taxable amount after discounts.</span>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Currency Symbol</label>
                            <input type="text" name="currency_symbol" class="form-control" value="<?= e($settings['currency_symbol'] ?? '₹') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Seat Hold Timeout (Minutes)</label>
                            <input type="number" name="hold_timeout_minutes" class="form-control" required min="5" max="60" value="<?= e($settings['hold_timeout_minutes'] ?? '15') ?>">
                            <span class="text-muted small" style="font-size: 0.75rem;">Reservation lock held during checkout.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Gateway & SMTP Settings -->
        <div class="col-lg-6">
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h5 class="admin-card-title"><i class="bi bi-credit-card me-2 text-success"></i>Razorpay Gateway Configuration</h5>
                </div>
                <div class="admin-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Gateway Mode</label>
                            <select name="razorpay_mode" class="form-select">
                                <option value="test" <?= ($settings['razorpay_mode'] ?? 'test') === 'test' ? 'selected' : '' ?>>Test Mode (Sandbox)</option>
                                <option value="live" <?= ($settings['razorpay_mode'] ?? '') === 'live' ? 'selected' : '' ?>>Live Production</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Razorpay Key ID</label>
                            <input type="text" name="razorpay_key_id" class="form-control font-monospace" value="<?= e($settings['razorpay_key_id'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-dark">Razorpay Key Secret</label>
                            <input type="password" name="razorpay_key_secret" class="form-control font-monospace" placeholder="••••••••" value="<?= e($settings['razorpay_key_secret'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-dark">Webhook Secret</label>
                            <input type="password" name="razorpay_webhook_secret" class="form-control font-monospace" placeholder="••••••••" value="<?= e($settings['razorpay_webhook_secret'] ?? '') ?>">
                            <span class="text-muted small" style="font-size: 0.75rem;">Webhook URL: <code><?= url('/api/webhook/razorpay') ?></code></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-header">
                    <h5 class="admin-card-title"><i class="bi bi-envelope-at me-2 text-warning"></i>SMTP Email Setup</h5>
                </div>
                <div class="admin-card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold text-dark">SMTP Host</label>
                            <input type="text" name="mail_host" class="form-control" value="<?= e($settings['mail_host'] ?? 'smtp.mailtrap.io') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">SMTP Port</label>
                            <input type="number" name="mail_port" class="form-control" value="<?= e($settings['mail_port'] ?? '2525') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Username</label>
                            <input type="text" name="mail_username" class="form-control" value="<?= e($settings['mail_username'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Password</label>
                            <input type="password" name="mail_password" class="form-control" placeholder="••••••••" value="<?= e($settings['mail_password'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold">
                    <i class="bi bi-save me-1"></i> Save Platform Settings
                </button>
            </div>
        </div>
    </div>
</form>
