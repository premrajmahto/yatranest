<?php
$user = auth()->user();
?>
<header class="admin-header">
    <div class="d-flex align-items-center gap-3">
        <button class="btn btn-sm btn-light border d-lg-none" type="button" onclick="document.getElementById('adminSidebar').classList.toggle('show')">
            <i class="bi bi-list fs-5"></i>
        </button>
        <span class="fw-semibold text-muted small d-none d-sm-inline">
            <i class="bi bi-clock me-1"></i> <?= date('D, d M Y') ?>
        </span>
    </div>

    <div class="d-flex align-items-center gap-3">
        <div class="dropdown">
            <button class="btn btn-sm btn-light border dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-person-fill-gear text-primary"></i>
                <span class="fw-semibold"><?= e($user['name'] ?? 'Admin') ?></span>
                <span class="badge bg-secondary text-uppercase" style="font-size: 0.65rem;">
                    <?= e($user['roles'][0] ?? 'Admin') ?>
                </span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
                <li><a class="dropdown-item" href="<?= url('/admin/settings') ?>"><i class="bi bi-gear me-2"></i> Settings</a></li>
                <li><a class="dropdown-item" href="<?= url('/') ?>" target="_blank"><i class="bi bi-globe me-2"></i> Visit Website</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="<?= url('/logout') ?>" class="m-0">
                        <?= csrf_field() ?>
                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
