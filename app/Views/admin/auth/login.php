<div class="auth-card" style="border-top: 4px solid #008080;">
    <div class="text-center mb-4">
        <div class="d-inline-flex p-3 bg-light rounded-circle text-primary fs-3 mb-2">
            <i class="bi bi-shield-lock"></i>
        </div>
        <h3 class="fw-bold text-dark mb-1">Admin Portal</h3>
        <p class="text-muted small">Restricted access for authorized YatraNest managers.</p>
    </div>

    <form action="<?= url('/admin/login') ?>" method="POST">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label class="form-label small fw-semibold text-dark">Staff Email</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-person-badge text-muted"></i></span>
                <input type="email" name="email" class="form-control border-start-0" required placeholder="admin@yatranest.com" value="<?= e(old('email')) ?>">
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label small fw-semibold text-dark">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-lock text-muted"></i></span>
                <input type="password" name="password" class="form-control border-start-0" required placeholder="••••••••">
            </div>
        </div>

        <button type="submit" class="btn btn-dark w-100 py-2 fw-semibold mb-3">
            <i class="bi bi-box-arrow-in-right me-1"></i> Authenticate
        </button>

        <div class="text-center small text-muted">
            <a href="<?= url('/') ?>" class="text-secondary text-decoration-none"><i class="bi bi-arrow-left me-1"></i> Return to Public Site</a>
        </div>
    </form>
</div>
