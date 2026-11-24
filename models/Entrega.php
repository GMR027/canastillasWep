<?php

namespace Model;

/** Entrega (acta) de una compra de Canastillas de la Baja. */
class Entrega extends ModeloDB
{
    public int $id = 0;
    public int $venta_id = 0;
    public string $fecha = '';
    public ?string $recibio_nombre = null;
    public ?string $firma_archivo = null;
    public ?string $notas = null;
    public ?string $ubicacion_maps = null;
    public ?float $ubicacion_lat = null;
    public ?float $ubicacion_lon = null;
    public int $fotos = 0;

    public function __construct(array $fila = [])
    {
        foreach ($fila as $clave => $valor) {
            if (!property_exists($this, $clave) || $valor === null) {
                continue;
            }
            $this->$clave = match (gettype($this->$clave)) {
                'integer' => (int) $valor,
                'double' => (float) $valor,
                default => $valor,
            };
        }
    }

    /** @return Entrega[] */
    public static function deVenta(int $ventaId): array
    {
        $filas = static::filas(
            'SELECT e.id, e.venta_id, e.fecha, e.recibio_nombre, e.firma_archivo, e.notas,
                    e.ubicacion_maps, e.ubicacion_lat, e.ubicacion_lon,
                    (SELECT COUNT(*) FROM entrega_fotos f WHERE f.entrega_id = e.id) AS fotos
               FROM entregas e
              WHERE e.venta_id = ?
              ORDER BY e.fecha, e.id',
            'i',
            [$ventaId]
        );
        return array_map(static fn(array $f): self => new self($f), $filas);
    }

    public static function buscar(int $id, int $clienteId): ?self
    {
        $fila = static::fila(
            'SELECT e.id, e.venta_id, e.fecha, e.recibio_nombre, e.firma_archivo, e.notas,
                    e.ubicacion_maps, e.ubicacion_lat, e.ubicacion_lon,
                    (SELECT COUNT(*) FROM entrega_fotos f WHERE f.entrega_id = e.id) AS fotos
               FROM entregas e
               JOIN ventas v ON v.id = e.venta_id
              WHERE e.id = ? AND v.cliente_id = ? AND v.empresa_id = ?',
            'iii',
            [$id, $clienteId, empresaId()]
        );
        return $fila ? new self($fila) : null;
    }

    /** @return array<int, array{id: int, tomada_en: ?string, fecha: ?string}> */
    public function fotos(): array
    {
        return static::filas(
            'SELECT f.id, f.tomada_en, e.fecha
               FROM entrega_fotos f
               JOIN entregas e ON e.id = f.entrega_id
              WHERE f.entrega_id = ?
              ORDER BY f.id',
            'i',
            [$this->id]
        );
    }

    /** @return array<int, array<string, mixed>> productos realmente entregados */
    public function renglones(): array
    {
        return static::filas(
            'SELECT r.producto_id, r.cantidad, pr.nombre, pr.sku, pr.unidad
               FROM entrega_renglones r
               JOIN productos pr ON pr.id = r.producto_id
              WHERE r.entrega_id = ?
              ORDER BY r.id',
            'i',
            [$this->id]
        );
    }

    public function recibio(): string
    {
        $nombre = trim((string) $this->recibio_nombre);
        return $nombre !== '' ? $nombre : 'No registrado';
    }

    public function mapa(): ?string
    {
        if (!empty($this->ubicacion_maps)) {
            return $this->ubicacion_maps;
        }
        if ($this->ubicacion_lat !== null && $this->ubicacion_lon !== null) {
            return 'https://www.google.com/maps?q=' . $this->ubicacion_lat . ',' . $this->ubicacion_lon;
        }
        return null;
    }

    public function firmaUrl(): ?string
    {
        return !empty($this->firma_archivo) ? '/mi-cuenta/entregas/' . $this->id . '/firma' : null;
    }

    public function actaUrl(): string
    {
        return '/mi-cuenta/entregas/' . $this->id . '/acta';
    }

    public function tieneFotos(): bool
    {
        return $this->fotos > 0;
    }
}
