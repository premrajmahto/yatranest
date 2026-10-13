<?php

namespace App\Core;

class Router {
    private array $routes = [];
    private array $middlewareGroups = [];

    public function get(string $path, array|callable $handler, array $middleware = []): self {
        $this->addRoute('GET', $path, $handler, $middleware);
        return $this;
    }

    public function post(string $path, array|callable $handler, array $middleware = []): self {
        $this->addRoute('POST', $path, $handler, $middleware);
        return $this;
    }

    public function put(string $path, array|callable $handler, array $middleware = []): self {
        $this->addRoute('PUT', $path, $handler, $middleware);
        return $this;
    }

    public function delete(string $path, array|callable $handler, array $middleware = []): self {
        $this->addRoute('DELETE', $path, $handler, $middleware);
        return $this;
    }

    private function addRoute(string $method, string $path, array|callable $handler, array $middleware = []): void {
        $path = '/' . trim($path, '/');
        if ($path === '') $path = '/';

        // Convert {param} to regex named group (?P<param>[^/]+)
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        $regex = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'regex' => $regex,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(Request $request, Response $response): mixed {
        $requestMethod = $request->getMethod();
        $requestPath = $request->getPath();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            if (preg_match($route['regex'], $requestPath, $matches)) {
                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = urldecode($value);
                    }
                }

                // Execute middleware pipeline
                foreach ($route['middleware'] as $mw) {
                    $mwInstance = is_string($mw) ? new $mw() : $mw;
                    $result = $mwInstance->handle($request, $response);
                    if ($result === false) {
                        return null;
                    }
                }

                $handler = $route['handler'];

                if (is_callable($handler)) {
                    return call_user_func_array($handler, array_merge([$request, $response], array_values($params)));
                }

                if (is_array($handler) && count($handler) === 2) {
                    [$class, $action] = $handler;
                    $controller = new $class();
                    return call_user_func_array([$controller, $action], array_merge([$request, $response], array_values($params)));
                }
            }
        }

        // Route Not Found 404
        $response->setStatusCode(404);
        view('front/errors/404', ['title' => 'Page Not Found']);
        return null;
    }
}
