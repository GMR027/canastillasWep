<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Canastillas de la Baja</title>
  <link rel="stylesheet" href="/build/css/app.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cabin:ital,wght@0,400..700;1,400..700&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

</head>
<body>
<div class="navbar-admin">
  <div class="titulo">
    <img src="src/img/LogoWeb.png" alt="">
  </div>
  <div class="links">
    <a href="/">Regresar</a>
  </div>
</div>

<section class="contenedor">
  <h1>Login</h1>
  <div class="login">
  <form class="formulario" action="" method="post">
    <label for="username">Usuario:</label>
    <input type="text" id="username" name="username" required>

    <label for="password">Contraseña:</label>
    <input type="password" id="password" name="password" required>

    <div class="flex-center">
      <button type="submit">Iniciar Sesión</button>
    </div>
  </form>
  </div>
<?php
 template('footer');
?>