<?php
$currentPath = $_SERVER['REQUEST_URI'] ?? '/admin';
?>
<aside class="admin-sidebar" id="adminSidebar">
    <a href="<?= url('/admin') ?>" class="admin-brand">
        <i class="bi bi-airplane-engines text-warning fs-4"></i>
        <span>YatraNest</span> Admin
    </a>

    <div class="admin-nav-category">Overview</div>
    <a href="<?= url('/admin') ?>" class="admin-nav-link <?= $currentPath === '/admin' ? 'active' : '' ?>">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>

    <div class="admin-nav-category">Inventory & Tours</div>
    <a href="<?= url('/admin/packages') ?>" class="admin-nav-link <?= str_starts_with($currentPath, '/admin/packages') ? 'active' : '' ?>">
        <i class="bi bi-box-seam"></i> Tour Packages
    </a>
    <a href="<?= url('/admin/destinations') ?>" class="admin-nav-link <?= str_starts_with($currentPath, '/admin/destinations') ? 'active' : '' ?>">
        <i class="bi bi-geo-alt"></i> Destinations
    </a>
    <a href="<?= url('/admin/departures') ?>" class="admin-nav-link <?= str_starts_with($currentPath, '/admin/departures') ? 'active' : '' ?>">
        <i class="bi bi-calendar3"></i> Departures & Seats
    </a>

    <div class="admin-nav-category">Bookings & Finance</div>
    <a href="<?= url('/admin/bookings') ?>" class="admin-nav-link <?= str_starts_with($currentPath, '/admin/bookings') ? 'active' : '' ?>">
        <i class="bi bi-ticket-perforated"></i> Bookings
    </a>
    <a href="<?= url('/admin/payments') ?>" class="admin-nav-link <?= str_starts_with($currentPath, '/admin/payments') ? 'active' : '' ?>">
        <i class="bi bi-credit-card"></i> Payments & Refunds
    </a>
    <a href="<?= url('/admin/coupons') ?>" class="admin-nav-link <?= str_starts_with($currentPath, '/admin/coupons') ? 'active' : '' ?>">
        <i class="bi bi-tag"></i> Coupons & Promos
    </a>

    <div class="admin-nav-category">Customers & Content</div>
    <a href="<?= url('/admin/customers') ?>" class="admin-nav-link <?= str_starts_with($currentPath, '/admin/customers') ? 'active' : '' ?>">
        <i class="bi bi-people"></i> Customers
    </a>
    <a href="<?= url('/admin/content/messages') ?>" class="admin-nav-link <?= str_starts_with($currentPath, '/admin/content/messages') ? 'active' : '' ?>">
        <i class="bi bi-envelope"></i> Contact Inquiries
    </a>
    <a href="<?= url('/admin/content/reviews') ?>" class="admin-nav-link <?= str_starts_with($currentPath, '/admin/content/reviews') ? 'active' : '' ?>">
        <i class="bi bi-star"></i> Reviews Moderation
    </a>

    <div class="admin-nav-category">Administration</div>
    <a href="<?= url('/admin/settings') ?>" class="admin-nav-link <?= str_starts_with($currentPath, '/admin/settings') ? 'active' : '' ?>">
        <i class="bi bi-sliders"></i> Site Settings
    </a>
    <a href="<?= url('/admin/audit') ?>" class="admin-nav-link <?= str_starts_with($currentPath, '/admin/audit') ? 'active' : '' ?>">
        <i class="bi bi-journal-text"></i> Audit Logs
    </a>
    <a href="<?= url('/') ?>" target="_blank" class="admin-nav-link text-warning mt-3">
        <i class="bi bi-box-arrow-up-right"></i> View Live Site
    </a>
</aside>
