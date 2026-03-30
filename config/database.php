<?php 

function database(): mysqli {
  $db = new mysqli('localhost', 'root', '', 'canastillas');
  if(!$db) {
    echo 'Error al conectar a la base de datos';
    exit;
  }
  return $db;
}