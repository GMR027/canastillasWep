<?php
include __DIR__ . '/../../includes/app.php';
use App\Reportes;
$reportes = Reportes::mostrarTodos();

// Lee el parametro st de la URL y lo convierte a entero para comparar con ===.
$mensaje = (int) ($_GET['st'] ?? 0);


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
    <a href="/cerrar-sesion.php">Cerrar Sesion</a>
  </div>
</div>

<section class="contenedor">
  <h1>Listado de entregas</h1>
  <P>Nombre de cliente: Juan Pérez</P>
  <p>Contacto: juan.perez@example.com</p>
  <p class="hTabletTablas">Fecha: 10/marzo/2026</p>
  <table class="tablas">
    <thead>
      <tr>
        <th class="hMovilTablas">ID</th>
        <th class="hMovilTablas">Fecha</th>
        <th class="info">Producto</th>
        <th>Cantidad</th>
        <th>Foto Entrega</th>
        <th class="hMovilTablas">Ver Reporte</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($reportes as $reporte): ?>
      <tr>
        <td class="hMovilTablas"><?php echo $reporte->id; ?></td>
            <!-- Muestra la fecha en formato dia/mes_en_texto/anio (ejemplo: 28/marzo/2026). -->
        <td class="hMovilTablas"><?php echo $reporte->fechaFormateada(); ?></td>
        <td class="info"><?php echo $reporte->nombre_producto; ?></td>
        <td><?php echo $reporte->cantidad; ?></td>
        <td class="foto-entrega-container">
          <img src="/public/image/<?php echo $reporte->imagen; ?>" alt="Foto de entrega" class="foto-entrega">
        </td>
        <td class="hMovilTablas">
          <a class="button" href="/public/cliente/detalleReportes.php?id=<?php echo $reporte->id; ?>">Reporte</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <div class="flex-center">
    <a class="button hTabletTablas" href="detalleReportes.php">Reporte</a>
  </div>
</section>


<?php
 template('footer');
?>