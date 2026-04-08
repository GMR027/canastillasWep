<?php
require 'includes/app.php';
require 'Router.php';

$router = new Router();

// Publica
$router->get('/',       'views/inicio.php');
$router->get('/login',  'views/login.php');
$router->post('/login', 'views/login.php');
$router->get('/logout', 'views/logout.php');

// Admin — reportes
$router->get('/admin',                    'views/admin/index.php',            'soloAdmin');
$router->post('/admin',                   'views/admin/index.php',            'soloAdmin');
$router->get('/admin/reportes/crear',     'views/admin/reportes/crear.php',   'soloAdmin');
$router->post('/admin/reportes/crear',    'views/admin/reportes/crear.php',   'soloAdmin');
$router->get('/admin/reportes/editar',    'views/admin/reportes/editar.php',  'soloAdmin');
$router->post('/admin/reportes/editar',   'views/admin/reportes/editar.php',  'soloAdmin');
$router->get('/admin/reportes/detalle',   'views/admin/reportes/detalle.php', 'soloAdmin');
$router->get('/admin/reportes/recibo',    'views/admin/reportes/recibo.php',  'soloAdmin');

// Admin — productos
$router->get('/admin/productos',          'views/admin/productos/index.php',  'soloAdmin');
$router->post('/admin/productos',         'views/admin/productos/index.php',  'soloAdmin');
$router->get('/admin/productos/crear',    'views/admin/productos/crear.php',  'soloAdmin');
$router->post('/admin/productos/crear',   'views/admin/productos/crear.php',  'soloAdmin');
$router->get('/admin/productos/editar',   'views/admin/productos/editar.php', 'soloAdmin');
$router->post('/admin/productos/editar',  'views/admin/productos/editar.php', 'soloAdmin');

// Admin — pedidos
$router->get('/admin/pedidos',            'views/admin/pedidos/index.php',    'soloAdmin');
$router->post('/admin/pedidos',           'views/admin/pedidos/index.php',    'soloAdmin');
$router->get('/admin/pedidos/crear',      'views/admin/pedidos/crear.php',    'soloAdmin');
$router->post('/admin/pedidos/crear',     'views/admin/pedidos/crear.php',    'soloAdmin');
$router->get('/admin/pedidos/detalle',    'views/admin/pedidos/detallePedido.php', 'soloAdmin');
$router->get('/admin/pedidos/editar',     'views/admin/pedidos/editar.php',   'soloAdmin');
$router->post('/admin/pedidos/editar',    'views/admin/pedidos/editar.php',   'soloAdmin');

// Admin — usuarios
$router->get('/admin/usuarios',           'views/admin/usuarios/index.php',   'soloAdmin');
$router->post('/admin/usuarios',          'views/admin/usuarios/index.php',   'soloAdmin');
$router->get('/admin/usuarios/crear',     'views/admin/usuarios/crear.php',   'soloAdmin');
$router->post('/admin/usuarios/crear',    'views/admin/usuarios/crear.php',   'soloAdmin');
$router->get('/admin/usuarios/editar',    'views/admin/usuarios/editar.php',  'soloAdmin');
$router->post('/admin/usuarios/editar',   'views/admin/usuarios/editar.php',  'soloAdmin');

// Cliente
$router->get('/cliente',          'views/cliente/index.php',   'soloCliente');
$router->get('/cliente/detalle',  'views/cliente/detalle.php', 'soloCliente');

// Proveedor
$router->get('/proveedor', 'views/proveedor/index.php', 'soloProveedor');

$vista = $router->comprobarRutas();
if ($vista) {
    include $vista;
}
