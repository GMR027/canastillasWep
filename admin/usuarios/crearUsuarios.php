<?php
require_once __DIR__ . '/../../includes/app.php';
soloAdmin();
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
    <a class="button" href="index.php">Regresar</a>
    <a class="button" href="/cerrar-sesion.php">Cerrar Sesion</a>
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
  <form action="/admin/usuarios/crearUsuarios.php" class="formulario" method="POST">
    <?php include __DIR__ . '/../../templates/formularioUsuarios.php'; ?>
    <div class="flex-center">
      <button type="submit">Crear Usuario</button>
    </div>
  </form>
</section>

<?php
 template('footer');
?>