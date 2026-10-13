<?php

namespace App\Core;

class Request {
    private array $get;
    private array $post;
    private array $server;
    private array $files;
    private ?array $jsonBody = null;

    public function __construct(array $get = [], array $post = [], array $server = [], array $files = []) {
        $this->get = $get ?: $_GET;
        $this->post = $post ?: $_POST;
        $this->server = $server ?: $_SERVER;
        $this->files = $files ?: $_FILES;
    }

    public static function createFromGlobals(): self {
        return new self($_GET, $_POST, $_SERVER, $_FILES);
    }

    public function getMethod(): string {
        $method = strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
        if ($method === 'POST' && isset($this->post['_method'])) {
            return strtoupper($this->post['_method']);
        }
        return $method;
    }

    public function getPath(): string {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';

        // Strip subfolder or script name if hosted in subdirectory
        $scriptName = $this->server['SCRIPT_NAME'] ?? '';
        $baseDir = dirname($scriptName);
        if ($baseDir !== '/' && $baseDir !== '\\' && str_starts_with($path, $baseDir)) {
            $path = substr($path, strlen($baseDir));
        }

        $path = '/' . trim($path, '/');
        return $path === '' ? '/' : $path;
    }

    public function getQuery(string $key, mixed $default = null): mixed {
        return $this->get[$key] ?? $default;
    }

    public function getAllQuery(): array {
        return $this->get;
    }

    public function getPost(string $key, mixed $default = null): mixed {
        return $this->post[$key] ?? $default;
    }

    public function getAllPost(): array {
        return $this->post;
    }

    public function input(string $key, mixed $default = null): mixed {
        if ($this->isJson()) {
            $json = $this->getJson();
            return $json[$key] ?? $default;
        }
        return $this->post[$key] ?? $this->get[$key] ?? $default;
    }

    public function all(): array {
        if ($this->isJson()) {
            return array_merge($this->get, $this->getJson());
        }
        return array_merge($this->get, $this->post);
    }

    public function isJson(): bool {
        $contentType = $this->server['CONTENT_TYPE'] ?? '';
        return str_contains(strtolower($contentType), 'application/json');
    }

    public function getJson(): array {
        if ($this->jsonBody === null) {
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true);
            $this->jsonBody = is_array($data) ? $data : [];
        }
        return $this->jsonBody;
    }

    public function getRawBody(): string {
        return file_get_contents('php://input');
    }

    public function getFile(string $key): ?array {
        return $this->files[$key] ?? null;
    }

    public function getIp(): string {
        return $this->server['HTTP_X_FORWARDED_FOR'] ?? $this->server['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    public function getUserAgent(): string {
        return $this->server['HTTP_USER_AGENT'] ?? 'Unknown';
    }

    public function isAjax(): bool {
        return !empty($this->server['HTTP_X_REQUESTED_WITH']) &&
            strtolower($this->server['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
