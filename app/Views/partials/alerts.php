<?php
$successMsg = flash('success');
$errorMsg = flash('error');
$infoMsg = flash('info');
$warningMsg = flash('warning');
?>

<div class="container mt-3">
    <?php if ($successMsg): ?>
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill fs-5 me-2 text-success"></i>
        <div><?= e($successMsg) ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php if ($errorMsg): ?>
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-5 me-2 text-danger"></i>
        <div><?= e($errorMsg) ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php if ($warningMsg): ?>
    <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
        <i class="bi bi-exclamation-circle-fill fs-5 me-2 text-warning"></i>
        <div><?= e($warningMsg) ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php if ($infoMsg): ?>
    <div class="alert alert-info alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
        <i class="bi bi-info-circle-fill fs-5 me-2 text-info"></i>
        <div><?= e($infoMsg) ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
</div>
