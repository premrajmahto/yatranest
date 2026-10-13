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
                    <a href="<?= url('/my-bookings') ?>" class="dashboard-sidebar-item active">
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

        <!-- Main Content -->
        <div class="col-lg-9">
            <div class="card p-4 border-0 shadow-sm rounded-4 bg-white">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                    <h4 class="fw-bold text-dark m-0">My Tour Bookings</h4>
                    <!-- Filter Pills -->
                    <div class="btn-group btn-group-sm">
                        <a href="<?= url('/my-bookings') ?>" class="btn <?= empty($filter) ? 'btn-secondary' : 'btn-outline-secondary' ?>">All</a>
                        <a href="<?= url('/my-bookings?filter=upcoming') ?>" class="btn <?= ($filter ?? '') === 'upcoming' ? 'btn-secondary' : 'btn-outline-secondary' ?>">Upcoming</a>
                        <a href="<?= url('/my-bookings?filter=completed') ?>" class="btn <?= ($filter ?? '') === 'completed' ? 'btn-secondary' : 'btn-outline-secondary' ?>">Completed</a>
                        <a href="<?= url('/my-bookings?filter=cancelled') ?>" class="btn <?= ($filter ?? '') === 'cancelled' ? 'btn-secondary' : 'btn-outline-secondary' ?>">Cancelled</a>
                    </div>
                </div>

                <?php if (empty($bookings)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-calendar-x fs-1 text-secondary d-block mb-3"></i>
                    <h5>No Bookings In This Category</h5>
                    <p class="small text-muted mb-4">You have not booked any tours matching this status.</p>
                    <a href="<?= url('/packages') ?>" class="btn yn-btn-primary btn-sm px-4">Find A Tour Package</a>
                </div>
                <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($bookings as $b): ?>
                    <div class="card border rounded-3 p-3 transition-hover">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-2 text-center text-md-start">
                                <img src="<?= e($b['package_image']) ?>" alt="" class="rounded-3 object-fit-cover w-100" style="height: 80px;">
                            </div>
                            <div class="col-md-5">
                                <span class="badge bg-light text-primary border small font-monospace mb-1"><?= e($b['booking_reference']) ?></span>
                                <h6 class="fw-bold text-dark mb-1"><?= e($b['package_title']) ?></h6>
                                <span class="small text-muted d-block"><i class="bi bi-geo-alt me-1"></i><?= e($b['destination_name']) ?></span>
                                <span class="small text-muted"><i class="bi bi-calendar3 me-1"></i><?= date('d M Y', strtotime($b['departure_date'])) ?></span>
                            </div>
                            <div class="col-md-2 text-md-center">
                                <span class="text-muted small d-block">Amount:</span>
                                <strong class="text-dark small"><?= format_currency($b['total_amount']) ?></strong>
                                <span class="badge <?= $b['payment_status'] === 'paid' ? 'bg-success' : 'bg-warning text-dark' ?> d-block mt-1" style="font-size: 0.7rem;">
                                    <?= strtoupper($b['payment_status']) ?>
                                </span>
                            </div>
                            <div class="col-md-3 text-md-end">
                                <span class="badge <?= $b['booking_status'] === 'confirmed' ? 'bg-success' : ($b['booking_status'] === 'cancelled' ? 'bg-danger' : 'bg-warning text-dark') ?> mb-2 d-inline-block">
                                    <?= strtoupper($b['booking_status']) ?>
                                </span>
                                <div>
                                    <a href="<?= url('/my-bookings/' . $b['booking_reference']) ?>" class="btn btn-sm btn-outline-primary me-1">
                                        View
                                    </a>
                                    <a href="<?= url('/booking/invoice/' . $b['booking_reference']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Invoice">
                                        <i class="bi bi-file-earmark-text"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
