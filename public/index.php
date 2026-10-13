<?php

declare(strict_types=1);

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Autoload & helpers
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/app/Helpers/Helpers.php';

use App\Core\App;

// Bootstrap application
$app = new App(dirname(__DIR__));
$app->run();
