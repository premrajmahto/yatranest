<div class="auth-card">
    <div class="text-center mb-4">
        <h3 class="fw-bold text-dark">Forgot Password</h3>
        <p class="text-muted small">Enter your account email to receive a secure password reset link.</p>
    </div>

    <form action="<?= url('/forgot-password') ?>" method="POST">
        <?= csrf_field() ?>

        <div class="mb-4">
            <label class="form-label small fw-semibold text-dark">Account Email</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                <input type="email" name="email" class="form-control border-start-0" required placeholder="name@example.com" value="<?= e(old('email')) ?>">
            </div>
        </div>

        <button type="submit" class="btn yn-btn-primary w-100 py-2 fw-semibold mb-3">
            Send Reset Instructions
        </button>

        <div class="text-center small text-muted">
            Remembered your password? <a href="<?= url('/login') ?>" class="text-secondary fw-bold text-decoration-none">Sign In</a>
        </div>
    </form>
</div>
