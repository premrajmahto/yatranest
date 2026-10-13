<?php

namespace App\Core;

class View {
    private string $viewsPath;

    public function __construct(string $viewsPath) {
        $this->viewsPath = rtrim($viewsPath, '/\\');
    }

    public function render(string $view, array $data = [], ?string $layout = 'main'): void {
        $viewFile = $this->viewsPath . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            Logger::error("View not found: $viewFile");
            throw new \RuntimeException("View [$view] not found at $viewFile");
        }

        // Extract variables to view scope
        extract($data, EXTR_SKIP);

        // Buffer view content
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout === null) {
            echo $content;
            return;
        }

        $layoutFile = $this->viewsPath . '/layouts/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            Logger::error("Layout not found: $layoutFile");
            throw new \RuntimeException("Layout [$layout] not found at $layoutFile");
        }

        require $layoutFile;
    }
}
