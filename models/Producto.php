<?php

namespace Model;

/**
 * Producto del catalogo publico de Canastillas (tabla productos de cotizaciones).
 * La ficha tecnica se arma con `descripcion`.
 */
class Producto
{
    public int $id = 0;
    public string $nombre = '';
    public string $sku = '';
    public string $categoria = '';
    public string $descripcion = '';
    public ?string $imagen = null;
    public ?string $imagen_uso = null;

    public function __construct(array $fila = [])
    {
        $this->id = (int) ($fila['id'] ?? 0);
        $this->nombre = (string) ($fila['nombre'] ?? '');
        $this->sku = (string) ($fila['sku'] ?? '');
        $this->categoria = (string) ($fila['categoria'] ?? '');
        $this->descripcion = (string) ($fila['descripcion'] ?? '');
        $this->imagen = ($fila['imagen'] ?? '') ?: null;
        $this->imagen_uso = ($fila['imagen_uso'] ?? '') ?: null;
    }

    /** @return Producto[] */
    public static function all(): array
    {
        return Catalogo::productos();
    }

    /** Identificador apto para atributos HTML (C-1 -> c-1). */
    public function slug(): string
    {
        return strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $this->sku));
    }

    /** Primera linea de la descripcion. */
    public function subtitulo(): string
    {
        return rtrim($this->lineas()[0] ?? '', '.');
    }

    /** Medida principal, p. ej. "40x60 cm". */
    public function dimension(): ?string
    {
        return $this->especificaciones()['Dimensión'] ?? null;
    }

    /** Texto de "Tipo de uso" sin el prefijo. */
    public function uso(): ?string
    {
        foreach ($this->lineas() as $linea) {
            if (preg_match('/^Tipo de uso:\s*(.+)$/u', $linea, $m)) {
                return self::capitalizar(rtrim($m[1], '.'));
            }
        }
        return null;
    }

    /**
     * Convierte la descripcion de la BD en pares clave => valor.
     * Las frases sin "clave:" se agregan en 'Notas'.
     */
    public function especificaciones(): array
    {
        $specs = [];
        $notas = [];

        foreach (array_slice($this->lineas(), 1) as $linea) {
            if (str_starts_with($linea, 'Tipo de uso:')) {
                continue;
            }
            $frases = preg_split('/(?<=\.)\s+(?=\p{Lu})|;\s+/u', $linea) ?: [$linea];
            foreach ($frases as $frase) {
                $frase = rtrim(trim($frase), '.');
                if ($frase === '') {
                    continue;
                }
                if (preg_match('/^([^:]{2,40}):\s*(.+)$/u', $frase, $m)) {
                    [$clave, $valor] = [trim($m[1]), trim($m[2])];
                    if (preg_match('/^(.+?),\s*([^,:]{2,20}):\s*(.+)$/u', $valor, $c)) {
                        $clave .= ' / ' . trim($c[2]);
                        $valor = trim($c[1]) . ' / ' . trim($c[3]);
                    }
                    $specs[self::capitalizar($clave)] = self::capitalizar($valor);
                } else {
                    $notas[] = self::capitalizar($frase);
                }
            }
        }

        if ($notas) {
            $specs['Notas'] = implode('. ', $notas);
        }

        return $specs;
    }

    /** URL de la imagen principal (proxy /img/catalogo) o null. */
    public function imagenPrincipal(): ?string
    {
        return imagenCatalogo($this->imagen);
    }

    /** URL de la imagen de uso (producto instalado) o null. */
    public function imagenUso(): ?string
    {
        return imagenCatalogo($this->imagen_uso);
    }

    /** Imagen para la tarjeta: principal y, si falta, la de uso. */
    public function imagenTarjeta(): ?string
    {
        return $this->imagenPrincipal() ?? $this->imagenUso();
    }

    public function mensajeCotizacion(): string
    {
        return "Hola, me interesa cotizar el producto {$this->sku} - {$this->nombre}.";
    }

    /**
     * Descripcion que usa el sitio: la de cotizaciones si ya trae el formato
     * estructurado; si no, una ficha de respaldo por SKU.
     */
    public function descripcionEfectiva(): string
    {
        $descripcion = trim($this->descripcion);
        $estructurada = str_contains($descripcion, "\n") && str_contains($descripcion, ':');
        if ($estructurada) {
            return $descripcion;
        }

        static $respaldo = null;
        $respaldo ??= require __DIR__ . '/../data/productos_fallback.php';
        return (string) ($respaldo[$this->sku] ?? $descripcion);
    }

    private function lineas(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/u', $this->descripcionEfectiva()))));
    }

    private static function capitalizar(string $texto): string
    {
        return mb_strtoupper(mb_substr($texto, 0, 1)) . mb_substr($texto, 1);
    }
}
