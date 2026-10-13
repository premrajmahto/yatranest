<div class="auth-card">
    <div class="text-center mb-4">
        <h3 class="fw-bold text-dark">Create Account</h3>
        <p class="text-muted small">Join YatraNest for seamless holiday reservations and exclusive travel perks.</p>
    </div>

    <form action="<?= url('/register') ?>" method="POST">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label class="form-label small fw-semibold text-dark">Full Name *</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-person text-muted"></i></span>
                <input type="text" name="name" class="form-control border-start-0" required placeholder="John Doe" value="<?= e(old('name')) ?>">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-semibold text-dark">Email Address *</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                <input type="email" name="email" class="form-control border-start-0" required placeholder="name@example.com" value="<?= e(old('email')) ?>">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-semibold text-dark">Mobile Number *</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-telephone text-muted"></i></span>
                <input type="tel" name="phone" class="form-control border-start-0" required placeholder="+91 98765 43210" value="<?= e(old('phone')) ?>">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-semibold text-dark">Password *</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-lock text-muted"></i></span>
                <input type="password" name="password" class="form-control border-start-0" required placeholder="Minimum 6 characters">
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label small fw-semibold text-dark">Confirm Password *</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-shield-lock text-muted"></i></span>
                <input type="password" name="password_confirmation" class="form-control border-start-0" required placeholder="Re-enter password">
            </div>
        </div>

        <button type="submit" class="btn yn-btn-primary w-100 py-2 fw-semibold mb-3">
            Create Customer Account
        </button>

        <div class="text-center small text-muted">
            Already registered? <a href="<?= url('/login') ?>" class="text-secondary fw-bold text-decoration-none">Sign In</a>
        </div>
    </form>
</div>
