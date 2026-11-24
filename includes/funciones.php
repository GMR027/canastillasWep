<?php

/** Escapa texto para imprimirlo en HTML. */
function s(?string $texto): string
{
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Carga un archivo .env sencillo (KEY=VALUE) sin dependencias externas.
 * Las variables reales del entorno tienen prioridad sobre el archivo.
 */
function cargarEnv(string $archivo): void
{
    if (!is_file($archivo) || !is_readable($archivo)) {
        return;
    }

    foreach (file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linea) {
        $linea = trim($linea);
        if ($linea === '' || str_starts_with($linea, '#')) {
            continue;
        }
        if (!str_contains($linea, '=')) {
            continue;
        }
        [$clave, $valor] = array_map('trim', explode('=', $linea, 2));
        if ($clave === '' || getenv($clave) !== false) {
            continue;
        }
        $valor = trim($valor, " \t\n\r\0\x0B\"'");
        $_ENV[$clave] = $valor;
        putenv($clave . '=' . $valor);
    }
}

/**
 * Lee una variable de entorno. La real (Kubernetes, shell) tiene prioridad;
 * includes/.env es solo el respaldo local.
 */
function env(string $clave, ?string $porDefecto = null): ?string
{
    $valor = getenv($clave);
    if ($valor === false || $valor === '') {
        $valor = $_ENV[$clave] ?? null;
    }
    return ($valor === null || $valor === '') ? $porDefecto : (string) $valor;
}

function debuguear(mixed $variable): never
{
    echo '<pre>';
    var_dump($variable);
    echo '</pre>';
    exit;
}

function redirigir(string $url, int $codigo = 303): never
{
    header('Location: ' . $url, true, $codigo);
    exit;
}

/** Datos generales del sitio (contacto, redes, cobertura). */
function sitio(?string $clave = null): mixed
{
    static $datos = null;
    $datos ??= require __DIR__ . '/../data/sitio.php';
    return $clave === null ? $datos : ($datos[$clave] ?? null);
}

/**
 * Ruta publica de un recurso compilado por gulp (build/).
 * Lleva ?v=<fecha del archivo> para evitar cache viejo.
 */
function asset(string $ruta): string
{
    $ruta = '/build/' . ltrim($ruta, '/');
    $archivo = __DIR__ . '/../' . ltrim($ruta, '/');
    $version = is_file($archivo) ? filemtime($archivo) : false;
    return $version ? $ruta . '?v=' . $version : $ruta;
}

/** Ruta publica de una imagen dentro de build/img. */
function img(string $ruta): string
{
    return asset('img/' . implode('/', array_map('rawurlencode', explode('/', ltrim($ruta, '/')))));
}

/** Enlace de WhatsApp con mensaje prellenado. */
function whatsapp(string $mensaje = ''): string
{
    $url = 'https://wa.me/' . sitio('whatsapp');
    return $mensaje === '' ? $url : $url . '?text=' . rawurlencode($mensaje);
}

/** Devuelve el SVG de un icono de views/templates/iconos.php. */
function icono(string $nombre, string $clase = 'icono'): string
{
    static $iconos = null;
    $iconos ??= require __DIR__ . '/../views/templates/iconos.php';
    $svg = $iconos[$nombre] ?? '';
    return str_replace('<svg ', '<svg class="' . s($clase) . '" aria-hidden="true" focusable="false" ', $svg);
}

function dinero(float|string|null $monto): string
{
    return '$' . number_format((float) $monto, 2, '.', ',');
}

function fecha(?string $fecha): string
{
    if (!$fecha) {
        return '';
    }
    $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
    $t = strtotime($fecha);
    return date('j', $t) . ' ' . $meses[(int) date('n', $t) - 1] . ' ' . date('Y', $t);
}

/** URL publica de una imagen del catalogo (servida por ImagenesController). */
function imagenCatalogo(?string $archivo): ?string
{
    $archivo = basename((string) $archivo);
    return $archivo !== '' ? '/img/catalogo/' . rawurlencode($archivo) : null;
}
