<div class="auth-card">
    <div class="text-center mb-4">
        <h3 class="fw-bold text-dark">Welcome Back</h3>
        <p class="text-muted small">Sign in to manage your bookings and view trip itineraries.</p>
    </div>

    <form action="<?= url('/login') ?>" method="POST">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label class="form-label small fw-semibold text-dark">Email Address</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                <input type="email" name="email" class="form-control border-start-0" required placeholder="name@example.com" value="<?= e(old('email')) ?>">
            </div>
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <label class="form-label small fw-semibold text-dark">Password</label>
                <a href="<?= url('/forgot-password') ?>" class="text-secondary small text-decoration-none">Forgot Password?</a>
            </div>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-lock text-muted"></i></span>
                <input type="password" name="password" class="form-control border-start-0" required placeholder="••••••••">
            </div>
        </div>

        <button type="submit" class="btn yn-btn-primary w-100 py-2 fw-semibold mb-3">
            Sign In to Account
        </button>

        <div class="text-center small text-muted">
            Don't have an account yet? <a href="<?= url('/register') ?>" class="text-secondary fw-bold text-decoration-none">Register Now</a>
        </div>
    </form>
</div>
