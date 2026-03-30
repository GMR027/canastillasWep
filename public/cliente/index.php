<?php
include __DIR__ . '/../../includes/app.php';
 soloCliente();
use App\Reportes;
$reportes = Reportes::mostrarPorCliente($_SESSION['id']);

// Lee el parametro st de la URL y lo convierte a entero para comparar con ===.
$mensaje = (int) ($_GET['st'] ?? 0);

template('headerHTML');
?>

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
  <P>Nombre de cliente: <?php echo escaparValores($_SESSION['nombre']); ?></P>
  <p>Contacto: <?php echo escaparValores(!empty($reportes) ? $reportes[0]->telefono : ''); ?></p> <!-- Muestra el telefono del primer reporte si existe, de lo contrario muestra una cadena vacia. -->
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