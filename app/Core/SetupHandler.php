<?php

namespace App\Core;

use PDO;
use PDOException;

class SetupHandler {
    public static function handle(PDOException $e, array $config): void {
        $basePath = dirname(__DIR__, 2);
        $envFile = $basePath . '/.env';
        $envExists = file_exists($envFile) || file_exists(dirname($basePath) . '/.env');

        // Check if unconfigured or default localhost credentials
        $isDefaultOrMissing = !$envExists || ($config['username'] === 'root' && empty($config['password']));

        // If configured with real credentials but DB server is temporarily down in production:
        if (!$isDefaultOrMissing && !config('app.debug', false)) {
            http_response_code(503);
            die("<!DOCTYPE html><html><head><title>Service Temporarily Unavailable</title></head><body style='font-family:sans-serif;text-align:center;padding:50px;'><h2>Database Connection Error</h2><p>We are experiencing a temporary database issue. Please try again in a few moments.</p></body></html>");
        }

        // Determine current application URL
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
        $appUrl = $protocol . $host;

        $postError = null;
        $manualEnvSnippet = null;

        // Process Instant SQLite Launch (Zero Configuration)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['launch_sqlite'])) {
            try {
                $sqliteDir = $basePath . '/storage';
                if (!is_dir($sqliteDir)) {
                    @mkdir($sqliteDir, 0777, true);
                }
                $sqliteFile = $sqliteDir . '/database.sqlite';
                $sqlitePdo = new PDO('sqlite:' . $sqliteFile);
                $sqlitePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $sqlitePdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                SqliteSeeder::seed($sqlitePdo);

                $secret = bin2hex(random_bytes(16));
                $envContent = <<<ENV
APP_NAME="YatraNest - Tour & Travel Booking"
APP_ENV=production
APP_DEBUG=false
APP_URL="{$appUrl}"
APP_SECRET="{$secret}"

# Database Configuration (Portable SQLite)
DB_CONNECTION=sqlite
DB_DATABASE="storage/database.sqlite"

# Payment Gateway (Razorpay Test Mode)
RAZORPAY_KEY_ID=rzp_test_sampleKey12345
RAZORPAY_KEY_SECRET=rzp_test_secretSample67890
RAZORPAY_WEBHOOK_SECRET=whsec_sampleRazorpayWebhookSecret
RAZORPAY_MODE=test

# Email Configuration
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@yatranest.com
MAIL_FROM_NAME="YatraNest Holidays"

# Session & Booking
SESSION_LIFETIME=7200
DEFAULT_CURRENCY=INR
CURRENCY_SYMBOL="₹"
HOLD_EXPIRATION_MINUTES=15
ENV;

                $written = @file_put_contents($envFile, $envContent);

                if ($written !== false) {
                    self::renderSuccessView($appUrl);
                    exit;
                } else {
                    $manualEnvSnippet = $envContent;
                }
            } catch (\Throwable $ex) {
                $postError = "SQLite Initialization Failed: " . $ex->getMessage();
            }
        }

        // Process POST setup form (MySQL)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup_db'])) {
            $dbHost = trim((string)($_POST['db_host'] ?? 'localhost'));
            $dbPort = (int)($_POST['db_port'] ?? 3306);
            $dbName = trim((string)($_POST['db_name'] ?? ''));
            $dbUser = trim((string)($_POST['db_user'] ?? ''));
            $dbPass = (string)($_POST['db_pass'] ?? '');
            $importSql = !empty($_POST['import_data']);

