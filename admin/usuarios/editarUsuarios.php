<?php
require_once __DIR__ . '/../../includes/app.php';
require_once __DIR__ . '/../../includes/app.php';
 soloAdmin();
use App\Usuarios;

$idValido = validarID($_GET['id']);
$usuario = Usuarios::buscarID($idValido);

$errores = Usuarios::getErrores();

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  $args = $_POST;
  $usuario->sincronizarCambios($args);
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
  <h1>Editar Usuario</h1>
  <form action="" class="formulario" method="POST" enctype="multipart/form-data">
    <?php include __DIR__ . '/../../templates/formularioUsuarios.php'; ?>
    <div class="flex-center">
      <button type="submit">Actualizar Usuario</button>
    </div>
  </form>
</section>

<?php
 template('footer');
?>