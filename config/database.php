<?php 

function database(): mysqli {
  $host     = getenv('DB_HOST')     ?: 'localhost';
  $user     = getenv('DB_USER')     ?: 'root';
  $password = getenv('DB_PASSWORD') ?: '';
  $name     = getenv('DB_NAME')     ?: 'canastillas';

  $db = new mysqli($host, $user, $password, $name);
  if ($db->connect_error) {
    echo 'Error al conectar a la base de datos';
    exit;
  }
  return $db;
}