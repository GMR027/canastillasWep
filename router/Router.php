<?php

namespace MVC;

class Router
{
    private array $rutas = ['GET' => [], 'POST' => []];

    public function get(string $url, callable|array $fn): void
    {
        $this->rutas['GET'][$url] = $fn;
    }

    public function post(string $url, callable|array $fn): void
    {
        $this->rutas['POST'][$url] = $fn;
    }

    public function comprobarRutas(): void
    {
        $url = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/') ?: '/';
        $metodo = $_SERVER['REQUEST_METHOD'] === 'HEAD' ? 'GET' : $_SERVER['REQUEST_METHOD'];

        foreach ($this->rutas[$metodo] ?? [] as $ruta => $fn) {
            // /productos/{slug} → grupo [a-z0-9-]+ ; {archivo:regex} → patron propio
            $patron = '#^' . preg_replace_callback(
                '#\{(\w+)(?::([^}]+))?\}#',
                fn(array $m) => '(?P<' . $m[1] . '>' . ($m[2] ?? '[a-z0-9-]+') . ')',
                $ruta
            ) . '$#';
            if (preg_match($patron, $url, $coincidencias)) {
                $parametros = array_filter($coincidencias, 'is_string', ARRAY_FILTER_USE_KEY);
                call_user_func($fn, $this, ...$parametros);
                return;
            }
        }

        $this->noEncontrado();
    }

    public function noEncontrado(): void
    {
        http_response_code(404);
        $this->render('paginas/404', ['titulo' => 'Página no encontrada']);
    }

    public function render(string $vista, array $datos = []): void
    {
        extract($datos, EXTR_SKIP);

        ob_start();
        include __DIR__ . "/../views/{$vista}.php";
        $contenido = ob_get_clean();

        include __DIR__ . '/../views/layout.php';
    }
}
