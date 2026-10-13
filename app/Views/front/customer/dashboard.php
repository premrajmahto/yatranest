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
                    <a href="<?= url('/dashboard') ?>" class="dashboard-sidebar-item active">
                        <i class="bi bi-grid"></i> Dashboard Overview
                    </a>
                    <a href="<?= url('/my-bookings') ?>" class="dashboard-sidebar-item">
                        <i class="bi bi-ticket-perforated"></i> My Bookings
                    </a>
                    <a href="<?= url('/profile') ?>" class="dashboard-sidebar-item">
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

        <!-- Main Dashboard Content -->
        <div class="col-lg-9">
            <!-- Welcome Banner -->
            <div class="p-4 rounded-4 text-white mb-4 shadow-sm" style="background: linear-gradient(135deg, #0d233a 0%, #008080 100%);">
                <h3 class="fw-bold mb-1">Hello, <?= e($user['name']) ?>! 👋</h3>
                <p class="mb-0 text-white-50">Manage your holiday reservations, download tax invoices, and prepare for upcoming adventures.</p>
            </div>

            <!-- Metric Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card p-3 border-0 shadow-sm rounded-4 bg-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small d-block">Total Bookings</span>
                                <h3 class="fw-extrabold text-dark mb-0"><?= count($bookings) ?></h3>
                            </div>
                            <div class="p-3 bg-light rounded-circle text-primary fs-3">
                                <i class="bi bi-ticket-perforated"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card p-3 border-0 shadow-sm rounded-4 bg-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small d-block">Upcoming Trips</span>
                                <h3 class="fw-extrabold text-success mb-0"><?= count($upcoming_bookings) ?></h3>
                            </div>
                            <div class="p-3 bg-light rounded-circle text-success fs-3">
                                <i class="bi bi-calendar-event"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card p-3 border-0 shadow-sm rounded-4 bg-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small d-block">Completed Trips</span>
                                <h3 class="fw-extrabold text-info mb-0"><?= count($completed_bookings) ?></h3>
                            </div>
                            <div class="p-3 bg-light rounded-circle text-info fs-3">
                                <i class="bi bi-check2-all"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings Table -->
            <div class="card p-4 border-0 shadow-sm rounded-4 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-dark m-0">Recent Trips</h5>
                    <a href="<?= url('/my-bookings') ?>" class="text-secondary small fw-semibold text-decoration-none">View All Bookings <i class="bi bi-arrow-right"></i></a>
                </div>

                <?php if (empty($bookings)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-suitcase-lg fs-1 text-secondary d-block mb-2"></i>
                    <p class="mb-3">You don't have any bookings yet. Ready for your next journey?</p>
                    <a href="<?= url('/packages') ?>" class="btn yn-btn-primary btn-sm">Browse Holiday Packages</a>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light small">
                            <tr>
                                <th>Booking Ref</th>
                                <th>Tour Package</th>
                                <th>Departure</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($bookings, 0, 5) as $b): ?>
                            <tr>
                                <td class="fw-bold font-monospace small text-primary"><?= e($b['booking_reference']) ?></td>
                                <td>
                                    <span class="fw-semibold text-dark d-block"><?= e($b['package_title']) ?></span>
                                    <span class="small text-muted"><i class="bi bi-geo-alt me-1"></i><?= e($b['destination_name']) ?></span>
                                </td>
                                <td class="small"><?= date('d M Y', strtotime($b['departure_date'])) ?></td>
                                <td class="fw-bold small"><?= format_currency($b['total_amount']) ?></td>
                                <td>
                                    <span class="badge <?= $b['booking_status'] === 'confirmed' ? 'bg-success' : ($b['booking_status'] === 'cancelled' ? 'bg-danger' : 'bg-warning text-dark') ?>">
                                        <?= strtoupper($b['booking_status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= url('/my-bookings/' . $b['booking_reference']) ?>" class="btn btn-sm btn-outline-secondary">
                                        Details
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
