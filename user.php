<?php
require './includes/app.php';
$db = database();

$nombre = $db->escape_string("Agustin");
$telefono = $db->escape_string("66666");
$correo = $db->escape_string("langostin@correo.com");
$rol = $db->escape_string("1");
$password = $db->escape_string(password_hash('123', PASSWORD_BCRYPT));


$query = "INSERT INTO usuarios (nombre, telefono, correo, rol, contrasena) VALUES ('$nombre', '$telefono', '$correo', '$rol', '$password')";
echo $query;
$resultado = $db->query($query);
?>