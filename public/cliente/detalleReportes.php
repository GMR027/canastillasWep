<?php
require '../../includes/app.php';
 soloCliente();
use App\Reportes;
validarID($_GET['id']);
$reporte = Reportes::buscarID($_GET['id']);
// Verifica que el reporte pertenezca al cliente en sesion.
if(!$reporte || (int) $reporte->cliente !== (int) $_SESSION['id']) {
  header('Location: /public/cliente/index.php');
  exit;
}


template('headerHTML');

?>

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
  <p class="hMovilTablas">Lugar de entrega: <?php echo $reporte->ubicacion_nombre; ?>, <?php echo $reporte->lugar; ?></p>
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
  <p><?php echo $reporte->descripcion_producto; ?></p>
</section>
<section class="contenedor">
  <h2>Foto de entrega</h2>
  <div class="foto-entrega-detalle-container">
    <img src="/public/image/<?php echo $reporte->imagen; ?>" alt="Foto de entrega" class="foto-entrega">
  </div>
</section>

<?php if($reporte->comentarios): ?>
  <section class="contenedor">
    <h2>Comentarios</h2>
    <p><?php echo nl2br(escaparValores($reporte->comentarios)); ?></p>
  </section>
<?php endif; ?> 

<div class="botones-detalle width-50">
    <a class="button" href="<?php echo $reporte->maps ?>" target="_blank" rel="noopener noreferrer">Ver ubicación en Google Maps</a>
    <a class="button" href="/public/cliente/index.php">Regresar</a>
</div>

<?php
 template('footer');
?>