<?php

namespace Controllers;

use Model\Compra;
use Model\Entrega;
use Model\Galeria;
use MVC\Router;

/**
 * Proxy de la galeria publica y de las fotos/actas del portal.
 *
 * Solo sirve fotos que hoy estan entre las ultimas de Canastillas de la Baja
 * (Galeria::fotoPermitida) y las pide firmadas a cotizaciones en modo
 * "galeria": con el logo, sin fecha ni ubicacion del cliente y sin EXIF.
 * Las guarda en cache en el disco del pod.
 */
class FotosController
{
    private const VIGENCIA = 300;
    private const ANCHO_GALERIA = 1200;

    public static function galeria(Router $router, string $id): void
    {
        $fotoId = (int) $id;
        if (!Galeria::fotoPermitida($fotoId)) {
            self::noDisponible();
        }

        $cache = self::rutaCache("galeria-{$fotoId}-" . self::ANCHO_GALERIA . '.jpg');
        $contenido = is_file($cache) ? (string) file_get_contents($cache) : '';
        if ($contenido === '') {
            [$contenido] = self::pedirFirmada($fotoId, self::ANCHO_GALERIA, 'galeria') ?? self::noDisponible();
            $temporal = $cache . '.' . bin2hex(random_bytes(4)) . '.tmp';
            if (@file_put_contents($temporal, $contenido) === false || !@rename($temporal, $cache)) {
                @unlink($temporal);
            }
        }

        header('Content-Type: image/jpeg');
        header('Content-Length: ' . strlen($contenido));
        header('Cache-Control: public, max-age=86400');
        header('X-Content-Type-Options: nosniff');
        echo $contenido;
    }


    /**
     * Foto privada de una compra del cliente con sesion.
     * Se pide firmada a cotizaciones con la marca de agua completa.
     */
    public static function foto(Router $router, string $id): void
    {
        $cliente = soloCliente();
        $fotoId = (int) $id;
        $ancho = in_array((int) ($_GET['w'] ?? 0), [400, 1600], true) ? (int) $_GET['w'] : 1600;

        if (!Compra::fotoDelCliente($fotoId, $cliente->id)) {
            self::noDisponible();
        }

        [$contenido, $tipo] = self::pedirFirmada($fotoId, $ancho) ?? self::noDisponible();

        header('Content-Type: ' . $tipo);
        header('Content-Length: ' . strlen($contenido));
        header('Cache-Control: private, max-age=86400');
        header('X-Content-Type-Options: nosniff');
        echo $contenido;
    }

    /**
     * Firma de la entrega. Solo se sirve si la entrega pertenece a una compra
     * del cliente con sesion; el archivo se pide al servicio interno.
     */
    public static function firma(Router $router, string $id): void
    {
        $cliente = soloCliente();
        $entrega = Entrega::buscar((int) $id, $cliente->id);

        if (!$entrega || !$entrega->firma_archivo) {
            self::noDisponible();
        }

        $archivo = basename($entrega->firma_archivo);
        $base = rtrim((string) env('COTIZACIONES_URL', ''), '/');
        if ($base === '') {
            self::noDisponible();
        }

        $curl = curl_init($base . '/public/image/' . rawurlencode($archivo));
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_MAXFILESIZE => 8 * 1024 * 1024,
        ]);
        $contenido = curl_exec($curl);
        $codigo = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $tipo = (string) curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
        curl_close($curl);

        if (!is_string($contenido) || $codigo !== 200 || !preg_match('#^image/(jpeg|png|webp)#', $tipo)) {
            self::noDisponible();
        }

        header('Content-Type: ' . $tipo);
        header('Content-Length: ' . strlen($contenido));
        header('Cache-Control: private, max-age=86400');
        header('X-Content-Type-Options: nosniff');
        echo $contenido;
    }

    /**
     * Pide la foto a cotizaciones con una URL firmada (HMAC con vigencia corta).
     *
     * @return array{0: string, 1: string}|null [bytes, tipo]
     */
    private static function pedirFirmada(int $fotoId, int $ancho, string $modo = ''): ?array
    {
        $secreto = (string) env('PORTAL_FOTOS_SECRETO', '');
        $base = rtrim((string) env('COTIZACIONES_URL', ''), '/');
        if (strlen($secreto) < 32 || $base === '') {
            return null;
        }

        $expira = time() + self::VIGENCIA;
        $datos = "{$fotoId}|{$ancho}|{$expira}" . ($modo !== '' ? "|{$modo}" : '');
        $parametros = ['id' => $fotoId, 'w' => $ancho, 'exp' => $expira, 'sig' => hash_hmac('sha256', $datos, $secreto)];
        if ($modo !== '') {
            $parametros['modo'] = $modo;
        }

        $curl = curl_init($base . '/portal/entregas/foto?' . http_build_query($parametros));
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_MAXFILESIZE => 15 * 1024 * 1024,
        ]);
        $contenido = curl_exec($curl);
        $codigo = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $tipo = (string) curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
        curl_close($curl);

        if (!is_string($contenido) || $codigo !== 200 || !preg_match('#^image/(jpeg|png|webp)#', $tipo)) {
            error_log("[FotosController] foto {$fotoId}" . ($modo !== '' ? " ({$modo})" : '') . ": HTTP {$codigo} {$tipo}");
            return null;
        }
        return [$contenido, $tipo];
    }

    private static function rutaCache(string $archivo): string
    {
        $dir = sys_get_temp_dir() . '/cdb-img';
        if (!is_dir($dir)) {
            @mkdir($dir, 0700, true);
        }
        return $dir . '/' . $archivo;
    }

    private static function noDisponible(): never
    {
        http_response_code(404);
        header('Cache-Control: no-store');
        exit;
    }
}