            if (empty($dbName) || empty($dbUser)) {
                $postError = "Please enter both the Database Name and Database Username from your hosting control panel.";
            } else {
                try {
                    $dsn = sprintf("mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4", $dbHost, $dbPort, $dbName);
                    $testPdo = new PDO($dsn, $dbUser, $dbPass, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => true,
                    ]);

                    // Import database schema and seed data if checked
                    if ($importSql) {
                        $sqlPath = $basePath . '/database/database.sql';
                        if (file_exists($sqlPath)) {
                            $sql = file_get_contents($sqlPath);
                            if (!empty($sql)) {
                                $testPdo->exec($sql);
                            }
                        }
                    }

                    // Prepare .env content
                    $secret = bin2hex(random_bytes(16));
                    $envContent = <<<ENV
APP_NAME="YatraNest - Tour & Travel Booking"
APP_ENV=production
APP_DEBUG=false
APP_URL="{$appUrl}"
APP_SECRET="{$secret}"

# Database Configuration (Hostinger / MySQL)
DB_HOST="{$dbHost}"
DB_PORT={$dbPort}
DB_NAME="{$dbName}"
DB_USER="{$dbUser}"
DB_PASS="{$dbPass}"

# Payment Gateway (Razorpay Test Mode)
RAZORPAY_KEY_ID=rzp_test_sampleKey12345
RAZORPAY_KEY_SECRET=rzp_test_secretSample67890
RAZORPAY_WEBHOOK_SECRET=whsec_sampleRazorpayWebhookSecret
RAZORPAY_MODE=test

# Email Configuration
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@yatranest.com
MAIL_FROM_NAME="YatraNest Holidays"

# Session & Booking
SESSION_LIFETIME=7200
DEFAULT_CURRENCY=INR
CURRENCY_SYMBOL="₹"
HOLD_EXPIRATION_MINUTES=15
ENV;

                    $written = @file_put_contents($envFile, $envContent);

                    if ($written !== false) {
                        self::renderSuccessView($appUrl);
                        exit;
                    } else {
                        $manualEnvSnippet = $envContent;
                    }
                } catch (PDOException $ex) {
                    $postError = "Database Connection Failed: " . $ex->getMessage();
                }
            }
        }

        self::renderSetupView($e, $config, $appUrl, $postError, $manualEnvSnippet);
        exit;
    }

    private static function renderSuccessView(string $appUrl): void {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Database Setup Completed - YatraNest</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
            <style>
                body { background: #f8fafc; min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: system-ui, -apple-system, sans-serif; }
                .card-box { max-width: 620px; width: 100%; border-radius: 1.25rem; box-shadow: 0 20px 40px rgba(0,0,0,0.06); }
            </style>
        </head>
        <body class="p-3">
            <div class="card card-box border-0 bg-white p-4 p-md-5 text-center">
                <div class="mb-3 text-success">
                    <i class="bi bi-check-circle-fill" style="font-size: 4rem;"></i>
                </div>
                <h3 class="fw-bold text-dark mb-2">Database Connected Successfully!</h3>
                <p class="text-secondary small mb-4">
                    Your <code>.env</code> file has been written and all database tables, sample tour packages, destinations, and default accounts are ready.
                </p>

                <div class="bg-light p-3 rounded-3 text-start small mb-4 border">
                    <strong class="d-block mb-2 text-dark"><i class="bi bi-key-fill text-primary me-1"></i> Default Administrative Credentials:</strong>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-secondary">Admin Login:</span>
                        <span class="font-monospace fw-semibold">admin@yatranest.com</span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-secondary">Admin Password:</span>
                        <span class="font-monospace fw-semibold">Admin@12345</span>
                    </div>
                </div>

                <a href="<?= htmlspecialchars($appUrl) ?>/" class="btn btn-primary btn-lg rounded-pill fw-semibold shadow-sm">
                    <i class="bi bi-arrow-right-circle me-1"></i> Launch YatraNest Website
                </a>
            </div>
        </body>
        </html>
        <?php
    }

    private static function renderSetupView(PDOException $e, array $config, string $appUrl, ?string $postError, ?string $manualEnvSnippet): void {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Database Setup Assistant - YatraNest</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
            <style>
                body { background: #f1f5f9; min-height: 100vh; padding: 2rem 1rem; font-family: system-ui, -apple-system, sans-serif; }
                .setup-container { max-width: 860px; margin: 0 auto; }
                .brand-header { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: white; border-radius: 1rem 1rem 0 0; padding: 2rem; }
                .card-body-box { background: white; border-radius: 0 0 1rem 1rem; padding: 2rem; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
                .guide-step { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 1rem 1.25rem; margin-bottom: 1rem; }
                .step-num { width: 28px; height: 28px; background: #0284c7; color: white; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.85rem; margin-right: 0.5rem; }
            </style>
        </head>
        <body>
            <div class="setup-container">
                <div class="brand-header text-center">
                    <h3 class="fw-bold mb-1"><i class="bi bi-compass text-warning me-2"></i>YatraNest Setup Assistant</h3>
                    <p class="text-light opacity-75 small mb-0">Database Configuration for Live Hosting (Hostinger / cPanel / VPS)</p>
                </div>

                <div class="card-body-box">
                    <!-- Current Status Alert -->
                    <div class="alert alert-warning border-0 d-flex align-items-start gap-3 mb-4">
                        <i class="bi bi-exclamation-triangle-fill fs-3 text-warning flex-shrink-0"></i>
                        <div>
                            <strong class="d-block mb-1">Database Connection Required</strong>
                            <p class="small mb-1">
                                The application attempted to connect to MySQL using default credentials, but was rejected by the server:
                            </p>
                            <code class="small text-danger bg-white p-1 rounded border d-block text-break">
                                <?= htmlspecialchars($e->getMessage()) ?>
                            </code>
                        </div>
                    </div>

                    <?php if ($manualEnvSnippet): ?>
                    <div class="alert alert-info border-0 mb-4">
                        <h6 class="fw-bold text-dark"><i class="bi bi-file-earmark-code me-1"></i> Connection Verified! Please Save .env File</h6>
                        <p class="small mb-2">Database credentials are correct! However, your web server has read-only file permissions. Please copy this snippet and save it as <code>.env</code> in your root directory via Hostinger File Manager:</p>
                        <textarea class="form-control font-monospace small" rows="8" readonly onclick="this.select()"><?= htmlspecialchars($manualEnvSnippet) ?></textarea>
                        <a href="<?= htmlspecialchars($appUrl) ?>/" class="btn btn-success btn-sm mt-2"><i class="bi bi-arrow-repeat"></i> Reload Website</a>
                    </div>
                    <?php endif; ?>

                    <?php if ($postError): ?>
                    <div class="alert alert-danger border-0 small mb-4">
                        <i class="bi bi-x-circle-fill me-1"></i> <?= htmlspecialchars($postError) ?>
                    </div>
                    <?php endif; ?>

                    <!-- 1-Click Instant Launch Option -->
                    <div class="card border-0 mb-4 text-white p-4 rounded-4 shadow-sm" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
                        <div class="row align-items-center g-3">
                            <div class="col-md-7">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-warning text-dark fw-bold text-uppercase px-2 py-1">Fastest Option</span>
                                    <h5 class="fw-bold mb-0 text-white">Instant 1-Click Launch</h5>
                                </div>
                                <p class="small mb-0 text-white opacity-90">
                                    Don't want to create a MySQL database in Hostinger right now? Click to initialize YatraNest instantly. All tour packages, bookings, and admin dashboard work immediately!
                                </p>
                            </div>
                            <div class="col-md-5 text-md-end">
                                <form method="POST" class="m-0">
                                    <input type="hidden" name="launch_sqlite" value="1">
                                    <button type="submit" class="btn btn-warning btn-lg fw-bold rounded-pill px-4 shadow py-2 text-dark">
                                        <i class="bi bi-lightning-charge-fill me-1"></i> Launch Website Now
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center my-4">
                        <hr class="flex-grow-1 my-0">
                        <span class="px-3 text-muted small fw-semibold text-uppercase bg-white">OR CONNECT HOSTINGER MYSQL BELOW</span>
                        <hr class="flex-grow-1 my-0">
                    </div>

                    <div class="row g-4">
                        <!-- Form Section -->
                        <div class="col-lg-7">
                            <div class="p-3 border rounded-3 bg-white">
                                <h5 class="fw-bold text-dark mb-3"><i class="bi bi-lightning-charge-fill text-primary me-1"></i> Quick Connect & Setup</h5>
                                <p class="small text-muted mb-3">
                                    Enter your MySQL database details from your <strong>Hostinger hPanel</strong> (Databases &rarr; MySQL Databases):
                                </p>

                                <form method="POST">
                                    <input type="hidden" name="setup_db" value="1">
                                    <div class="row g-2 mb-2">
                                        <div class="col-8">
                                            <label class="form-label small fw-semibold">Database Host</label>
                                            <input type="text" name="db_host" class="form-control form-control-sm" value="<?= htmlspecialchars($_POST['db_host'] ?? 'localhost') ?>" required>
                                            <div class="form-text" style="font-size:0.75rem;">Usually <code>localhost</code> on Hostinger</div>
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label small fw-semibold">Port</label>
                                            <input type="number" name="db_port" class="form-control form-control-sm" value="<?= htmlspecialchars($_POST['db_port'] ?? '3306') ?>" required>
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold">Database Name</label>
                                        <input type="text" name="db_name" class="form-control form-control-sm" placeholder="e.g. u123456789_yatranest" value="<?= htmlspecialchars($_POST['db_name'] ?? '') ?>" required>
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold">Database Username</label>
                                        <input type="text" name="db_user" class="form-control form-control-sm" placeholder="e.g. u123456789_dbuser" value="<?= htmlspecialchars($_POST['db_user'] ?? '') ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Database Password</label>
                                        <input type="password" name="db_pass" class="form-control form-control-sm" placeholder="Your database password" value="<?= htmlspecialchars($_POST['db_pass'] ?? '') ?>">
                                    </div>

                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" name="import_data" value="1" id="importCheck" checked>
                                        <label class="form-check-label small" for="importCheck">
                                            <strong>Auto-import database tables & sample tours</strong> (Recommended)
                                        </label>
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100 fw-semibold shadow-sm">
                                        <i class="bi bi-database-check me-1"></i> Connect & Initialize Database
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Guide Section -->
                        <div class="col-lg-5">
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-info-circle text-primary me-1"></i> Hostinger Setup Guide</h6>

                            <div class="guide-step">
                                <div class="d-flex align-items-center mb-1">
                                    <span class="step-num">1</span>
                                    <strong class="small text-dark">Create Database</strong>
                                </div>
                                <p class="small text-muted mb-0" style="font-size:0.8rem;">
                                    In Hostinger hPanel, go to <strong>Databases &rarr; MySQL Databases</strong>. Enter a database name, user, and password, then click <strong>Create</strong>.
                                </p>
                            </div>

                            <div class="guide-step">
                                <div class="d-flex align-items-center mb-1">
                                    <span class="step-num">2</span>
                                    <strong class="small text-dark">Enter Credentials</strong>
                                </div>
                                <p class="small text-muted mb-0" style="font-size:0.8rem;">
                                    Fill in the form on the left with the exact database name, username, and password you just created.
                                </p>
                            </div>

                            <div class="guide-step">
                                <div class="d-flex align-items-center mb-1">
                                    <span class="step-num">3</span>
                                    <strong class="small text-dark">Manual Import Option</strong>
                                </div>
                                <p class="small text-muted mb-0" style="font-size:0.8rem;">
                                    If you prefer phpMyAdmin, click <strong>Enter phpMyAdmin</strong> from hPanel, select your DB, click <strong>Import</strong>, and upload <code>database/database.sql</code>.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
}
