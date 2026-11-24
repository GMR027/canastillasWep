<?php

namespace Model;

/**
 * Catalogo publico de Canastillas leido de la BD de cotizaciones.
 *
 * Una sola consulta por peticion: productos activos y publicados en web de
 * la empresa 2 (Canastillas de la Baja). Si la BD no responde, el catalogo
 * queda vacio y disponible() devuelve false (la pagina lo avisa en lugar de
 * romperse).
 */
final class Catalogo extends ModeloDB
{
    /** @var Producto[]|null */
    private static ?array $productos = null;

    /** @var array<string, Categoria> por slug */
    private static array $categorias = [];

    private static bool $disponible = true;

    private const SQL = 'SELECT p.id, p.nombre, p.sku, p.descripcion, p.imagen, p.imagen_uso,
                                c.id AS categoria_id, c.nombre AS categoria, c.slug AS categoria_slug,
                                c.descripcion AS categoria_descripcion, c.imagen AS categoria_imagen,
                                c.orden AS categoria_orden
                           FROM productos p
                           JOIN categorias_producto c ON c.id = p.categoria_id
                          WHERE p.activo = 1 AND p.publicado_web = 1
                            AND (p.empresa_id = ? OR EXISTS (
                                 SELECT 1 FROM producto_empresa pe WHERE pe.producto_id = p.id AND pe.empresa_id = ?))
                          ORDER BY c.orden, c.nombre, p.sku, p.nombre';

    private static function cargar(): void
    {
        if (self::$productos !== null) {
            return;
        }

        self::$productos = [];
        try {
            $empresa = empresaId();
            $filas = static::filas(self::SQL, 'ii', [$empresa, $empresa]);
        } catch (\mysqli_sql_exception $e) {
            error_log('[Catalogo] ' . $e->getMessage());
            self::$disponible = false;
            return;
        }

        foreach ($filas as $fila) {
            $producto = new Producto($fila);
            self::$productos[] = $producto;

            $slug = (string) $fila['categoria_slug'];
            if (!isset(self::$categorias[$slug])) {
                self::$categorias[$slug] = new Categoria([
                    'id' => $fila['categoria_id'],
                    'slug' => $slug,
                    'nombre' => $fila['categoria'],
                    'resumen' => $fila['categoria_descripcion'],
                    'imagen' => $fila['categoria_imagen'],
                ]);
            }
            self::$categorias[$slug]->agregar($producto);
        }
    }

    public static function disponible(): bool
    {
        self::cargar();
        return self::$disponible;
    }

    /** @return Categoria[] en el orden definido en cotizaciones */
    public static function categorias(): array
    {
        self::cargar();
        return array_values(self::$categorias);
    }

    public static function categoria(string $slug): ?Categoria
    {
        self::cargar();
        return self::$categorias[$slug] ?? null;
    }

    /** @return Producto[] */
    public static function productos(): array
    {
        self::cargar();
        return self::$productos;
    }

    /**
     * ¿El proxy de imagenes puede servir este archivo? Solo imagenes de
     * productos activos de la empresa (publicados o pedibles en el portal) y
     * portadas de sus categorias. Nunca fotos de entrega, firmas ni logos.
     */
    public static function imagenPermitida(string $archivo): bool
    {
        $empresa = empresaId();
        return static::fila(
            'SELECT 1
               FROM productos p
               LEFT JOIN categorias_producto c ON c.id = p.categoria_id
              WHERE p.activo = 1
                AND p.publicado_web = 1
                AND (p.empresa_id = ? OR EXISTS (
                     SELECT 1 FROM producto_empresa pe WHERE pe.producto_id = p.id AND pe.empresa_id = ?))
                AND (p.imagen = ? OR p.imagen_uso = ? OR c.imagen = ?)
              LIMIT 1',
            'iisss',
            [$empresa, $empresa, $archivo, $archivo, $archivo]
        ) !== null;
    }
}
