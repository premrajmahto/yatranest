<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold text-dark m-0">Operations Dashboard</h3>
        <p class="text-muted small m-0">Real-time booking revenue, seat inventory, and departure analytics.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/admin/packages/create') ?>" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Add Tour Package
        </a>
        <a href="<?= url('/admin/departures/create') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-calendar-plus me-1"></i> Add Departure
        </a>
    </div>
</div>

<!-- Key Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <span class="text-muted small fw-semibold text-uppercase d-block">Total Bookings</span>
                <h3 class="fw-extrabold text-dark mb-0"><?= (int)$stats['total_bookings'] ?></h3>
                <span class="small text-success"><i class="bi bi-check-circle"></i> <?= (int)$stats['confirmed_bookings'] ?> Confirmed</span>
            </div>
            <div class="stat-icon blue">
                <i class="bi bi-ticket-perforated"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <span class="text-muted small fw-semibold text-uppercase d-block">Revenue Collected</span>
                <h3 class="fw-extrabold text-dark mb-0"><?= format_currency($stats['total_revenue']) ?></h3>
                <span class="small text-muted"><?= (int)$stats['paid_payments'] ?> Transactions</span>
            </div>
            <div class="stat-icon green">
                <i class="bi bi-currency-rupee"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <span class="text-muted small fw-semibold text-uppercase d-block">Pending Bookings</span>
                <h3 class="fw-extrabold text-dark mb-0"><?= (int)$stats['pending_bookings'] ?></h3>
                <span class="small text-warning"><i class="bi bi-hourglass-split"></i> Awaiting Action</span>
            </div>
            <div class="stat-icon amber">
                <i class="bi bi-clock"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <span class="text-muted small fw-semibold text-uppercase d-block">Active Packages</span>
                <h3 class="fw-extrabold text-dark mb-0"><?= (int)$stats['total_packages'] ?></h3>
                <span class="small text-info"><?= (int)$stats['total_destinations'] ?> Destinations</span>
            </div>
            <div class="stat-icon teal">
                <i class="bi bi-box-seam"></i>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card p-3 border-0 bg-white shadow-sm rounded-3">
            <span class="text-muted small">Registered Customers:</span>
            <strong class="fs-4 text-dark"><?= (int)$stats['total_customers'] ?></strong>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 border-0 bg-white shadow-sm rounded-3">
            <span class="text-muted small">Cancelled Bookings:</span>
            <strong class="fs-4 text-danger"><?= (int)$stats['cancelled_bookings'] ?></strong>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 border-0 bg-white shadow-sm rounded-3">
            <span class="text-muted small">Refunds Processed:</span>
            <strong class="fs-4 text-warning"><?= format_currency($stats['total_refunds']) ?></strong>
        </div>
    </div>
</div>

<!-- Interactive Chart.js Row -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="admin-card h-100">
            <div class="admin-card-header">
                <h5 class="admin-card-title"><i class="bi bi-graph-up text-primary me-2"></i>Revenue Analytics (Past 6 Months)</h5>
            </div>
            <div class="admin-card-body">
                <canvas id="revenueChart" style="max-height: 280px;"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="admin-card h-100">
            <div class="admin-card-header">
                <h5 class="admin-card-title"><i class="bi bi-pie-chart text-secondary me-2"></i>Booking Share</h5>
            </div>
            <div class="admin-card-body d-flex align-items-center justify-content-center">
                <canvas id="statusChart" style="max-height: 250px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Tables: Recent Bookings & Upcoming Departures -->
