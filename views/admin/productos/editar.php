<?php
use App\Productos;

$idValido = validarID($_GET['id'] ?? null, '/admin/productos');
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
    <a class="button" href="/admin/productos">Regresar</a>
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
  <h1>Editar Producto</h1>
  <form action="" class="formulario" method="POST">
    <?php include TEMPLATES_URL . '/formularioCrearProductos.php'; ?>
    <div class="flex-center">
      <button type="submit">Actualizar Producto</button>
    </div>
  </form>
</section>

<?php
 template('footer');
?>
