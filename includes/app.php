<?php

/**
 * Arranque del sitio publico de Canastillas de la Baja.
 *
 * No usa la base de datos propia de Canastillas: todo el catalogo, las fotos
 * de entrega y (en la siguiente fase) el login salen de la base de datos de
 * cotizaciones.
 */

declare(strict_types=1);

require __DIR__ . '/funciones.php';

// Variables locales: includes/.env o .env en la raiz (Kubernetes tiene prioridad).
cargarEnv(__DIR__ . '/.env');
cargarEnv(dirname(__DIR__) . '/.env');

// Autoload PSR-4 minimo, sin depender de Composer para el sitio publico.
spl_autoload_register(static function (string $clase): void {
    $prefijos = [
        'Model\\'       => dirname(__DIR__) . '/models/',
        'Controllers\\' => dirname(__DIR__) . '/controllers/',
        'MVC\\'         => dirname(__DIR__) . '/router/',
    ];

    foreach ($prefijos as $prefijo => $directorio) {
        if (!str_starts_with($clase, $prefijo)) {
            continue;
        }
        $archivo = $directorio . str_replace('\\', '/', substr($clase, strlen($prefijo))) . '.php';
        if (is_file($archivo)) {
            require $archivo;
        }
        return;
    }
});

if (is_file(dirname(__DIR__) . '/vendor/autoload.php')) {
    require dirname(__DIR__) . '/vendor/autoload.php';
}

date_default_timezone_set('America/Mazatlan');
mb_internal_encoding('UTF-8');

$enDesarrollo = env('APP_ENV', 'produccion') === 'desarrollo';
ini_set('display_errors', $enDesarrollo ? '1' : '0');
error_reporting(E_ALL);

require __DIR__ . '/database.php';
require __DIR__ . '/sesion.php';
require __DIR__ . '/limitador.php';

iniciarSesion();
