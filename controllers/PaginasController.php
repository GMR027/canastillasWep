<?php

namespace Controllers;

use Model\Catalogo;
use Model\Galeria;
use Model\Producto;
use MVC\Router;

class PaginasController
{
    public static function inicio(Router $router): void
    {
        $productos = Producto::all();

        $router->render('paginas/inicio', [
            'titulo' => 'Canastillas y marcos de concreto en Baja California Sur',
            'descripcion' => 'Canastillas C-1 y C-3 y marcos de concreto para vialidades. Fabricación y entrega en La Paz, Todos Santos, Los Cabos y Ciudad Constitución.',
            'pagina' => 'inicio',
            'productos' => $productos,
            'catalogoDisponible' => Catalogo::disponible(),
            'totalProductos' => count($productos),
            'galeria' => Galeria::fotos(),
            'script' => ['sitio-inicio.js', 'sitio-modal.js'],
        ]);
    }

    public static function noEncontrado(Router $router): void
    {
        $router->noEncontrado();
    }
}
