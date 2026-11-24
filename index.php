<?php

// Servidor de desarrollo (php -S localhost:8000 -t . index.php):
// los archivos reales (CSS, JS, imagenes, video) los sirve el propio servidor.
if (PHP_SAPI === 'cli-server') {
    $ruta = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (is_string($ruta) && $ruta !== '/' && !str_contains($ruta, '..') && is_file(__DIR__ . $ruta)) {
        return false;
    }
}

require_once __DIR__ . '/includes/app.php';

use Controllers\AuthController;
use Controllers\CuentaController;
use Controllers\FotosController;
use Controllers\ImagenesController;
use Controllers\PaginasController;
use MVC\Router;

$router = new Router();

// Sitio publico
$router->get('/', [PaginasController::class, 'inicio']);

// Portal de clientes: sesion
$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

// Portal de clientes: compras de solo consulta
$router->get('/mi-cuenta', [CuentaController::class, 'inicio']);
$router->get('/mi-cuenta/compras', [CuentaController::class, 'compras']);
$router->get('/mi-cuenta/compras/{id:[0-9]+}', [CuentaController::class, 'detalle']);
$router->get('/mi-cuenta/entregas/{id:[0-9]+}/acta', [CuentaController::class, 'acta']);
$router->get('/mi-cuenta/entregas/{id:[0-9]+}/firma', [FotosController::class, 'firma']);
$router->get('/mi-cuenta/fotos/{id:[0-9]+}', [FotosController::class, 'foto']);

// Imagenes servidas desde cotizaciones
$router->get('/img/entregas/{id:[0-9]+}', [FotosController::class, 'galeria']);
$router->get('/img/catalogo/{archivo:[A-Za-z0-9_-]+\.(?:jpe?g|png|webp)}', [ImagenesController::class, 'catalogo']);

$router->comprobarRutas();
