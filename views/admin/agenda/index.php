<?php
use App\Usuarios;
$usuarios = Usuarios::mostrarTodos();
template('headerHTML');
?>
<div class="navbar-admin">
  <div class="titulo">
    <img src="/src/img/LogoWeb.png" alt="">
  </div>
  <div class="navbar links-admin">
    <a class="button" href="/admin">Regresar</a>
    <a class="button hMovil" href="/logout">Cerrar Sesion</a>
  </div>
</div>
<h1>Listado de usuarios</h1>

<table class="tablas">
  <thead>
    <tr>
      <th>Nombre</th>
      <th>Telefono</th>
    </tr>
  </thead>
  <tbody class="tbody-margin">
    <?php foreach($usuarios as $usuario): ?>
    <tr>
      <td><?php echo $usuario->nombre; ?></td>
      <td><a href="tel:<?php echo $usuario->telefono; ?>" class="button">Llamar</a></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>


<?php
 template('footer');
?>

