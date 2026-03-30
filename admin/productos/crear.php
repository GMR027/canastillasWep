<?php
include __DIR__ . '/../../includes/app.php';
soloAdmin();
use App\Productos;
$errores = Productos::getErrores();
$producto = new Productos();

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  $producto = new Productos($_POST);

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
  <h1>Crear Productos</h1>
  <form action="/admin/productos/crear.php" class="formulario" method="POST">
    <?php include __DIR__ . '/../../templates/formularioCrearProductos.php'; ?>
    <div class="flex-center">
      <button type="submit">Crear Producto</button>
    </div>
  </form>
</section>

<?php
 template('footer');
?>