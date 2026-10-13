<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? e($title) . ' | YatraNest' : 'YatraNest - Premium Tour & Travel Booking' ?></title>
    <meta name="description" content="<?= isset($meta_description) ? e($meta_description) : 'Discover best domestic and international holiday tour packages with YatraNest. Transparent pricing, instant booking confirmation, and 24/7 travel support.' ?>">
    
    <!-- Open Graph SEO -->
    <meta property="og:title" content="<?= isset($title) ? e($title) : 'YatraNest Holidays' ?>">
    <meta property="og:description" content="<?= isset($meta_description) ? e($meta_description) : 'Book your dream vacation package with YatraNest.' ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= url($_SERVER['REQUEST_URI'] ?? '/') ?>">
    <meta property="og:image" content="<?= isset($meta_image) ? e($meta_image) : asset('images/og-banner.jpg') ?>">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
</head>
<body class="d-flex flex-column min-vh-100">

    <?php require dirname(__DIR__) . '/partials/navbar.php'; ?>
    <?php require dirname(__DIR__) . '/partials/alerts.php'; ?>

    <main class="flex-grow-1">
        <?= $content ?>
    </main>

    <?php require dirname(__DIR__) . '/partials/footer.php'; ?>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= asset('js/main.js') ?>"></script>
    <?php if (isset($extra_js)): ?>
        <?= $extra_js ?>
    <?php endif; ?>
</body>
</html>
