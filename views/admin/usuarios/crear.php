<?php
use App\Usuarios;
$errores = Usuarios::getErrores();
$usuario = new Usuarios();

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  $usuario = new Usuarios($_POST);

  $errores = $usuario->validar();
  if(empty($errores)) {
    $usuario->guardar();
  }
}

template('headerHTML');
?>

<body>
<div class="navbar-admin">
  <div class="titulo">
    <img src="/src/img/LogoWeb.png" alt="">
  </div>
  <div class="navbar links-admin">
    <a class="button" href="/admin/usuarios">Regresar</a>
    <a class="button" href="/logout">Cerrar Sesion</a>
  </div>
</div>

<section class="mensajesError contenedor">
  <?php foreach($errores as $error) :?>
    <div class="error">
      <?php echo $error;?>
    </div>
  <?php endforeach; ?>
</section>

<section class="contenedor">
  <h1>Crear Usuarios</h1>
  <form action="/admin/usuarios/crear" class="formulario" method="POST">
    <?php include TEMPLATES_URL . '/formularioUsuarios.php'; ?>
    <div class="flex-center">
      <button type="submit">Crear Usuario</button>
    </div>
  </form>
</section>

<?php
 template('footer');
?>
