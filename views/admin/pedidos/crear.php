<?php
use App\Pedidos;
use Intervention\Image\ImageManager;
$errores = Pedidos::getErrores();
$pedido = new Pedidos();

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  $pedido = new Pedidos($_POST);

  $nombreImagen = md5(uniqid(rand(), true)) . ".jpg";
  if($_FILES['imagen']['tmp_name']) {
    $manager = ImageManager::gd();
    $imagen = $manager->read($_FILES['imagen']['tmp_name'])->cover(800, 600);
    $pedido->sincImage($nombreImagen);
  }

  $errores = $pedido->validar();

  if(empty($errores)) {
    if(isset($imagen)) {
      if(!is_dir(CARPETA_IMAGEN)) {
        mkdir(CARPETA_IMAGEN, 0755, true);
      }
      $imagen->toJpeg()->save(CARPETA_IMAGEN . $nombreImagen);
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
    <a class="button" href="/admin/pedidos">Regresar</a>
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
  <h1>Crear Pedido</h1>
  <form action="/admin/pedidos/crear" class="formulario" method="POST" enctype="multipart/form-data">
    <?php include TEMPLATES_URL . '/formularioPedidos.php'; ?>
    <div class="flex-center">
      <button type="submit">Crear Pedido</button>
    </div>
  </form>
</section>

<?php
 template('footer');
?>
