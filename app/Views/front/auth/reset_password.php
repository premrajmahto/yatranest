<div class="auth-card">
    <div class="text-center mb-4">
        <h3 class="fw-bold text-dark">Reset Password</h3>
        <p class="text-muted small">Choose a strong new password for your account.</p>
    </div>

    <form action="<?= url('/reset-password') ?>" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <input type="hidden" name="email" value="<?= e($email) ?>">

        <div class="mb-3">
            <label class="form-label small fw-semibold text-dark">New Password</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-lock text-muted"></i></span>
                <input type="password" name="password" class="form-control border-start-0" required placeholder="Minimum 6 characters">
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label small fw-semibold text-dark">Confirm New Password</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-shield-lock text-muted"></i></span>
                <input type="password" name="password_confirmation" class="form-control border-start-0" required placeholder="Re-enter new password">
            </div>
        </div>

        <button type="submit" class="btn yn-btn-primary w-100 py-2 fw-semibold mb-3">
            Update Password
        </button>

        <div class="text-center small text-muted">
            <a href="<?= url('/login') ?>" class="text-secondary fw-bold text-decoration-none">Back to Login</a>
        </div>
    </form>
</div>
