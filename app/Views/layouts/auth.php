<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? e($title) . ' | YatraNest' : 'Authentication | YatraNest' ?></title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 & Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
</head>
<body class="bg-light d-flex flex-column min-vh-100 justify-content-between">
    <div class="py-4 text-center">
        <a href="<?= url('/') ?>" class="d-inline-flex align-items-center gap-2 text-decoration-none text-dark fs-3 fw-bold">
            <i class="bi bi-airplane-engines text-primary"></i>
            Yatra<span class="text-secondary">Nest</span>
        </a>
    </div>

    <div class="container my-auto">
        <?php require dirname(__DIR__) . '/partials/alerts.php'; ?>
        <div class="row justify-content-center">
            <div class="col-12">
                <?= $content ?>
            </div>
        </div>
    </div>

    <div class="py-4 text-center text-muted small">
        &copy; <?= date('Y') ?> YatraNest Holidays. All rights reserved. &bull; <a href="<?= url('/') ?>" class="text-secondary text-decoration-none">Back to Home</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= asset('js/main.js') ?>"></script>
</body>
</html>