<div class="row g-4">
    <!-- Recent Bookings -->
    <div class="col-lg-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="admin-card-title">Recent Customer Bookings</h5>
                <a href="<?= url('/admin/bookings') ?>" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table admin-table mb-0">
                    <thead>
                        <tr>
                            <th>Ref</th>
                            <th>Customer</th>
                            <th>Package</th>
                            <th>Amount</th>
                            <th>Booking</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_bookings)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">No bookings recorded yet.</td></tr>
                        <?php else: ?>
                        <?php foreach ($recent_bookings as $b): ?>
                        <tr>
                            <td class="font-monospace fw-bold small text-primary"><?= e($b['booking_reference']) ?></td>
                            <td>
                                <strong class="d-block small"><?= e($b['customer_name']) ?></strong>
                                <span class="text-muted" style="font-size: 0.75rem;"><?= e($b['customer_email']) ?></span>
                            </td>
                            <td class="small"><?= e(mb_strimwidth($b['package_title'], 0, 25, '...')) ?></td>
                            <td class="fw-bold small"><?= format_currency($b['total_amount']) ?></td>
                            <td>
                                <span class="badge <?= $b['booking_status'] === 'confirmed' ? 'bg-success' : ($b['booking_status'] === 'cancelled' ? 'bg-danger' : 'bg-warning text-dark') ?>" style="font-size: 0.7rem;">
                                    <?= strtoupper($b['booking_status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= url('/admin/bookings/' . $b['id']) ?>" class="btn btn-sm btn-light border py-0 px-2" title="Manage">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Upcoming Departures -->
    <div class="col-lg-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="admin-card-title">Upcoming Departures</h5>
                <a href="<?= url('/admin/departures') ?>" class="btn btn-sm btn-outline-secondary">Manage</a>
            </div>
            <div class="admin-card-body p-0">
                <ul class="list-group list-group-flush small">
                    <?php if (empty($upcoming_departures)): ?>
                    <li class="list-group-item text-center py-4 text-muted">No upcoming departures.</li>
                    <?php else: ?>
                    <?php foreach ($upcoming_departures as $dep): ?>
                    <li class="list-group-item p-3">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <strong class="text-dark"><?= e($dep['package_title']) ?></strong>
                            <span class="badge <?= $dep['available_seats'] > 5 ? 'bg-success' : ($dep['available_seats'] > 0 ? 'bg-warning text-dark' : 'bg-danger') ?>">
                                <?= (int)$dep['available_seats'] ?> seats
                            </span>
                        </div>
                        <div class="d-flex justify-content-between text-muted" style="font-size: 0.78rem;">
                            <span><i class="bi bi-calendar3 me-1"></i><?= date('d M Y', strtotime($dep['departure_date'])) ?></span>
                            <span>Total Capacity: <?= (int)$dep['total_seats'] ?></span>
                        </div>
                    </li>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Revenue Chart
    const ctxRev = document.getElementById('revenueChart').getContext('2d');
    const revenueLabels = <?= json_encode($chart_revenue_labels ?? ['May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct']) ?>;
    const revenueData = <?= json_encode($chart_revenue_data ?? [120000, 185000, 240000, 310000, 290000, 420000]) ?>;

    new Chart(ctxRev, {
        type: 'line',
        data: {
            labels: revenueLabels,
            datasets: [{
                label: 'Monthly Revenue (₹)',
                data: revenueData,
                borderColor: '#008080',
                backgroundColor: 'rgba(0, 128, 128, 0.08)',
                fill: true,
                tension: 0.35,
                borderWidth: 2.5,
                pointBackgroundColor: '#008080',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(val) { return '₹ ' + (val / 1000) + 'k'; }
                    }
                }
            }
        }
    });

    // 2. Status Doughnut Chart
    const ctxStatus = document.getElementById('statusChart').getContext('2d');
    new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: ['Confirmed', 'Pending', 'Cancelled'],
            datasets: [{
                data: [
                    <?= (int)$stats['confirmed_bookings'] ?>,
                    <?= (int)$stats['pending_bookings'] ?>,
                    <?= (int)$stats['cancelled_bookings'] ?>
                ],
                backgroundColor: ['#16a34a', '#f59e0b', '#dc2626'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
});
</script>
