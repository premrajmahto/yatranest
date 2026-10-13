<div class="container py-5">
    <div class="row g-4">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3">
            <div class="card p-3 border-0 shadow-sm rounded-4 bg-white">
                <div class="text-center p-3 border-bottom mb-3">
                    <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center fs-3 mb-2" style="width: 65px; height: 65px;">
                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                    </div>
                    <h6 class="fw-bold text-dark mb-0"><?= e($user['name']) ?></h6>
                    <span class="small text-muted"><?= e($user['email']) ?></span>
                </div>

                <nav class="d-flex flex-column">
                    <a href="<?= url('/dashboard') ?>" class="dashboard-sidebar-item">
                        <i class="bi bi-grid"></i> Dashboard Overview
                    </a>
                    <a href="<?= url('/my-bookings') ?>" class="dashboard-sidebar-item">
                        <i class="bi bi-ticket-perforated"></i> My Bookings
                    </a>
                    <a href="<?= url('/profile') ?>" class="dashboard-sidebar-item active">
                        <i class="bi bi-person-gear"></i> Profile & Security
                    </a>
                    <hr class="my-2 text-muted">
                    <form method="POST" action="<?= url('/logout') ?>" class="m-0">
                        <?= csrf_field() ?>
                        <button type="submit" class="dashboard-sidebar-item w-100 text-start border-0 bg-transparent text-danger">
                            <i class="bi bi-box-arrow-right"></i> Sign Out
                        </button>
                    </form>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <div class="card p-4 border-0 shadow-sm rounded-4 bg-white mb-4">
                <h4 class="fw-bold text-dark mb-4">Edit Profile Information</h4>

                <form action="<?= url('/profile/update') ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Full Name *</label>
                            <input type="text" name="name" class="form-control" required value="<?= e($user['name']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Email Address (Read-only)</label>
                            <input type="email" class="form-control bg-light" disabled value="<?= e($user['email']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Mobile Phone Number</label>
                            <input type="tel" name="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>" placeholder="+91 98765 43210">
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn yn-btn-primary px-4">Save Changes</button>
                    </div>
                </form>
            </div>

            <div class="card p-4 border-0 shadow-sm rounded-4 bg-white">
                <h4 class="fw-bold text-dark mb-4">Change Password</h4>

                <form action="<?= url('/profile/password') ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label small fw-semibold text-dark">Current Password *</label>
                            <input type="password" name="current_password" class="form-control" required placeholder="Enter current password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">New Password *</label>
                            <input type="password" name="new_password" class="form-control" required placeholder="Minimum 6 characters">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Confirm New Password *</label>
                            <input type="password" name="new_password_confirmation" class="form-control" required placeholder="Re-type new password">
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-outline-danger px-4">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
