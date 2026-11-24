<?php

namespace Controllers;

use Model\Compra;
use Model\Entrega;
use MVC\Router;

class CuentaController
{
    public static function inicio(Router $router): void
    {
        soloCliente();
        redirigir('/mi-cuenta/compras');
    }

    public static function compras(Router $router): void
    {
        $cliente = soloCliente();
        $filtro = (string) ($_GET['filtro'] ?? 'todas');
        $lista = Compra::delCliente($cliente->id, (int) ($_GET['pagina'] ?? 1), $filtro);

        $router->render('cuenta/compras', [
            'titulo' => 'Mis compras',
            'descripcion' => 'Consulta el estatus, las entregas y las actas de tus compras.',
            'pagina' => 'cuenta',
            'cliente' => $cliente,
            'lista' => $lista,
        ]);
    }

    public static function detalle(Router $router, string $id): void
    {
        $cliente = soloCliente();
        $compra = Compra::buscar((int) $id, $cliente->id);

        if (!$compra) {
            $router->noEncontrado();
            return;
        }

        $router->render('cuenta/detalle', [
            'titulo' => 'Compra ' . $compra->folio,
            'descripcion' => 'Detalle de tu compra y sus entregas.',
            'pagina' => 'cuenta',
            'cliente' => $cliente,
            'compra' => $compra,
            'detalles' => $compra->detalles(),
            'entregas' => $compra->entregas(),
            'script' => ['sitio-modal.js'],
        ]);
    }

    public static function acta(Router $router, string $id): void
    {
        $cliente = soloCliente();
        $entrega = Entrega::buscar((int) $id, $cliente->id);

        if (!$entrega) {
            $router->noEncontrado();
            return;
        }

        $compra = Compra::buscar($entrega->venta_id, $cliente->id);
        if (!$compra) {
            $router->noEncontrado();
            return;
        }

        $detalles = $compra->detalles();
        $renglones = $entrega->renglones();
        $fotos = $entrega->fotos();

        require dirname(__DIR__) . '/views/cuenta/acta.php';
    }
}
