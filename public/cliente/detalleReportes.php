<?php
require '../../includes/app.php';
use App\Reportes;
validarID($_GET['id']);
$reporte = Reportes::buscarID($_GET['id']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Canastillas de la Baja</title>
  <link rel="stylesheet" href="/build/css/app.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cabin:ital,wght@0,400..700;1,400..700&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

</head>
<body>
<div class="navbar-admin">
  <div class="titulo">
    <img src="/src/img/LogoWeb.png" alt="">
  </div>
  <div class="links">
    <a href="/index.php">Inicio</a>
    <a href="/cerrar-sesion.php">Cerrar Sesion</a>
  </div>
</div>

<section class="contenedor">
  <h1>Detalle de entrega</h1>
  <P>Nombre de cliente: <?php echo $reporte->nombre_cliente; ?></P>
  <p>Contacto: <?php echo $reporte->telefono; ?></p>

  <!-- Inicio de iteracion de entregas -->
  <p class="hTabletTablas">Fecha: <?php echo $reporte->fecha; ?></p>
  <p class="hMovilTablas">Lugar de entrega: <?php echo $reporte->ubicacion_nombre; ?></p>
  <table class="tablas">
    <thead>
      <tr>
        <th class="hMovilTablas">ID</th>
        <th class="hMovilTablas">Fecha</th>
        <th class="info">Producto</th>
        <th>Cantidad</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td class="hMovilTablas"><?php echo $reporte->id; ?></td>
        <td class="hMovilTablas"><?php echo $reporte->fechaFormateada(); ?></td>
        <td class="info"><?php echo $reporte->nombre_producto; ?></td>
        <td><?php echo $reporte->cantidad; ?></td> 
    </tbody>
  </table>
</section>
<section class="contenedor">
  <h2>Foto de entrega</h2>
  <div class="foto-entrega-detalle-container">
    <img src="/public/image/<?php echo $reporte->imagen; ?>" alt="Foto de entrega" class="foto-entrega">
  </div>
</section>

<div class="botones-detalle width-50">
    <a class="button" href="<?php echo $reporte->maps ?>" target="_blank" rel="noopener noreferrer">Ver ubicación en Google Maps</a>
    <a class="button" href="/public/cliente/index.php">Regresar</a>
</div>

<?php
 template('footer');
?>