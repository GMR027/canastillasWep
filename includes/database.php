<?php

/**
 * Conexion perezosa a la base de datos de cotizaciones.
 *
 * Las paginas publicas no abren conexion hasta que un modelo la necesita,
 * asi que el sitio sigue respondiendo (con avisos) si MySQL no esta arriba.
 */
function db(): mysqli
{
    static $conexion = null;

    if ($conexion === null) {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $conexion = new mysqli(
            env('DB_HOST', 'localhost'),
            env('DB_USER', ''),
            env('DB_PASSWORD', ''),
            env('DB_NAME', 'cotizaciones'),
            (int) env('DB_PORT', '3306')
        );
        $conexion->set_charset('utf8mb4');
        $conexion->query("SET time_zone = '" . (new DateTime())->format('P') . "'");
    }

    return $conexion;
}

/** Id de "Canastillas de la Baja" en la tabla empresas de cotizaciones. */
function empresaId(): int
{
    return (int) env('EMPRESA_ID', '2');
}
