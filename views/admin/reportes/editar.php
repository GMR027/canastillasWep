<?php
use App\Reportes;
use Intervention\Image\ImageManager;

$idValido = validarID($_GET['id'] ?? null, '/admin');
$reporte = Reportes::buscarID($idValido);

$clientes = $db->query("SELECT id, nombre FROM usuarios");
$productos = $db->query("SELECT id, nombre FROM productos");
$ubicacion = $db->query("SELECT id, nombre FROM ubicacion");

$errores = Reportes::getErrores();

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  $args = $_POST;
  $reporte->sincronizarCambios($args);
  $errores = $reporte->validar();

  $nombreImagen = md5(uniqid(rand(), true)) . ".jpg";
  if($_FILES['imagen']['tmp_name']) {
    $manager = ImageManager::gd();
    $imagen = $manager->read($_FILES['imagen']['tmp_name'])->cover(800, 600);
  }

  if(empty($errores)) {
    if(isset($imagen)) {
      $reporte->sincImage($nombreImagen);
      if(!is_dir(CARPETA_IMAGEN)) {
        mkdir(CARPETA_IMAGEN, 0755, true);
      }
      $imagen->toJpeg()->save(CARPETA_IMAGEN . $nombreImagen);
    }
    $reporte->guardar();
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
    <a class="button" href="/admin">Regresar</a>
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
  <h1>Editar Reporte</h1>
  <form action="" method="POST" enctype="multipart/form-data" class="formulario">
    <?php include TEMPLATES_URL . '/formularioReportes.php'; ?>
    <div class="flex-center">
      <button type="submit">Editar Reporte</button>
    </div>
  </form>
</section>


<?php
 template('footer');
?>
