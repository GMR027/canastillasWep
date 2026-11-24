<?php

namespace Model;

/**
 * Galeria de entregas de la pagina de inicio.
 *
 * Una foto por entrega (la primera que se subio) de las ultimas entregas de
 * las ventas vigentes de Canastillas de la Baja (EMPRESA_ID = 2). Solo se lee
 * el id de la foto: la imagen se pide firmada a cotizaciones en modo
 * "galeria", que la devuelve con el logo y sin fecha ni ubicacion del cliente.
 */
final class Galeria extends ModeloDB
{
    public const LIMITE = 10;

    /** @var int[]|null */
    private static ?array $fotos = null;

    /** @return int[] ids de foto, de la entrega mas reciente a la mas antigua */
    public static function fotos(): array
    {
        if (self::$fotos !== null) {
            return self::$fotos;
        }

        try {
            $filas = static::filas(
                "SELECT MIN(f.id) AS id
                   FROM entrega_fotos f
                   JOIN entregas e ON e.id = f.entrega_id
                   JOIN ventas v   ON v.id = e.venta_id
                  WHERE v.empresa_id = ? AND v.estado = 'registrada'
                  GROUP BY e.id
                  ORDER BY MAX(e.fecha) DESC, e.id DESC
                  LIMIT " . self::LIMITE,
                'i',
                [empresaId()]
            );
        } catch (\mysqli_sql_exception $e) {
            error_log('[Galeria] ' . $e->getMessage());
            $filas = [];
        }

        return self::$fotos = array_map(static fn (array $f): int => (int) $f['id'], $filas);
    }

    /** Solo las fotos que hoy estan en la galeria se pueden pedir sin sesion. */
    public static function fotoPermitida(int $id): bool
    {
        return in_array($id, self::fotos(), true);
    }
}
