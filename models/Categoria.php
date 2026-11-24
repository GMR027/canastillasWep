<?php

namespace Model;

/** Categoria del catalogo publico (tabla categorias_producto). Ver Catalogo. */
class Categoria
{
    public int $id = 0;
    public string $slug = '';
    public string $nombre = '';
    public string $resumen = '';
    public ?string $imagen = null;

    /** @var Producto[] */
    private array $productos = [];

    public function __construct(array $datos = [])
    {
        $this->id = (int) ($datos['id'] ?? 0);
        $this->slug = (string) ($datos['slug'] ?? '');
        $this->nombre = (string) ($datos['nombre'] ?? '');
        $this->resumen = (string) ($datos['resumen'] ?? '');
        $this->imagen = ($datos['imagen'] ?? '') ?: null;
    }

    /** @return Categoria[] */
    public static function all(): array
    {
        return Catalogo::categorias();
    }

    public static function buscar(string $slug): ?self
    {
        return Catalogo::categoria($slug);
    }

    public function agregar(Producto $producto): void
    {
        $this->productos[] = $producto;
    }

    public function url(): string
    {
        return '/productos/' . $this->slug;
    }

    /** URL de la portada: la de la categoria o, si no tiene, la del primer producto. */
    public function portada(): ?string
    {
        if ($this->imagen) {
            return imagenCatalogo($this->imagen);
        }
        foreach ($this->productos as $producto) {
            if ($producto->imagenPrincipal()) {
                return $producto->imagenPrincipal();
            }
        }
        return null;
    }

    /** @return Producto[] */
    public function productos(): array
    {
        return $this->productos;
    }

    public function totalProductos(): int
    {
        return count($this->productos);
    }
}
