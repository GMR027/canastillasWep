<?php
use App\Reportes;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

$errores = Reportes::getErrores();
$reporte = new Reportes();

$clientes = $db->query("SELECT id, nombre FROM usuarios");
$productos = $db->query("SELECT id, nombre FROM productos");
$ubicacion = $db->query("SELECT id, nombre FROM ubicacion");

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  $reporte = new Reportes($_POST);

  $nombreImagen = md5(uniqid(rand(), true)) . ".jpg";
  if($_FILES['imagen']['tmp_name']) {
    $manager = new ImageManager(Driver::class);
    $imagen = $manager->read($_FILES['imagen']['tmp_name'])->cover(800, 600, 'center');
    $reporte->sincImage($nombreImagen);
  }

  $errores = $reporte->validar();

  if(empty($errores)) {
    if(isset($imagen)) {
      if(!is_dir(CARPETA_IMAGEN)) {
        mkdir(CARPETA_IMAGEN);
      }
      $imagen->save(CARPETA_IMAGEN . $nombreImagen);
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
  <h1>Crear Reporte</h1>
  <form action="/admin/reportes/crear" class="formulario" method="POST" enctype="multipart/form-data">
    <?php include TEMPLATES_URL . '/formularioReportes.php'; ?>
    <div class="flex-center">
      <button type="submit">Crear Reporte</button>
    </div>
  </form>
</section>

<?php
 template('footer');
?>
