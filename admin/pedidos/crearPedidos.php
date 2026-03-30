<?php
require_once __DIR__ . '/../../includes/app.php';
 soloAdmin();
use App\Pedidos;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
$errores = Pedidos::getErrores();
$pedido = new Pedidos();

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  $pedido = new Pedidos($_POST);
  
  $nombreImagen = md5(uniqid(rand(), true)) . ".jpg";
  // debugear($_FILES);
    if($_FILES['imagen']['tmp_name']) {
      $manager = new ImageManager(Driver::class);
      $imagen = $manager->read($_FILES['imagen']['tmp_name'])->cover(800, 600, 'center');
      $pedido->sincImage($nombreImagen);
    }

  $errores = $pedido->validar();
  
  if(empty($errores)) {
    if(isset($imagen)) { //isset verifica que la variable $imagen fue creada, lo que solo ocurre si se subió un archivo.
      if(!is_dir(CARPETA_IMAGEN)) {
        mkdir(CARPETA_IMAGEN);
      }
      $imagen->save(CARPETA_IMAGEN . $nombreImagen);
    }
    $pedido->guardar();
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
  <h1>Crear Pedido</h1>
  <form action="/admin/pedidos/crearPedidos.php" class="formulario" method="POST" enctype="multipart/form-data">
    <?php include __DIR__ . '/../../templates/formularioPedidos.php'; ?>
    <div class="flex-center">
      <button type="submit">Crear Pedido</button>
    </div>
  </form>
</section>

<?php
 template('footer');
?>