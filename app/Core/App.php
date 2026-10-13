<?php

namespace App\Core;

class App {
    private static ?self $instance = null;
    private array $config = [];
    private string $basePath;
    private Router $router;
    private Auth $auth;
    private View $view;

    public function __construct(string $basePath) {
        self::$instance = $this;
        $this->basePath = rtrim($basePath, '/\\');

        $this->loadEnv();
        $this->loadConfig();
        date_default_timezone_set($this->getConfig('app.timezone', 'Asia/Kolkata'));
        Logger::init($this->basePath . '/storage');
        Session::start();

        $this->auth = new Auth();
        $this->router = new Router();
        $this->view = new View($this->basePath . '/app/Views');
    }

    public static function getInstance(): self {
        return self::$instance ?? new self(dirname(__DIR__, 2));
    }

    public function getBasePath(): string {
        return $this->basePath;
    }

    public function getRouter(): Router {
        return $this->router;
    }

    public function getAuth(): Auth {
        return $this->auth;
    }

    public function getView(): View {
        return $this->view;
    }

    public function renderView(string $view, array $data = [], ?string $layout = 'main'): void {
        $this->view->render($view, $data, $layout);
    }

    public function getConfig(string $key, mixed $default = null): mixed {
        $segments = explode('.', $key);
        $file = array_shift($segments);

        if (!isset($this->config[$file])) {
            $configPath = $this->basePath . '/config/' . $file . '.php';
            if (file_exists($configPath)) {
                $this->config[$file] = require $configPath;
            } else {
                return $default;
            }
        }

        $value = $this->config[$file];
        foreach ($segments as $segment) {
            if (!is_array($value) || !isset($value[$segment])) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    private function loadEnv(): void {
        $envCandidates = [
            $this->basePath . '/.env',
            dirname($this->basePath) . '/.env',
            $this->basePath . '/.env.production',
            $this->basePath . '/.env.local',
        ];

        foreach ($envCandidates as $envFile) {
            if (file_exists($envFile)) {
                $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line) || str_starts_with($line, '#')) {
                        continue;
                    }
                    if (str_contains($line, '=')) {
                        [$key, $value] = explode('=', $line, 2);
                        $key = trim($key);
                        $value = trim($value);
                        // Strip quotes if present
                        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                            (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                            $value = substr($value, 1, -1);
                        }
                        $_ENV[$key] = $value;
                        $_SERVER[$key] = $value;
                        putenv("$key=$value");
                    }
                }
                break;
            }
        }
    }

    private function loadConfig(): void {
        // Preload base configs
        foreach (['app', 'database', 'mail', 'razorpay'] as $cfg) {
            $path = $this->basePath . '/config/' . $cfg . '.php';
            if (file_exists($path)) {
                $this->config[$cfg] = require $path;
            }
        }
    }

    public function run(): void {
        $request = Request::createFromGlobals();
        $response = new Response();

        // Load route definitions
        $router = $this->router;
        require_once $this->basePath . '/routes/web.php';
        require_once $this->basePath . '/routes/api.php';

        try {
            $this->router->dispatch($request, $response);
        } catch (\Throwable $e) {
            Logger::error("Unhandled exception: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            if (config('app.debug', false)) {
                $response->setStatusCode(500);
                view('front/errors/500', [
                    'title' => 'Server Error',
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
            } else {
                $response->setStatusCode(500);
                view('front/errors/500', [
                    'title' => 'Server Error',
                    'message' => 'An unexpected error occurred. Please try again later.'
                ]);
            }
        }
    }
}
