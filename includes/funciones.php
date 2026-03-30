<?php

//rutas principales
define('TEMPLATES_URL', __DIR__ . '/../templates');
define('CARPETA_IMAGEN', __DIR__ . '/../public/image/');

//var_dump(CARPETA_IMAGEN);
//var_dump(TEMPLATES_URL);

function template(string $nombre, array $datos = []) {
    extract($datos);
    include TEMPLATES_URL . "/$nombre.php";
}

function debugear($variable) {
    echo '<pre>';
    var_dump($variable);
    echo '</pre>';
    exit;
} 

function escaparValores($html) {
    $html = htmlspecialchars($html);
    return $html;
}

function validarID($id) {
    // Si no viene el parametro 'id' en la URL, redirige al listado.
    if(is_null($id)) {
        header('Location: index.php');
        exit;
    }
    // Verifica que sea un numero entero valido.
    $id = filter_var($id, FILTER_VALIDATE_INT);

    if(!$id) {
        header('Location: index.php');
        exit;
    }
    return $id;
}

function iniciarSesion() : bool {
  session_start();

  if(!isset($_SESSION['login'])) {
    header('Location: /login..php');
    exit;
  }
  return true;
}

function soloAdmin() : void {
  iniciarSesion();
  if((int) $_SESSION['rol'] !== 1) {
    header('Location: /public/cliente/index.php');
    exit;
  }
}

function soloCliente() : void {
  iniciarSesion();
  if((int) $_SESSION['rol'] !== 2) {
    header('Location: /login..php');
    exit;
  }
}

function soloProveedor() : void {
  iniciarSesion();
  if((int) $_SESSION['rol'] !== 3) {
    header('Location: /login..php');
    exit;
  }
}
