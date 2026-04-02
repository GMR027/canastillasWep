<?php

class Router {
    protected array $routes = [];

    public function get(string $url, string $archivo, string $middleware = ''): void {
        $this->routes['GET'][$url] = ['archivo' => $archivo, 'middleware' => $middleware];
    }

    public function post(string $url, string $archivo, string $middleware = ''): void {
        $this->routes['POST'][$url] = ['archivo' => $archivo, 'middleware' => $middleware];
    }

    public function comprobarRutas(): ?string {
        $method = $_SERVER['REQUEST_METHOD'];
        $url    = parse_url($_SERVER['REQUEST_URI'])['path'];

        $ruta = $this->routes[$method][$url] ?? null;

        if (!$ruta) {
            http_response_code(404);
            echo '404 - Página no encontrada';
            return null;
        }

        if ($ruta['middleware']) {
            ($ruta['middleware'])();
        }

        return $ruta['archivo'];
    }
}
