<?php
require 'funciones.php';
require __DIR__ .'./../config/database.php';
require __DIR__ .'./../vendor/autoload.php';

$db = database();

//var_dump($db);

use App\Reportes;
Reportes::setDB($db);

use App\Pedidos;
Pedidos::setDB($db);

use App\Usuarios;
Usuarios::setDB($db);

use App\Productos;
Productos::setDB($db);
