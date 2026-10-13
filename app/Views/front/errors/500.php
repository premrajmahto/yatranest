<div class="container py-5 my-5 text-center">
    <div class="py-5">
        <h1 class="display-1 fw-extrabold text-danger">500</h1>
        <h2 class="fw-bold text-dark mb-3">Internal Server Error</h2>
        <p class="text-muted lead mx-auto mb-4" style="max-width: 500px;">
            <?= e($message ?? 'An unexpected error occurred while processing your request. Please try again.') ?>
        </p>

        <?php if (!empty($trace) && config('app.debug', false)): ?>
        <div class="text-start bg-dark text-light p-4 rounded-3 font-monospace small mx-auto my-4 overflow-auto" style="max-width: 800px; max-height: 300px;">
            <strong class="text-danger">Exception in <?= e($file ?? '') ?> (Line <?= e($line ?? '') ?>):</strong><br>
            <?= nl2br(e($message ?? '')) ?><br><br>
            <strong>Stack Trace:</strong><br>
            <?= nl2br(e($trace)) ?>
        </div>
        <?php endif; ?>

        <a href="<?= url('/') ?>" class="btn yn-btn-primary px-4 py-2">
            <i class="bi bi-house-door me-1"></i> Return to Homepage
        </a>
    </div>
</div>
