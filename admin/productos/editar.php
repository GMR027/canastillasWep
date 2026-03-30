<?php
require_once __DIR__ . '/../../includes/app.php';
 soloAdmin();
use App\Productos;

$idValido = validarID($_GET['id']);
$producto = Productos::buscarID($idValido);

$errores = Productos::getErrores();

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  $args = $_POST;
  $producto->sincronizarCambios($args);
  $errores = $producto->validar();

  if(empty($errores)) {

      $producto->guardar();
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
  <h1>Editar Producto</h1>
  <form action="" class="formulario" method="POST">
    <?php include __DIR__ . '/../../templates/formularioCrearProductos.php'; ?>
    <div class="flex-center">
      <button type="submit">Actualizar Producto</button>
    </div>
  </form>
</section>

<?php
 template('footer');
?>