<?php

namespace Model;

/**
 * Compra (venta registrada) de un cliente de Canastillas de la Baja.
 *
 * El portal es de solo consulta: no crea pedidos ni modifica ventas. Toda
 * consulta filtra por cliente y por EMPRESA_ID.
 */
class Compra extends ModeloDB
{
    public const POR_PAGINA = 15;

    public const FILTROS = [
        'todas' => 'Todas',
        'en-proceso' => 'En proceso',
        'entregadas' => 'Entregadas',
        'canceladas' => 'Canceladas',
    ];

    public int $id = 0;
    public string $folio = '';
    public int $empresa_id = 0;
    public int $cliente_id = 0;
    public string $fecha = '';
    public string $estado = 'registrada';
    public float $subtotal = 0;
    public float $descuento = 0;
    public bool $aplica_iva = true;
    public float $iva = 0;
    public float $total = 0;
    public float $cobrado = 0;
    public ?string $condiciones_envio = null;
    public ?string $condiciones_compra = null;
    public ?string $comentarios = null;
    public string $entrega_estado = 'pendiente';
    public ?string $entrega_fecha = null;
    public int $renglones = 0;
    public int $total_entregas = 0;
    public int $total_fotos = 0;

    public function __construct(array $fila = [])
    {
        foreach ($fila as $clave => $valor) {
            if (!property_exists($this, $clave) || $valor === null) {
                continue;
            }
            $this->$clave = match (gettype($this->$clave)) {
                'integer' => (int) $valor,
                'double' => (float) $valor,
                'boolean' => (bool) $valor,
                default => $valor,
            };
        }
    }

    /** @return array{compras: Compra[], total: int, paginas: int, pagina: int, filtro: string, conteos: array<string, int>} */
    public static function delCliente(int $clienteId, int $pagina = 1, string $filtro = 'todas'): array
    {
        $empresa = empresaId();
        $filtro = isset(self::FILTROS[$filtro]) ? $filtro : 'todas';

        $conteosFila = static::fila(
            "SELECT COUNT(*) AS todas,
                    COALESCE(SUM(v.estado = 'cancelada'), 0) AS canceladas,
                    COALESCE(SUM(v.estado <> 'cancelada' AND v.entrega_estado LIKE 'entregado%'), 0) AS entregadas,
                    COALESCE(SUM(v.estado = 'registrada' AND v.entrega_estado NOT LIKE 'entregado%'), 0) AS en_proceso
               FROM ventas v
              WHERE v.cliente_id = ? AND v.empresa_id = ?",
            'ii',
            [$clienteId, $empresa]
        ) ?? [];

        $conteos = [
            'todas' => (int) ($conteosFila['todas'] ?? 0),
            'en-proceso' => (int) ($conteosFila['en_proceso'] ?? 0),
            'entregadas' => (int) ($conteosFila['entregadas'] ?? 0),
            'canceladas' => (int) ($conteosFila['canceladas'] ?? 0),
        ];

        $condicion = match ($filtro) {
            'en-proceso' => "v.estado = 'registrada' AND v.entrega_estado NOT LIKE 'entregado%'",
            'entregadas' => "v.estado <> 'cancelada' AND v.entrega_estado LIKE 'entregado%'",
            'canceladas' => "v.estado = 'cancelada'",
            default => '1 = 1',
        };

        $total = $conteos[$filtro];
        $paginas = max(1, (int) ceil($total / self::POR_PAGINA));
        $pagina = min(max(1, $pagina), $paginas);
        $desde = ($pagina - 1) * self::POR_PAGINA;

        $filas = static::filas(
            "SELECT v.id, v.folio, v.empresa_id, v.cliente_id, v.fecha, v.estado,
                    v.subtotal, v.descuento, v.aplica_iva, v.iva, v.total,
                    v.condiciones_envio, v.condiciones_compra, v.comentarios,
                    v.entrega_estado, v.entrega_fecha,
                    COALESCE((SELECT SUM(ROUND(c.importe * 100))
                                FROM ingresos i
                                JOIN cobros c ON c.ingreso_id = i.id
                               WHERE i.venta_id = v.id
                                 AND i.estado = 'vigente'
                                 AND c.estado = 'vigente'), 0) / 100 AS cobrado,
                    (SELECT COUNT(*) FROM venta_detalles d WHERE d.venta_id = v.id) AS renglones,
                    (SELECT COUNT(*) FROM entregas e WHERE e.venta_id = v.id) AS total_entregas,
                    (SELECT COUNT(*) FROM entrega_fotos f
                       JOIN entregas e ON e.id = f.entrega_id
                      WHERE e.venta_id = v.id) AS total_fotos
               FROM ventas v
              WHERE v.cliente_id = ? AND v.empresa_id = ? AND {$condicion}
              ORDER BY v.fecha DESC, v.id DESC
              LIMIT ? OFFSET ?",
            'iiii',
            [$clienteId, $empresa, self::POR_PAGINA, $desde]
        );

        return [
            'compras' => array_map(static fn(array $f): self => new self($f), $filas),
            'total' => $total,
            'paginas' => $paginas,
            'pagina' => $pagina,
            'filtro' => $filtro,
            'conteos' => $conteos,
        ];
    }

