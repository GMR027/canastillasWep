<?php

namespace Model;

/**
 * Ayudantes para consultas preparadas contra la BD de cotizaciones.
 * Todos los valores van como parametros: nunca se concatenan en el SQL.
 */
abstract class ModeloDB
{
    protected static function db(): \mysqli
    {
        return db();
    }

    /** @return array<int, array<string, mixed>> */
    protected static function filas(string $sql, string $tipos = '', array $valores = []): array
    {
        $consulta = static::db()->prepare($sql);
        if ($tipos !== '') {
            $consulta->bind_param($tipos, ...$valores);
        }
        $consulta->execute();
        $resultado = $consulta->get_result();
        $filas = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
        $consulta->close();
        return $filas;
    }

    protected static function fila(string $sql, string $tipos = '', array $valores = []): ?array
    {
        return static::filas($sql, $tipos, $valores)[0] ?? null;
    }

    /** Ejecuta INSERT/UPDATE y devuelve las filas afectadas. */
    protected static function ejecutar(string $sql, string $tipos = '', array $valores = []): int
    {
        $consulta = static::db()->prepare($sql);
        if ($tipos !== '') {
            $consulta->bind_param($tipos, ...$valores);
        }
        $consulta->execute();
        $afectadas = $consulta->affected_rows;
        $consulta->close();
        return $afectadas;
    }
}
