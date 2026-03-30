<?php
require_once __DIR__ . '/../../includes/app.php';
 soloAdmin();
use App\Reportes;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

validarID($_GET['id']);
$reporte = Reportes::buscarID($_GET['id']);

$clientes = $db->query("SELECT id, nombre FROM usuarios");
$productos = $db->query("SELECT id, nombre FROM productos");
$ubicacion = $db->query("SELECT id, nombre FROM ubicacion");

// Se inicializa vacío. Solo se llenará si el formulario fue enviado y hay errores.
$errores = Reportes::getErrores();

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  $args = $_POST;
  $reporte->sincronizarCambios($args);
  $errores = $reporte->validar();

  // Procesar imagen solo si se subió un archivo
  $nombreImagen = md5(uniqid(rand(), true)) . ".jpg";
  if($_FILES['imagen']['tmp_name']) {
    $manager = new ImageManager(Driver::class);
    $imagen = $manager->read($_FILES['imagen']['tmp_name'])->cover(800, 600, 'center');
  }

  if(empty($errores)) {
    if(isset($imagen)) {
      // Solo sincroniza y guarda la imagen si no hay errores de validación
      $reporte->sincImage($nombreImagen);
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
    <a class="button" href="/admin/index.php">Regresar</a>
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
  <h1>Editar Reporte</h1>
  <form action="" method="POST" enctype="multipart/form-data" class="formulario">
    <?php include '../../templates/formularioReportes.php'; ?>
    <div class="flex-center">
      <button type="submit">Editar Reporte</button>
    </div>
  </form>
</section>


<?php
 template('footer');
?>