    public static function buscar(int $id, int $clienteId): ?self
    {
        $fila = static::fila(
            "SELECT v.id, v.folio, v.empresa_id, v.cliente_id, v.fecha, v.estado,
                    v.subtotal, v.descuento, v.aplica_iva, v.iva, v.total,
                    v.condiciones_envio, v.condiciones_compra, v.comentarios,
                    v.entrega_estado, v.entrega_fecha,
                    COALESCE((SELECT SUM(ROUND(c.importe * 100))
                                FROM ingresos i
                                JOIN cobros c ON c.ingreso_id = i.id
                               WHERE i.venta_id = v.id
                                 AND i.estado = 'vigente'
                                 AND c.estado = 'vigente'), 0) / 100 AS cobrado,
                    (SELECT COUNT(*) FROM venta_detalles d WHERE d.venta_id = v.id) AS renglones,
                    (SELECT COUNT(*) FROM entregas e WHERE e.venta_id = v.id) AS total_entregas,
                    (SELECT COUNT(*) FROM entrega_fotos f
                       JOIN entregas e ON e.id = f.entrega_id
                      WHERE e.venta_id = v.id) AS total_fotos
               FROM ventas v
              WHERE v.id = ? AND v.cliente_id = ? AND v.empresa_id = ?",
            'iii',
            [$id, $clienteId, empresaId()]
        );
        return $fila ? new self($fila) : null;
    }

    /** @return array<int, array<string, mixed>> renglones con nombre y SKU */
    public function detalles(): array
    {
        return static::filas(
            'SELECT d.producto_id, d.cantidad, d.precio_unitario, d.descuento_pct, d.subtotal,
                    pr.nombre, pr.sku, pr.unidad, pr.imagen
               FROM venta_detalles d
               JOIN productos pr ON pr.id = d.producto_id
              WHERE d.venta_id = ?
              ORDER BY d.id',
            'i',
            [$this->id]
        );
    }

    /** @return Entrega[] */
    public function entregas(): array
    {
        return Entrega::deVenta($this->id);
    }

    public function cancelada(): bool
    {
        return $this->estado === 'cancelada';
    }

    public function entregada(): bool
    {
        return str_starts_with($this->entrega_estado, 'entregado');
    }

    public function enProceso(): bool
    {
        return !$this->cancelada() && !$this->entregada();
    }

    public function estadoClave(): string
    {
        if ($this->cancelada()) {
            return 'cancelada';
        }
        return $this->entregada() ? 'entregada' : 'en-proceso';
    }

    public function etiquetaEstado(): string
    {
        return match ($this->estadoClave()) {
            'cancelada' => 'Cancelada',
            'entregada' => 'Entregada',
            default => 'En proceso',
        };
    }

    /** Estatus de pago: pagada, parcial, pendiente o no aplica (venta cancelada). */
    public function pagoEstadoClave(): string
    {
        if ($this->cancelada()) {
            return 'no-aplica';
        }
        if ($this->cobrado >= $this->total - 0.01) {
            return 'pagada';
        }
        return $this->cobrado > 0 ? 'parcial' : 'pago-pendiente';
    }

    public function pagoEtiqueta(): string
    {
        return match ($this->pagoEstadoClave()) {
            'pagada' => 'Pagada',
            'parcial' => 'Parcial',
            'no-aplica' => 'No aplica',
            default => 'Pendiente',
        };
    }

    public function saldoPendiente(): float
    {
        return max(0, round($this->total - $this->cobrado, 2));
    }

    /** ¿La foto pertenece a una compra de este cliente? */
    public static function fotoDelCliente(int $fotoId, int $clienteId): bool
    {
        return static::fila(
            'SELECT f.id
               FROM entrega_fotos f
               JOIN entregas e ON e.id = f.entrega_id
               JOIN ventas v ON v.id = e.venta_id
              WHERE f.id = ? AND v.cliente_id = ? AND v.empresa_id = ?
              LIMIT 1',
            'iii',
            [$fotoId, $clienteId, empresaId()]
        ) !== null;
    }
}
