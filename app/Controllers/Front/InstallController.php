<?php

namespace App\Controllers\Front;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\SetupHandler;
use PDOException;

class InstallController extends Controller {
    public function index(Request $request, Response $response): void {
        SetupHandler::renderInstaller();
    }

    public function setup(Request $request, Response $response): void {
        SetupHandler::renderInstaller();
    }
}
