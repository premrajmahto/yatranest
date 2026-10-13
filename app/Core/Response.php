<?php

namespace App\Core;

class Response {
    private int $statusCode = 200;
    private array $headers = [];

    public function setStatusCode(int $code): self {
        $this->statusCode = $code;
        return $this;
    }

    public function getStatusCode(): int {
        return $this->statusCode;
    }

    public function setHeader(string $name, string $value): self {
        $this->headers[$name] = $value;
        return $this;
    }

    public function sendHeaders(): void {
        if (!headers_sent()) {
            http_response_code($this->statusCode);
            foreach ($this->headers as $name => $value) {
                header("$name: $value");
            }
        }
    }

    public function json(mixed $data, int $status = 200): void {
        $this->setStatusCode($status);
        $this->setHeader('Content-Type', 'application/json; charset=utf-8');
        $this->sendHeaders();
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public function redirect(string $url, int $status = 302): void {
        $target = str_starts_with($url, 'http://') || str_starts_with($url, 'https://') ? $url : url($url);
        header("Location: $target", true, $status);
        exit;
    }

    public function download(string $filePath, ?string $filename = null): void {
        if (!file_exists($filePath)) {
            $this->setStatusCode(404);
            die("File not found");
        }

        $filename = $filename ?: basename($filePath);
        $mime = mime_content_type($filePath) ?: 'application/octet-stream';

        $this->setHeader('Content-Description', 'File Transfer');
        $this->setHeader('Content-Type', $mime);
        $this->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $this->setHeader('Expires', '0');
        $this->setHeader('Cache-Control', 'must-revalidate');
        $this->setHeader('Pragma', 'public');
        $this->setHeader('Content-Length', (string)filesize($filePath));
        $this->sendHeaders();

        readfile($filePath);
        exit;
    }
}
