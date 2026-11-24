<?php

namespace Controllers;

use Model\Catalogo;
use MVC\Router;

/**
 * Imagenes del catalogo de Canastillas.
 *
 * Los archivos viven en el volumen de cotizaciones (public/image). Este proxy:
 *  1. solo sirve archivos que pertenecen a productos activos de Canastillas;
 *  2. los pide al servicio interno de cotizaciones y los guarda en cache en
 *     el disco del pod; los nombres son unicos e inmutables.
 */
class ImagenesController
{
    private const MAX_BYTES = 8 * 1024 * 1024;
    private const TIPOS = ['image/jpeg', 'image/png', 'image/webp'];

    public static function catalogo(Router $router, string $archivo): void
    {
        $archivo = basename($archivo);
        $cache = self::rutaCache($archivo);

        if (!is_file($cache)) {
            try {
                $permitida = Catalogo::imagenPermitida($archivo);
            } catch (\mysqli_sql_exception $e) {
                error_log('[ImagenesController] ' . $e->getMessage());
                self::noEncontrada(503);
            }
            if (!$permitida || !self::descargar($archivo, $cache)) {
                self::noEncontrada();
            }
        }

        $etag = '"' . md5($archivo . filesize($cache)) . '"';
        header('Cache-Control: public, max-age=31536000, immutable');
        header('ETag: ' . $etag);
        header('X-Content-Type-Options: nosniff');

        if (trim($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) {
            http_response_code(304);
            return;
        }

        header('Content-Type: ' . (mime_content_type($cache) ?: 'application/octet-stream'));
        header('Content-Length: ' . filesize($cache));
        readfile($cache);
    }

    private static function rutaCache(string $archivo): string
    {
        $dir = sys_get_temp_dir() . '/cdb-img';
        if (!is_dir($dir)) {
            @mkdir($dir, 0700, true);
        }
        return $dir . '/' . $archivo;
    }

    /** Descarga de cotizaciones a la cache (escritura atomica). */
    private static function descargar(string $archivo, string $destino): bool
    {
        $base = rtrim((string) env('COTIZACIONES_URL', ''), '/');
        if ($base === '') {
            return false;
        }

        $curl = curl_init($base . '/public/image/' . rawurlencode($archivo));
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_MAXFILESIZE => self::MAX_BYTES,
        ]);
        $contenido = curl_exec($curl);
        $codigo = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);

        if (!is_string($contenido) || $codigo !== 200 || strlen($contenido) > self::MAX_BYTES) {
            error_log("[ImagenesController] {$archivo}: HTTP {$codigo}");
            return false;
        }

        $tipo = (new \finfo(FILEINFO_MIME_TYPE))->buffer($contenido);
        if (!in_array($tipo, self::TIPOS, true)) {
            error_log("[ImagenesController] {$archivo}: tipo {$tipo}");
            return false;
        }

        $temporal = $destino . '.' . bin2hex(random_bytes(4)) . '.tmp';
        if (@file_put_contents($temporal, $contenido) === false) {
            return false;
        }
        return @rename($temporal, $destino);
    }

    private static function noEncontrada(int $codigo = 404): never
    {
        http_response_code($codigo);
        header('Cache-Control: no-store');
        exit;
    }
}
