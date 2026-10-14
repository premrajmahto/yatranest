<?php
$currentPath = $_SERVER['REQUEST_URI'] ?? '/';
$user = auth()->user();
?>
<nav class="navbar navbar-expand-lg navbar-yn sticky-top py-3">
    <div class="container">
        <a class="navbar-brand" href="<?= url('/') ?>">
            <i class="bi bi-airplane-engines text-primary fs-3"></i>
            Yatra<span>Nest</span>
        </a>

        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= $currentPath === '/' ? 'active' : '' ?>" href="<?= url('/') ?>">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_starts_with($currentPath, '/destinations') ? 'active' : '' ?>" href="<?= url('/destinations') ?>">Destinations</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_starts_with($currentPath, '/packages') ? 'active' : '' ?>" href="<?= url('/packages') ?>">Tour Packages</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_starts_with($currentPath, '/hotels') ? 'active' : '' ?>" href="<?= url('/hotels') ?>">Hotels</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_starts_with($currentPath, '/about') ? 'active' : '' ?>" href="<?= url('/about') ?>">About Us</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_starts_with($currentPath, '/contact') ? 'active' : '' ?>" href="<?= url('/contact') ?>">Contact</a>
                </li>
                <?php if ($user): ?>
                <li class="nav-item">
                    <a class="nav-link <?= str_starts_with($currentPath, '/my-bookings') ? 'active' : '' ?>" href="<?= url('/my-bookings') ?>">My Bookings</a>
                </li>
                <?php endif; ?>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <a href="<?= whatsapp_url('Hi YatraNest, I would like to enquire about travel packages and bookings.') ?>" 
                   target="_blank" 
                   rel="noopener" 
                   class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1 fw-bold px-2 py-1" 
                   style="border-color: #25D366; color: #1ebe5d;"
                   title="Direct WhatsApp: +91 60005 95647" 
                   aria-label="Direct WhatsApp Contact">
                    <i class="bi bi-whatsapp fs-6 text-success"></i>
                    <span class="d-none d-xl-inline small">+91 60005 95647</span>
                    <span class="d-xl-none small">WhatsApp</span>
                </a>
                <?php if ($user): ?>
                    <?php if (auth()->isAdmin()): ?>
                        <a href="<?= url('/admin') ?>" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-speedometer2 me-1"></i> Admin
                        </a>
                    <?php endif; ?>
                    <div class="dropdown">
                        <button class="btn btn-sm yn-btn-secondary dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i>
                            <span><?= e($user['name']) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
                            <li><a class="dropdown-item" href="<?= url('/dashboard') ?>"><i class="bi bi-grid me-2 text-secondary"></i> Dashboard</a></li>
                            <li><a class="dropdown-item" href="<?= url('/my-bookings') ?>"><i class="bi bi-ticket-perforated me-2 text-secondary"></i> My Bookings</a></li>
                            <li><a class="dropdown-item" href="<?= url('/profile') ?>"><i class="bi bi-gear me-2 text-secondary"></i> Profile Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="<?= url('/logout') ?>" class="m-0">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?= url('/login') ?>" class="btn btn-sm yn-btn-outline">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Login
                    </a>
                    <a href="<?= url('/register') ?>" class="btn btn-sm yn-btn-primary">
                        <i class="bi bi-person-plus me-1"></i> Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
